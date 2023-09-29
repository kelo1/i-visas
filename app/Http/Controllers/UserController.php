<?php

namespace App\Http\Controllers;

use App\PreScreening;
use App\Client;
use App\User;
use App\Location;
use App\Role;
use App\visamgr_branches;
use App\visamgr_applications;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Messages;
use App\Notifications\PrescreeningNotification;
use App\Notifications\ApplicationNotification;
use App\Notifications\SubmitPrescreeningNotifcation;
use App\Notifications\DeclinePrescreeningNotification;
use App\UserReference;
use App\BranchReference;
use Illuminate\Support\Str;
use Carbon\Carbon;
use DateTime;
use function PHPUnit\Framework\isEmpty;

class UserController extends Controller
{
    //Enable middleware route for authentication
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return User::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

     //Create Super Admin User
    public function createUser(Request $request){

       $user_role = $request->role;

       $locations = $request->countries;

       $user_reference =$request->user_reference_no;

        if(strtolower($user_role) == 'agent'){

            $agentRole = Role::where('name','agent')->first();

            $request->validate([
                'email'=>'email|unique:users',
                'password'=>'required|string|confirmed',
                'status'=>'required',
                'branch_id'=>'required',
            ]);



            $agent= User::create([

                'first_name'=>$request->first_name,
                'last_name'=>$request->last_name,
                'email'=>$request->email,
                'password'=>Hash::make($request->password),
                'status'=>strtoupper($request->status),
                'branch_id'=>$request->branch_id
                //'phone'=>$request->phone
              ]);

              $itemId = DB::getPdo()->lastInsertId();


              if($user_reference!=NULL){
                $request->validate([
                'user_reference_no'=>'string|unique:user_references',
                ]);

                 //insert user reference
              DB::table('user_references')->insert([
                'user_id' => $itemId,
                'user_reference_no' => $user_reference,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            }


              if($locations !== ""){

                DB::table('user_locations')->insert([
                    'user_id' => $agent->id,
                    'location_id' => $locations,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
             }


            $agent->roles()->attach($agentRole);



            $response = [
                'first_name'=>$request->first_name
            ];

            return response($response, 201);
        }


        if(strtolower($user_role) == 'admin'){

            $adminRole = Role::where('name','admin')->first();

            $request->validate([
                'email'=>'string|unique:users',
                'password'=>'required|string|confirmed',
                'status'=>'required',
                'branch_id'=>'required',
                'user_reference_no'=>'string|unique:user_references',
            ]);

            $admin= User::create([
                'first_name'=>$request->first_name,
                'last_name'=>$request->last_name,
                'email'=>$request->email,
                'password'=>Hash::make($request->password),
                'status'=>strtoupper($request->status),
                'branch_id'=>$request->branch_id
              ]);


              $itemId = DB::getPdo()->lastInsertId();

              if($user_reference!=NULL){
                $request->validate([
                'user_reference_no'=>'string|unique:user_references',
                ]);

                 //insert user reference
              DB::table('user_references')->insert([
                'user_id' => $itemId,
                'user_reference_no' => $user_reference,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            }

              if($locations !== ""){

                DB::table('user_locations')->insert([
                    'user_id' => $admin->id,
                    'location_id' => $locations,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
             }

              $admin->roles()->attach($adminRole);

              //$admin_token =  $admin->createToken($admin->first_name)->plainTextToken;

              $response = [
                'first_name'=>$request->first_name
            ];

            return response($response, 201);
        }



    }



    public function generatePassword()
    {
        do {
            $comb = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
            $shfl = str_shuffle($comb);
            $pwd = substr($shfl,0,8);
        } while (Client::where("password", "=", $pwd)->first());

        return $pwd;
    }


    // Create Client
     public function createClient(Request $request)
    {
       $user_id = $request->user_id;

        $username = User::where('id',$user_id)->value('first_name');

       //Validate Entery request
       $request->validate([
        'first_name'=>'required|string',
        'last_name'=>'required|string',
        'email'=>'required|string',
        //'password'=>'required|string|confirmed',
        'phone'=>'required|string|unique:clients,phone',
        //phone number

    ]);

    $client_password = $this->generatePassword();

    $client = Client::create([

        'first_name'=>$request->first_name,
        'middle_name'=>$request->middle_name,
        'last_name'=>$request->last_name,
        'email'=>$request->email,
        'password'=>Hash::make($client_password),
        'phone'=>$request->phone,
        'country'=>$request->country,
        'agent_reference'=>$request->agent_reference,
        'created_by'=>'USER',
        'USER'=>$username,
        'sms_verified'=>1,
        'email_verified_at'=>Carbon::now()
        //'remember_token'=>$token
      ]);

      $client_id = Client::where('email',$request['email'])->value('id');
      $client_fname = Client::where('id',$client_id)->value('first_name');
      $client_mname = Client::where('id',$client_id)->value('middle_name');
      $client_lname = Client::where('id',$client_id)->value('last_name');
      $client_email = Client::where('id',$client_id)->value('email');
      $client_phone = Client::where('id',$client_id)->value('phone');
      $client_location = Client::where('id',$client_id)->value('country');

      //Populate Prescreening table
      DB::table('pre_screenings')->insert([
          'client_first_name' => $client_fname,
          'client_middle_name' => $client_mname,
          'client_last_name' => $client_lname,
          'client_email' => $client_email,
          'client_phone'=>$client_phone,
          'client_id'=>$client_id,
          'country_of_residence'=>$client_location,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now()
      ]);

//Assign client office
$user_reference_temp = UserReference::where('user_reference_no', $request->agent_reference)->first();

$branch_reference_temp = BranchReference::where('branch_reference_no', $request->agent_reference)->first();


if($branch_reference_temp){

    $branch_reference = $request->agent_reference;

    $branchid_assigned = BranchReference::where('branch_reference_no',$branch_reference)->value('branch_id');

    $branch_manager = visamgr_branches::where('id', $branchid_assigned)->value('DEFAULT_USER');

    $client_office = Location::where('COUNTRY',$client_location)->first();

    $client->location()->attach($client_office);


     DB::table('clients')
     ->where('id', $client_id)
     ->update(['agent_reference' => $branch_reference,
                  'client_office' => $branchid_assigned,
                  'DEFAULT_USER' => $branch_manager
     ]);



     DB::table('pre_screenings')
     ->where('client_id', $client_id)
     ->update(['client_office' => $branchid_assigned]);


}

elseif(!$branch_reference_temp && $user_reference_temp){

  $agent_reference = $request->agent_reference;

    $userid_assigned = DB::table('user_references')->where('user_reference_no', $agent_reference)->value('user_id');

    $client_office = Location::where('COUNTRY',$client_location)->first();

    $client->location()->attach($client_office);

    $country_id  = DB::table('client_location')->where('client_id',$client_id)->value('location_id');

    $branch_id = User::where('id',$userid_assigned)->value('branch_id');


    DB::table('clients')
     ->where('id', $client_id)
     ->update(['agent_reference' => $agent_reference,
                  'DEFAULT_USER' => $userid_assigned,
                  'client_office'=>$branch_id
     ]);

     DB::table('pre_screenings')
     ->where('client_id', $client_id)
     ->update(['client_office' => $branch_id]);


}
elseif(!$branch_reference_temp && !$user_reference_temp && $request->defaultbranch!=NULL){

    $check_anglo_german_id = visamgr_branches::findOrFail($request->defaultbranch);

    $userid_assigned = DB::table('visamgr_branches')->where('id',  $request->defaultbranch)->value('DEFAULT_USER');


   if($check_anglo_german_id==true){


       DB::table('clients')
        ->where('id', $client_id)
        ->update(['client_office' => $request->defaultbranch,
                   'DEFAULT_USER'=> $userid_assigned]);


        DB::table('pre_screenings')
        ->where('client_id', $client_id)
        ->update(['client_office' => $request->defaultbranch]);

   }
   else{
    return response(["Message" => "The set default branch does not exist"], 404);
   }


}
else{

    $client_office = Location::where('COUNTRY',$client_location)->first();

    $client->location()->attach($client_office);

    $country_id  = DB::table('client_location')->where('client_id',$client_id)->value('location_id');

    $branch_id = Location::where('id',$country_id)->value('BRANCH_ID');

    //$default_location = visamgr_branches::where('id',$branch_id)->value('LOCATION_CODE');
    //Update client & pre-screening table with client office
        DB::table('clients')
        ->where('id', $client_id)
        ->update(['client_office' => $branch_id]);


        DB::table('pre_screenings')
        ->where('client_id', $client_id)
        ->update(['client_office' => $branch_id]);

}

$new_client_phone = $client_phone;


   $params = array(
    'credentials' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
    ],
    'region' => env('AWS_DEFAULT_REGION'),
    'version' => 'latest'
);
$sns = new \Aws\Sns\SnsClient($params);

$args = array(
        "MessageAttributes" =>[



        'AWS.SNS.SMS.SMSType'=>[
            'DataType' => 'String',
            'StringValue' =>'Transactional'
            ]

        ],
    "Message" => "Your Password is : ".$client_password,
    "PhoneNumber" => $new_client_phone
);

$result = $sns->publish($args);



   return response([
    'message'=>'Client Created successfully',
    'id'=>$client_id,
    //'token'=>$token
], 201);



    }


    public function submitPrescreen(Request $request){

        //$client_id = $request->session()->get('id');
        $client_id = $request->id;

        $user_id = $request->user_id;


        $username = User::where('id',$user_id)->value('first_name');

       /*if(session()->get('id')!$client_id){

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
             'other_languages'=>'string',
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

        DB::table('pre_screenings')
            ->where('client_id', $client_id)
            ->update(['prescreened' => 1,
                'prescreened_status' => 3,
                    'how_we_can_help_you_question'=>$request->how_we_can_help_you_question,
                    'residency_question'=>$request->residency_question,
                    'dob'=>$request->dob,
                    'english_proficiency'=>$request->english_proficiency,
                    'other_languages'=>$request->other_languages,
                    'visa_refusal'=>$request->visa_refusal,
                    'in_your_own_words'=>$request->in_your_own_words,
                    'created_by'=>$username,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
             ]);


             $MESSAGE_SUBJECT = 'Pre-Screening Approved';

             $MESSAGE_TAG = 'Pre-Screening';

             $uuid = Str::uuid()->toString();


             $client_name = Client::where('id', $client_id)
             ->value('first_name');

             $toAddress = Client::where('id', $client_id)
             ->value('email');

             $message = "Dear ".$client_name."," ."Your Preescreening has been approved. Kindly proceed to create your application " ;

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
                'Message' => 'Pre-Screening Approved'
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
             'other_languages'=>'string',
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

    if($request->residency_question=='yes'||$request->residency_question=='Yes'){

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

    // update prescreened_status to 3
             DB::table('pre_screenings')
            ->where('client_id', $client_id)
            ->update(['prescreened' => 1,
                    'prescreened_status' => 3,
                    'how_we_can_help_you_question'=>$request->how_we_can_help_you_question,
                    'residency_question'=>$request->residency_question,
                    'dob'=>$request->dob,
                    'english_proficiency'=>$request->english_proficiency,
                    'other_languages'=>$request->other_languages,
                    'visa_refusal'=>$request->visa_refusal,
                    'in_your_own_words'=>$request->in_your_own_words,
                    'created_by'=>$username,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
             ]);


 $MESSAGE_SUBJECT = 'Pre-Screening Approved';

        $MESSAGE_TAG = 'Pre-Screening';

        $uuid = Str::uuid()->toString();


        $client_name = Client::where('id', $client_id)
        ->value('first_name');

        $toAddress = Client::where('id', $client_id)
        ->value('email');

        $message = "Dear ".$client_name."," ."Your Preescreening has been approved. Kindly proceed to create your application" ;

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
                'Message' => 'Pre-Screening Approved'
            ]);

            return response($response, 200);
        }
        else{
            //You can't do anything actually ;)
        }
    }


    public function generateApplicationID()
    {
        do {
            $application_id = random_int(100000, 999999);
        } while (visamgr_applications::where("APPTYPE_ID", "=", $application_id)->first());

        return $application_id;
    }

//create application
public function createApplication(Request $request){
    $client_id = $request->id;


    $user_id = $request->user_id;


    $username = User::where('id',$user_id)->value('first_name');


    $app_status=visamgr_applications::where('CLIENT_ID',$client_id)->value('APPSTATUS');

    $app_id = visamgr_applications::where('CLIENT_ID',$client_id)->value('APPTYPE_ID');


    if($client_id && (is_numeric($app_status) && $app_status < 3)){
        $response =([
            'Message' => 'You already have an existing application!',
            'attributes'=>  visamgr_applications::where('APPTYPE_ID',$app_id)->get(),

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
    'created_by'=>$username
]);


return response($response, 201);
//    // echo($app);
}



public function submitApplication(Request $request){

    $id = $request->APPTYPE_ID;

    $user_id = $request->user_id;

    $client_id = $request->client_id;

    $username = User::where('id',$user_id)->value('first_name');

        $application_status  =  visamgr_applications::where('APPTYPE_ID', $id)
        ->value('APPSTATUS');

        //Check if application_status is in draft, then insert into prescreening table and update prescreening_status =2
        if($application_status==0){

             //Validate entry into Application table
           /*  $request->validate([
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
        'LOCATION_NAME'=>'required|string',
        'LOCATION_CODE'=>'required|string',
        'FAX'=>'required|string',
        'VATRATE'=>'required|string',
        'COUNTRY_PREFIX'=>'required|string',
        'NUMBER_OF_OTHERROOMS'=>'required|string',
        'DATE_MOVED_IN_ADDRESS'=>'date|required',
        'HOME_QUESTION_ANSWER'=>'string',
        'NUMBER_OF_BEDROOMS'=>'integer|required',
        'WHO_LIVES_THERE'=>'string|required',
        'PREVIOUS_ADDRESS'=>'string',
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
        'QUALIFICATION'=>'string|required',
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
        'RECEIVED_PUBLIC_FUNDS'=>'required|string',
        'GIVE_FALSE_INFO'=>'required|string',
        'WORK_WITHOUT_PERMIT'=>'required|string',
        'USED_DECEPTION'=>'required|string',
        'BREACHED_OTHER_LAWS'=>'required|string',
        'VISA_REFUSAL_QUESTION'=>'string|required',
        'PERMISSION_REFUSAL'=>'string|required',
        'ASYLUM_REFUSAL'=>'required|string',
        'EVER_DEPORTED'=>'required|string',
        'EVER_BANNED'=>'required|string',
        'CRIMINAL_OFFENSE'=>'required|string',
        'PASSPORT_UPLOAD'=>'mimes:doc,docx,pdf,txt,csv|max:2048|required',
        'DEPENDENT_PASSPORT_UPLOAD'=>'mimes:doc,docx,pdf,txt,csv|max:2048|required',
        'UTILITY_BILL_UPLOAD'=>'mimes:doc,docx,pdf,txt,csv|max:2048|required',
        'BRP_UPLOAD'=>'mimes:doc,docx,pdf,txt,csv|max:2048|required',
        'PREVIOUS_VISA_UPLOAD'=>'mimes:doc,docx,pdf,txt,csv|max:2048|required',
        'REFUSAL_LETTER_UPLOAD'=>'mimes:doc,docx,pdf,txt,csv|max:2048|required',
        'EDUCATIONAL_CERT_UPLOAD'=>'mimes:doc,docx,pdf,txt,csv|max:2048|required',
        'ENGLISH_CERT_UPLOAD'=>'mimes:doc,docx,pdf,txt,csv|max:2048|required',
        'MARRIAGE_CERT_UPLOAD'=>'mimes:doc,docx,pdf,txt,csv|max:2048|required',
        'BANK_STATEMENT_UPLOAD'=>'mimes:doc,docx,pdf,txt,csv|max:2048|required',
        'MOTIVATIONAL_LETTER_UPLOAD'=>'mimes:doc,docx,pdf,txt,csv|max:2048|required',
        'RESUME_UPLOAD'=>'mimes:doc,docx,pdf,txt,csv|max:2048|required',
        'ACADEMIC_TRANSCRIPTS_UPLOAD'=>'mimes:doc,docx,pdf,txt,csv|max:2048|required',
        'CAS_LETTERS_UPLOAD'=>'mimes:doc,docx,pdf,txt,csv|max:2048|required',
        'RECOMMENDATION_LETTERS_UPLOAD'=>'mimes:doc,docx,pdf,txt,csv|max:2048|required',
        'RESEARCH_PROPOSAL_UPLOAD '=>'mimes:doc,docx,pdf,txt,csv|max:2048|required',
        'OTHER_UPLOAD'=>'mimes:doc,docx,pdf,txt,csv|max:2048|required'
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
        'BRP_ISSUE_DATE'=>'date|required',
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
        'LANDLORD_EMAIL'=>'string',
        'LANDLORD_MOBILE'=>'string|required',
        'LANDLORD_ADDRESS1'=>'string|required',
        'LANDLORD_ADDRESS2'=>'string|required',
        'LANDLORD_LOCATION_NAME'=>'string|required',
        'LANDLORD_LOCATION_CODE'=>'string|required',
        'LANDLORD_TOWN'=>'string|required',
        'LANDLORD_COUNTY'=>'string|required',
        'LANDLORD_POSTCODE'=>'string|required',
        'LANDLORD_COUNTRY_PREFIX'=>'string|required',
        'LANDLORD_COUNTRY'=>'string|required',
        'LANDLORD_FAX'=>'string|required',
        'LANDLORD_VRATE'=>'string|required',

    ]);
}

if($request->PREVIOUS_ADDRESS){
    $request->validate([
    'TOWN_PREVIOUS'=>'string|required',
    'COUNTY_PREVIOUS'=>'string|required',
    'POSTCODE_PREVIOUS'=>'string|required',
    'COUNTRY_PREVIOUS'=>'string|required',
    'PREVIOUS_ADDRESS2'=>'string|required',
    'PREVIOUS_LOCATION_NAME'=>'string|required',
    'PREVIOUS_LOCATION_CODE'=>'string|required',
    'PREVIOUS_COUNTRY_PREFIX'=>'string|required',
    'PREVIOUS_FAX'=>'string|required',
    'PREVIOUS_VRATE'=>'string|required',

    ]);
}

if(strtoupper($request->DO_YOU_HAVE_CHILDREN) =='YES'){
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

if($request->QUALIFICATION){
    app('App\Http\Controllers\QualificationController')-> store($request);
}

if(strtoupper($request->PASSED_RECOGNIZED_TEST)=='YES'){
    $request->validate([
        'WHAT_TEST_DID_YOU_PASS'=>'string|required',
    ]);
}

if(strtoupper($request->EMPLOYMENT_STATUS)=='EMPLOYED' || strtoupper($request->EMPLOYMENT_QUESTION)=='SELF-EMPLOYED'){
    app('App\Http\Controllers\EmploymentController')-> store($request);
}

if(!empty(strtoupper($request->LAST_FIVE_VISITS))){

    app('App\Http\Controllers\TravelFiveController')-> store($request);
}

if(strtoupper($request->OUT_OF_THE_UK_BEFORE)=='YES'){
    app('App\Http\Controllers\TravelController')-> store($request);
}
if(strtoupper($request->CRIMINAL_OFFENSE)=='YES'){
    app('App\Http\Controllers\CharacterController')-> store($request);
}

if(!empty($request->MEMBERSHIP)){
    app('App\Http\Controllers\MembershipController')-> store($request);
}

if(!empty($request->BANK_NAME)){
    app('App\Http\Controllers\MaintenanceController')-> store($request);
}

if(strtoupper($request->ENTERED_UK_MEANS)=='YES'){

    DB::table('visamgr_applications')
    ->where('APPTYPE_ID', $id)
    ->update([
        'ENTERED_UK_MEANS'=>$request->ENTERED_UK_MEANS,
        'REASON_FOR_ILEGAL_ENTRY'=>$request->REASON_FOR_ILEGAL_ENTRY
    ]);

}

if(strtoupper($request->EVER_STAYED_BEYOND_EXPIRY)=='YES'){

    DB::table('visamgr_applications')
    ->where('APPTYPE_ID', $id)
    ->update([
        'EVER_STAYED_BEYOND_EXPIRY'=>$request->EVER_STAYED_BEYOND_EXPIRY,
        'REASON_FOR_STAYING_BEYOND_EXPIRY'=>$request->REASON_FOR_STAYING_BEYOND_EXPIRY
    ]);

}

if(strtoupper($request->BREACHED_CONDITION_FOR_LEAVE)=='YES'){

    DB::table('visamgr_applications')
    ->where('APPTYPE_ID', $id)
    ->update([
        'BREACHED_CONDITION_FOR_LEAVE'=>$request->BREACHED_CONDITION_FOR_LEAVE,
		 'REASON_FOR_BREACH'=>$request->REASON_FOR_BREACH,
        'BREACH_COUNTRY'=>$request->BREACH_COUNTRY
    ]);

}

if(strtoupper($request->WORK_WITHOUT_PERMIT)=='YES'){

    DB::table('visamgr_applications')
    ->where('APPTYPE_ID', $id)
    ->update([
        'WORK_WITHOUT_PERMIT'=>$request->WORK_WITHOUT_PERMIT,
		 'REASON_FOR_WORK_WITHOUT_PERMIT'=>$request->REASON_FOR_WORK_WITHOUT_PERMIT,
    ]);

}

if(strtoupper($request->RECEIVED_PUBLIC_FUNDS)=='YES'){

    DB::table('visamgr_applications')
    ->where('APPTYPE_ID', $id)
    ->update([
        'RECEIVED_PUBLIC_FUNDS'=>$request->RECEIVED_PUBLIC_FUNDS,
		 'REASON_RECEIVING_FUNDS'=>$request->REASON_RECEIVING_FUNDS,
    ]);

}

if(strtoupper($request->GIVE_FALSE_INFO)=='YES'){

    DB::table('visamgr_applications')
    ->where('APPTYPE_ID', $id)
    ->update([
        'GIVE_FALSE_INFO'=>$request->GIVE_FALSE_INFO,
		 'REASON_FOR_FALSE_INFO'=>$request->REASON_FOR_FALSE_INFO,
    ]);

}

if(strtoupper($request->USED_DECEPTION)=='YES'){

    DB::table('visamgr_applications')
    ->where('APPTYPE_ID', $id)
    ->update([
        'USED_DECEPTION'=>$request->USED_DECEPTION,
         'REASON_FOR_DECEPTION'=>$request->REASON_FOR_DECEPTION,
    ]);

}

if(strtoupper($request->BREACHED_OTHER_LAWS)=='YES'){

    DB::table('visamgr_applications')
    ->where('APPTYPE_ID', $id)
    ->update([
        'BREACHED_OTHER_LAWS'=>$request->BREACHED_OTHER_LAWS,
		 'REASON_FOR_BREACHING__LAWS'=>$request->REASON_FOR_BREACHING__LAWS,
    ]);

}

if(strtoupper($request->VISA_REFUSAL_QUESTION)=='YES'){

    DB::table('visamgr_applications')
    ->where('APPTYPE_ID', $id)
    ->update([
        'VISA_REFUSAL_QUESTION'=>$request->VISA_REFUSAL_QUESTION,
		 'REASON_FOR_REFUSAL'=>$request->REASON_FOR_REFUSAL,
    ]);

}

if(strtoupper($request->PERMISSION_REFUSAL)=='YES'){

    DB::table('visamgr_applications')
    ->where('APPTYPE_ID', $id)
    ->update([
        'PERMISSION_REFUSAL'=>$request->PERMISSION_REFUSAL,
		 'REASON_FOR_PERMISSION_REFUSAL'=>$request->REASON_FOR_PERMISSION_REFUSAL,
    ]);

}

if(strtoupper($request->ASYLUM_REFUSAL)=='YES'){

    DB::table('visamgr_applications')
    ->where('APPTYPE_ID', $id)
    ->update([
        'ASYLUM_REFUSAL'=>$request->ASYLUM_REFUSAL,
		 'REASON_FOR_ASYLUM_REFUSAL'=>$request->REASON_FOR_ASYLUM_REFUSAL,
    ]);

}

if(strtoupper($request->EVER_DEPORTED)=='YES'){

    DB::table('visamgr_applications')
    ->where('APPTYPE_ID', $id)
    ->update([
        'EVER_DEPORTED'=>$request->EVER_DEPORTED,
		 'REASON_FOR_DEPORTATION'=>$request->REASON_FOR_DEPORTATION,
    ]);

}

if(strtoupper($request->EVER_BANNED)=='YES'){

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
        ->update(['APPSTATUS' => 3,
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
              'BRP_ISSUE_DATE'=>$request->BRP_ISSUE_DATE,
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
              'MARRIED_BEFORE_QUESTION'=>$request->MARRIED_BEFORE_QUESTION,
              'PARTNER_MARRIED_BEFORE'=>$request->PARTNER_MARRIED_BEFORE,
              'DO_YOU_HAVE_FAMILY_IN_HOME_COUNTRY'=>$request->DO_YOU_HAVE_FAMILY_IN_HOME_COUNTRY,
              'QUALIFICATION'=>$request->QUALIFICATION,
              'HAVE_DEGREE_IN_ENGLISH'=>$request->HAVE_DEGREE_IN_ENGLISH,
              'PASSED_RECOGNIZED_TEST'=>$request->PASSED_RECOGNIZED_TEST,
              'WHAT_TEST_DID_YOU_PASS'=>$request->WHAT_TEST_DID_YOU_PASS,
              'WHEN_DID_YOU_ENTER_UK'=>$request->WHEN_DID_YOU_ENTER_UK,
              'DID_YOU_ENTER_LEGALLY'=>$request->DID_YOU_ENTER_LEGALLY,
              'VISA_REASON'=>$request->VISA_REASON,
              'VISA_START_DATE'=>$request->VISA_START_DATE,
              'VISA_STATUS'=>$request->VISA_STATUS,
              'OUT_OF_THE_UK_BEFORE'=>$request->OUT_OF_THE_UK_BEFORE,
              'CRIMINAL_OFFENSE'=>$request->CRIMINAL_OFFENSE,
              'updated_at' => Carbon::now()
        ]);



        $MESSAGE_SUBJECT = 'Visa Application Status';

        $MESSAGE_TAG = 'Visa Application';

        $uuid = Str::uuid()->toString();

        $fromAddress = User::where('id',$user_id)
        ->value('email');

        $client_name = Client::where('id', $client_id)
        ->value('first_name');

        $toAddress = Client::where('id', $client_id)
        ->value('email');

        $message = "Dear ".$client_name."," ."Congratulations, your visa application has been approved." ;

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

             $response =([
                'Message' => 'Application Approved!'
            ]);

            return response($response, 201);

        }
        //Check if application_status is in draft, then update table with respective values and update prescreening_status =2
        elseif($application_status==1){

                 //Validate entry into Application table
                 $request->validate([
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
            'LOCATION_NAME'=>'required|string',
            'LOCATION_CODE'=>'required|string',
            'FAX'=>'required|string',
            'VATRATE'=>'required|string',
            'COUNTRY_PREFIX'=>'required|string',
            'NUMBER_OF_OTHERROOMS'=>'required|string',
            'DATE_MOVED_IN_ADDRESS'=>'date|required',
            'HOME_QUESTION_ANSWER'=>'string',
            'NUMBER_OF_BEDROOMS'=>'integer|required',
            'WHO_LIVES_THERE'=>'string|required',
            'PREVIOUS_ADDRESS1'=>'string',
            'PREVIOUS_ADDRESS2'=>'string',
            'PREVIOUS_LOCATION_NAME'=>'string',
            'PREVIOUS_LOCATION_CODE'=>'string',
            'PREVIOUS_COUNTRY_PREFIX'=>'string',
            'PREVIOUS_FAX'=>'string',
            'PREVIOUS_VRATE'=>'string',
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
            'QUALIFICATION'=>'string|required',
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
           /* 'PASSPORT_UPLOAD'=>'mimes:doc,docx,pdf,txt,csv|max:2048|required',
            'DEPENDENT_PASSPORT_UPLOAD'=>'mimes:doc,docx,pdf,txt,csv|max:2048|required',
            'UTILITY_BILL_UPLOAD'=>'mimes:doc,docx,pdf,txt,csv|max:2048|required',
            'BRP_UPLOAD'=>'mimes:doc,docx,pdf,txt,csv|max:2048|required',
            'PREVIOUS_VISA_UPLOAD'=>'mimes:doc,docx,pdf,txt,csv|max:2048|required',
            'REFUSAL_LETTER_UPLOAD'=>'mimes:doc,docx,pdf,txt,csv|max:2048|required',
            'EDUCATIONAL_CERT_UPLOAD'=>'mimes:doc,docx,pdf,txt,csv|max:2048|required',
            'ENGLISH_CERT_UPLOAD'=>'mimes:doc,docx,pdf,txt,csv|max:2048|required',
            'MARRIAGE_CERT_UPLOAD'=>'mimes:doc,docx,pdf,txt,csv|max:2048|required',
            'BANK_STATEMENT_UPLOAD'=>'mimes:doc,docx,pdf,txt,csv|max:2048|required'*/

        ]);


    if(strtoupper($request->NAME_CHANGE_QUESTION)=='YES'){
    app('App\Http\Controllers\NameChangeController')-> nameChange($request);

      }

    if(strtoupper($request->OTHER_NATIONALITY_QUESTION)=='YES'){
        app('App\Http\Controllers\OtherNationalityController')-> store($request);

    }
    if(strtoupper($request->BRP_QUESTION) =='YES'){
        $request->validate([
            'BRP_NUMBER'=>'string|required',
            'BRP_ISSUE_DATE'=>'date|required',
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
            'LANDLORD_NAME'=>'string|required',
           // 'LANDLORD_ADDRESS'=>'string|required',
            'LANDLORD_EMAIL'=>'string',
            'LANDLORD_MOBILE'=>'string|required',
            'LANDLORD_ADDRESS1'=>'string|required',
            'LANDLORD_ADDRESS2'=>'string|required',
            'LANDLORD_LOCATION_NAME'=>'string|required',
            'LANDLORD_LOCATION_CODE'=>'string|required',
            'LANDLORD_TOWN'=>'string|required',
            'LANDLORD_COUNTY'=>'string|required',
            'LANDLORD_POSTCODE'=>'string|required',
            'LANDLORD_COUNTRY_PREFIX'=>'string|required',
            'LANDLORD_COUNTRY'=>'string|required',
            'LANDLORD_FAX'=>'string|required',
            'LANDLORD_VRATE'=>'string|required',

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
        app('App\Http\Controllers\NumberofChildrenController')-> store($request);

    }

    if(strtoupper($request->PARTNER_MARRIED_BEFORE)=='YES'){
        app('App\Http\Controllers\PartnerMarriedBeforeController')-> store($request);
    }

    if($request->WHO_YOU_HAVE_BACK_HOME){
        app('App\Http\Controllers\PeopleAtHomeController')-> store($request);


    }

    if($request->QUALIFICATION){
        app('App\Http\Controllers\QualificationController')-> store($request);
    }

    if(strtoupper($request->PASSED_RECOGNIZED_TEST)=='YES'){
        $request->validate([
            'WHAT_TEST_DID_YOU_PASS'=>'string|required',
        ]);
    }

    if(strtoupper($request->EMPLOYMENT_STATUS)=='EMPLOYED' || strtoupper($request->EMPLOYMENT_QUESTION)=='SELF-EMPLOYED'){
        app('App\Http\Controllers\EmploymentController')-> store($request);
    }

    if(!empty(strtoupper($request->LAST_FIVE_VISITS))){

        app('App\Http\Controllers\TravelFiveController')-> store($request);
    }

    if(strtoupper($request->OUT_OF_THE_UK_BEFORE)=='YES'){
        app('App\Http\Controllers\TravelController')-> store($request);
    }

    if(strtoupper($request->CRIMINAL_OFFENSE)=='YES'){
        app('App\Http\Controllers\CharacterController')-> store($request);
    }

    if(strtoupper($request->ENTERED_UK_MEANS)=='YES'){

        DB::table('visamgr_applications')
        ->where('APPTYPE_ID', $id)
        ->update([
            'ENTERED_UK_MEANS'=>$request->ENTERED_UK_MEANS,
            'REASON_FOR_ILEGAL_ENTRY'=>$request->REASON_FOR_ILEGAL_ENTRY
        ]);

    }

    if(strtoupper($request->EVER_STAYED_BEYOND_EXPIRY)=='YES'){

        DB::table('visamgr_applications')
        ->where('APPTYPE_ID', $id)
        ->update([
            'EVER_STAYED_BEYOND_EXPIRY'=>$request->EVER_STAYED_BEYOND_EXPIRY,
            'REASON_FOR_STAYING_BEYOND_EXPIRY'=>$request->REASON_FOR_STAYING_BEYOND_EXPIRY
        ]);

    }

    if(strtoupper($request->BREACHED_CONDITION_FOR_LEAVE)=='YES'){

        DB::table('visamgr_applications')
        ->where('APPTYPE_ID', $id)
        ->update([
            'BREACHED_CONDITION_FOR_LEAVE'=>$request->BREACHED_CONDITION_FOR_LEAVE,
             'REASON_FOR_BREACH'=>$request->REASON_FOR_BREACH,
            'BREACH_COUNTRY'=>$request->BREACH_COUNTRY
        ]);

    }

    if(strtoupper($request->WORK_WITHOUT_PERMIT)=='YES'){

        DB::table('visamgr_applications')
        ->where('APPTYPE_ID', $id)
        ->update([
            'WORK_WITHOUT_PERMIT'=>$request->WORK_WITHOUT_PERMIT,
             'REASON_FOR_WORK_WITHOUT_PERMIT'=>$request->REASON_FOR_WORK_WITHOUT_PERMIT,
        ]);

    }


    if(strtoupper($request->RECEIVED_PUBLIC_FUNDS) =='YES'){

        DB::table('visamgr_applications')
        ->where('APPTYPE_ID', $id)
        ->update([
            'RECEIVED_PUBLIC_FUNDS'=>$request->RECEIVED_PUBLIC_FUNDS,
             'REASON_RECEIVING_FUNDS'=>$request->REASON_RECEIVING_FUNDS,
        ]);

    }

    if(strtoupper($request->GIVE_FALSE_INFO) =='YES'){

        DB::table('visamgr_applications')
        ->where('APPTYPE_ID', $id)
        ->update([
            'GIVE_FALSE_INFO'=>$request->GIVE_FALSE_INFO,
             'REASON_FOR_FALSE_INFO'=>$request->REASON_FOR_FALSE_INFO,
        ]);

    }


    if(strtoupper($request->USED_DECEPTION) =='YES'){

        DB::table('visamgr_applications')
        ->where('APPTYPE_ID', $id)
        ->update([
            'USED_DECEPTION'=>$request->USED_DECEPTION,
             'REASON_FOR_DECEPTION'=>$request->REASON_FOR_DECEPTION,
        ]);

    }

    if(strtoupper($request->BREACHED_OTHER_LAWS) =='YES'){

        DB::table('visamgr_applications')
        ->where('APPTYPE_ID', $id)
        ->update([
            'BREACHED_OTHER_LAWS'=>$request->BREACHED_OTHER_LAWS,
             'REASON_FOR_BREACHING__LAWS'=>$request->REASON_FOR_BREACHING__LAWS,
        ]);

    }

    if(strtoupper($request->VISA_REFUSAL_QUESTION)=='YES'){

        DB::table('visamgr_applications')
        ->where('APPTYPE_ID', $id)
        ->update([
            'VISA_REFUSAL_QUESTION'=>$request->VISA_REFUSAL_QUESTION,
             'REASON_FOR_REFUSAL'=>$request->REASON_FOR_REFUSAL,
        ]);

    }


    if(strtoupper($request->PERMISSION_REFUSAL)=='YES'){

        DB::table('visamgr_applications')
        ->where('APPTYPE_ID', $id)
        ->update([
            'PERMISSION_REFUSAL'=>$request->PERMISSION_REFUSAL,
             'REASON_FOR_PERMISSION_REFUSAL'=>$request->REASON_FOR_PERMISSION_REFUSAL,
        ]);

    }

    if(strtoupper($request->ASYLUM_REFUSAL)=='YES'){

        DB::table('visamgr_applications')
        ->where('APPTYPE_ID', $id)
        ->update([
            'ASYLUM_REFUSAL'=>$request->ASYLUM_REFUSAL,
             'REASON_FOR_ASYLUM_REFUSAL'=>$request->REASON_FOR_ASYLUM_REFUSAL,
        ]);

    }


    if(strtoupper($request->EVER_DEPORTED) =='YES'){

        DB::table('visamgr_applications')
        ->where('APPTYPE_ID', $id)
        ->update([
            'EVER_DEPORTED'=>$request->EVER_DEPORTED,
             'REASON_FOR_DEPORTATION'=>$request->REASON_FOR_DEPORTATION,
        ]);

    }

    if(strtoupper($request->EVER_BANNED) =='YES'){

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
            ->update(['APPSTATUS' => 3,
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
                  'BRP_ISSUE_DATE'=>$request->BRP_ISSUE_DATE,
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
                  'MARRIED_BEFORE_QUESTION'=>$request->MARRIED_BEFORE_QUESTION,
                  'PARTNER_MARRIED_BEFORE'=>$request->PARTNER_MARRIED_BEFORE,
                  'DO_YOU_HAVE_FAMILY_IN_HOME_COUNTRY'=>$request->DO_YOU_HAVE_FAMILY_IN_HOME_COUNTRY,
                  'QUALIFICATION'=>$request->QUALIFICATION,
                  'HAVE_DEGREE_IN_ENGLISH'=>$request->HAVE_DEGREE_IN_ENGLISH,
                  'PASSED_RECOGNIZED_TEST'=>$request->PASSED_RECOGNIZED_TEST,
                  'WHAT_TEST_DID_YOU_PASS'=>$request->WHAT_TEST_DID_YOU_PASS,
                  'WHEN_DID_YOU_ENTER_UK'=>$request->WHEN_DID_YOU_ENTER_UK,
                  'DID_YOU_ENTER_LEGALLY'=>$request->DID_YOU_ENTER_LEGALLY,
                  'VISA_REASON'=>$request->VISA_REASON,
                  'VISA_START_DATE'=>$request->VISA_START_DATE,
                  'VISA_STATUS'=>$request->VISA_STATUS,
                  'OUT_OF_THE_UK_BEFORE'=>$request->OUT_OF_THE_UK_BEFORE,
                  'CRIMINAL_OFFENSE'=>$request->CRIMINAL_OFFENSE,
                  'updated_at' => Carbon::now()
            ]);


            $MESSAGE_SUBJECT = 'Visa Application Status';

                $MESSAGE_TAG = 'Visa Application';

                $uuid = Str::uuid()->toString();

                $fromAddress = User::where('id',$user_id)
                ->value('email');

                $client_name = Client::where('id', $client_id)
                ->value('first_name');

                $toAddress = Client::where('id', $client_id)
                ->value('email');

                $message = "Dear ".$client_name."," ."Congratulations, your visa application has been approved." ;

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

             $response =([
                'Message' => 'Application Submitted, pending approval'
            ]);

            return response($response, 201);
        }
        else{
            //You can't do anything actually ;)
        }

    }




//Query User by id
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //return User::find($id);
       // $id = $request->id;
       $alluser = DB::table('users')
       ->leftJoin('role_user', 'users.id', '=', 'role_user.user_id')
        ->leftJoin('user_references', 'user_references.user_id', '=', 'users.id')
        ->leftJoin('roles', 'role_user.role_id', '=', 'roles.id')
        ->leftJoin('visamgr_branches', 'users.branch_id', '=', 'visamgr_branches.id')
        ->select('users.id AS id','users.first_name', 'users.last_name', 'users.email', 'users.status', 'users.branch_id', 'visamgr_branches.LOCATION_CODE', 'users.created_at', 'roles.id AS role_id', 'roles.name','user_references.user_reference_no')
        ->where('users.id', $id)
        ->get();

       $locations = DB::table('user_locations')->where('user_id', $id)->get();

       $response =([
        'Message' => 'User details',
        'user_details'=>$alluser,
       // 'locations'=>$this->convert_from_latin1_to_utf8_recursively($locations),
       'locations'=>$locations
    ]);

    return response($response, 200);
    }


    public function getAllUsers(Request $request)
    {
        return DB::table('users')
        ->leftJoin('role_user', 'users.id', '=', 'role_user.user_id')
        ->leftJoin('user_references', 'user_references.user_id', '=', 'users.id')
        ->leftJoin('roles', 'role_user.role_id', '=', 'roles.id')
        ->leftJoin('visamgr_branches', 'users.branch_id', '=', 'visamgr_branches.id')
        ->select('users.id AS id','users.first_name', 'users.last_name', 'users.email', 'users.status', 'users.branch_id', 'visamgr_branches.LOCATION_CODE', 'users.created_at', 'roles.id AS role_id', 'roles.name','user_references.user_reference_no')
        /*/ ->join('role_user', 'users.id', '=', 'role_user.user_id')
        ->join('roles', 'role_user.role_id', '=', 'roles.id')
        ->join('locations', 'users.branch_id', '=', 'locations.id')
        ->select('users.id AS id','users.first_name', 'users.last_name', 'users.email', 'users.status', 'users.branch_id', 'visamgr_branches.LOCATION_CODE', 'users.created_at', 'roles.id AS role_id', 'roles.name')
        //->select('users.id AS id','users.first_name', 'users.last_name', 'users.email', 'users.created_at', 'roles.id AS role_id', 'roles.name')
        */->get();
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
        $locations = $request->countries;
        $user_id =$request->id;
        $user = User::find($user_id);

        $user->update($request->all());



        if(($request->user_reference_no)!= NULL){

//Check if user_id exist and user_reference_no exists

$check_user_reference = UserReference::where('user_id',$user_id)->first();


$is_user_refence_no =   UserReference::where('user_reference_no',$request->user_reference_no)->where('user_id',$user_id)->first();


            // $request->validate([
            //     'user_reference_no'=>'string|unique:user_references',
            //   //  'user_id'=>'int|'
            // ]);



        if($is_user_refence_no){

           //do nothing

        }

        else{

            if($check_user_reference){

                $request->validate([
                    'user_reference_no'=>'string|unique:user_references',
                  //  'user_id'=>'int|'
                ]);

                DB::table('user_references')
                ->where('user_id', $user_id)
                ->update(['user_reference_no' => $request->user_reference_no,
                        'updated_at' => Carbon::now()
                    ]);
            }

            else{
              $request->validate([
                'user_reference_no'=>'string|unique:user_references',
              //  'user_id'=>'int|'
            ]);


            DB::table('user_references')->insert([
                'user_id'=> $user_id,
                'user_reference_no' => $request->user_reference_no,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);


        }


         }

		}


        if($request->role){
        DB::table('role_user')
         ->where('user_id', $request->id)
        ->update(['role_id' => $request->role]);

        }

        $usrloc = DB::table('user_locations')->where('user_id', $request->id)->first();

        if ($usrloc === null) {
        // user doesn't exist
        DB::table('user_locations')->insert([
            'user_id' => $request->id,
            'location_id' => $locations,
        ]);
        }
        else{
            DB::table('user_locations')
            ->where('user_id', $request->id)
            ->update(['location_id' => $locations]);
        }
         $response =([
            'Message' => 'User Updated Successfully'
        ]);

        return response($response, 200);

    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
     //Login User
     public function login(Request $request){
        $fields = $request->validate([
            'email'=>'required|email',
            'password'=>'required|string'
        ]);

        //Check Email
        $user = User::where('email',$fields['email'])->first();

        //Check Password
        if(!$user || !Hash::check($fields['password'], $user->password)){
            return response([
                'message'=>'Invalid Credentials!'
            ], 401);

        }

        $role = DB::table('users')
       ->join('role_user', 'users.id', '=', 'role_user.user_id')
       ->join('roles', 'role_user.role_id', '=', 'roles.id')
       ->select('roles.name AS RoleName')
       ->where('email', $fields['email'])
       ->get();

        //Store id in session
        $admin_token =  $user->createToken($user->first_name)->plainTextToken;

            $response = [
                'id'=> $user->id,
                'first_name'=> $user->first_name,
                'last_name'=> $user->last_name,
                'email'=> $user->email,
                'role'=> $role[0]->RoleName,
                'token'=>$admin_token
            ];

             return response($response, 201);


    }
    //Logout User
    public function logout(Request $request){

        $request->bearerToken();

        $user_id = $request->id;

        $user = User::find($user_id);

         $tokenId = $user_id;


       $user->tokens()->where('tokenable_id', $tokenId)->delete();

        //$request->session()->regenerateToken();
        return response([
            'message'=>'Logout Successful'
        ], 200);
    }

    //Delete user
    public function destroy(Request $request)
    {

       // Permision to delete a User
       //Get user id
       $id = $request->id;

        //Get role_id from role_user table for the user
       $query = DB::table('role_user')->where('user_id',$id)->value('role_id');


        if($query=='1'){
            return response([
                'message'=>'You are not authorized to perform this action!'
            ], 401);
        }
        else{
            return User::destroy($id);

        }

    }

    //Search User by name
    public function search($name)
    {

        return $this->convert_from_latin1_to_utf8_recursively(User::where('first_name','like','%'.$name.'%')->get());

    }



}
