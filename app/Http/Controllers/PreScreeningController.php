<?php

namespace App\Http\Controllers;

use App\Application;
use App\Client;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\PreScreening;
use Illuminate\Support\Facades\Auth;
use App\Messages;
use App\Notifications\PrescreeningNotification;
use App\Notifications\SubmitPrescreeningNotifcation;
use App\Notifications\DeclinePrescreeningNotification;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Exists;

class PreScreeningController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {

        if (is_numeric($id)) {

            if($id == 1){
                $prescreening = DB::table('pre_screenings')
                ->leftJoin('visamgr_branches', 'pre_screenings.client_office', '=', 'visamgr_branches.id')
                ->select('pre_screenings.*','visamgr_branches.LOCATION_CODE')
               // ->whereIn('pre_screenings.client_office', $branches)
                ->get();

                return $this->convert_from_latin1_to_utf8_recursively($prescreening);

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



        $prescreening = DB::table('pre_screenings')
        ->leftJoin('visamgr_branches', 'pre_screenings.client_office', '=', 'visamgr_branches.id')
        ->select('pre_screenings.*','visamgr_branches.LOCATION_CODE')
        ->whereIn('pre_screenings.client_office', $branches)
        ->get();

        return $this->convert_from_latin1_to_utf8_recursively($prescreening);

        }
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


    public function savePrescreenDraft(Request $request){

       //$client_id = $request->session()->get('id');

       $client_id = $request->id;


        if($request->how_we_can_help_you_question=='other'||$request->how_we_can_help_you_question=='Other'){

            /*$request->validate([
                'how_we_can_help_you'=>'required|string|max:200'

            ]);*/
            //update record in table
            DB::table('pre_screenings')
            ->where('client_id', $client_id)
            ->update([
         'how_we_can_help_you'=>$request->how_we_can_help_you,
               ]);

        }

        if($request->residency_question=='no'||$request->residency_question=='No'){

            // $request->validate([
            //     'type_of_permission'=>'required|string',
            //     'expiry'=>'required|date'

            // ]);
             //update record in table
             DB::table('pre_screenings')
             ->where('client_id', $client_id)
             ->update([
                'type_of_permission'=>$request->type_of_permission,
                'expiry'=>$request->expiry
                ]);
        }

        if($request->english_proficiency=='yes'||$request->english_proficiency=='Yes'){
            // $request->validate([
            //     'english_proficiency_level'=>'required|string'

            // ]);

            DB::table('pre_screenings')
            ->where('client_id', $client_id)
            ->update([
               'english_proficiency_level'=>$request->english_proficiency_level

               ]);


        }

        if($request->visa_refusal=='yes'||$request->visa_refusal=='Yes'){
            // $request->validate([
            //     'visa_refusal_reason'=>'required|string|max:200'

            // ]);

            DB::table('pre_screenings')
            ->where('client_id', $client_id)
            ->update([
               'visa_refusal_reason'=>$request->visa_refusal_reason

               ]);


        }



        // update prescreened_status to 1
            DB::table('pre_screenings')
            ->where('client_id', $client_id)
            ->update(['prescreened_status' => 1,
                    'how_we_can_help_you_question'=>$request->how_we_can_help_you_question,
                    'residency_question'=>$request->residency_question,
                    'dob'=>$request->dob,
                    'english_proficiency'=>$request->english_proficiency,
                    'other_languages'=>$request->other_languages,
                    'visa_refusal'=>$request->visa_refusal,
                    'in_your_own_words'=>$request->in_your_own_words,
                    'updated_at' => Carbon::now()
            ]);

         $response =([
            'Message' => 'Pre-Screening saved as draft'
        ]);

        return response($response, 201);
    }

    public function submitPrescreen(Request $request){

        //$client_id = $request->session()->get('id');
        $client_id = $request->id;


       /*if(session()->get('id')!=$client_id){

        $response =([
            'Message' => 'Re-login'
        ]);
        return response($response, 401);

      }*/

        $client_prescreen= PreScreening::find($client_id);

        $prescreening_status  =  PreScreening::where('client_id',$client_id)
        ->value('prescreened_status');

        //Check if prescreening_status is in draft, then insert into prescreening table and update prescreening_status =2
        if($prescreening_status==0){

             //Validate entry into Prescreening table
                    //Validate entry into Prescreening table
        $request->validate([
            'how_we_can_help_you_question'=>'required',
             'residency_question'=>'string',
             'dob'=>'string',
             'english_proficiency'=>'string',
            // 'other_languages'=>'string',
             'visa_refusal'=>'string',
           'in_your_own_words'=>'string|max:200'
    ]);

    if($request->how_we_can_help_you_question=='other'||$request->how_we_can_help_you_question=='Other'){

        $request->validate([
            'how_we_can_help_you'=>'required|string|max:200'

        ]);
        //update record in table
        DB::table('pre_screenings')
        ->where('client_id', $client_id)
        ->update([
     'how_we_can_help_you'=>$request->how_we_can_help_you,
           ]);

    }

    if($request->residency_question == "no" || $request->residency_question == "No"){

        $request->validate([
            'type_of_permission'=>'required|string',
            'expiry'=>'required|date'

        ]);
         //update record in table
         DB::table('pre_screenings')
         ->where('client_id', $client_id)
         ->update([
            'type_of_permission'=>$request->type_of_permission,
            'expiry'=>$request->expiry
            ]);
    }

    if($request->english_proficiency=='yes'||$request->english_proficiency=='Yes'){
        $request->validate([
            'english_proficiency_level'=>'required|string'

        ]);

        DB::table('pre_screenings')
        ->where('client_id', $client_id)
        ->update([
           'english_proficiency_level'=>$request->english_proficiency_level

           ]);


    }

    if($request->visa_refusal=='yes'||$request->visa_refusal=='Yes'){
        $request->validate([
            'visa_refusal_reason'=>'required|string|max:200'

        ]);

        DB::table('pre_screenings')
        ->where('client_id', $client_id)
        ->update([
           'visa_refusal_reason'=>$request->visa_refusal_reason

           ]);

    }

        DB::table('pre_screenings')
            ->where('client_id', $client_id)
            ->update(['prescreened_status' => 2,
                    'how_we_can_help_you_question'=>$request->how_we_can_help_you_question,
                    'residency_question'=>$request->residency_question,
                    'dob'=>$request->dob,
                    'english_proficiency'=>$request->english_proficiency,
                    'other_languages'=>$request->other_languages,
                    'visa_refusal'=>$request->visa_refusal,
                    'in_your_own_words'=>$request->in_your_own_words,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
             ]);


             $MESSAGE_SUBJECT = 'Pre-Screening Submited';

             $MESSAGE_TAG = 'General';

             $uuid = Str::uuid()->toString();


             $client_name = Client::where('id', $client_id)
             ->value('first_name');

             $toAddress = Client::where('id', $client_id)
             ->value('email');

             $message = "Dear ".$client_name.", <br/><br/>" ."Thank you for submitting your pre-screening request. <br /> An agent will be in touch with you shortly. <br /><br/> Kind regards. " ;

             DB::table('messages')->insert([
                 'MESSAGE'=>$message,
                 'MESSAGE_SUBJECT'=>$MESSAGE_SUBJECT,
                 'MESSAGE_TAG'=>$MESSAGE_TAG,
                 'SENDER'=>'i-visas',
                 'CONVERSATION_ID'=>$uuid,
                 'RECEIPIENT'=>$client_id,
                 'created_at' => Carbon::now(),
                 'updated_at' => Carbon::now(),
                 ]);

                 $itemId = DB::getPdo()->lastInsertId();

             //Send email to client and update notification table
             $message = Messages::find($itemId);
             $client = Client::findOrFail($client_id);
             $client->notify(new SubmitPrescreeningNotifcation($client, $client_name, $toAddress));

             DB::table('notifications')->insert([
                 'DATA'=>$message,
                 'MESSAGE_ID'=>$itemId,
                 'CONVERSATION_ID'=>$uuid,
                 'created_at' => Carbon::now(),
                 'updated_at' => Carbon::now(),
             ]);

             $response =([
                'Message' => 'Pre-screening Submitted, pending approval'
            ]);

            return response($response, 200);

        }
        //Check if prescreening_status is in draft, then update table with respective values and update prescreening_status =2
        elseif($prescreening_status==1){

                 //Validate entry into Prescreening table
                    //Validate entry into Prescreening table
        $request->validate([
            'how_we_can_help_you_question'=>'required',
             'residency_question'=>'string',
             'dob'=>'string',
             'english_proficiency'=>'string',
            // 'other_languages'=>'string',
             'visa_refusal'=>'string',
           'in_your_own_words'=>'string|max:200'
    ]);

    if($request->how_we_can_help_you_question=='other'||$request->how_we_can_help_you_question=='Other'){

        $request->validate([
            'how_we_can_help_you'=>'required|string|max:200'

        ]);
        //update record in table
        DB::table('pre_screenings')
        ->where('client_id', $client_id)
        ->update([
     'how_we_can_help_you'=>$request->how_we_can_help_you,
           ]);

    }

    if($request->residency_question=='no'||$request->residency_question=='No'){

        $request->validate([
            'type_of_permission'=>'required|string',
            'expiry'=>'required|date'

        ]);
         //update record in table
         DB::table('pre_screenings')
         ->where('client_id', $client_id)
         ->update([
            'type_of_permission'=>$request->type_of_permission,
            'expiry'=>$request->expiry
            ]);
    }

    if($request->english_proficiency=='yes'||$request->english_proficiency=='Yes'){
        $request->validate([
            'english_proficiency_level'=>'required|string'

        ]);

        DB::table('pre_screenings')
        ->where('client_id', $client_id)
        ->update([
           'english_proficiency_level'=>$request->english_proficiency_level

           ]);


    }

    if($request->visa_refusal=='yes'||$request->visa_refusal=='Yes'){
        $request->validate([
            'visa_refusal_reason'=>'required|string|max:200'

        ]);

        DB::table('pre_screenings')
        ->where('client_id', $client_id)
        ->update([
           'visa_refusal_reason'=>$request->visa_refusal_reason

           ]);

    }

    // update prescreened_status to 2
             DB::table('pre_screenings')
            ->where('client_id', $client_id)
            ->update(['prescreened_status' => 2,
                    'how_we_can_help_you_question'=>$request->how_we_can_help_you_question,
                    'residency_question'=>$request->residency_question,
                    'dob'=>$request->dob,
                    'english_proficiency'=>$request->english_proficiency,
                    'other_languages'=>$request->other_languages,
                    'visa_refusal'=>$request->visa_refusal,
                    'in_your_own_words'=>$request->in_your_own_words,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
             ]);


 $MESSAGE_SUBJECT = 'Pre-screening Submited';

        $MESSAGE_TAG = 'General';

        $uuid = Str::uuid()->toString();


        $client_name = Client::where('id', $client_id)
        ->value('first_name');

        $toAddress = Client::where('id', $client_id)
        ->value('email');

        $message = "Dear ".$client_name.", <br/><br/>" ."Thank you for submitting your pre-screening request. <br /> An agent will be in touch with you shortly. <br /><br/> Kind regards. " ;


        DB::table('messages')->insert([
            'MESSAGE'=>$message,
            'MESSAGE_SUBJECT'=>$MESSAGE_SUBJECT,
            'MESSAGE_TAG'=>$MESSAGE_TAG,
            'SENDER'=>'i-visas',
            'CONVERSATION_ID'=>$uuid,
            'RECEIPIENT'=>$client_id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            ]);

            $itemId = DB::getPdo()->lastInsertId();

        //Send email to client and update notification table
        $message = Messages::find($itemId);
        $client = Client::findOrFail($client_id);
        $client->notify(new SubmitPrescreeningNotifcation($client, $client_name, $toAddress));

        DB::table('notifications')->insert([
            'DATA'=>$message,
            'MESSAGE_ID'=>$itemId,
            'CONVERSATION_ID'=>$uuid,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);




             $response =([
                'Message' => 'Pre-screening Submitted, pending approval'
            ]);

            return response($response, 200);
        }
        else{
            //You can't do anything actually ;)
        }
    }

//Query Client by id
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */



    public function show($id)
    {
        return PreScreening::find($id);
    }

   /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $client_id)
    {
        $client = PreScreening::find($client_id);
        // if($request){

        // }
        $client->update($request->all());

        return $client;
    }


     //Search Client by name
     public function searchByClientID($id)
     {

         return PreScreening::where('client_id',$id)->get();

     }

    public function search($search, $id)
    {


            //  $query = DB::table('clients')
            //          ->rightJoin('pre_screenings','clients.id','=','pre_screenings.client_id')
            //          ->where('pre_screenings.client_email','like','%'.$search.'%',
            //                   'or', 'pre_screenings.client_first_name','like','%'.$search.'%','or',
            //                   'pre_screenings.client_last_name','like','%'.$search.'%','or',
            //                   'clients.client_office','like','%'.$search.'%')
            //         ->select('clients.client_office','pre_screenings.*')
            //          ->get();


        if (is_numeric($id)) {

            if($id == 1){
                $query = DB::table('pre_screenings')
                ->leftJoin('visamgr_branches', 'pre_screenings.client_office', '=', 'visamgr_branches.id')
                ->select('pre_screenings.*','visamgr_branches.LOCATION_CODE')
                //->whereIn('pre_screenings.client_office', $branches)
                ->where(function ($query) use($search) {
                $query->where('pre_screenings.client_email','like','%'.$search.'%')
                ->orWhere('pre_screenings.client_first_name','like','%'.$search.'%')
                ->orWhere('pre_screenings.client_last_name','like','%'.$search.'%')
                ->orWhere('visamgr_branches.LOCATION_CODE','like','%'.$search.'%');
                })->get();

                 $response = [
                    'message'=> 'great!',
                    'attributes'=>$this->convert_from_latin1_to_utf8_recursively($query)
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

            if(count($branches) > 0) {
                    $query = DB::table('pre_screenings')
                    ->leftJoin('visamgr_branches', 'pre_screenings.client_office', '=', 'visamgr_branches.id')
                    ->select('pre_screenings.*','visamgr_branches.LOCATION_CODE')
                    ->whereIn('pre_screenings.client_office', $branches)
                    ->where(function ($query) use($search) {
                    $query->where('pre_screenings.client_email','like','%'.$search.'%')
                    ->orWhere('pre_screenings.client_first_name','like','%'.$search.'%')
                    ->orWhere('pre_screenings.client_last_name','like','%'.$search.'%')
                    ->orWhere('visamgr_branches.LOCATION_CODE','like','%'.$search.'%');
                    })->get();

                     $response = [
                        'message'=> 'great!',
                        'attributes'=>$this->convert_from_latin1_to_utf8_recursively($query)
                        ];

                        return response($response, 200);
            }
            else {
                $response = [
                    'message'=> 'great!',
                    'attributes'=>$this->convert_from_latin1_to_utf8_recursively($branches)
                    ];

                    return response($response, 200);
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



     Public function preScreeningByClientLocation(Request $request){
        $client_office = $request->client_office;


        $prescreening_location = DB::table('pre_screenings')
        ->leftJoin('visamgr_branches', 'pre_screenings.client_office', '=', 'visamgr_branches.id')
        ->select('pre_screenings.*','visamgr_branches.LOCATION_CODE')
        ->where(function ($query) use($client_office) {
           $query->where('visamgr_branches.LOCATION_CODE','like','%'.$client_office.'%');
        })->get();
        $response = [
            'message'=> 'great!',
            'attributes'=>$this->convert_from_latin1_to_utf8_recursively($prescreening_location)
            ];

            return response($response, 200);

     }

     public function top30Prescreening($id){

        if (is_numeric($id)) {

            if($id == 1){
                $prescreenings  = DB::select('SELECT PS.*, BR.LOCATION_CODE FROM pre_screenings AS PS LEFT JOIN visamgr_branches AS BR ON PS.client_office = BR.id  ORDER BY created_at DESC LIMIT 30 ');

                $response = [
                    'message'=> 'great!',
                    'attributes'=>$this->convert_from_latin1_to_utf8_recursively($prescreenings)
                    ];

                return response($response, 200);

            }

            else{

            $user_branch = User::where('id',$id)->value('branch_id');
            $user_other_branches = DB::table('user_locations')->where('user_id', $id)->value('location_id');
            $Decoded_user_other_branches = json_decode($user_other_branches, true);

            $branches = array($user_branch);

            if(count($Decoded_user_other_branches)>0){
                foreach($Decoded_user_other_branches as $key=>$other_branches) {
                    array_push($branches, $other_branches['value']);
                   }

            }


             if(count($branches) > 0){

            $prescreenings  = DB::select('SELECT PS.*, BR.LOCATION_CODE FROM pre_screenings AS PS LEFT JOIN visamgr_branches AS BR ON PS.client_office = BR.id WHERE PS.client_office IN ('.implode(',', $branches).') ORDER BY created_at DESC LIMIT 30 ');

            $response = [
                'message'=> 'great!',
                'attributes'=>$this->convert_from_latin1_to_utf8_recursively($prescreenings)
                ];

            return response($response, 200);

            }
            else {
                $response = [
                    'message'=> 'total Top 30 Clients are '. count($branches),
                    'attributes'=>$this->convert_from_latin1_to_utf8_recursively($branches)
                    ];

                return response($response, 200);
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


    //Admin Role, to Approve or Decline Screenings
       public function approveScreening(Request $request){

        /// return $request->all();

        $user_id = $request->id;
        $client_id = $request->client_id;

        $username = User::where('id',$user_id)->value('first_name');



        $prescreening_status  =  PreScreening::where('client_id',$client_id)
        ->value('prescreened_status');



        if($prescreening_status==3){
            $response = [
                'message'=> 'Client has already been Pre-Screened!',

                ];

                return response($response, 200);
        }

        elseif($prescreening_status==2){

            //get client id, per the id selected
            $client_id  =  PreScreening::where('client_id',$client_id)
            ->value('client_id');

            if(!$client_id){

                    $response = [
                'message'=> 'Client does not exist!',

                ];

                return response($response, 200);
                }


                // Update client Pre_Screenings table record
                DB::table('pre_screenings')
                ->where('client_id', $client_id)
                ->update(['prescreened' => 1,
                    'prescreened_status'=>3,
                    'USER'=>$username
                ]);



        $MESSAGE_SUBJECT = 'Pre-screening Approved';

        $MESSAGE_TAG = 'General';

        $uuid = Str::uuid()->toString();

        $fromAddress = User::where('id',$user_id)
        ->value('email');

        $client_name = Client::where('id', $client_id)
        ->value('first_name');

        $toAddress = Client::where('id', $client_id)
        ->value('email');

        $message = "Dear ".$client_name.",<br /><br />" ."Congratulations, your Pre-screening has been approved! <br />kindly proceed to [ <a href='".env('FRONTEND_URL')."/dashboard/application_list'><strong>create an application</strong></a> ] <br /><br /> Kind regards. " ;

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
        $client->notify(new PreScreeningNotification($client, $message,$client_name, $toAddress, $fromAddress, $MESSAGE_SUBJECT));

        DB::table('notifications')->insert([
            'DATA'=>$message,
            'MESSAGE_ID'=>$itemId,
            'CONVERSATION_ID'=>$uuid,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);


                $response = [
                    'message'=> 'Client Screening Approved!',

                    ];

                    return response($response, 200);

            }

        else{
            $response = [
                'message'=> 'Client is yet to submit Pre-Screening!',

                ];

                return response($response, 200);
        }


    }

    public function declineScreening(Request $request){
        $user_id = $request->id;
         //get id of client to update
       $client_id  =  PreScreening::where('client_id',$request->client_id)->value('client_id');

       if(!$client_id){

            $response = [
                    'message'=> 'Client does not exist!',

                ];

                return response($response, 200);
       }

    // Update client Pre_Screenings table record
       DB::table('pre_screenings')
                        ->where('client_id', $client_id)
                        ->update(['prescreened_status' => 1
                    ]);

                    $toAddress = Client::where('id', $client_id)
                    ->value('email');




                    $client_name = Client::where('id', $client_id)
                    ->value('first_name');


                    $uuid = Str::uuid()->toString();



                    $request->validate([
                        'MESSAGE'=>'required|string',
                    ]);

                    $message = "Dear ".$client_name.",<br /><br />" ."Unfortunately, your Pre-screening has been declined! <br />Kindly refer to the below remarks provided by Agent <br/> <hr /> <i>".$request->MESSAGE."<i/> <hr /><br /> Kind regards. " ;




                    DB::table('Messages')->insert([
                        'MESSAGE'=>$message,
                        'MESSAGE_SUBJECT'=>'Pre-screening Declined',
                        'MESSAGE_TAG'=>'General',
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
                     $client->notify(new DeclinePrescreeningNotification($client, $message, $client_name, $toAddress));

                     //Store Message notification in database
                       DB::table('notifications')->insert([
                        'DATA'=>$request->MESSAGE,
                        'MESSAGE_ID'=>$itemId,
                        'CONVERSATION_ID'=>$uuid,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);


        $response = [
            'message'=> 'Client Screening Declined!',

        ];

        return response($response, 200);

    }

}
