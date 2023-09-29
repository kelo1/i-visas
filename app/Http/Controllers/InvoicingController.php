<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\visamgr_applications;
use App\Client;
use App\Billing;
use App\Invoicing;
use App\Settings;
use App\visamgr_branches;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use LaravelDaily\Invoices\Invoice;
use LaravelDaily\Invoices\Classes\Buyer;
use LaravelDaily\Invoices\Classes\Party;
use LaravelDaily\Invoices\Classes\InvoiceItem;
use Illuminate\Support\Facades\Storage;
use App\Messages;
use App\Notifications\PaymentNotification;
use Illuminate\Support\Str;
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

class InvoicingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Invoicing::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function generateInvoice(Request $request)
    {
        $user_id = $request->UserID;
        //$client_id =$request->client_id;
        //$billing_id = $request->billing_id;
        $Invoice_items = $request->InvoiceItems;
        $application_id = $request->APPTYPE_ID;

        $client_id =visamgr_applications::where('APPTYPE_ID',$application_id )->value('CLIENT_ID');


        $client_first_name = Client::where('id',$client_id)->value('first_name');
        $client_last_name = Client::where('id',$client_id)->value('last_name');
        $client_name = $client_first_name.' '.$client_last_name;
        $client_phone = Client::where('id',$client_id)->value('phone');
        $client_email = Client::where('id',$client_id)->value('email');
        $company_name = Settings::where('id',1)->value('COMPANY_NAME');
        $company_phone = Settings::where('id',1)->value('COMPANY_PHONE');
        $company_email = Settings::where('id',1)->value('COMPANY_EMAIL');
        $client_branch = Client::where('id',$client_id)->value('client_office');

        $client = new Party([
            'name'          => $company_name,
            'phone'         =>  $company_phone,

            'custom_fields' => [
                'email'         => $company_email,
              //  'business id' => '365#GG',
            ],

        ]);

        $customer = new Party([
            'name'          => $client_name,
            'phone'       => $client_phone,

            // 'custom_fields' => [
            //     'order number' => '> 654321 <',
            // ],
            'custom_fields' => [
                'email'          => $client_email,
                'application id'        => $application_id,
              //  'business id' => '365#GG',
            ],
        ]);

        $InvoiceDecoded = json_decode($Invoice_items, true);



        $allinv = "";
        $items = array();

        foreach($InvoiceDecoded as $key=>$invoiceitem) {
            //$vat;
            $allinv .= 'billing ID:'.$invoiceitem['billingitem_'.$key] .'---'. 'Quantity:'.$invoiceitem['quantity_'.$key] .'---'. 'Amount:'.$invoiceitem['amount_'.$key]; // $name is the Name of Room
            $item_description = Billing::where('id',$invoiceitem['billingitem_'.$key])->value('DESCRIPTION');
            $vat_applicable_value = Billing::where('id',$invoiceitem['billingitem_'.$key])->value('VAT_APPLICABLE');

            if($vat_applicable_value==1){
                $vat = visamgr_branches::where('id',$client_branch)->value('VAT_RATE');
            }
            else{
                $vat = 0;
            }
            array_push($items, (new InvoiceItem())->title($item_description)->pricePerUnit($invoiceitem['amount_'.$key])->quantity($invoiceitem['quantity_'.$key])->taxByPercent($vat));

             $vat_amt = ($invoiceitem['amount_'.$key])*($vat/100);
        }



        $invoice = Invoice::make('invoice')
        // ->series('BIG')
         // ability to include translated invoice status
         // in case it was paid
        // ->status(__('invoices::invoice.paid'))
         ->sequence($this->generateRandomSequence())
         ->series('PP')
         ->serialNumberFormat('{SERIES}{SEQUENCE}')
         ->seller($client)
         ->buyer($customer)
         ->date(now())
         ->dateFormat('d/m/Y')
         ->payUntilDays(14)
         ->currencySymbol('£')
         ->currencyCode('GBP')
         ->currencyFormat('{SYMBOL}{VALUE}')
         ->currencyThousandsSeparator('.')
         ->currencyDecimalPoint(',')
         ->filename($application_id.'_invoice')
         ->addItems($items)
         ->logo(public_path('vendor/invoices/sample-logo.png'))
         // You can additionally save generated invoice to configured disk
         ->save('s3');


         $s3client = new S3Client([
            'credentials' => [
                'key'    => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
            'region' => env('AWS_DEFAULT_REGION'),
            'version' => 'latest',
        ]);

        $bucket_name = env('AWS_BUCKET');

         $result = $s3client->getObject([
            'Bucket' => $bucket_name,
            'Key'    => $application_id.'_invoice.pdf',
        ]);


		// $fileUrl = $invoice->url();

         $fileUrl =  Storage::disk('s3')->temporaryUrl(
            $application_id.'_invoice.pdf',
            now()->addMinutes(10)
        );

        //Insert items into invoicing table
        DB::table('invoicings')->insert([
            'INVOICE_NUMBER'=>$invoice->getSerialNumber(),
            'APPLICATION_ID'=>$application_id,
            'CLIENT_ID'=>$client_id,
            'INVOICE_DETAILS'=>$Invoice_items ,
            /* 'BILLING_ID'=>$billing_id,
            'AMOUNT'=>$request->AMOUNT,
            'QUANTITY'=>$request->QUANTITY,
            'VAT'=> $vat_amt, */
           'INVOICE'=>$fileUrl,
            'USER'=>$user_id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
         ]);


          DB::table('visamgr_applications') ->where('APPTYPE_ID', $application_id)->update([
            'APPSTATUS'=>4,
            ///'PAYMENT_STATUS'=>0,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
           ]);



         $toAddress = Client::where('id', $client_id)
         ->value('email');


         $client_name = Client::where('id', $client_id)
         ->value('first_name');


         $uuid = Str::uuid()->toString();


         $message = 'Dear '.$client_name.',' .'<br/><br/>An invoice has been generated for your Application with ID: <strong>'.$application_id.'</strong>' ;


         DB::table('Messages')->insert([
             'MESSAGE'=>$message,
             'MESSAGE_SUBJECT'=>'Invoice Generation',
             'MESSAGE_TAG'=>'Payment',
             'SENDER'=>'i-visas',
             'USER_ID'=>$user_id,
             'CONVERSATION_ID'=>$uuid,
             'RECEIPIENT'=>$client_id,
             'created_at' => Carbon::now(),
             'updated_at' => Carbon::now(),
             ]);

          //Get recently inserted id
     $itemId = DB::getPdo()->lastInsertId();
     // dd($itemId);

          //Send message notification to Client and update notification table
          $message = Messages::find($itemId);
          $client = Client::findOrFail($client_id);
          $client->notify(new PaymentNotification($client, $message, $client_name, $toAddress));

          //Store Message notification in database
            DB::table('notifications')->insert([
             'DATA'=>$message,
             'MESSAGE_ID'=>$itemId,
             'CONVERSATION_ID'=>$uuid,
             'created_at' => Carbon::now(),
             'updated_at' => Carbon::now(),
         ]);


     //$link = $invoice->url();
     // Then send email to party with link

     // And return invoice itself to browser or have a different view
    // return $invoice->stream();


         $response = [
            'message'=> 'Invoice generated successfully!',
            'url'=> base64_encode($fileUrl),
            'filename'=>$application_id.'_invoice.pdf'///
            ];

            return response($response, 201);
    }


    public function generateRandomSequence()
    {
        do {
            $random_sequence = random_int(100000, 999999);
        } while (Invoicing::where("INVOICE_NUMBER", "=", $random_sequence)->first());

        return $random_sequence;
    }



    public function editInvoice(Request $request)
    {

        $user_id = $request->UserID;
        $client_id =$request->CLIENT_ID;
        $invoice_number =$request->INVOICE_NUMBER;
        $Invoice_items = $request->InvoiceItems;
        $application_id = $request->APPTYPE_ID;

        //$client_id =visamgr_applications::where('APPTYPE_ID',$application_id )->value('CLIENT_ID');


        $client_first_name = Client::where('id',$client_id)->value('first_name');
        $client_last_name = Client::where('id',$client_id)->value('last_name');
        $client_name = $client_first_name.' '.$client_last_name;
        $client_phone = Client::where('id',$client_id)->value('phone');
        $client_email = Client::where('id',$client_id)->value('email');
        $company_name = Settings::where('id',1)->value('COMPANY_NAME');
        $company_phone = Settings::where('id',1)->value('COMPANY_PHONE');
        $company_email = Settings::where('id',1)->value('COMPANY_EMAIL');
        $client_branch = Client::where('id',$client_id)->value('client_office');

        $client = new Party([
            'name'          => $company_name,
            'phone'         =>  $company_phone,

            'custom_fields' => [
                'email'         => $company_email,
              //  'business id' => '365#GG',
            ],

        ]);

        $customer = new Party([
            'name'          => $client_name,
            'phone'       => $client_phone,

            // 'custom_fields' => [
            //     'order number' => '> 654321 <',
            // ],
            'custom_fields' => [
                'email'          => $client_email,
                'application id'        => $application_id,
              //  'business id' => '365#GG',
            ],
        ]);

        $InvoiceDecoded = json_decode($Invoice_items, true);



        $allinv = "";
        $items = array();

        foreach($InvoiceDecoded as $key=>$invoiceitem) {
            //$vat;
            $allinv .= 'billing ID:'.$invoiceitem['billingitem_'.$key] .'---'. 'Quantity:'.$invoiceitem['quantity_'.$key] .'---'. 'Amount:'.$invoiceitem['amount_'.$key]; // $name is the Name of Room
            $item_description = Billing::where('id',$invoiceitem['billingitem_'.$key])->value('DESCRIPTION');
            $vat_applicable_value = Billing::where('id',$invoiceitem['billingitem_'.$key])->value('VAT_APPLICABLE');

            if($vat_applicable_value==1){
                $vat = visamgr_branches::where('id',$client_branch)->value('VAT_RATE');
            }
            else{
                $vat = 0;
            }
            array_push($items, (new InvoiceItem())->title($item_description)->pricePerUnit($invoiceitem['amount_'.$key])->quantity($invoiceitem['quantity_'.$key])->taxByPercent($vat));

            // $vat_amt = ($invoiceitem['amount_'.$key])*($vat/100);
        }



        $invoice = Invoice::make('invoice')
        // ->series('BIG')
         // ability to include translated invoice status
         // in case it was paid
        // ->status(__('invoices::invoice.paid'))
         ->sequence(intval(substr($invoice_number,2)))
         ->series('PP')
         ->serialNumberFormat('{SERIES}{SEQUENCE}')
         ->seller($client)
         ->buyer($customer)
         ->date(now())
         ->dateFormat('d/m/Y')
         ->payUntilDays(14)
         ->currencySymbol('£')
         ->currencyCode('GBP')
         ->currencyFormat('{SYMBOL}{VALUE}')
         ->currencyThousandsSeparator('.')
         ->currencyDecimalPoint(',')
         //->filename($application_id.date('m-d-Y-His A e').'_invoice')
		 ->filename($application_id.'_invoice')
         ->addItems($items)
         ->logo(public_path('vendor/invoices/sample-logo.png'))
         // You can additionally save generated invoice to configured disk
         ->save('s3');

         $s3client = new S3Client([
            'credentials' => [
                'key'    => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
            'region' => env('AWS_DEFAULT_REGION'),
            'version' => 'latest',
        ]);

        $bucket_name = env('AWS_BUCKET');

         $result = $s3client->getObject([
            'Bucket' => $bucket_name,
            'Key'    => $application_id.'_invoice.pdf',
        ]);


		// $fileUrl = $invoice->url();

         $fileUrl =  Storage::disk('s3')->temporaryUrl(
            $application_id.'_invoice.pdf',
            now()->addMinutes(10)
        );
        //$fileUrl = 'https://testbackend.i-visas.com/storage/app/public/'.$application_id.'_invoice.pdf';

        //Insert items into invoicing table
        DB::table('invoicings') ->where('APPLICATION_ID', $application_id)->update([
            'INVOICE_NUMBER'=>$invoice_number,
            'APPLICATION_ID'=>$application_id,
            'CLIENT_ID'=>$client_id,
            'INVOICE_DETAILS'=>$Invoice_items ,
            /* 'BILLING_ID'=>$billing_id,
            'AMOUNT'=>$request->AMOUNT,
            'QUANTITY'=>$request->QUANTITY,
            'VAT'=> $vat_amt, */
           'INVOICE'=>$fileUrl,
            'USER'=>$user_id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
         ]);




     //$link = $invoice->url();
     // Then send email to party with link

     // And return invoice itself to browser or have a different view
    // return $invoice->stream();





         $toAddress = Client::where('id', $client_id)
         ->value('email');


         $client_name = Client::where('id', $client_id)
         ->value('first_name');


         $uuid = Str::uuid()->toString();


         $message = 'Dear '.$client_name.',' .'<br/><br/>The invoice for your Application with ID: <strong>'.$application_id. '</strong>, has been updated' ;


         DB::table('Messages')->insert([
             'MESSAGE'=>$message,
             'MESSAGE_SUBJECT'=>'Invoice Update',
             'MESSAGE_TAG'=>'Payment',
             'SENDER'=>'i-visas',
             'USER_ID'=>$user_id,
             'CONVERSATION_ID'=>$uuid,
             'RECEIPIENT'=>$client_id,
             'created_at' => Carbon::now(),
             'updated_at' => Carbon::now(),
             ]);

          //Get recently inserted id
     $itemId = DB::getPdo()->lastInsertId();
     // dd($itemId);

          //Send message notification to Client and update notification table
          $message = Messages::find($itemId);
          $client = Client::findOrFail($client_id);
          $client->notify(new PaymentNotification($client, $message, $client_name, $toAddress));

          //Store Message notification in database
            DB::table('notifications')->insert([
             'DATA'=>$message,
             'MESSAGE_ID'=>$itemId,
             'CONVERSATION_ID'=>$uuid,
             'created_at' => Carbon::now(),
             'updated_at' => Carbon::now(),
         ]);


         $response = [
            'message'=> 'Invoice updated successfully!',
            'url'=> base64_encode($fileUrl),
            //'filename'=>$application_id.date('m-d-Y-His A e').'_invoice.pdf'///
			'filename'=>$application_id.'_invoice.pdf'
            ];

            return response($response, 201);
    }

    public function InvoiceByClientID($clientid)
    {
        return DB::table('invoicings')->where('CLIENT_ID',$clientid)->get();
    }

    public function addonInvoice (Request $request)
    {

        $user_id = $request->UserID;
        $client_id =$request->CLIENT_ID;
        $invoice_number =$request->INVOICE_NUMBER;
        $Invoice_items = $request->InvoiceItems;
        $application_id = $request->APPTYPE_ID;

        //$client_id =visamgr_applications::where('APPTYPE_ID',$application_id )->value('CLIENT_ID');


        $client_first_name = Client::where('id',$client_id)->value('first_name');
        $client_last_name = Client::where('id',$client_id)->value('last_name');
        $client_name = $client_first_name.' '.$client_last_name;
        $client_phone = Client::where('id',$client_id)->value('phone');
        $client_email = Client::where('id',$client_id)->value('email');
        $company_name = Settings::where('id',1)->value('COMPANY_NAME');
        $company_phone = Settings::where('id',1)->value('COMPANY_PHONE');
        $company_email = Settings::where('id',1)->value('COMPANY_EMAIL');
        $client_branch = Client::where('id',$client_id)->value('client_office');

        $client = new Party([
            'name'          => $company_name,
            'phone'         =>  $company_phone,

            'custom_fields' => [
                'email'         => $company_email,
              //  'business id' => '365#GG',
            ],

        ]);

        $customer = new Party([
            'name'          => $client_name,
            'phone'       => $client_phone,

            // 'custom_fields' => [
            //     'order number' => '> 654321 <',
            // ],
            'custom_fields' => [
                'email'          => $client_email,
                'application id'        => $application_id,
              //  'business id' => '365#GG',
            ],
        ]);

        $InvoiceDecoded = json_decode($Invoice_items, true);



        $allinv = "";
        $items = array();

        foreach($InvoiceDecoded as $key=>$invoiceitem) {
           // $vat;
            $allinv .= 'billing ID:'.$invoiceitem['billingitem_'.$key] .'---'. 'Quantity:'.$invoiceitem['quantity_'.$key] .'---'. 'Amount:'.$invoiceitem['amount_'.$key]; // $name is the Name of Room
            $item_description = Billing::where('id',$invoiceitem['billingitem_'.$key])->value('DESCRIPTION');
            $vat_applicable_value = Billing::where('id',$invoiceitem['billingitem_'.$key])->value('VAT_APPLICABLE');

            if($vat_applicable_value==1){
                $vat = visamgr_branches::where('id',$client_branch)->value('VAT_RATE');
            }
            else{
                $vat = 0;
            }
            array_push($items, (new InvoiceItem())->title($item_description)->pricePerUnit($invoiceitem['amount_'.$key])->quantity($invoiceitem['quantity_'.$key])->taxByPercent($vat));

            // $vat_amt = ($invoiceitem['amount_'.$key])*($vat/100);
        }



        $invoice = Invoice::make('invoice')
        // ->series('BIG')
         // ability to include translated invoice status
         // in case it was paid
        // ->status(__('invoices::invoice.paid'))
         ->sequence(intval(substr($invoice_number,2)))
         ->series('PP')
         ->serialNumberFormat('{SERIES}{SEQUENCE}')
         ->seller($client)
         ->buyer($customer)
         ->date(now())
         ->dateFormat('d/m/Y')
         ->payUntilDays(14)
         ->currencySymbol('£')
         ->currencyCode('GBP')
         ->currencyFormat('{SYMBOL}{VALUE}')
         ->currencyThousandsSeparator('.')
         ->currencyDecimalPoint(',')
         ->filename($application_id.'_invoice')
         ->addItems($items)
         ->logo(public_path('vendor/invoices/sample-logo.png'))
         // You can additionally save generated invoice to configured disk
         ->save('s3');


         $s3client = new S3Client([
            'credentials' => [
                'key'    => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
            'region' => env('AWS_DEFAULT_REGION'),
            'version' => 'latest',
        ]);

        $bucket_name = env('AWS_BUCKET');

         $result = $s3client->getObject([
            'Bucket' => $bucket_name,
            'Key'    => $application_id.'_invoice.pdf',
        ]);


		// $fileUrl = $invoice->url();

         $fileUrl =  Storage::disk('s3')->temporaryUrl(
            $application_id.'_invoice.pdf',
            now()->addMinutes(10)
        );

        //Insert items into invoicing table
        DB::table('invoicings') ->where('APPLICATION_ID', $application_id)->update([
            'INVOICE_NUMBER'=>$invoice_number,
            'APPLICATION_ID'=>$application_id,
            'CLIENT_ID'=>$client_id,
            'INVOICE_DETAILS'=>$Invoice_items ,
            /* 'BILLING_ID'=>$billing_id,
            'AMOUNT'=>$request->AMOUNT,
            'QUANTITY'=>$request->QUANTITY,
            'VAT'=> $vat_amt, */
            'PAYMENT_STATUS'=>null,
            'INVOICE'=>$fileUrl,
            'USER'=>$user_id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
         ]);

         $app_status = visamgr_applications::where('APPTYPE_ID', $application_id)->value('APPSTATUS');

         if($app_status==5){
            //update APPSTATUS AND PAYMENT_STATUS back to approved and unpaid in application table
            DB::table('visamgr_applications') ->where('APPTYPE_ID', $application_id)->update([
                'APPSTATUS'=>4,
                //'PAYMENT_STATUS'=>0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
         }



         $toAddress = Client::where('id', $client_id)
         ->value('email');


         $client_name = Client::where('id', $client_id)
         ->value('first_name');


         $uuid = Str::uuid()->toString();


         $message = 'Dear '.$client_name.',' .'<br/><br/>The invoice for your Application: <strong>'.$application_id. '</strong>, has been updated' ;


         DB::table('Messages')->insert([
             'MESSAGE'=>$message,
             'MESSAGE_SUBJECT'=>'Invoice Update',
             'MESSAGE_TAG'=>'Payment',
             'SENDER'=>'i-visas',
             'USER_ID'=>$user_id,
             'CONVERSATION_ID'=>$uuid,
             'RECEIPIENT'=>$client_id,
             'created_at' => Carbon::now(),
             'updated_at' => Carbon::now(),
             ]);

          //Get recently inserted id
     $itemId = DB::getPdo()->lastInsertId();
     // dd($itemId);

          //Send message notification to Client and update notification table
          $message = Messages::find($itemId);
          $client = Client::findOrFail($client_id);
          $client->notify(new PaymentNotification($client, $message, $client_name, $toAddress));

          //Store Message notification in database
            DB::table('notifications')->insert([
             'DATA'=>$message,
             'MESSAGE_ID'=>$itemId,
             'CONVERSATION_ID'=>$uuid,
             'created_at' => Carbon::now(),
             'updated_at' => Carbon::now(),
         ]);




     //$link = $invoice->url();
     // Then send email to party with link

     // And return invoice itself to browser or have a different view
    // return $invoice->stream();


         $response = [
            'message'=> 'Invoice updated successfully!',
            'url'=> base64_encode($fileUrl),
            'filename'=>$application_id.'_invoice.pdf'///
            ];

            return response($response, 200);

    }

    public function genInvoicePDF (Request $request)
    {

        //$user_id = $request->UserID;
        //$client_id =$request->CLIENT_ID;
        //$invoice_number =$request->INVOICE_NUMBER;
       // $Invoice_items = $request->InvoiceItems;
        $application_id = $request->APPTYPE_ID;

      // $Invoice_URL = DB::table('invoicings')->where([['INVOICE_NUMBER','=',$invoice_number],['CLIENT_ID','=', $client_id]])->first('INVOICE');

     $s3client = new S3Client([
        'credentials' => [
            'key'    => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
        ],
        'region' => env('AWS_DEFAULT_REGION'),
        'version' => 'latest',
    ]);


    $bucket_name = env('AWS_BUCKET');

    $url = $application_id.'_invoice.pdf';

    try {
        // Get the object.
        $result = $s3client->getObject([
            'Bucket' => $bucket_name,
            'Key'    => $url
        ]);

    $url_doc = Storage::disk('s3')->temporaryUrl(
        $url,
        now()->addMinutes(10)
    );

    $response =([
        'URL' => base64_encode($url_doc),
        'contentType' => $result['ContentType'],
        'filename'=> $application_id.'_invoice.pdf'
    ]);

    return response($response, 200);


    } catch (S3Exception $e) {
        echo $e->getMessage() . PHP_EOL;
    }



       /*  $response = [
            'message'=> 'Invoice generated successfully!',
            'url'=>$Invoice_URL,
            'filename'=>$application_id.'_invoice.pdf',///
            ];

            return response($response, 200);*/
    }

    public function payInvoice (Request $request)
    {

        $user_id = $request->UserID;
        $client_id =$request->CLIENT_ID;
        $invoice_number =$request->INVOICE_NUMBER;
        $Invoice_items = $request->InvoiceItems;
        $application_id = $request->APPTYPE_ID;

        //$client_id =visamgr_applications::where('APPTYPE_ID',$application_id )->value('CLIENT_ID');


        $client_first_name = Client::where('id',$client_id)->value('first_name');
        $client_last_name = Client::where('id',$client_id)->value('last_name');
        $client_name = $client_first_name.' '.$client_last_name;
        $client_phone = Client::where('id',$client_id)->value('phone');
        $client_email = Client::where('id',$client_id)->value('email');
        $company_name = Settings::where('id',1)->value('COMPANY_NAME');
        $company_phone = Settings::where('id',1)->value('COMPANY_PHONE');
        $company_email = Settings::where('id',1)->value('COMPANY_EMAIL');
        $client_branch = Client::where('id',$client_id)->value('client_office');

        $client = new Party([
            'name'          => $company_name,
            'phone'         =>  $company_phone,

            'custom_fields' => [
                'email'         => $company_email,
              //  'business id' => '365#GG',
            ],

        ]);

        $customer = new Party([
            'name'          => $client_name,
            'phone'       => $client_phone,

            // 'custom_fields' => [
            //     'order number' => '> 654321 <',
            // ],
            'custom_fields' => [
                'email'          => $client_email,
                'application id'        => $application_id,
              //  'business id' => '365#GG',
            ],
        ]);

        $InvoiceDecoded = json_decode($Invoice_items, true);



        $allinv = "";
        $items = array();
        $count = 0;
        $paid = 0;

        $total_cost = 0;
        $total_tax = 0;

        foreach($InvoiceDecoded as $key=>$invoiceitem) {
            //$vat;
            $allinv .= 'billing ID:'.$invoiceitem['billingitem_'.$key] .'---'. 'Quantity:'.$invoiceitem['quantity_'.$key] .'---'. 'Amount:'.$invoiceitem['amount_'.$key]; // $name is the Name of Room
            $item_description = Billing::where('id',$invoiceitem['billingitem_'.$key])->value('DESCRIPTION');
            $vat_applicable_value = Billing::where('id',$invoiceitem['billingitem_'.$key])->value('VAT_APPLICABLE');

            if($vat_applicable_value==1){
                $vat = visamgr_branches::where('id',$client_branch)->value('VAT_RATE');
            }
            else{
                $vat = 0;
            }

            if($invoiceitem['paid_'.$key] === "1")
            {
                $paid++;

                $total_cost += round(floatval($invoiceitem['quantity_'.$key]) * floatval($invoiceitem['amount_'.$key]) + floatval($invoiceitem['quantity_'.$key]) * floatval($invoiceitem['amount_'.$key])*($vat/100), 2);
                $total_tax +=  round(floatval($invoiceitem['quantity_'.$key]) * floatval($invoiceitem['amount_'.$key])*($vat/100),2);
            }

            array_push($items, (new InvoiceItem())->title($item_description)->pricePerUnit($invoiceitem['amount_'.$key])->quantity($invoiceitem['quantity_'.$key])->taxByPercent($vat));

            // $vat_amt = ($invoiceitem['amount_'.$key])*($vat/100);
            $count++;
        }

        if($count === $paid) {

            $invoice = Invoice::make('invoice')
            // ->series('BIG')
             // ability to include translated invoice status
             // in case it was paid
             ->status(__('invoices::invoice.paid'))
             ->sequence(intval(substr($invoice_number,2)))
             ->series('PP')
             ->serialNumberFormat('{SERIES}{SEQUENCE}')
             ->seller($client)
             ->buyer($customer)
             ->date(now())
             ->dateFormat('d/m/Y')
             ->payUntilDays(14)
             ->currencySymbol('£')
             ->currencyCode('GBP')
             ->currencyFormat('{SYMBOL}{VALUE}')
             ->currencyThousandsSeparator('.')
             ->currencyDecimalPoint(',')
             ->filename($application_id.'_invoice')
             ->addItems($items)
             ->logo(public_path('vendor/invoices/sample-logo.png'))
             // You can additionally save generated invoice to configured disk
             ->save('s3');

           //update APPSTATUS AND PAYMENT_STATUS in application table
           DB::table('visamgr_applications') ->where('APPTYPE_ID', $application_id)->update([
            'APPSTATUS'=>5,
            //'PAYMENT_STATUS'=>1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
           ]);
           //Send message notification to client
             $toAddress = Client::where('id', $client_id)
         ->value('email');


         $client_name = Client::where('id', $client_id)
         ->value('first_name');


         $uuid = Str::uuid()->toString();


         $message = 'Dear '.$client_name.',' .'<br/><br/>We have received your full payment amount of <strong>£'.$total_cost.'</strong>'.'for your Invoice with number: '.' <strong>'.$invoice_number.'</strong>' ;


         DB::table('Messages')->insert([
             'MESSAGE'=>$message,
             'MESSAGE_SUBJECT'=>'Payment Status',
             'MESSAGE_TAG'=>'Payment',
             'SENDER'=>'i-visas',
             'USER_ID'=>$user_id,
             'CONVERSATION_ID'=>$uuid,
             'RECEIPIENT'=>$client_id,
             'created_at' => Carbon::now(),
             'updated_at' => Carbon::now(),
             ]);

          //Get recently inserted id
     $itemId = DB::getPdo()->lastInsertId();
     // dd($itemId);

          //Send message notification to Client and update notification table
          $message = Messages::find($itemId);
          $client = Client::findOrFail($client_id);
          $client->notify(new PaymentNotification($client, $message, $client_name, $toAddress));

          //Store Message notification in database
            DB::table('notifications')->insert([
             'DATA'=>$message,
             'MESSAGE_ID'=>$itemId,
             'CONVERSATION_ID'=>$uuid,
             'created_at' => Carbon::now(),
             'updated_at' => Carbon::now(),
         ]);

        }
        else {
        $invoice = Invoice::make('invoice')
        // ->series('BIG')
         // ability to include translated invoice status
         // in case it was paid
        // ->status(__('invoices::invoice.paid'))
         ->sequence(intval(substr($invoice_number,2)))
         ->series('PP')
         ->serialNumberFormat('{SERIES}{SEQUENCE}')
         ->seller($client)
         ->buyer($customer)
         ->date(now())
         ->dateFormat('d/m/Y')
         ->payUntilDays(14)
         ->currencySymbol('£')
         ->currencyCode('GBP')
         ->currencyFormat('{SYMBOL}{VALUE}')
         ->currencyThousandsSeparator('.')
         ->currencyDecimalPoint(',')
         ->filename($application_id.'_invoice')
         ->addItems($items)
         ->logo(public_path('vendor/invoices/sample-logo.png'))
         // You can additionally save generated invoice to configured disk
         ->save('s3');
        }

        $s3client = new S3Client([
            'credentials' => [
                'key'    => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
            'region' => env('AWS_DEFAULT_REGION'),
            'version' => 'latest',
        ]);

        $bucket_name = env('AWS_BUCKET');

         $result = $s3client->getObject([
            'Bucket' => $bucket_name,
            'Key'    => $application_id.'_invoice.pdf',
        ]);


		// $fileUrl = $invoice->url();

         $fileUrl =  Storage::disk('s3')->temporaryUrl(
            $application_id.'_invoice.pdf',
            now()->addMinutes(10)
        );

        //Insert items into invoicing table
        DB::table('invoicings') ->where('APPLICATION_ID', $application_id)->update([
            'INVOICE_NUMBER'=>$invoice_number,
            'APPLICATION_ID'=>$application_id,
            'CLIENT_ID'=>$client_id,
            'INVOICE_DETAILS'=>$Invoice_items,
            /* 'BILLING_ID'=>$billing_id,
            'AMOUNT'=>$request->AMOUNT,
            'QUANTITY'=>$request->QUANTITY,
            'VAT'=> $vat_amt, */
            'PAYMENT_STATUS'=>($count === $paid) ? 1 : null,
            'PAYMENT_AMOUNT'=> ($count === $paid) ? $total_cost : null,
            'INVOICE'=>$fileUrl,
            'USER'=>$user_id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
         ]);






     //$link = $invoice->url();
     // Then send email to party with link

     // And return invoice itself to browser or have a different view
    // return $invoice->stream();


         $response = [
            'message'=> 'Invoice updated successfully!',
            'url'=> base64_encode($fileUrl),
            'filename'=>$application_id.'_invoice.pdf',///
            'total_cost'=> $total_cost,
            'total_tax'=> $total_tax,
            ];

            return response($response, 200);
    }


    public function InvoiceByAppID($appid)
    {
        return DB::table('invoicings')
        ->join('clients', 'invoicings.CLIENT_ID', '=', 'clients.id')
        ->join('visamgr_branches', 'clients.client_office', '=', 'visamgr_branches.id')
        ->select('invoicings.*','clients.first_name','clients.middle_name','clients.last_name','visamgr_branches.VAT_RATE')
        ->where('APPLICATION_ID',$appid)->get();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
