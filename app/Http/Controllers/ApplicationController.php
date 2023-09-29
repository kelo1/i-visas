<?php

namespace App\Http\Controllers;

use App\visamgr_applications;
use App\visamgr_characters;
use App\visamgr_children;
use App\visamgr_dependants;
use App\visamgr_employment;
use App\visamgr_locations;
use App\visamgr_maintenance;
use App\visamgr_memberships;
use App\visamgr_name_change;
use App\visamgr_names_of_people_at_address;
use App\visamgr_other_nationality;
use App\visamgr_qualifications;
use App\visamgr_tracking;
use App\PreScreening;
use App\Client;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Messages;
use App\Notes;
use App\Notifications\ApplicationNotification;//DeclineApplicationNotification
use App\Notifications\DeclineApplicationNotification; //ReturnApplicationDraftNotification
use App\Notifications\ReturnApplicationDraftNotification;
use App\PartnerMarriedBefore;
use App\PreviousMarriage;
use Illuminate\Support\Str;
use DateTime;
use Exception;
use Aws\S3\Exception\S3Exception;

use Carbon\Carbon;
use Aws\S3\S3Client;
use League\Flysystem\AwsS3v3\AwsS3V3Adapter;
use League\Flysystem\Filesystem;

class ApplicationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    public function index($id)
    {

       // $id = $request->user_id;

        if (is_numeric($id)) {

            if($id == 1){
                $application = DB::table('visamgr_applications')
                ->leftJoin('clients', 'visamgr_applications.CLIENT_ID', '=', 'clients.id')
                ->leftJoin('visamgr_branches', 'clients.client_office', '=', 'visamgr_branches.id')
                ->leftJoin('invoicings', 'visamgr_applications.APPTYPE_ID', '=', 'invoicings.APPLICATION_ID')
                 ->select('visamgr_applications.*', 'visamgr_branches.LOCATION_CODE', 'visamgr_branches.VAT_RATE', 'invoicings.INVOICE_NUMBER', 'invoicings.PAYMENT_STATUS', 'invoicings.PAYMENT_AMOUNT')
               //  ->whereIn('clients.client_office', $branches)
                 ->get();

                return $this->convert_from_latin1_to_utf8_recursively($application);

            }

            else{

            $user_branch = User::where('id',$id)->value('branch_id');
            $user_other_branches = DB::table('user_locations')->where('user_id', $id)->value('location_id');
          //  $user_other_branches = DB::table('user_locations')->where('user_id', $id)->get();
            $Decoded_user_other_branches = json_decode($user_other_branches, true);
            $branches = array($user_branch);

            if(count($Decoded_user_other_branches)>0){
                foreach($Decoded_user_other_branches as $key=>$other_branches) {
                    array_push($branches, $other_branches['value']);

                      }
            }


            }

        $application = DB::table('visamgr_applications')
        ->leftJoin('clients', 'visamgr_applications.CLIENT_ID', '=', 'clients.id')
        ->leftJoin('visamgr_branches', 'clients.client_office', '=', 'visamgr_branches.id')
        ->leftJoin('invoicings', 'visamgr_applications.APPTYPE_ID', '=', 'invoicings.APPLICATION_ID')
         ->select('visamgr_applications.*', 'visamgr_branches.LOCATION_CODE', 'visamgr_branches.VAT_RATE', 'invoicings.INVOICE_NUMBER', 'invoicings.PAYMENT_STATUS', 'invoicings.PAYMENT_AMOUNT')
         ->whereIn('clients.client_office', $branches)
         ->get();

        return $this->convert_from_latin1_to_utf8_recursively($application);
        }

        else {
            $response =([
                'Message' => 'Invalid User ID'
            ]);

            return response($response, 400);
        }
    }


    public static function convert_from_latin1_to_utf8_recursively($dat)
    {
       if (is_string($dat)) {
          return utf8_encode($dat);
       } elseif (is_array($dat)) {
          $ret = [];
          foreach ($dat as $i => $d) $ret[ $i ] = self::convert_from_latin1_to_utf8_recursively($d);

          return $ret;
       } elseif (is_object($dat)) {
          foreach ($dat as $i => $d) $dat->$i = self::convert_from_latin1_to_utf8_recursively($d);

          return $dat;
       } else {
          return $dat;
       }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

        //Generate random application id
        public function generateApplicationID()
        {
            do {
                $application_id = random_int(100000, 999999);
            } while (visamgr_applications::where("APPTYPE_ID", "=", $application_id)->first());

            return $application_id;
        }


        public function CheckApplicationCreationStatus (Request $request)
        {
            $client_id = $request->clientid;

            $prescreeningStatus = PreScreening::where([
                ['client_id','=',$client_id],
                ['prescreened_status','=','3']
            ])->get();

            $client_App_Status = visamgr_applications::where([
                ['CLIENT_ID','=', $client_id],
                ['APPSTATUS', '!=', '5']
            ])->get();

            if(sizeof($prescreeningStatus) > 0 && sizeof($client_App_Status) === 0){

                $response =([
                    'Message' => 'Application Creation allowed',
                    'status'=>  'true',
                    'clientid'=>  $client_id,
                ]);

                return response($response, 200);
            }
            else {

                $response =([
                    'Message' => 'Application Creation Not allowed Yet',
                    'status'=>  'false',
                ]);

                return response($response, 200);

            }

        }


    public function checkclientapplicationStatus(Request $request){
        $client_id = $request->id;

        $app_status=visamgr_applications::where('CLIENT_ID',$client_id)->value('APPSTATUS');

        $app_id = visamgr_applications::where('CLIENT_ID',$client_id)->value('APPTYPE_ID');

      //  $prescreening_status = PreScreening::where('CLIENT_ID',$client_id)->value('APPTYPE_ID');

        if($client_id && (is_numeric($app_status) && $app_status < 3)){
            $response =([
                'Message' => 'You already have an existing application!',
                'attributes'=>  $this->convert_from_latin1_to_utf8_recursively(visamgr_applications::where('APPTYPE_ID',$app_id)->get()),
                'check'=>false
            ]);

            return response($response, 200);

          }

    }


        //Populate Application table, once Pre-screening is aprroved and customer
    public function createApplication(Request $request){
        $client_id = $request->id;



        $app_status=visamgr_applications::where('CLIENT_ID',$client_id)->value('APPSTATUS');

        $app_id = visamgr_applications::where('CLIENT_ID',$client_id)->value('APPTYPE_ID');


        if($client_id && (is_numeric($app_status) && $app_status < 3)){
            $response =([
                'Message' => 'You already have an existing application!',
                'attributes'=>  $this->convert_from_latin1_to_utf8_recursively(visamgr_applications::where('APPTYPE_ID',$app_id)->get()),

            ]);

            return response($response, 200);

          }



      $client_fname = PreScreening::where('client_id',$client_id)->value('client_first_name');
      $client_mname = PreScreening::where('client_id',$client_id)->value('client_middle_name');
      $client_lname = PreScreening::where('client_id',$client_id)->value('client_last_name');
      $client_email = PreScreening::where('client_id',$client_id)->value('client_email');
      $client_phone = PreScreening::where('client_id',$client_id)->value('client_phone');
      $client_dob = PreScreening::where('client_id',$client_id)->value('dob');
      $client_country_of_residence = PreScreening::where('client_id',$client_id)->value('country_of_residence');
      //Check if the client has created an application before, and ask client what type of application they would like to create





      //Populate Prescreening table

     $client_office = Client::where('id',$client_id)->value('client_office');





    //   if($client_id && ($app_status==1)){
    //     $response =([
    //         'Message' => 'You already have an existing application!',
    //         'attributes'=>  visamgr_applications::where('APPTYPE_ID',$app_id)->get(),

    //     ]);

    //     return response($response, 200);


    //   }

      $application_id =$this->generateApplicationID();
      DB::table('visamgr_applications')->insert([
          'CLIENT_ID'=>$client_id,
          'FIRSTNAME' => $client_fname,
          'MIDDLENAME'=>$client_mname,
          'LASTNAME' => $client_lname,
          'EMAIL' => $client_email,
          'MOBILE'=>$client_phone,
          'COUNTRY'=>$client_country_of_residence,
          'DOB'=>$client_dob,
          'CLIENT_OFFICE'=>$client_office,
          'APPTYPE_ID'=>$application_id,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now()
      ]);

      $itemId = DB::getPdo()->lastInsertId();


      $response =([
        'Message' => 'Application Creation initiated',
        'APPLICATION_ID'=>$itemId,
        'CLIENT_ID'=>$client_id,
        'FIRSTNAME' => $client_fname,
        'MIDDLENAME'=>$client_mname,
        'LASTNAME' => $client_lname,
        'EMAIL' => $client_email,
        'MOBILE'=>$client_phone,
        'COUNTRY'=>$client_country_of_residence,
        'APPTYPE_ID'=>$application_id,
        'DOB'=>$client_dob,
    ]);


    return response($response, 201);
    //    // echo($app);
    }

    //
    public function saveApplicationDraft(Request $request){


       $id = $request->APPTYPE_ID;


    if(strtoupper($request->NAME_CHANGE_QUESTION)=='YES'){
    app('App\Http\Controllers\NameChangeController')-> nameChange($request);

      }

    if(strtoupper($request->OTHER_NATIONALITY_QUESTION)=='YES'){
        app('App\Http\Controllers\OtherNationalityController')-> store($request);

    }


    if(strtoupper($request->DO_YOU_HAVE_FAMILY_IN_HOME_COUNTRY)=='YES'){

        app('App\Http\Controllers\NameofPeopleatAddressController')-> store($request);
//FAMILY_IN_HOME_COUNTRY
    }



    if(strtoupper($request->DO_YOU_HAVE_CHILDREN) =='YES'){
       // $request->NUMBER_OF_DEPENDENT_CHILDREN;
        app('App\Http\Controllers\NumberofChildrenController')-> store($request);

    }

    if($request->WHO_YOU_HAVE_BACK_HOME){
        app('App\Http\Controllers\PeopleAtHomeController')-> store($request);


    }

    if($request->QUALIFICATION_QUESTION){
        app('App\Http\Controllers\QualificationController')-> store($request);
    }



    if(!empty($request->EMPLOYMENT_STATUS)){
        app('App\Http\Controllers\EmploymentController')-> store($request);
    }

    if(strtoupper($request->LASTFIVE) == "YES"){

        app('App\Http\Controllers\TravelFiveController')-> store($request);
       // app('App\Http\Controllers\TravelFiveController')-> anyOtherCountryTravelled($request);
    }
    else if(strtoupper($request->LASTFIVE) == "NO"){
        app('App\Http\Controllers\TravelFiveController')-> RemoveLastfive($request->APPTYPE_ID);
    }
    else {}

    if(($request->OUT_OF_THE_UK_BEFORE)=='Yes'){
        app('App\Http\Controllers\TravelController')-> store($request);
    }

    if(!empty($request->CRIMINAL_OFFENSE)){
        app('App\Http\Controllers\CharacterController')-> store($request);
    }

    if(!empty($request->MEMBERSHIP)){
        app('App\Http\Controllers\MembershipController')-> store($request);
    }

    /* if(!empty($request->BANKACCT)){
        app('App\Http\Controllers\MaintenanceController')-> store($request);
    } */
    if(strtoupper($request->BANKACCT) == 'YES'){
        app('App\Http\Controllers\MaintenanceController')-> store($request);
    }
    else if(strtoupper($request->BANKACCT) == 'NO'){
        app('App\Http\Controllers\MaintenanceController')-> destroy($request->APPTYPE_ID);
    }
    else {}


    if(!empty($request->ENTERED_UK_MEANS)){

        DB::table('visamgr_applications')
        ->where('APPTYPE_ID', $id)
        ->update([
            'ENTERED_UK_MEANS'=>$request->ENTERED_UK_MEANS,
            'REASON_FOR_ILEGAL_ENTRY'=>$request->REASON_FOR_ILEGAL_ENTRY
        ]);

    }


    if(!empty($request->EVER_STAYED_BEYOND_EXPIRY)){

        DB::table('visamgr_applications')
        ->where('APPTYPE_ID', $id)
        ->update([
            'EVER_STAYED_BEYOND_EXPIRY'=>$request->EVER_STAYED_BEYOND_EXPIRY,
            'REASON_FOR_STAYING_BEYOND_EXPIRY'=>$request->REASON_FOR_STAYING_BEYOND_EXPIRY
        ]);

    }

    if(!empty($request->BREACHED_CONDITION_FOR_LEAVE)){

        DB::table('visamgr_applications')
        ->where('APPTYPE_ID', $id)
        ->update([
            'BREACHED_CONDITION_FOR_LEAVE'=>$request->BREACHED_CONDITION_FOR_LEAVE,
             'REASON_FOR_BREACH'=>$request->REASON_FOR_BREACH,
            'BREACH_COUNTRY'=>$request->BREACH_COUNTRY
        ]);

    }

    if(!empty($request->WORK_WITHOUT_PERMIT)){

        DB::table('visamgr_applications')
        ->where('APPTYPE_ID', $id)
        ->update([
            'WORK_WITHOUT_PERMIT'=>$request->WORK_WITHOUT_PERMIT,
             'REASON_FOR_WORK_WITHOUT_PERMIT'=>$request->REASON_FOR_WORK_WITHOUT_PERMIT,
        ]);

    }


    if(!empty($request->RECEIVED_PUBLIC_FUNDS)){

        DB::table('visamgr_applications')
        ->where('APPTYPE_ID', $id)
        ->update([
            'RECEIVED_PUBLIC_FUNDS'=>$request->RECEIVED_PUBLIC_FUNDS,
             'REASON_RECEIVING_FUNDS'=>$request->REASON_RECEIVING_FUNDS,
        ]);

    }

    if(!empty($request->GIVE_FALSE_INFO)){

        DB::table('visamgr_applications')
        ->where('APPTYPE_ID', $id)
        ->update([
            'GIVE_FALSE_INFO'=>$request->GIVE_FALSE_INFO,
             'REASON_FOR_FALSE_INFO'=>$request->REASON_FOR_FALSE_INFO,
        ]);

    }


    if(!empty($request->USED_DECEPTION)){

        DB::table('visamgr_applications')
        ->where('APPTYPE_ID', $id)
        ->update([
            'USED_DECEPTION'=>$request->USED_DECEPTION,
             'REASON_FOR_DECEPTION'=>$request->REASON_FOR_DECEPTION,
        ]);

    }

    if(!empty($request->BREACHED_OTHER_LAWS)){

        DB::table('visamgr_applications')
        ->where('APPTYPE_ID', $id)
        ->update([
            'BREACHED_OTHER_LAWS'=>$request->BREACHED_OTHER_LAWS,
             'REASON_FOR_BREACHING__LAWS'=>$request->REASON_FOR_BREACHING__LAWS,
        ]);

    }

    if(!empty($request->VISA_REFUSAL_QUESTION)){

        DB::table('visamgr_applications')
        ->where('APPTYPE_ID', $id)
        ->update([
            'VISA_REFUSAL_QUESTION'=>$request->VISA_REFUSAL_QUESTION,
             'REASON_FOR_REFUSAL'=>$request->REASON_FOR_REFUSAL,
        ]);

    }

    if(!empty($request->PERMISSION_REFUSAL)){

        DB::table('visamgr_applications')
        ->where('APPTYPE_ID', $id)
        ->update([
            'PERMISSION_REFUSAL'=>$request->PERMISSION_REFUSAL,
             'REASON_FOR_PERMISSION_REFUSAL'=>$request->REASON_FOR_PERMISSION_REFUSAL,
        ]);

    }

    if(!empty($request->ASYLUM_REFUSAL)){

        DB::table('visamgr_applications')
        ->where('APPTYPE_ID', $id)
        ->update([
            'ASYLUM_REFUSAL'=>$request->ASYLUM_REFUSAL,
             'REASON_FOR_ASYLUM_REFUSAL'=>$request->REASON_FOR_ASYLUM_REFUSAL,
        ]);

    }

    if(!empty($request->EVER_DEPORTED)){

        DB::table('visamgr_applications')
        ->where('APPTYPE_ID', $id)
        ->update([
            'EVER_DEPORTED'=>$request->EVER_DEPORTED,
             'REASON_FOR_DEPORTATION'=>$request->REASON_FOR_DEPORTATION,
        ]);

    }

    if(!empty($request->EVER_BANNED)){

        DB::table('visamgr_applications')
        ->where('APPTYPE_ID', $id)
        ->update([
            'EVER_BANNED'=>$request->EVER_BANNED,
             'REASON_FOR_BAN'=>$request->REASON_FOR_BAN,
        ]);

    }



if(strtoupper($request->MARRIED_BEFORE_QUESTION)=='YES'){
    app('App\Http\Controllers\PreviousMarriageController')-> store($request);


}

if(strtoupper($request->PARTNER_MARRIED_BEFORE)=='YES'){
    app('App\Http\Controllers\PartnerMarriedBeforeController')-> store($request);


}





    //compute time_lived_at_address
    $today = date("Y-m-d");
    $end_date = $request->DATE_MOVED_IN_ADDRESS;
    $datetime1 = new DateTime($today);
    $datetime2 = new DateTime($end_date);
    $diff = $datetime1->diff($datetime2);
    $days = $diff->format('%a');


        DB::table('visamgr_applications')
            ->where('APPTYPE_ID', $id)
            ->update(['APPSTATUS' => 1,
                  'NAME_CHANGE_QUESTION'=>$request->NAME_CHANGE_QUESTION,
                  'COUNTRY_OF_BIRTH'=>$request->COUNTRY_OF_BIRTH,
                  'NATIONALITY'=>$request->NATIONALITY,
                  'OTHER_NATIONALITY_QUESTION'=>$request->OTHER_NATIONALITY_QUESTION,
                  'PLACE_OF_BIRTH'=>$request->PLACE_OF_BIRTH,
                  'GENDER'=>$request->GENDER,
                  'PASSPORT_NO'=>$request->PASSPORT_NO,
                  'PASSPORT_ISSUED'=>$request->PASSPORT_ISSUED,
                  'PASSPORT_EXPIRY'=>$request->PASSPORT_EXPIRY,
                  'ISSUING_AUTHORITY'=>$request->ISSUING_AUTHORITY,
                  'PLACE_OF_ISSUE'=>$request->PLACE_OF_ISSUE,
                  'BRP_QUESTION'=>$request->BRP_QUESTION,
                  'BRP_NUMBER'=>$request->BRP_NUMBER,
                 // 'BRP_ISSUE_DATE'=>$request->BRP_ISSUE_DATE,
                  'BRP_EXPIRY_DATE'=>$request->BRP_EXPIRY_DATE,
                  'NATIONAL_ID_QUESTION'=>$request->NATIONAL_ID_QUESTION,
                  'NATIONAL_ID_NO'=>$request->NATIONAL_ID_NO,
                  'NAME_MOTHER'=>$request->NAME_MOTHER,
                  'DOB_MOTHER'=>$request->DOB_MOTHER,
                  'NATIONALITY_MOTHER'=>$request->NATIONALITY_MOTHER,
                  'PLACE_OF_BIRTH_MOTHER'=>$request->PLACE_OF_BIRTH_MOTHER,
                  'NAME_FATHER'=>$request->NAME_FATHER,
                  'DOB_FATHER'=>$request->DOB_FATHER,
                  'NATIONALITY_FATHER'=>$request->NATIONALITY_FATHER,
                  'PLACE_OF_BIRTH_FATHER'=>$request->PLACE_OF_BIRTH_FATHER,
                  'ARE_YOU_IN_UK'=>$request->ARE_YOU_IN_UK,
                  'UK_NI_QUESTION'=>$request->UK_NI_QUESTION,
                  'UK_NI'=>$request->UK_NI,
                  'UK_DRIVER_LICENSE_QUESTION'=>$request->UK_DRIVER_LICENSE_QUESTION,
                  'UK_DRIVER_LICENSE'=>$request->UK_DRIVER_LICENSE,
                  'ADDRESS1'=>$request->ADDRESS1,
                  'ADDRESS2'=>$request->ADDRESS2,
                  'TOWN'=>$request->TOWN,
                  'COUNTY'=>$request->COUNTY,
                  'POSTCODE'=>$request->POSTCODE,
                  'COUNTRY'=>$request->COUNTRY,
                  'DATE_MOVED_IN_ADDRESS'=>$request->DATE_MOVED_IN_ADDRESS,
                  'TIME_LIVED_AT_ADDRESS'=>$days,
                  'LOCATION_NAME'=>$request->LOCATION_NAME,
                  'LOCATION_CODE'=>$request->LOCATION_CODE,
                  'FAX'=>$request->FAX,
                  'VATRATE'=>$request->VATRATE,
                  'COUNTRY_PREFIX'=>$request->COUNTRY_PREFIX,
                  'NUMBER_OF_OTHERROOMS'=>$request->NUMBER_OF_OTHERROOMS,
                  'HOME_QUESTION_ANSWER'=>$request->HOME_QUESTION_ANSWER,
                  'LANDLORD_NAME'=>$request->LANDLORD_NAME,
                 // 'LANDLORD_ADDRESS'=>$request->LANDLORD_ADDRESS,
                  'LANDLORD_EMAIL'=>$request->LANDLORD_EMAIL,
                  'LANDLORD_MOBILE'=>$request->LANDLORD_MOBILE,
                  'LANDLORD_ADDRESS1'=>$request->LANDLORD_ADDRESS1,
                  'LANDLORD_ADDRESS2'=>$request->LANDLORD_ADDRESS2,
                  'LANDLORD_LOCATION_NAME'=>$request->LANDLORD_LOCATION_NAME,
                  'LANDLORD_LOCATION_CODE'=>$request->LANDLORD_LOCATION_CODE,
                  'LANDLORD_TOWN'=>$request->LANDLORD_TOWN,
                  'LANDLORD_COUNTY'=>$request->LANDLORD_COUNTY,
                  'LANDLORD_POSTCODE'=>$request->LANDLORD_POSTCODE,
                  'LANDLORD_COUNTRY_PREFIX'=>$request->LANDLORD_COUNTRY_PREFIX,
                  'LANDLORD_COUNTRY'=>$request->LANDLORD_COUNTRY,
                  'LANDLORD_FAX'=>$request->LANDLORD_FAX,
                  'LANDLORD_VRATE'=>$request->LANDLORD_VRATE,
                  'NUMBER_OF_BEDROOMS'=>$request->NUMBER_OF_BEDROOMS,
                  'WHO_LIVES_THERE'=>$request->WHO_LIVES_THERE,
                  'PREVIOUS_ADDRESS1'=>$request->PREVIOUS_ADDRESS1,
                  'PREVIOUS_ADDRESS2'=>$request->PREVIOUS_ADDRESS2,
                  'PREVIOUS_LOCATION_NAME'=>$request->PREVIOUS_LOCATION_NAME,
                  'PREVIOUS_LOCATION_CODE'=>$request->PREVIOUS_LOCATION_CODE,
                  'PREVIOUS_COUNTRY_PREFIX'=>$request->PREVIOUS_COUNTRY_PREFIX,
                  'PREVIOUS_FAX'=>$request->PREVIOUS_FAX,
                  'PREVIOUS_VRATE'=>$request->PREVIOUS_VRATE,
                  'TOWN_PREVIOUS'=>$request->TOWN_PREVIOUS,
                  'COUNTY_PREVIOUS'=>$request->COUNTY_PREVIOUS,
                  'POSTCODE_PREVIOUS'=>$request->POSTCODE_PREVIOUS,
                  'COUNTRY_PREVIOUS'=>$request->COUNTRY_PREVIOUS,
                  'MARITAL_STATUS'=>$request->MARITAL_STATUS,
                  'DATE_OF_MARRIAGE'=>$request->DATE_OF_MARRIAGE,
                  'WHERE_YOU_GOT_MARRIED'=>$request->WHERE_YOU_GOT_MARRIED,
                  'NAME_OF_SPOUSE'=>$request->NAME_OF_SPOUSE,
                  'DOB_SPOUSE'=>$request->DOB_SPOUSE,
                  'NATIONALITY_SPOUSE'=>$request->NATIONALITY_SPOUSE,
                  'PASSPORT_SPOUSE'=>$request->PASSPORT_SPOUSE,
                  'WHERE_YOU_MET'=>$request->WHERE_YOU_MET,
                  'WHERE_RELATIONSHIP_BEGAN'=>$request->WHERE_RELATIONSHIP_BEGAN,
                  'WHEN_LAST_YOU_SAW_EACHOTHER'=>$request->WHEN_LAST_YOU_SAW_EACHOTHER,
                  'LIVE_TOGETHER_QUESTION'=>$request->LIVE_TOGETHER_QUESTION,
                  'DATE_LIVING_TOGETHER'=>$request->DATE_LIVING_TOGETHER,
                  'DO_YOU_HAVE_CHILDREN'=>$request->DO_YOU_HAVE_CHILDREN,
                  'NUMBER_OF_DEPENDENT_CHILDREN'=>$request->NUMBER_OF_DEPENDENT_CHILDREN,
                  'MARRIED_BEFORE_QUESTION'=>$request->MARRIED_BEFORE_QUESTION,
                  'PARTNER_MARRIED_BEFORE'=>$request->PARTNER_MARRIED_BEFORE,
                  'DO_YOU_HAVE_FAMILY_IN_HOME_COUNTRY'=>$request->DO_YOU_HAVE_FAMILY_IN_HOME_COUNTRY,
                  'QUALIFICATION_QUESTION'=>$request->QUALIFICATION_QUESTION,
                  'OTHER_CERTIFICATE'=>$request->OTHER_CERTIFICATE,
                  'HAVE_DEGREE_IN_ENGLISH'=>$request->HAVE_DEGREE_IN_ENGLISH,
                  'PASSED_RECOGNIZED_TEST'=>$request->PASSED_RECOGNIZED_TEST,
                  'WHAT_TEST_DID_YOU_PASS'=>$request->WHAT_TEST_DID_YOU_PASS,
                  'WHEN_DID_YOU_ENTER_UK'=>$request->WHEN_DID_YOU_ENTER_UK,
                  'DID_YOU_ENTER_LEGALLY'=>$request->DID_YOU_ENTER_LEGALLY,
                  'VISA_REASON'=>$request->VISA_REASON,
                  'VISA_START_DATE'=>$request->VISA_START_DATE,
                  'VISA_END_DATE'=>$request->VISA_END_DATE,
                  'VISA_STATUS'=>$request->VISA_STATUS,
                  'OUT_OF_THE_UK_BEFORE'=>$request->OUT_OF_THE_UK_BEFORE,
                  'ANY_OTHER_COUNTRY_VISITED'=>$request->ANY_OTHER_COUNTRY_VISITED,
                  'CRIMINAL_OFFENSE'=>$request->CRIMINAL_OFFENSE,

                  'updated_at' => Carbon::now()
            ]);

            $response =([
                'Message' => 'Application saved as draft'
            ]);

            return response($response, 201);



    }

        //,$application_id, $file
public function applicationUploads(Request $request){


    $application_id = $request->APPTYPE_ID;

    $currentTime = Carbon::now();

     if($request->PASSPORT_UPLOAD){
       // $file = $request->file('PASSPORT_UPLOAD');
        $file = $request->file;
        $filePath = $application_id.'/'.'passport_upload_'.$currentTime->toDateTimeString().'_'.$file->getClientOriginalName();
        Storage::disk('s3')->put($filePath, file_get_contents($file));

        /*DB::table('visamgr_applications')
            ->where('APPTYPE_ID', $application_id)
            ->update([
                'PASSPORT_UPLOAD' =>$filePath
            ]);*/

    }


    if($request->DEPENDENT_PASSPORT_UPLOAD){

        //$file = $request->file('DEPENDENT_PASSPORT_UPLOAD');
        $file = $request->file;
        $filePath = $application_id.'/'.'dependant_passport_upload_'.$currentTime->toDateTimeString().'_'.$file->getClientOriginalName();        Storage::disk('s3')->put($filePath, file_get_contents($file));

       /* DB::table('visamgr_applications')
            ->where('APPTYPE_ID', $application_id)
            ->update([
                //'DEPENDENT_PASSPORT_UPLOAD' =>$filePath
                'DEPENDENT_PASSPORT_UPLOAD' =>$filePath
            ]);*/

     }

    if($request->UTILITY_BILL_UPLOAD){

        //$file = $request->file('UTILITY_BILL_UPLOAD');
        $file = $request->file;
        $filePath = $application_id.'/'.'utility_bill_'.$currentTime->toDateTimeString().'_'.$file->getClientOriginalName();
        Storage::disk('s3')->put($filePath, file_get_contents($file));

       /* DB::table('visamgr_applications')
            ->where('APPTYPE_ID', $application_id)
            ->update([
               // 'UTILITY_BILL_UPLOAD' =>$filePath
               'UTILITY_BILL_UPLOAD' =>$filePath
            ]);*/


    }

    if($request->BRP_UPLOAD){

        //$file = $request->file('BRP_UPLOAD');
        $file = $request->file;
        $filePath = $application_id.'/'.'brp_'.$currentTime->toDateTimeString().'_'.$file->getClientOriginalName();
        Storage::disk('s3')->put($filePath, file_get_contents($file));

        // DB::table('visamgr_applications')
        //     ->where('APPTYPE_ID', $application_id)
        //     ->update([
        //         'BRP_UPLOAD' =>$filePath
        //     ]);



    }

    if($request->PREVIOUS_VISA_UPLOAD){

        $file = $request->file;
       // $file = $request->file('PREVIOUS_VISA_UPLOAD');
       $filePath = $application_id.'/'.'previous_visa_'.$currentTime->toDateTimeString().'_'.$file->getClientOriginalName();
        Storage::disk('s3')->put($filePath, file_get_contents($file));

        // DB::table('visamgr_applications')
        //     ->where('APPTYPE_ID', $application_id)
        //     ->update([
        //         'PREVIOUS_VISA_UPLOAD' =>$filePath
        //     ]);


    }

    if($request->REFUSAL_LETTER_UPLOAD){

        $file = $request->file;
        //$file = $request->file('REFUSAL_LETTER_UPLOAD');
        $filePath = $application_id.'/'.'refusal_letter_'.$currentTime->toDateTimeString().'_'.$file->getClientOriginalName();        Storage::disk('s3')->put($filePath, file_get_contents($file));

        // DB::table('visamgr_applications')
        //     ->where('APPTYPE_ID', $application_id)
        //     ->update([
        //         'REFUSAL_LETTER_UPLOAD' =>$filePath
        //     ]);

    }

    if($request->EDUCATIONAL_CERT_UPLOAD){

        $file = $request->file;
        //$file = $request->file('EDUCATIONAL_CERT_UPLOAD');
        $filePath = $application_id.'/'.'educational_cert_'.$currentTime->toDateTimeString().'_'.$file->getClientOriginalName();
        Storage::disk('s3')->put($filePath, file_get_contents($file));

        // DB::table('visamgr_applications')
        //     ->where('APPTYPE_ID', $application_id)
        //     ->update([
        //         'EDUCATIONAL_CERT_UPLOAD' =>$filePath
        //     ]);

    }

    if($request->ENGLISH_CERT_UPLOAD){

        $file = $request->file;
        //$file = $request->file('ENGLISH_CERT_UPLOAD');
        $filePath = $application_id.'/'.'english_cert_'.$currentTime->toDateTimeString().'_'.$file->getClientOriginalName();
        Storage::disk('s3')->put($filePath, file_get_contents($file));

        // DB::table('visamgr_applications')
        //     ->where('APPTYPE_ID', $application_id)
        //     ->update([
        //         'ENGLISH_CERT_UPLOAD' =>$filePath
        //     ]);


    }

    if($request->MARRIAGE_CERT_UPLOAD){

        $file = $request->file;
        //$file = $request->file('MARRIAGE_CERT_UPLOAD');
        $filePath = $application_id.'/'.'marriage_cert_'.$currentTime->toDateTimeString().'_'.$file->getClientOriginalName();
        Storage::disk('s3')->put($filePath, file_get_contents($file));

        // DB::table('visamgr_applications')
        //     ->where('APPTYPE_ID', $application_id)
        //     ->update([
        //         'MARRIAGE_CERT_UPLOAD' =>$filePath
        //     ]);


    }

    if($request->BANK_STATEMENT_UPLOAD){

        $file = $request->file;
        //$file = $request->file('BANK_STATEMENT_UPLOAD');
        $filePath = $application_id.'/'.'bank_statement_'.$currentTime->toDateTimeString().'_'.$file->getClientOriginalName();
        Storage::disk('s3')->put($filePath, file_get_contents($file));

        // DB::table('visamgr_applications')
        //     ->where('APPTYPE_ID', $application_id)
        //     ->update([
        //         'BANK_STATEMENT_UPLOAD' =>$filePath
        //     ]);



     }

	 //Other Uploads
     if($request->MOTIVATIONAL_LETTER_UPLOAD){

        $file = $request->file;

        $filePath = $application_id.'/'.'motivational_letter_'.$currentTime->toDateTimeString().'_'.$file->getClientOriginalName();
        Storage::disk('s3')->put($filePath, file_get_contents($file));

        // DB::table('visamgr_applications')
        //     ->where('APPTYPE_ID', $application_id)
        //     ->update([
        //         'MOTIVATIONAL_LETTER_UPLOAD' =>$filePath
        //     ]);




     }

     if($request->RESUME_UPLOAD){

        $file = $request->file;

        $filePath = $application_id.'/'.'resume_'.$currentTime->toDateTimeString().'_'.$file->getClientOriginalName();
        Storage::disk('s3')->put($filePath, file_get_contents($file));

        // DB::table('visamgr_applications')
        //     ->where('APPTYPE_ID', $application_id)
        //     ->update([
        //         'RESUME_UPLOAD' =>$filePath
        //     ]);



     }

     if($request->ACADEMIC_TRANSCRIPTS_UPLOAD){

        $file = $request->file;

        $filePath = $application_id.'/'.'academic_transcript_'.$currentTime->toDateTimeString().'_'.$file->getClientOriginalName();
        Storage::disk('s3')->put($filePath, file_get_contents($file));

        // DB::table('visamgr_applications')
        //     ->where('APPTYPE_ID', $application_id)
        //     ->update([
        //         'ACADEMIC_TRANSCRIPTS_UPLOAD' =>$filePath
        //     ]);



     }

     if($request->CAS_LETTERS_UPLOAD){

        $file = $request->file;

        $filePath = $application_id.'/'.'cas_letter_'.$currentTime->toDateTimeString().'_'.$file->getClientOriginalName();
        Storage::disk('s3')->put($filePath, file_get_contents($file));

        // DB::table('visamgr_applications')
        //     ->where('APPTYPE_ID', $application_id)
        //     ->update([
        //         'CAS_LETTERS_UPLOAD' =>$filePath
        //     ]);



     }

     if($request->RECOMMENDATION_LETTERS_UPLOAD){

        $file = $request->file;

        $filePath = $application_id.'/'.'recommendation_letter_'.$currentTime->toDateTimeString().'_'.$file->getClientOriginalName();
        Storage::disk('s3')->put($filePath, file_get_contents($file));

        // DB::table('visamgr_applications')
        //     ->where('APPTYPE_ID', $application_id)
        //     ->update([
        //         'RECOMMENDATION_LETTERS_UPLOAD' =>$filePath
        //     ]);



     }

     if($request->RESEARCH_PROPOSAL_UPLOAD){

        $file = $request->file;

        $filePath = $application_id.'/'.'research_proposal_upload_'.$currentTime->toDateTimeString().'_'.$file->getClientOriginalName();
        Storage::disk('s3')->put($filePath, file_get_contents($file));

        // DB::table('visamgr_applications')
        //     ->where('APPTYPE_ID', $application_id)
        //     ->update([
        //         'RESEARCH_PROPOSAL_UPLOAD' =>$filePath
        //     ]);



     }

     if($request->OTHER_UPLOAD){

        $file = $request->file;

        $filePath = $application_id.'/'.'other_upload_'.$currentTime->toDateTimeString().'_'.$file->getClientOriginalName();
        Storage::disk('s3')->put($filePath, file_get_contents($file));

        // DB::table('visamgr_applications')
        //     ->where('APPTYPE_ID', $application_id)
        //     ->update([
        //         'OTHER_UPLOAD' =>$filePath
        //     ]);



     }

     $response =([
        'Message' => 'Document Uploaded successfully'
    ]);

    return response($response, 201);

}

public function retrieveUploads($APPTYPE_ID){

    $application_id = $APPTYPE_ID;

      $s3client = new S3Client([
        'credentials' => [
            'key'    => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
        ],
        'region' => env('AWS_DEFAULT_REGION'),
        'version' => 'latest',
    ]);


 $bucket_name = env('AWS_BUCKET');


    $objects = $s3client->getIterator('ListObjects',array(
        'Bucket' => $bucket_name,
        'Prefix' => $application_id,
    ));


    $files = array();
    foreach ($objects as $object) {

        array_push($files, $object['Key']);

    }


    $response =([
        'URL' => $files,

      ]);
   return $response;

}



public function download(Request $request){

    $url = $request->url;
    $action = $request->action;

    $s3client = new S3Client([
        'credentials' => [
            'key'    => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
        ],
        'region' => env('AWS_DEFAULT_REGION'),
        'version' => 'latest',
    ]);


 $bucket_name = env('AWS_BUCKET');


 try {
    // Get the object.
    $result = $s3client->getObject([
        'Bucket' => $bucket_name,
        'Key'    => $url,
    ]);

   /* header("Content-Type: {$result['ContentType']}");
    echo $result['Body'];*/

    $url_doc = "";
    $response =([]);

    if($action == 'preview') {

        $url_doc = Storage::disk('s3')->temporaryUrl(
            $url,
            now()->addMinutes(10)
        );

        $response =([
            'URL' => base64_encode($url_doc),
            'contentType' => $result['ContentType'],
            'action' => $action
        ]);
    }
    else {

        $url_doc = Storage::disk('s3')->temporaryUrl(
            $url,
            now()->addMinutes(10),
            ['ResponseContentDisposition' => 'attachment']
        );

        $response =([
            'URL' => base64_encode($url_doc),
            'contentType' => $result['ContentType'],
            'action' => $action
        ]);

    }

    return response($response, 200);

   /*  $response =([
        'URL' => base64_encode($url_doc),
        'contentType' => $result['ContentType'],
        'action' => $action
    ]); */

} catch (S3Exception $e) {
    //echo $e->getMessage() . PHP_EOL;
    $response =([
        'message' => $e->getMessage() . PHP_EOL,
    ]);
    return response($response, 400);
}

}

/* public function download(Request $request){

    $url = $request->url;

    $s3client = new S3Client([
        'credentials' => [
            'key'    => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
        ],
        'region' => env('AWS_DEFAULT_REGION'),
        'version' => 'latest',
    ]);


 $bucket_name = env('AWS_BUCKET');


 try {
    // Get the object.
    $result = $s3client->getObject([
        'Bucket' => $bucket_name,
        'Key'    => $url,
    ]);

    $mimetype = $result['ContentType'] . "\n";

    //$data = base64_encode($result['Body']);

    //$blob = 'data:'.$mimetype.';base64,'.$data;

    $fname = time()."_".$result['Body'];

    if(!Storage::disk('public')->put(''.substr($request->url, strpos($request->url, "/") + 1).'', $result['Body'], 'public')){

        $response =([
            'message' => $e->getMessage() . PHP_EOL,
        ]);
        return response($response, 400);
    }

    $urll = Storage::url(''.substr($request->url, strpos($request->url, "/") + 1).'');

    $response =([
        'URL' => $urll,//$blob,
        'type' => $request['Body']->getType(),
        'req_url'=> substr($request->url, strpos($request->url, "/") + 1) ,
        'contentType' => $result['ContentType']
    ]);

    return response($response, 200);

} catch (S3Exception $e) {
    //echo $e->getMessage() . PHP_EOL;
    $response =([
        'message' => $e->getMessage() . PHP_EOL,
    ]);
    return response($response, 400);
}

} */



/*public function deleteUpload($url){
   // $url = $request->url;


    $s3client = new S3Client([
        'credentials' => [
            'key'    => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
        ],
        'region' => env('AWS_DEFAULT_REGION'),
        'version' => 'latest',
    ]);

    $bucket_name = env('AWS_BUCKET');


    try
    {


        $result = $s3client->deleteObject([
            'Bucket' => $bucket_name,
            'Key'    => $url
        ]);

        if ($result['DeleteMarker'])
        {
            $response =([
                'upload' => 'file was deleted'
              ]);

              return response($response, 200);


        } else {

            $response =([
                'upload' => 'file was not deleted'
              ]);

              return response($response, 400);
        }
    }
    catch (S3Exception $e) {
        exit('Error: ' . $e->getAwsErrorMessage() . PHP_EOL);
    }



}*/

public function deleteUpload(Request $request){

    $url = $request->url;

   /*  $response =([
        'upload' => $url
      ]);

      return response($response, 200); */

    $s3client = new S3Client([
        'credentials' => [
            'key'    => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
        ],
        'region' => env('AWS_DEFAULT_REGION'),
        'version' => 'latest',
    ]);

    $bucket_name = env('AWS_BUCKET');

    try
    {


        $result = $s3client->deleteObject([
            'Bucket' => $bucket_name,
            'Key'    => $url
        ]);


        if ($result['DeleteMarker'])
        {

		$response =([
                'upload' => 'file was not deleted'
              ]);

              return response($response, 400);

        } else {
			$response =([
                'upload' => 'file was deleted'
              ]);

              return response($response, 200);

        }
    }
    catch (S3Exception $e) {
        exit('Error: ' . $e->getAwsErrorMessage() . PHP_EOL);
    }

}


public function submitApplication(Request $request){

    $id = $request->APPTYPE_ID;


        $application_status  =  visamgr_applications::where('APPTYPE_ID', $id)
        ->value('APPSTATUS');

        //Check if application_status is in draft, then insert into prescreening table and update prescreening_status =2
        if($application_status==0){




if(strtoupper($request->NAME_CHANGE_QUESTION)=='YES'){
app('App\Http\Controllers\NameChangeController')-> nameChange($request);

  }

if(strtoupper($request->OTHER_NATIONALITY_QUESTION)=='YES'){
    app('App\Http\Controllers\OtherNationalityController')-> store($request);

}
if(strtoupper($request->BRP_QUESTION) =='YES'){
    $request->validate([
        'BRP_NUMBER'=>'string|required',
       // 'BRP_ISSUE_DATE'=>'date|required',
        'BRP_EXPIRY_DATE'=>'date|required'
    ]);
}

if(strtoupper($request->UK_NI_QUESTION) =='YES'){
    $request->validate([
    'UK_NI'=>'string|required'
    ]);
}

if(strtoupper($request->UK_DRIVER_LICENSE_QUESTION) =='YES'){
    $request->validate([
       'UK_DRIVER_LICENSE'=>'string|required'
    ]);
}

if(strtoupper($request->HOME_QUESTION_ANSWER) =='RENTED'){
    $request->validate([
        'LANDLORD_NAME'=>'string|required',
       // 'LANDLORD_ADDRESS'=>'string|required',
       // 'LANDLORD_EMAIL'=>'string',
        'LANDLORD_MOBILE'=>'string|required',
        'LANDLORD_ADDRESS1'=>'string|required',
       // 'LANDLORD_ADDRESS2'=>'string|required',
        // 'LANDLORD_LOCATION_NAME'=>'string|required',
        // 'LANDLORD_LOCATION_CODE'=>'string|required',
        'LANDLORD_TOWN'=>'string|required',
       // 'LANDLORD_COUNTY'=>'string|required',
       // 'LANDLORD_POSTCODE'=>'string|required',
        // 'LANDLORD_COUNTRY_PREFIX'=>'string|required',
        'LANDLORD_COUNTRY'=>'string|required',
        // 'LANDLORD_FAX'=>'string|required',
        // 'LANDLORD_VRATE'=>'string|required',

    ]);
}

if($request->PREVIOUS_ADDRESS){
    $request->validate([
    'TOWN_PREVIOUS'=>'string|required',
    'COUNTY_PREVIOUS'=>'string|required',
    'POSTCODE_PREVIOUS'=>'string|required',
    'COUNTRY_PREVIOUS'=>'string|required',
    //'PREVIOUS_ADDRESS2'=>'string|required'

    ]);
}

if(strtoupper($request->DO_YOU_HAVE_CHILDREN) =='YES'){
    //$request->NUMBER_OF_DEPENDENT_CHILDREN;
    app('App\Http\Controllers\NumberofChildrenController')-> store($request);

}


if(strtoupper($request->MARRIED_BEFORE_QUESTION)=='YES'){
    app('App\Http\Controllers\PreviousMarriageController')-> store($request);


}

if(strtoupper($request->PARTNER_MARRIED_BEFORE)=='YES'){
    app('App\Http\Controllers\PartnerMarriedBeforeController')-> store($request);


}

if(strtoupper($request->DO_YOU_HAVE_FAMILY_IN_HOME_COUNTRY)=='YES'){
    app('App\Http\Controllers\PeopleAtHomeontroller')-> store($request);


}

if($request->WHO_YOU_HAVE_BACK_HOME){
    app('App\Http\Controllers\PeopleAtHomeController')-> store($request);


}

if($request->QUALIFICATION_QUESTION){
    app('App\Http\Controllers\QualificationController')-> store($request);
}

if(strtoupper($request->PASSED_RECOGNIZED_TEST)=='YES'){
    $request->validate([
        'WHAT_TEST_DID_YOU_PASS'=>'string|required',
    ]);
}

if(!empty($request->EMPLOYMENT_STATUS)){
    app('App\Http\Controllers\EmploymentController')-> store($request);
}

/* if(!empty(strtoupper($request->LAST_FIVE_VISITS))){

    app('App\Http\Controllers\TravelFiveController')-> store($request);
   // app('App\Http\Controllers\TravelFiveController')-> anyOtherCountryTravelled($request);
} */

if(strtoupper($request->LASTFIVE) == "YES"){

    app('App\Http\Controllers\TravelFiveController')-> store($request);
   // app('App\Http\Controllers\TravelFiveController')-> anyOtherCountryTravelled($request);
}
else if(strtoupper($request->LASTFIVE) == "NO"){
    app('App\Http\Controllers\TravelFiveController')-> RemoveLastfive($request->APPTYPE_ID);
}
else {}



if(($request->OUT_OF_THE_UK_BEFORE)=='Yes'){
    app('App\Http\Controllers\TravelController')-> store($request);
}
if(!empty($request->CRIMINAL_OFFENSE)){
    app('App\Http\Controllers\CharacterController')-> store($request);
}

if(!empty($request->MEMBERSHIP)){
    app('App\Http\Controllers\MembershipController')-> store($request);
}

if(strtoupper($request->BANKACCT) == 'YES'){
    app('App\Http\Controllers\MaintenanceController')-> store($request);
}
else if(strtoupper($request->BANKACCT) == 'NO'){
    app('App\Http\Controllers\MaintenanceController')-> destroy($request->APPTYPE_ID);
}
else {}

if(!empty($request->ENTERED_UK_MEANS)){

    DB::table('visamgr_applications')
    ->where('APPTYPE_ID', $id)
    ->update([
        'ENTERED_UK_MEANS'=>$request->ENTERED_UK_MEANS,
        'REASON_FOR_ILEGAL_ENTRY'=>$request->REASON_FOR_ILEGAL_ENTRY
    ]);

}

if(!empty($request->EVER_STAYED_BEYOND_EXPIRY)){

    DB::table('visamgr_applications')
    ->where('APPTYPE_ID', $id)
    ->update([
        'EVER_STAYED_BEYOND_EXPIRY'=>$request->EVER_STAYED_BEYOND_EXPIRY,
        'REASON_FOR_STAYING_BEYOND_EXPIRY'=>$request->REASON_FOR_STAYING_BEYOND_EXPIRY
    ]);

}

if(!empty($request->BREACHED_CONDITION_FOR_LEAVE)){

    DB::table('visamgr_applications')
    ->where('APPTYPE_ID', $id)
    ->update([
        'BREACHED_CONDITION_FOR_LEAVE'=>$request->BREACHED_CONDITION_FOR_LEAVE,
		 'REASON_FOR_BREACH'=>$request->REASON_FOR_BREACH,
        'BREACH_COUNTRY'=>$request->BREACH_COUNTRY
    ]);

}

if(!empty($request->WORK_WITHOUT_PERMIT)){

    DB::table('visamgr_applications')
    ->where('APPTYPE_ID', $id)
    ->update([
        'WORK_WITHOUT_PERMIT'=>$request->WORK_WITHOUT_PERMIT,
		 'REASON_FOR_WORK_WITHOUT_PERMIT'=>$request->REASON_FOR_WORK_WITHOUT_PERMIT,
    ]);

}

if(!empty($request->RECEIVED_PUBLIC_FUNDS)){

    DB::table('visamgr_applications')
    ->where('APPTYPE_ID', $id)
    ->update([
        'RECEIVED_PUBLIC_FUNDS'=>$request->RECEIVED_PUBLIC_FUNDS,
		 'REASON_RECEIVING_FUNDS'=>$request->REASON_RECEIVING_FUNDS,
    ]);

}

if(!empty($request->GIVE_FALSE_INFO)){

    DB::table('visamgr_applications')
    ->where('APPTYPE_ID', $id)
    ->update([
        'GIVE_FALSE_INFO'=>$request->GIVE_FALSE_INFO,
		 'REASON_FOR_FALSE_INFO'=>$request->REASON_FOR_FALSE_INFO,
    ]);

}

if(!empty($request->USED_DECEPTION)){

    DB::table('visamgr_applications')
    ->where('APPTYPE_ID', $id)
    ->update([
        'USED_DECEPTION'=>$request->USED_DECEPTION,
         'REASON_FOR_DECEPTION'=>$request->REASON_FOR_DECEPTION,
    ]);

}

if(!empty($request->BREACHED_OTHER_LAWS)){

    DB::table('visamgr_applications')
    ->where('APPTYPE_ID', $id)
    ->update([
        'BREACHED_OTHER_LAWS'=>$request->BREACHED_OTHER_LAWS,
		 'REASON_FOR_BREACHING__LAWS'=>$request->REASON_FOR_BREACHING__LAWS,
    ]);

}

if(!empty($request->VISA_REFUSAL_QUESTION)){

    DB::table('visamgr_applications')
    ->where('APPTYPE_ID', $id)
    ->update([
        'VISA_REFUSAL_QUESTION'=>$request->VISA_REFUSAL_QUESTION,
		 'REASON_FOR_REFUSAL'=>$request->REASON_FOR_REFUSAL,
    ]);

}

if(!empty($request->PERMISSION_REFUSAL)){

    DB::table('visamgr_applications')
    ->where('APPTYPE_ID', $id)
    ->update([
        'PERMISSION_REFUSAL'=>$request->PERMISSION_REFUSAL,
		 'REASON_FOR_PERMISSION_REFUSAL'=>$request->REASON_FOR_PERMISSION_REFUSAL,
    ]);

}

if(!empty($request->ASYLUM_REFUSAL)){

    DB::table('visamgr_applications')
    ->where('APPTYPE_ID', $id)
    ->update([
        'ASYLUM_REFUSAL'=>$request->ASYLUM_REFUSAL,
		 'REASON_FOR_ASYLUM_REFUSAL'=>$request->REASON_FOR_ASYLUM_REFUSAL,
    ]);

}

if(!empty($request->EVER_DEPORTED)){

    DB::table('visamgr_applications')
    ->where('APPTYPE_ID', $id)
    ->update([
        'EVER_DEPORTED'=>$request->EVER_DEPORTED,
		 'REASON_FOR_DEPORTATION'=>$request->REASON_FOR_DEPORTATION,
    ]);

}

if(!empty($request->EVER_BANNED)){

    DB::table('visamgr_applications')
    ->where('APPTYPE_ID', $id)
    ->update([
        'EVER_BANNED'=>$request->EVER_BANNED,
		 'REASON_FOR_BAN'=>$request->REASON_FOR_BAN,
    ]);

}

    //compute time_lived_at_address
    $today = date("Y-m-d");
    $end_date = $request->DATE_MOVED_IN_ADDRESS;
    $datetime1 = new DateTime($today);
    $datetime2 = new DateTime($end_date);
    $diff = $datetime1->diff($datetime2);
    $days = $diff->format('%a');

    DB::table('visamgr_applications')
        ->where('APPTYPE_ID', $id)
        ->update(['APPSTATUS' => 2,
              'NAME_CHANGE_QUESTION'=>$request->NAME_CHANGE_QUESTION,
              'COUNTRY_OF_BIRTH'=>$request->COUNTRY_OF_BIRTH,
              'NATIONALITY'=>$request->NATIONALITY,
              'OTHER_NATIONALITY_QUESTION'=>$request->OTHER_NATIONALITY_QUESTION,
              'PLACE_OF_BIRTH'=>$request->PLACE_OF_BIRTH,
              'PASSPORT_NO'=>$request->PASSPORT_NO,
              'PASSPORT_ISSUED'=>$request->PASSPORT_ISSUED,
              'PASSPORT_EXPIRY'=>$request->PASSPORT_EXPIRY,
              'ISSUING_AUTHORITY'=>$request->ISSUING_AUTHORITY,
              'PLACE_OF_ISSUE'=>$request->PLACE_OF_ISSUE,
              'BRP_QUESTION'=>$request->BRP_QUESTION,
              'BRP_NUMBER'=>$request->BRP_NUMBER,
              //'BRP_ISSUE_DATE'=>$request->BRP_ISSUE_DATE,
              'BRP_EXPIRY_DATE'=>$request->BRP_EXPIRY_DATE,
              'NATIONAL_ID_QUESTION'=>$request->NATIONAL_ID_QUESTION,
              'NATIONAL_ID_NO'=>$request->NATIONAL_ID_NO,
              'NAME_MOTHER'=>$request->NAME_MOTHER,
              'DOB_MOTHER'=>$request->DOB_MOTHER,
              'NATIONALITY_MOTHER'=>$request->NATIONALITY_MOTHER,
              'PLACE_OF_BIRTH_MOTHER'=>$request->PLACE_OF_BIRTH_MOTHER,
              'NAME_FATHER'=>$request->NAME_FATHER,
              'DOB_FATHER'=>$request->DOB_FATHER,
              'NATIONALITY_FATHER'=>$request->NATIONALITY_FATHER,
              'PLACE_OF_BIRTH_FATHER'=>$request->PLACE_OF_BIRTH_FATHER,
              'UK_NI_QUESTION'=>$request->UK_NI_QUESTION,
              'UK_NI'=>$request->UK_NI,
              'UK_DRIVER_LICENSE_QUESTION'=>$request->UK_DRIVER_LICENSE_QUESTION,
              'UK_DRIVER_LICENSE'=>$request->UK_DRIVER_LICENSE,
              'ADDRESS1'=>$request->ADDRESS1,
              'ADDRESS2'=>$request->ADDRESS2,
              'TOWN'=>$request->TOWN,
              'COUNTY'=>$request->COUNTY,
              'POSTCODE'=>$request->POSTCODE,
              'LOCATION_NAME'=>$request->LOCATION_NAME,
              'LOCATION_CODE'=>$request->LOCATION_CODE,
              'FAX'=>$request->FAX,
              'VATRATE'=>$request->VATRATE,
              'COUNTRY_PREFIX'=>$request->COUNTRY_PREFIX,
              'NUMBER_OF_OTHERROOMS'=>$request->NUMBER_OF_OTHERROOMS,
              'COUNTRY'=>$request->COUNTRY,
              'ARE_YOU_IN_UK'=>$request->ARE_YOU_IN_UK,
              'TIME_LIVED_AT_ADDRESS'=>$days,
              'DATE_MOVED_IN_ADDRESS'=>$request->DATE_MOVED_IN_ADDRESS,
              'HOME_QUESTION_ANSWER'=>$request->HOME_QUESTION_ANSWER,
              'LANDLORD_NAME'=>$request->LANDLORD_NAME,
              //'LANDLORD_ADDRESS'=>$request->LANDLORD_ADDRESS,
              'LANDLORD_EMAIL'=>$request->LANDLORD_EMAIL,
              'LANDLORD_MOBILE'=>$request->LANDLORD_MOBILE,
              'LANDLORD_EMAIL'=>$request->LANDLORD_EMAIL,
              'LANDLORD_MOBILE'=>$request->LANDLORD_MOBILE,
              'LANDLORD_ADDRESS1'=>$request->LANDLORD_ADDRESS1,
              'LANDLORD_ADDRESS2'=>$request->LANDLORD_ADDRESS2,
              'LANDLORD_LOCATION_NAME'=>$request->LANDLORD_LOCATION_NAME,
              'LANDLORD_LOCATION_CODE'=>$request->LANDLORD_LOCATION_CODE,
              'LANDLORD_TOWN'=>$request->LANDLORD_TOWN,
              'LANDLORD_COUNTY'=>$request->LANDLORD_COUNTY,
              'LANDLORD_POSTCODE'=>$request->LANDLORD_POSTCODE,
              'LANDLORD_COUNTRY_PREFIX'=>$request->LANDLORD_COUNTRY_PREFIX,
              'LANDLORD_COUNTRY'=>$request->LANDLORD_COUNTRY,
              'LANDLORD_FAX'=>$request->LANDLORD_FAX,
              'LANDLORD_VRATE'=>$request->LANDLORD_VRATE,
              'NUMBER_OF_BEDROOMS'=>$request->NUMBER_OF_BEDROOMS,
              'WHO_LIVES_THERE'=>$request->WHO_LIVES_THERE,
              'PREVIOUS_ADDRESS1'=>$request->PREVIOUS_ADDRESS1,
              'PREVIOUS_ADDRESS2'=>$request->PREVIOUS_ADDRESS2,
              'PREVIOUS_LOCATION_NAME'=>$request->PREVIOUS_LOCATION_NAME,
              'PREVIOUS_LOCATION_CODE'=>$request->PREVIOUS_LOCATION_CODE,
              'PREVIOUS_COUNTRY_PREFIX'=>$request->PREVIOUS_COUNTRY_PREFIX,
              'PREVIOUS_FAX'=>$request->PREVIOUS_FAX,
              'PREVIOUS_VRATE'=>$request->PREVIOUS_VRATE,
              'TOWN_PREVIOUS'=>$request->TOWN_PREVIOUS,
              'COUNTY_PREVIOUS'=>$request->COUNTY_PREVIOUS,
              'POSTCODE_PREVIOUS'=>$request->POSTCODE_PREVIOUS,
              'COUNTRY_PREVIOUS'=>$request->COUNTRY_PREVIOUS,
              'MARITAL_STATUS'=>$request->MARITAL_STATUS,
              'DATE_OF_MARRIAGE'=>$request->DATE_OF_MARRIAGE,
              'WHERE_YOU_GOT_MARRIED'=>$request->WHERE_YOU_GOT_MARRIED,
              'NAME_OF_SPOUSE'=>$request->NAME_OF_SPOUSE,
              'DOB_SPOUSE'=>$request->DOB_SPOUSE,
              'NATIONALITY_SPOUSE'=>$request->NATIONALITY_SPOUSE,
              'PASSPORT_SPOUSE'=>$request->PASSPORT_SPOUSE,
              'WHERE_YOU_MET'=>$request->WHERE_YOU_MET,
              'WHERE_RELATIONSHIP_BEGAN'=>$request->WHERE_RELATIONSHIP_BEGAN,
              'WHEN_LAST_YOU_SAW_EACHOTHER'=>$request->WHEN_LAST_YOU_SAW_EACHOTHER,
              'LIVE_TOGETHER_QUESTION'=>$request->LIVE_TOGETHER_QUESTION,
              'DATE_LIVING_TOGETHER'=>$request->DATE_LIVING_TOGETHER,
              'DO_YOU_HAVE_CHILDREN'=>$request->DO_YOU_HAVE_CHILDREN,
              'NUMBER_OF_DEPENDENT_CHILDREN'=>$request->NUMBER_OF_DEPENDENT_CHILDREN,
              'MARRIED_BEFORE_QUESTION'=>$request->MARRIED_BEFORE_QUESTION,
              'PARTNER_MARRIED_BEFORE'=>$request->PARTNER_MARRIED_BEFORE,
              'DO_YOU_HAVE_FAMILY_IN_HOME_COUNTRY'=>$request->DO_YOU_HAVE_FAMILY_IN_HOME_COUNTRY,
              'QUALIFICATION_QUESTION'=>$request->QUALIFICATION_QUESTION,
              'OTHER_CERTIFICATE'=>$request->OTHER_CERTIFICATE,
              'HAVE_DEGREE_IN_ENGLISH'=>$request->HAVE_DEGREE_IN_ENGLISH,
              'PASSED_RECOGNIZED_TEST'=>$request->PASSED_RECOGNIZED_TEST,
              'WHAT_TEST_DID_YOU_PASS'=>$request->WHAT_TEST_DID_YOU_PASS,
              'WHEN_DID_YOU_ENTER_UK'=>$request->WHEN_DID_YOU_ENTER_UK,
              'DID_YOU_ENTER_LEGALLY'=>$request->DID_YOU_ENTER_LEGALLY,
              'VISA_REASON'=>$request->VISA_REASON,
              'VISA_START_DATE'=>$request->VISA_START_DATE,
              'VISA_END_DATE'=>$request->VISA_END_DATE,
              'VISA_STATUS'=>$request->VISA_STATUS,
              'OUT_OF_THE_UK_BEFORE'=>$request->OUT_OF_THE_UK_BEFORE,
              'ANY_OTHER_COUNTRY_VISITED'=>$request->ANY_OTHER_COUNTRY_VISITED,
              'CRIMINAL_OFFENSE'=>$request->CRIMINAL_OFFENSE,
              'updated_at' => Carbon::now()
        ]);


             $response =([
                'Message' => 'Application Submitted, pending approval'
            ]);

            return response($response, 201);

        }
        //Check if application_status is in draft, then update table with respective values and update prescreening_status =2
        elseif($application_status==1){

                 //Validate entry into Application table
              /*   $request->validate([
                    //Personal Information
            'NAME_CHANGE_QUESTION'=>'string|required',
            'COUNTRY_OF_BIRTH'=>'string|required',
            'PLACE_OF_BIRTH'=>'string|required',
            'NATIONALITY'=>'string|required',
            'OTHER_NATIONALITY_QUESTION'=>'string|required',
            'PASSPORT_NO'=>'string|required',
            'PASSPORT_ISSUED'=>'date|required',
            'PASSPORT_EXPIRY'=>'date|required',
            'ISSUING_AUTHORITY'=>'string|required',
            'PLACE_OF_ISSUE'=>'string|required',
            'BRP_QUESTION'=>'string|required',
            'NATIONAL_ID_QUESTION'=>'string|required',
            'NATIONAL_ID_NO'=>'string|required',
            'NAME_MOTHER'=>'string|required',
            'DOB_MOTHER'=>'string|required',
            'NATIONALITY_MOTHER'=>'string|required',
            'PLACE_OF_BIRTH_MOTHER'=>'string|required',
            'NAME_FATHER'=>'string|required',
            'DOB_FATHER'=>'string|required',
            'NATIONALITY_FATHER'=>'string|required',
            'PLACE_OF_BIRTH_FATHER'=>'string|required',
            'UK_NI_QUESTION'=>'string|required',
            'UK_DRIVER_LICENSE_QUESTION'=>'string|required',
            'ADDRESS1'=>'string|required',
            'ADDRESS2'=>'string',
            'TOWN'=>'string',
            'COUNTY'=>'string',
            'POSTCODE'=>'string',
            'COUNTRY'=>'string',
            // 'LOCATION_CODE'=>'required|string',
            // 'FAX'=>'required|string',
            // 'VATRATE'=>'required|string',
            // 'COUNTRY_PREFIX'=>'required|string',
            'NUMBER_OF_OTHERROOMS'=>'required|string',
            'DATE_MOVED_IN_ADDRESS'=>'date|required',
            'HOME_QUESTION_ANSWER'=>'string',
            'NUMBER_OF_BEDROOMS'=>'integer|required',
            'WHO_LIVES_THERE'=>'string|required',
            'PREVIOUS_ADDRESS1'=>'string',
            'PREVIOUS_ADDRESS2'=>'string',
            'MARITAL_STATUS'=>'string|required',
            'DATE_OF_MARRIAGE'=>'date|required',
            'WHERE_YOU_GOT_MARRIED'=>'string|required',
            'NAME_OF_SPOUSE'=>'string|required',
            'DOB_SPOUSE'=>'date|required',
            'NATIONALITY_SPOUSE'=>'string|required',
            'PASSPORT_SPOUSE'=>'string',
            'WHERE_YOU_MET'=>'string',
            'WHERE_RELATIONSHIP_BEGAN'=>'string',
            'WHEN_LAST_YOU_SAW_EACHOTHER'=>'date',
            'LIVE_TOGETHER_QUESTION'=>'string',
            'DATE_LIVING_TOGETHER'=>'date',
            'DO_YOU_HAVE_CHILDREN'=>'string|required',
            'MARRIED_BEFORE_QUESTION'=>'string|required',
            'PARTNER_MARRIED_BEFORE'=>'string|required',
            'DO_YOU_HAVE_FAMILY_IN_HOME_COUNTRY'=>'string|required',
            'QUALIFICATION_QUESTION'=>'string|required',
            'OTHER_CERTIFICATE'=>'string|required',
            'HAVE_DEGREE_IN_ENGLISH'=>'string|required',
            'PASSED_RECOGNIZED_TEST'=>'string',
            'WHEN_DID_YOU_ENTER_UK'=>'date|required',
            'DID_YOU_ENTER_LEGALLY'=>'string|required',
            'VISA_REASON'=>'string|required',
            'VISA_START_DATE'=>'date|required',
            'VISA_STATUS'=>'string|required',
            'OUT_OF_THE_UK_BEFORE'=>'string|required',
            'ENTERED_UK_MEANS'=>'string|required',
            'EVER_STAYED_BEYOND_EXPIRY'=>'required|string',
            'BREACHED_CONDITION_FOR_LEAVE'=>'string|required',
            'WORK_WITHOUT_PERMIT'=>'string|required',
            'RECEIVED_PUBLIC_FUNDS'=>'required|string',
            'GIVE_FALSE_INFO'=>'required|string',
            'USED_DECEPTION'=>'required|string',
            'BREACHED_OTHER_LAWS'=>'required|string',
            'VISA_REFUSAL_QUESTION'=>'string|required',
            'PERMISSION_REFUSAL'=>'string|required',
            'ASYLUM_REFUSAL'=>'string|required',
            'EVER_DEPORTED'=>'required|string',
            'EVER_BANNED'=>'required|string',
            'CRIMINAL_OFFENSE'=>'string|required',
            'PASSPORT_UPLOAD'=>'mimes:doc,docx,pdf,txt,csv|max:2048|required',
            'DEPENDENT_PASSPORT_UPLOAD'=>'mimes:doc,docx,pdf,txt,csv|max:2048|required',
            'UTILITY_BILL_UPLOAD'=>'mimes:doc,docx,pdf,txt,csv|max:2048|required',
            'BRP_UPLOAD'=>'mimes:doc,docx,pdf,txt,csv|max:2048|required',
            'PREVIOUS_VISA_UPLOAD'=>'mimes:doc,docx,pdf,txt,csv|max:2048|required',
            'REFUSAL_LETTER_UPLOAD'=>'mimes:doc,docx,pdf,txt,csv|max:2048|required',
            'EDUCATIONAL_CERT_UPLOAD'=>'mimes:doc,docx,pdf,txt,csv|max:2048|required',
            'ENGLISH_CERT_UPLOAD'=>'mimes:doc,docx,pdf,txt,csv|max:2048|required',
            'MARRIAGE_CERT_UPLOAD'=>'mimes:doc,docx,pdf,txt,csv|max:2048|required',
            'BANK_STATEMENT_UPLOAD'=>'mimes:doc,docx,pdf,txt,csv|max:2048|required'

        ]);*/


    if(strtoupper($request->NAME_CHANGE_QUESTION)=='YES'){
    app('App\Http\Controllers\NameChangeController')-> nameChange($request);

      }

    if(strtoupper($request->OTHER_NATIONALITY_QUESTION)=='YES'){
        app('App\Http\Controllers\OtherNationalityController')-> store($request);

    }
    if(strtoupper($request->BRP_QUESTION) =='YES'){
        $request->validate([
            'BRP_NUMBER'=>'string|required',
           // 'BRP_ISSUE_DATE'=>'date|required',
            'BRP_EXPIRY_DATE'=>'date|required'
        ]);
    }

    if(strtoupper($request->UK_NI_QUESTION) =='YES'){
        $request->validate([
        'UK_NI'=>'string|required'
        ]);
    }

    if(strtoupper($request->UK_DRIVER_LICENSE_QUESTION) =='YES'){
        $request->validate([
           'UK_DRIVER_LICENSE'=>'string|required'
        ]);
    }

    if(strtoupper($request->HOME_QUESTION_ANSWER)=='RENTED'){
        $request->validate([
            //'LANDLORD_NAME'=>'string|required',
           // 'LANDLORD_ADDRESS'=>'string|required',
           'LANDLORD_NAME'=>'string|required',
           // 'LANDLORD_ADDRESS'=>'string|required',
           // 'LANDLORD_EMAIL'=>'string',
            'LANDLORD_MOBILE'=>'string|required',
            'LANDLORD_ADDRESS1'=>'string|required',
           // 'LANDLORD_ADDRESS2'=>'string|required',
            // 'LANDLORD_LOCATION_NAME'=>'string|required',
            // 'LANDLORD_LOCATION_CODE'=>'string|required',
            'LANDLORD_TOWN'=>'string|required',
           // 'LANDLORD_COUNTY'=>'string|required',
           // 'LANDLORD_POSTCODE'=>'string|required',
            // 'LANDLORD_COUNTRY_PREFIX'=>'string|required',
            'LANDLORD_COUNTRY'=>'string|required',
            // 'LANDLORD_FAX'=>'string|required',
            // 'LANDLORD_VRATE'=>'string|required',

        ]);
    }

    if(strtoupper($request->DO_YOU_HAVE_FAMILY_IN_HOME_COUNTRY)=='YES'){

        app('App\Http\Controllers\NameofPeopleatAddressController')-> store($request);

    }

    if($request->PREVIOUS_ADDRESS){
        $request->validate([
        'TOWN_PREVIOUS'=>'string|required',
        'COUNTY_PREVIOUS'=>'string|required',
        'POSTCODE_PREVIOUS'=>'string|required',
        'COUNTRY_PREVIOUS'=>'string|required',
        ]);
    }

    if(strtoupper($request->DO_YOU_HAVE_CHILDREN) =='YES'){
      //  $request->NUMBER_OF_DEPENDENT_CHILDREN;
        app('App\Http\Controllers\NumberofChildrenController')-> store($request);

    }

    if(strtoupper($request->PARTNER_MARRIED_BEFORE)=='YES'){
        app('App\Http\Controllers\PartnerMarriedBeforeController')-> store($request);
    }

    if($request->WHO_YOU_HAVE_BACK_HOME){
        app('App\Http\Controllers\PeopleAtHomeController')-> store($request);


    }

    if($request->QUALIFICATION_QUESTION){
        app('App\Http\Controllers\QualificationController')-> store($request);
    }

    if(strtoupper($request->PASSED_RECOGNIZED_TEST)=='YES'){
        $request->validate([
            'WHAT_TEST_DID_YOU_PASS'=>'string|required',
        ]);
    }

    if(!empty($request->EMPLOYMENT_STATUS)){
        app('App\Http\Controllers\EmploymentController')-> store($request);
    }

    if(!empty(strtoupper($request->LAST_FIVE_VISITS))){

        app('App\Http\Controllers\TravelFiveController')-> store($request);
//app('App\Http\Controllers\TravelFiveController')-> anyOtherCountryTravelled($request);
    }

    if(($request->OUT_OF_THE_UK_BEFORE)=='YES'){
        app('App\Http\Controllers\TravelController')-> store($request);
    }

    if(!empty($request->CRIMINAL_OFFENSE)){
        app('App\Http\Controllers\CharacterController')-> store($request);
    }

    if(!empty($request->ENTERED_UK_MEANS)){

        DB::table('visamgr_applications')
        ->where('APPTYPE_ID', $id)
        ->update([
            'ENTERED_UK_MEANS'=>$request->ENTERED_UK_MEANS,
            'REASON_FOR_ILEGAL_ENTRY'=>$request->REASON_FOR_ILEGAL_ENTRY
        ]);

    }

    if(!empty($request->EVER_STAYED_BEYOND_EXPIRY)){

        DB::table('visamgr_applications')
        ->where('APPTYPE_ID', $id)
        ->update([
            'EVER_STAYED_BEYOND_EXPIRY'=>$request->EVER_STAYED_BEYOND_EXPIRY,
            'REASON_FOR_STAYING_BEYOND_EXPIRY'=>$request->REASON_FOR_STAYING_BEYOND_EXPIRY
        ]);

    }

    if(!empty($request->BREACHED_CONDITION_FOR_LEAVE)){

        DB::table('visamgr_applications')
        ->where('APPTYPE_ID', $id)
        ->update([
            'BREACHED_CONDITION_FOR_LEAVE'=>$request->BREACHED_CONDITION_FOR_LEAVE,
             'REASON_FOR_BREACH'=>$request->REASON_FOR_BREACH,
            'BREACH_COUNTRY'=>$request->BREACH_COUNTRY
        ]);

    }

    if(!empty($request->WORK_WITHOUT_PERMIT)){

        DB::table('visamgr_applications')
        ->where('APPTYPE_ID', $id)
        ->update([
            'WORK_WITHOUT_PERMIT'=>$request->WORK_WITHOUT_PERMIT,
             'REASON_FOR_WORK_WITHOUT_PERMIT'=>$request->REASON_FOR_WORK_WITHOUT_PERMIT,
        ]);

    }


    if(!empty($request->RECEIVED_PUBLIC_FUNDS)){

        DB::table('visamgr_applications')
        ->where('APPTYPE_ID', $id)
        ->update([
            'RECEIVED_PUBLIC_FUNDS'=>$request->RECEIVED_PUBLIC_FUNDS,
             'REASON_RECEIVING_FUNDS'=>$request->REASON_RECEIVING_FUNDS,
        ]);

    }

    if(!empty($request->GIVE_FALSE_INFO)){

        DB::table('visamgr_applications')
        ->where('APPTYPE_ID', $id)
        ->update([
            'GIVE_FALSE_INFO'=>$request->GIVE_FALSE_INFO,
             'REASON_FOR_FALSE_INFO'=>$request->REASON_FOR_FALSE_INFO,
        ]);

    }


    if(!empty($request->USED_DECEPTION)){

        DB::table('visamgr_applications')
        ->where('APPTYPE_ID', $id)
        ->update([
            'USED_DECEPTION'=>$request->USED_DECEPTION,
             'REASON_FOR_DECEPTION'=>$request->REASON_FOR_DECEPTION,
        ]);

    }

    if(!empty($request->BREACHED_OTHER_LAWS)){

        DB::table('visamgr_applications')
        ->where('APPTYPE_ID', $id)
        ->update([
            'BREACHED_OTHER_LAWS'=>$request->BREACHED_OTHER_LAWS,
             'REASON_FOR_BREACHING__LAWS'=>$request->REASON_FOR_BREACHING__LAWS,
        ]);

    }

    if(!empty($request->VISA_REFUSAL_QUESTION)){

        DB::table('visamgr_applications')
        ->where('APPTYPE_ID', $id)
        ->update([
            'VISA_REFUSAL_QUESTION'=>$request->VISA_REFUSAL_QUESTION,
             'REASON_FOR_REFUSAL'=>$request->REASON_FOR_REFUSAL,
        ]);

    }


    if(!empty($request->PERMISSION_REFUSAL)){

        DB::table('visamgr_applications')
        ->where('APPTYPE_ID', $id)
        ->update([
            'PERMISSION_REFUSAL'=>$request->PERMISSION_REFUSAL,
             'REASON_FOR_PERMISSION_REFUSAL'=>$request->REASON_FOR_PERMISSION_REFUSAL,
        ]);

    }

    if(!empty($request->ASYLUM_REFUSAL)){

        DB::table('visamgr_applications')
        ->where('APPTYPE_ID', $id)
        ->update([
            'ASYLUM_REFUSAL'=>$request->ASYLUM_REFUSAL,
             'REASON_FOR_ASYLUM_REFUSAL'=>$request->REASON_FOR_ASYLUM_REFUSAL,
        ]);

    }


    if(!empty($request->EVER_DEPORTED)){

        DB::table('visamgr_applications')
        ->where('APPTYPE_ID', $id)
        ->update([
            'EVER_DEPORTED'=>$request->EVER_DEPORTED,
             'REASON_FOR_DEPORTATION'=>$request->REASON_FOR_DEPORTATION,
        ]);

    }

    if(!empty($request->EVER_BANNED)){

        DB::table('visamgr_applications')
        ->where('APPTYPE_ID', $id)
        ->update([
            'EVER_BANNED'=>$request->EVER_BANNED,
             'REASON_FOR_BAN'=>$request->REASON_FOR_BAN,
        ]);

    }



           //compute time_lived_at_address
    $today = date("Y-m-d");
    $end_date = $request->DATE_MOVED_IN_ADDRESS;
    $datetime1 = new DateTime($today);
    $datetime2 = new DateTime($end_date);
    $diff = $datetime1->diff($datetime2);
    $days = $diff->format('%a');

        DB::table('visamgr_applications')
            ->where('APPTYPE_ID', $id)
            ->update(['APPSTATUS' => 2,
                  'NAME_CHANGE_QUESTION'=>$request->NAME_CHANGE_QUESTION,
                  'COUNTRY_OF_BIRTH'=>$request->COUNTRY_OF_BIRTH,
                  'NATIONALITY'=>$request->NATIONALITY,
                  'OTHER_NATIONALITY_QUESTION'=>$request->OTHER_NATIONALITY_QUESTION,
                  'PLACE_OF_BIRTH'=>$request->PLACE_OF_BIRTH,
                  'PASSPORT_NO'=>$request->PASSPORT_NO,
                  'PASSPORT_ISSUED'=>$request->PASSPORT_ISSUED,
                  'PASSPORT_EXPIRY'=>$request->PASSPORT_EXPIRY,
                  'ISSUING_AUTHORITY'=>$request->ISSUING_AUTHORITY,
                  'PLACE_OF_ISSUE'=>$request->PLACE_OF_ISSUE,
                  'BRP_QUESTION'=>$request->BRP_QUESTION,
                  'BRP_NUMBER'=>$request->BRP_NUMBER,
               //   'BRP_ISSUE_DATE'=>$request->BRP_ISSUE_DATE,
                  'BRP_EXPIRY_DATE'=>$request->BRP_EXPIRY_DATE,
                  'NATIONAL_ID_QUESTION'=>$request->NATIONAL_ID_QUESTION,
                  'NATIONAL_ID_NO'=>$request->NATIONAL_ID_NO,
                  'NAME_MOTHER'=>$request->NAME_MOTHER,
                  'DOB_MOTHER'=>$request->DOB_MOTHER,
                  'NATIONALITY_MOTHER'=>$request->NATIONALITY_MOTHER,
                  'PLACE_OF_BIRTH_MOTHER'=>$request->PLACE_OF_BIRTH_MOTHER,
                  'NAME_FATHER'=>$request->NAME_FATHER,
                  'DOB_FATHER'=>$request->DOB_FATHER,
                  'NATIONALITY_FATHER'=>$request->NATIONALITY_FATHER,
                  'PLACE_OF_BIRTH_FATHER'=>$request->PLACE_OF_BIRTH_FATHER,
                  'UK_NI_QUESTION'=>$request->UK_NI_QUESTION,
                  'UK_NI'=>$request->UK_NI,
                  'UK_DRIVER_LICENSE_QUESTION'=>$request->UK_DRIVER_LICENSE_QUESTION,
                  'UK_DRIVER_LICENSE'=>$request->UK_DRIVER_LICENSE,
                  'ADDRESS1'=>$request->ADDRESS1,
                  'ADDRESS2'=>$request->ADDRESS1,
                  'TOWN'=>$request->TOWN,
                  'COUNTY'=>$request->COUNTY,
                  'POSTCODE'=>$request->POSTCODE,
                  'COUNTRY'=>$request->COUNTRY,
                  'LOCATION_NAME'=>$request->LOCATION_NAME,
                    'LOCATION_CODE'=>$request->LOCATION_CODE,
                    'FAX'=>$request->FAX,
                    'VATRATE'=>$request->VATRATE,
                    'COUNTRY_PREFIX'=>$request->COUNTRY_PREFIX,
                    'NUMBER_OF_OTHERROOMS'=>$request->NUMBER_OF_OTHERROOMS,
                  'TIME_LIVED_AT_ADDRESS'=>$days,
                  'DATE_MOVED_IN_ADDRESS'=>$request->DATE_MOVED_IN_ADDRESS,
                  'HOME_QUESTION_ANSWER'=>$request->HOME_QUESTION_ANSWER,
                  'LANDLORD_NAME'=>$request->LANDLORD_NAME,
                //  'LANDLORD_ADDRESS'=>$request->LANDLORD_ADDRESS,
                  'ARE_YOU_IN_UK'=>$request->ARE_YOU_IN_UK,
                  'LANDLORD_EMAIL'=>$request->LANDLORD_EMAIL,
                  'LANDLORD_MOBILE'=>$request->LANDLORD_MOBILE,
                  'LANDLORD_EMAIL'=>$request->LANDLORD_EMAIL,
                  'LANDLORD_MOBILE'=>$request->LANDLORD_MOBILE,
                  'LANDLORD_ADDRESS1'=>$request->LANDLORD_ADDRESS1,
                  'LANDLORD_ADDRESS2'=>$request->LANDLORD_ADDRESS2,
                  'LANDLORD_LOCATION_NAME'=>$request->LANDLORD_LOCATION_NAME,
                  'LANDLORD_LOCATION_CODE'=>$request->LANDLORD_LOCATION_CODE,
                  'LANDLORD_TOWN'=>$request->LANDLORD_TOWN,
                  'LANDLORD_COUNTY'=>$request->LANDLORD_COUNTY,
                  'LANDLORD_POSTCODE'=>$request->LANDLORD_POSTCODE,
                  'LANDLORD_COUNTRY_PREFIX'=>$request->LANDLORD_COUNTRY_PREFIX,
                  'LANDLORD_COUNTRY'=>$request->LANDLORD_COUNTRY,
                  'LANDLORD_FAX'=>$request->LANDLORD_FAX,
                  'LANDLORD_VRATE'=>$request->LANDLORD_VRATE,
                  'NUMBER_OF_BEDROOMS'=>$request->NUMBER_OF_BEDROOMS,
                  'WHO_LIVES_THERE'=>$request->WHO_LIVES_THERE,
                  'PREVIOUS_ADDRESS1'=>$request->PREVIOUS_ADDRESS1,
                  'PREVIOUS_ADDRESS2'=>$request->PREVIOUS_ADDRESS2,
                  'PREVIOUS_LOCATION_NAME'=>$request->PREVIOUS_LOCATION_NAME,
                  'PREVIOUS_LOCATION_CODE'=>$request->PREVIOUS_LOCATION_CODE,
                  'PREVIOUS_COUNTRY_PREFIX'=>$request->PREVIOUS_COUNTRY_PREFIX,
                  'PREVIOUS_FAX'=>$request->PREVIOUS_FAX,
                  'PREVIOUS_VRATE'=>$request->PREVIOUS_VRATE,
                  'TOWN_PREVIOUS'=>$request->TOWN_PREVIOUS,
                  'COUNTY_PREVIOUS'=>$request->COUNTY_PREVIOUS,
                  'POSTCODE_PREVIOUS'=>$request->POSTCODE_PREVIOUS,
                  'COUNTRY_PREVIOUS'=>$request->COUNTRY_PREVIOUS,
                  'MARITAL_STATUS'=>$request->MARITAL_STATUS,
                  'DATE_OF_MARRIAGE'=>$request->DATE_OF_MARRIAGE,
                  'WHERE_YOU_GOT_MARRIED'=>$request->WHERE_YOU_GOT_MARRIED,
                  'NAME_OF_SPOUSE'=>$request->NAME_OF_SPOUSE,
                  'DOB_SPOUSE'=>$request->DOB_SPOUSE,
                  'NATIONALITY_SPOUSE'=>$request->NATIONALITY_SPOUSE,
                  'PASSPORT_SPOUSE'=>$request->PASSPORT_SPOUSE,
                  'WHERE_YOU_MET'=>$request->WHERE_YOU_MET,
                  'WHERE_RELATIONSHIP_BEGAN'=>$request->WHERE_RELATIONSHIP_BEGAN,
                  'WHEN_LAST_YOU_SAW_EACHOTHER'=>$request->WHEN_LAST_YOU_SAW_EACHOTHER,
                  'LIVE_TOGETHER_QUESTION'=>$request->LIVE_TOGETHER_QUESTION,
                  'DATE_LIVING_TOGETHER'=>$request->DATE_LIVING_TOGETHER,
                  'DO_YOU_HAVE_CHILDREN'=>$request->DO_YOU_HAVE_CHILDREN,
                  'NUMBER_OF_DEPENDENT_CHILDREN'=>$request->NUMBER_OF_DEPENDENT_CHILDREN,
                  'MARRIED_BEFORE_QUESTION'=>$request->MARRIED_BEFORE_QUESTION,
                  'PARTNER_MARRIED_BEFORE'=>$request->PARTNER_MARRIED_BEFORE,
                  'DO_YOU_HAVE_FAMILY_IN_HOME_COUNTRY'=>$request->DO_YOU_HAVE_FAMILY_IN_HOME_COUNTRY,
                  'QUALIFICATION_QUESTION'=>$request->QUALIFICATION_QUESTION,
                  'OTHER_CERTIFICATE'=>$request->OTHER_CERTIFICATE,
                  'HAVE_DEGREE_IN_ENGLISH'=>$request->HAVE_DEGREE_IN_ENGLISH,
                  'PASSED_RECOGNIZED_TEST'=>$request->PASSED_RECOGNIZED_TEST,
                  'WHAT_TEST_DID_YOU_PASS'=>$request->WHAT_TEST_DID_YOU_PASS,
                  'WHEN_DID_YOU_ENTER_UK'=>$request->WHEN_DID_YOU_ENTER_UK,
                  'DID_YOU_ENTER_LEGALLY'=>$request->DID_YOU_ENTER_LEGALLY,
                  'VISA_REASON'=>$request->VISA_REASON,
                  'VISA_START_DATE'=>$request->VISA_START_DATE,
                  'VISA_END_DATE'=>$request->VISA_END_DATE,
                  'VISA_STATUS'=>$request->VISA_STATUS,
                  'OUT_OF_THE_UK_BEFORE'=>$request->OUT_OF_THE_UK_BEFORE,
                  'ANY_OTHER_COUNTRY_VISITED'=>$request->ANY_OTHER_COUNTRY_VISITED,
                  'CRIMINAL_OFFENSE'=>$request->CRIMINAL_OFFENSE,
                  'updated_at' => Carbon::now()
            ]);

             $response =([
                'Message' => 'Application Submitted, pending approval'
            ]);

            return response($response, 201);
        }
        else{
            //You can't do anything actually ;)
        }

    }

       //Admin Role, to Approve or Decline Screenings
       public function approveApplication(Request $request){

        $user_id = $request->id;
        $username = User::where('id',$user_id)->value('first_name');

         $id = $request->APPTYPE_ID;

         $application_status  =  visamgr_applications::where('APPTYPE_ID',$id)
         ->value('APPSTATUS');



         if($application_status==3){
             $response = [
                 'message'=> 'Application has already been approved!',

                 ];

                 return response($response, 204);
         }

         elseif($application_status == 2){

             //get application id, per the id selected
             $application_id  =  visamgr_applications::where('APPTYPE_ID',$id)
             ->value('APPTYPE_ID');

             $client_id =  visamgr_applications::where('APPTYPE_ID',$id)
             ->value('CLIENT_ID');

             if(!$application_id){

                     $response = [
                 'message'=> 'Client does not exist!',

                 ];

                 return response($response, 204);
                 }

                 // Update client visamgr_applications table record
                 DB::table('visamgr_applications')
                 ->where('APPTYPE_ID', $application_id)
                 ->update(['APPSTATUS'=>3,
                          'USER'=>$username

                 ]);

                 $MESSAGE_SUBJECT = 'Visa Application Status';

                 $MESSAGE_TAG = 'Travel';

                 $uuid = Str::uuid()->toString();

                 $fromAddress = User::where('id',$user_id)
                 ->value('email');

                 $client_name = Client::where('id', $client_id)
                 ->value('first_name');

                 $toAddress = Client::where('id', $client_id)
                 ->value('email');

                 $message = "Dear ".$client_name."," ."<br/><br/>Congratulations, your visa application has been approved." ;

                 DB::table('messages')->insert([
                     'MESSAGE'=>$message,
                     'MESSAGE_SUBJECT'=>$MESSAGE_SUBJECT,
                     'MESSAGE_TAG'=>$MESSAGE_TAG,
                     'SENDER'=>'i-visas',
                     'USER_ID'=>$user_id,
                     'CONVERSATION_ID'=>$uuid,
                     'RECEIPIENT'=>$client_id,
                     'created_at' => Carbon::now(),
                     'updated_at' => Carbon::now(),
                     ]);

                     $itemId = DB::getPdo()->lastInsertId();

                  //Send broadcast notification to user and update notification table
                  $message = Messages::find($itemId);
                  $client = Client::findOrFail($client_id);
                  $client->notify(new ApplicationNotification($client, $message,$client_name, $toAddress, $MESSAGE_SUBJECT));

                  DB::table('notifications')->insert([
                     'DATA'=>$message,
                     'MESSAGE_ID'=>$itemId,
                     'CONVERSATION_ID'=>$uuid,
                     'created_at' => Carbon::now(),
                     'updated_at' => Carbon::now(),
                 ]);



                 $response = [
                     'message'=> 'Application Approved!',

                     ];

                     return response($response, 200);

             }

         else{
             $response = [
                 'message'=> 'Client is yet to submit Application!',

                 ];

                 return response($response, 200);
         }
    }

    public function applicationsInReview($userid){

        $id = $userid;

        if (is_numeric($id)) {

            if($id == 1){
                $applications_in_review  = DB::table('visamgr_applications')
                ->leftJoin('clients', 'visamgr_applications.CLIENT_ID', '=', 'clients.id')
                ->leftJoin('visamgr_branches', 'clients.client_office', '=', 'visamgr_branches.id')
                ->select('visamgr_applications.*', 'visamgr_branches.LOCATION_CODE')
               // ->whereIn('clients.client_office', $branches)
                ->where('visamgr_applications.APPSTATUS','=',2)
                ->get();

                $response = [
                    'message'=> 'Applications in review stage',
                    'applications_in_review'=>$applications_in_review
                    ];

                    return response($response, 200);
            }

            else{

            $user_branch = User::where('id',$id)->value('branch_id');
            $user_other_branches = DB::table('user_locations')->where('user_id', $id)->value('location_id');
          //  $user_other_branches = DB::table('user_locations')->where('user_id', $id)->get();
            $Decoded_user_other_branches = json_decode($user_other_branches, true);
            $branches = array($user_branch);

            if(count($Decoded_user_other_branches)>0){
                foreach($Decoded_user_other_branches as $key=>$other_branches) {
                    array_push($branches, $other_branches['value']);
                   }
            }

        $applications_in_review  = DB::table('visamgr_applications')
        ->leftJoin('clients', 'visamgr_applications.CLIENT_ID', '=', 'clients.id')
        ->leftJoin('visamgr_branches', 'clients.client_office', '=', 'visamgr_branches.id')
        ->select('visamgr_applications.*', 'visamgr_branches.LOCATION_CODE')
        ->whereIn('clients.client_office', $branches)
        ->where('visamgr_applications.APPSTATUS','=',2)
        ->get();

        $response = [
            'message'=> 'Applications in review stage',
            'applications_in_review'=>$applications_in_review
            ];

            return response($response, 200);
        }
    }
        else {
            $response =([
                'Message' => 'Invalid User ID'
            ]);

            return response($response, 400);
        }
    }


    public function returnApplicationDraft(Request $request){

        $user_id = $request->id;

        $id = $request->APPTYPE_ID;
        //get id of client to update
      $application_id  =  visamgr_applications::where('APPTYPE_ID',$id)->value('APPTYPE_ID');

      if(!$application_id){

           $response = [
                   'message'=> 'Client does not exist!',

               ];

               return response($response, 400);
      }

      $client_id =  visamgr_applications::where('APPTYPE_ID',$id)
      ->value('CLIENT_ID');

      DB::table('visamgr_applications')
                       ->where('APPTYPE_ID', $application_id)
                       ->update(['APPSTATUS' => 1
                   ]);

                   $MESSAGE_SUBJECT = 'Visa Application Status';

                   $MESSAGE_TAG = 'Travel';

                   $uuid = Str::uuid()->toString();

                   $fromAddress = User::where('id',$user_id)
                   ->value('email');

                   $client_name = Client::where('id', $client_id)
                   ->value('first_name');

                   $toAddress = Client::where('id', $client_id)
                   ->value('email');

                   //$message = "Dear ".$client_name."," ."<br/><br/>Sorry, your visa application has been declined, and we cannot proceed with your application." ;
                   $message = "Dear ".$client_name.",<br /><br />" ."Unfortunately, your application has been sent back to draft <br />Kindly refer to the below remarks provided by Agent for your action <br/> <hr /> <i>".$request->MESSAGE."<i/> <hr /><br /> Kind regards. " ;



                   DB::table('messages')->insert([
                       'MESSAGE'=>$message,
                       'MESSAGE_SUBJECT'=>$MESSAGE_SUBJECT,
                       'MESSAGE_TAG'=>$MESSAGE_TAG,
                       'SENDER'=>'i-visas',
                       'USER_ID'=>$user_id,
                       'CONVERSATION_ID'=>$uuid,
                       'RECEIPIENT'=>$client_id,
                       'created_at' => Carbon::now(),
                       'updated_at' => Carbon::now(),
                       ]);

                       $itemId = DB::getPdo()->lastInsertId();

                    //Send broadcast notification to user and update notification table
                    $messageData = Messages::find($itemId);
                    $client = Client::findOrFail($client_id);
                    $client->notify(new ReturnApplicationDraftNotification($client, $messageData,$client_name, $toAddress, $MESSAGE_SUBJECT));

                    DB::table('notifications')->insert([
                       'DATA'=>$message,
                       'MESSAGE_ID'=>$itemId,
                       'CONVERSATION_ID'=>$uuid,
                       'created_at' => Carbon::now(),
                       'updated_at' => Carbon::now(),
                   ]);

                // insert into notes
                   DB::table('notes')->insert([
                    'APPLICATION_ID'=>$application_id,
                    'DESCRIPTION'=>$message,
                    'USER'=>$user_id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                 ]);



                   $response = [
                       'message'=> 'Application returned to draft!',

                       ];

                       return response($response, 200);


   }


    public function declineApplication(Request $request){

        $user_id = $request->id;

        $id = $request->APPTYPE_ID;
        //get id of client to update
      $application_id  =  visamgr_applications::where('APPTYPE_ID',$id)->value('APPTYPE_ID');

      if(!$application_id){

           $response = [
                   'message'=> 'Client does not exist!',

               ];

               return response($response, 400);
      }

      $client_id =  visamgr_applications::where('APPTYPE_ID',$id)
      ->value('CLIENT_ID');

      DB::table('visamgr_applications')
                       ->where('APPTYPE_ID', $application_id)
                       ->update(['APPSTATUS' => 9
                   ]);

                   $MESSAGE_SUBJECT = 'Visa Application Status';

                   $MESSAGE_TAG = 'Travel';

                   $uuid = Str::uuid()->toString();

                   $fromAddress = User::where('id',$user_id)
                   ->value('email');

                   $client_name = Client::where('id', $client_id)
                   ->value('first_name');

                   $toAddress = Client::where('id', $client_id)
                   ->value('email');

                   //$message = "Dear ".$client_name."," ."<br/><br/>Sorry, your visa application has been declined, and we cannot proceed with your application." ;
                   $message = "Dear ".$client_name.",<br /><br />" ."Unfortunately, your application has been declined! <br />Kindly refer to the below remarks provided by Agent <br/> <hr /> <i>".$request->MESSAGE."<i/> <hr /><br /> Kind regards. " ;



                   DB::table('messages')->insert([
                       'MESSAGE'=>$message,
                       'MESSAGE_SUBJECT'=>$MESSAGE_SUBJECT,
                       'MESSAGE_TAG'=>$MESSAGE_TAG,
                       'SENDER'=>'i-visas',
                       'USER_ID'=>$user_id,
                       'CONVERSATION_ID'=>$uuid,
                       'RECEIPIENT'=>$client_id,
                       'created_at' => Carbon::now(),
                       'updated_at' => Carbon::now(),
                       ]);

                       $itemId = DB::getPdo()->lastInsertId();

                    //Send broadcast notification to user and update notification table
                    $messageData = Messages::find($itemId);
                    $client = Client::findOrFail($client_id);
                    $client->notify(new DeclineApplicationNotification($client, $messageData,$client_name, $toAddress, $MESSAGE_SUBJECT));

                    DB::table('notifications')->insert([
                       'DATA'=>$message,
                       'MESSAGE_ID'=>$itemId,
                       'CONVERSATION_ID'=>$uuid,
                       'created_at' => Carbon::now(),
                       'updated_at' => Carbon::now(),
                   ]);

                // insert into notes
                   DB::table('notes')->insert([
                    'APPLICATION_ID'=>$application_id,
                    'DESCRIPTION'=>$message,
                    'USER'=>$user_id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                 ]);



                   $response = [
                       'message'=> 'Application Declined!',

                       ];

                       return response($response, 200);


   }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->convert_from_latin1_to_utf8_recursively(visamgr_applications::find($id));
    }

    public function showApplicationIdbyClient(Request $request)
    {
        $client_id = $request->CLIENT_ID;


        return $this->convert_from_latin1_to_utf8_recursively(visamgr_applications::where('client_id', $client_id)->get());



    }




    public function search_application_client($id)
    {

        return DB::table('visamgr_applications')
        ->leftJoin('clients', 'visamgr_applications.CLIENT_ID', '=', 'clients.id')
        ->leftJoin('visamgr_branches', 'clients.client_office', '=', 'visamgr_branches.id')
        ->leftjoin('invoicings', 'visamgr_applications.APPTYPE_ID', '=', 'invoicings.APPLICATION_ID')
         ->select('visamgr_applications.*', 'visamgr_branches.LOCATION_CODE', 'invoicings.INVOICE_NUMBER', 'invoicings.PAYMENT_STATUS', 'invoicings.PAYMENT_AMOUNT')
        ->where('visamgr_applications.CLIENT_ID',$id)->get();


    }

    public function showByApplicationID($application_id)
    {
        //$application_id = $request->APPTYPE_ID;

   // $application_details = visamgr_applications::where('APPTYPE_ID',$application_id)->get();

   $application_details = DB::table('visamgr_applications')->select("*")->where('APPTYPE_ID',$application_id)->get();

     $character_details =visamgr_characters::where('APPLICATION_ID',$application_id)->get();

     $children_details =visamgr_children::where('APPLICATION_ID',$application_id)->get();

     $dependants_details =visamgr_dependants::where('APPLICATION_ID',$application_id)->get();

     $employment_details =visamgr_employment::where('APPLICATION_ID',$application_id)->get();

     $location_details =visamgr_locations::where('APPLICATION_ID',$application_id)->get();

     $maintenance_details =visamgr_maintenance::where('APPLICATION_ID',$application_id)->get();


     $membership_details =visamgr_memberships::where('APPLICATION_ID',$application_id)->get();

     $name_change_details =visamgr_name_change::where('APPLICATION_ID',$application_id)->get();

     $names_of_people_at_address_details =visamgr_names_of_people_at_address::where('APPLICATION_ID',$application_id)->get();

     $nationality_details =visamgr_other_nationality::where('APPLICATION_ID',$application_id)->get();

     $qualification_details =visamgr_qualifications::where('APPLICATION_ID',$application_id)->get();

     $tracking_details = DB::table('visamgr_trackings')
     ->join('visamgr_tracking_types', 'visamgr_trackings.TRACKING_TYPE_ID', '=', 'visamgr_tracking_types.RECID')
     ->select('visamgr_trackings.APPLICATION_ID','visamgr_trackings.DIRECTION', 'visamgr_trackings.TRACKING_DATE', 'visamgr_trackings.TRACKING_ID', 'visamgr_trackings.TRACKING_NOTE', 'visamgr_trackings.created_at', 'visamgr_tracking_types.TRACK_CODE', 'visamgr_tracking_types.RECID', 'visamgr_tracking_types.TRACK_URL')
     ->where('visamgr_trackings.APPLICATION_ID',$application_id)
     ->get();

     $notes = DB::table('notes')
     ->join('users', 'notes.USER', '=', 'users.id')
     ->select('notes.*','users.first_name', 'users.last_name')->where('APPLICATION_ID',$application_id)
     ->get();


     $previous_marriage_details =PreviousMarriage::where('APPLICATION_ID',$application_id)->get();

     $partner_married_before_details = PartnerMarriedBefore::where('APPLICATION_ID',$application_id)->get();

     $documents = $this->retrieveUploads($application_id);
     $documents['baseurl'] = env('baseURL');


     $response =([
        'Message' => 'Application attributes',
           'application_details'=>$this->convert_from_latin1_to_utf8_recursively($application_details),
           'character_details'=>$this->convert_from_latin1_to_utf8_recursively($character_details),
            'children_details'=>$this->convert_from_latin1_to_utf8_recursively($children_details),
            'dependants_details'=>$this->convert_from_latin1_to_utf8_recursively($dependants_details),
            'employment_details'=>$this->convert_from_latin1_to_utf8_recursively($employment_details),
            'location_details'=>$this->convert_from_latin1_to_utf8_recursively($location_details),
            'maintenance_details'=>$this->convert_from_latin1_to_utf8_recursively($maintenance_details),
            'membership_details'=>$this->convert_from_latin1_to_utf8_recursively($membership_details),
            'name_change_details'=>$this->convert_from_latin1_to_utf8_recursively($name_change_details),
            'names_of_people_at_address_details'=>$this->convert_from_latin1_to_utf8_recursively($names_of_people_at_address_details),
            'nationality_details'=>$this->convert_from_latin1_to_utf8_recursively($nationality_details),
            'qualification_details'=>$this->convert_from_latin1_to_utf8_recursively($qualification_details),
            'tracking_details'=>$this->convert_from_latin1_to_utf8_recursively($tracking_details),
            'notes'=>$this->convert_from_latin1_to_utf8_recursively($notes),
            'previous_marriage_details'=>$this->convert_from_latin1_to_utf8_recursively($previous_marriage_details),
            'partner_married_before_details'=>$this->convert_from_latin1_to_utf8_recursively($partner_married_before_details),
            'documents' => $documents
    ]);

    return response(($response),
    200);


    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $client = visamgr_applications::find($id);

        $client->update($request->all());

        return $client;
    }

    /**
     * Remove the specified resource from storage.
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $id = $request->APPTYPE_ID;
        return visamgr_applications::destroy($id);
    }


    public function search($search, $clientid)
    {
        if(is_numeric($clientid)) {
        $search_item = DB::table('visamgr_applications')
            ->leftJoin('clients', 'visamgr_applications.CLIENT_ID', '=', 'clients.id')
            ->leftJoin('visamgr_branches', 'clients.client_office', '=', 'visamgr_branches.id')
            ->leftJoin('invoicings', 'visamgr_applications.APPTYPE_ID', '=', 'invoicings.APPLICATION_ID')
            ->select('visamgr_applications.*', 'visamgr_branches.LOCATION_CODE','invoicings.INVOICE_NUMBER', 'invoicings.PAYMENT_STATUS', 'invoicings.PAYMENT_AMOUNT')
            ->Where('visamgr_applications.CLIENT_ID', '=', $clientid )
            ->where(function ($query) use($search) {
               $query->where('visamgr_applications.APPTYPE_ID', 'like', '%' . $search . '%')
                  ->orWhere('visamgr_applications.EMAIL', 'like', '%' . $search . '%')
                  ->orWhere('visamgr_applications.LASTNAME', 'like', '%' . $search . '%')
                  ->orWhere('visamgr_branches.LOCATION_CODE', 'like', '%' . $search . '%')
                  ->orWhere('visamgr_applications.FIRSTNAME', 'like', '%' . $search . '%');
               })->get();

               $data = $this->convert_from_latin1_to_utf8_recursively($search_item);
               return  $data;
            }
            else {
                $response =([
                    'Message' => 'Invalid Client ID'
                ]);

                return response($response, 400);
            }
    }


    public function searchbyuser($search, $id)
    {
       // $id = $request->userid;

        if (is_numeric($id)) {

            if($id == 1){
                $search_item = DB::table('visamgr_applications')
                ->leftJoin('clients', 'visamgr_applications.CLIENT_ID', '=', 'clients.id')
                ->leftJoin('visamgr_branches', 'clients.client_office', '=', 'visamgr_branches.id')
                ->leftJoin('invoicings', 'visamgr_applications.APPTYPE_ID', '=', 'invoicings.APPLICATION_ID')
                ->select('visamgr_applications.*', 'visamgr_branches.LOCATION_CODE','invoicings.INVOICE_NUMBER', 'invoicings.PAYMENT_STATUS', 'invoicings.PAYMENT_AMOUNT')
               // ->whereIn('clients.client_office', $branches)
                ->where(function ($query) use($search) {
                $query->where('visamgr_applications.APPTYPE_ID', 'like', '%' . $search . '%')
                    ->orWhere('visamgr_applications.EMAIL', 'like', '%' . $search . '%')
                    ->orWhere('visamgr_applications.LASTNAME', 'like', '%' . $search . '%')
                    ->orWhere('visamgr_branches.LOCATION_CODE', 'like', '%' . $search . '%')
                    ->orWhere('visamgr_applications.FIRSTNAME', 'like', '%' . $search . '%');
                })->get();


                $data = $this->convert_from_latin1_to_utf8_recursively($search_item);
                return  $data;

            }
            else{

            $user_branch = User::where('id',$id)->value('branch_id');
            $user_other_branches = DB::table('user_locations')->where('user_id', $id)->value('location_id');
          //  $user_other_branches = DB::table('user_locations')->where('user_id', $id)->get();
            $Decoded_user_other_branches = json_decode($user_other_branches, true);
            $branches = array($user_branch);

            if(count($Decoded_user_other_branches)>0){
                foreach($Decoded_user_other_branches as $key=>$other_branches) {
                    array_push($branches, $other_branches['value']);
                   }
            }


            if(count($branches) > 0) {

                    $search_item = DB::table('visamgr_applications')
                    ->leftJoin('clients', 'visamgr_applications.CLIENT_ID', '=', 'clients.id')
                    ->leftJoin('visamgr_branches', 'clients.client_office', '=', 'visamgr_branches.id')
                    ->leftJoin('invoicings', 'visamgr_applications.APPTYPE_ID', '=', 'invoicings.APPLICATION_ID')
                    ->select('visamgr_applications.*', 'visamgr_branches.LOCATION_CODE','invoicings.INVOICE_NUMBER', 'invoicings.PAYMENT_STATUS', 'invoicings.PAYMENT_AMOUNT')
                    ->whereIn('clients.client_office', $branches)
                    ->where(function ($query) use($search) {
                    $query->where('visamgr_applications.APPTYPE_ID', 'like', '%' . $search . '%')
                        ->orWhere('visamgr_applications.EMAIL', 'like', '%' . $search . '%')
                        ->orWhere('visamgr_applications.LASTNAME', 'like', '%' . $search . '%')
                        ->orWhere('visamgr_branches.LOCATION_CODE', 'like', '%' . $search . '%')
                        ->orWhere('visamgr_applications.FIRSTNAME', 'like', '%' . $search . '%');
                    })->get();


                    $data = $this->convert_from_latin1_to_utf8_recursively($search_item);
                    return  $data;
            }
            else {
                return $branches;
            }

        }
    }
    else {
        $response =([
            'Message' => 'Invalid User ID'
        ]);

        return response($response, 400);
    }

}






     Public function ApplicationByClientLocation(Request $request){
        $client_office = $request->client_office;


        $application_location  = DB::table('visamgr_applications')
            ->leftJoin('clients', 'visamgr_applications.CLIENT_ID', '=', 'clients.id')
            ->leftJoin('visamgr_branches', 'clients.client_office', '=', 'visamgr_branches.id')
            ->leftJoin('invoicings', 'visamgr_applications.APPTYPE_ID', '=', 'invoicings.APPLICATION_ID')
            ->select('visamgr_applications.*', 'visamgr_branches.LOCATION_CODE',
 'invoicings.INVOICE_NUMBER', 'invoicings.PAYMENT_STATUS', 'invoicings.PAYMENT_AMOUNT')

            ->where(function ($query) use($client_office) {
               $query->where('visamgr_branches.LOCATION_CODE', 'like', '%' . $client_office . '%');
            })->get();



            $application_location = $this->convert_from_latin1_to_utf8_recursively($application_location);

        //DB::select('SELECT * FROM clients RIGHT JOIN visamgr_applications on clients.id = visamgr_applications.client_id where clients.client_office = '."'$client_office'");

        $response = [
            'message'=> 'great!',
            'attributes'=>$application_location
            ];

            return response($response, 200);


     }

     public function top30Applications($userid){


        if (is_numeric($userid)) {

            if($userid == 1){

                $applications  = DB::table('visamgr_applications')
                ->leftJoin('clients', 'visamgr_applications.CLIENT_ID', '=', 'clients.id')
                ->leftJoin('visamgr_branches', 'clients.client_office', '=', 'visamgr_branches.id')
                ->leftJoin('invoicings', 'visamgr_applications.APPTYPE_ID', '=', 'invoicings.APPLICATION_ID')
                ->select('visamgr_applications.*', 'visamgr_branches.LOCATION_CODE', 'invoicings.PAYMENT_STATUS',
          'invoicings.INVOICE_NUMBER', 'invoicings.PAYMENT_AMOUNT')
                ->groupBy('visamgr_applications.APPLICATION_ID')
              //  ->orderBy('visamgr_applications.created_at','desc','visamgr_applications.APPLICATION_ID','desc')
                ->orderBy('visamgr_applications.created_at','desc')
                ->take(30)->get();

                $applications = $this->convert_from_latin1_to_utf8_recursively($applications);

                $response = [
                    'message'=> 'great!',
                    'attributes'=>$applications
                    ];

                    return response($response, 200);
            }

            else{

            $user_branch = User::where('id',$userid)->value('branch_id');
            $user_other_branches = DB::table('user_locations')->where('user_id', $userid)->value('location_id');
            $Decoded_user_other_branches = json_decode($user_other_branches, true);

            $branches = array($user_branch);

            if(count($Decoded_user_other_branches)>0){
                foreach($Decoded_user_other_branches as $key=>$other_branches) {
                    array_push($branches, $other_branches['value']);
                   }

            }



        $applications  = DB::table('visamgr_applications')
        ->leftJoin('clients', 'visamgr_applications.CLIENT_ID', '=', 'clients.id')
        ->leftJoin('visamgr_branches', 'clients.client_office', '=', 'visamgr_branches.id')
        ->leftJoin('invoicings', 'visamgr_applications.APPTYPE_ID', '=', 'invoicings.APPLICATION_ID')
        ->select('visamgr_applications.*', 'visamgr_branches.LOCATION_CODE', 'invoicings.PAYMENT_STATUS',
  'invoicings.INVOICE_NUMBER', 'invoicings.PAYMENT_AMOUNT')
        ->whereIn('clients.client_office', $branches)
        ->groupBy('visamgr_applications.APPLICATION_ID')
        //  ->orderBy('visamgr_applications.created_at','desc','visamgr_applications.APPLICATION_ID','desc')
        ->orderBy('visamgr_applications.created_at','desc')
        ->take(30)->get();

        $applications = $this->convert_from_latin1_to_utf8_recursively($applications);

        $response = [
            'message'=> 'great!',
            'attributes'=>$applications
            ];

            return response($response, 200);
     }

    }

     else {
        $response =([
            'Message' => 'Invalid User ID'
        ]);

        return response($response, 400);
    }
    }

}
