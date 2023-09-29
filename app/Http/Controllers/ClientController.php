<?php

namespace App\Http\Controllers;
use App\Client;
use App\Location;
use App\Notifications\VerifyEmailNotification;
use App\PreScreening;
use App\visamgr_applications;
use App\visamgr_branches;
use App\BranchReference;
use App\User;
use App\UserReference;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Aws\S3\Exception\S3Exception;
use Exception;
use Aws\Sns\SnsClient;
use Aws\Exception\AwsException;
use Illuminate\Console\Application;
use Illuminate\Support\Facades\View;
use Mockery\Undefined;
use Svg\Tag\Rect;
use Symfony\Component\HttpFoundation\Session\Session;

use function PHPUnit\Framework\isEmpty;

class ClientController extends Controller
{

     //Enable middleware route for authentication
    /*public function __construct()
    {
        $this->middleware('auth');

    }*/

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Client::all();


    }

    public function getAllClients($id)
    {

        if (is_numeric($id)) {


            if($id == 1){
                $client_details = DB::table('clients')
                ->leftJoin('users', 'clients.default_user', '=', 'users.id')
                ->leftJoin('client_groups', 'clients.id', '=', 'client_groups.client_id')
                ->leftJoin('visamgr_groups', 'client_groups.group_id', '=', 'visamgr_groups.GROUP_ID')
                ->leftJoin('visamgr_branches', 'clients.client_office', '=', 'visamgr_branches.id')
                ->select('clients.*','visamgr_groups.GROUP_ID', 'visamgr_groups.GROUP_NAME', 'visamgr_branches.LOCATION_CODE', 'users.first_name AS users_firstname')
                //->whereIn('clients.client_office', $branches) ///->toSql() ;
                ->get();

                $data = $this->convert_from_latin1_to_utf8_recursively($client_details);
                return  $data;

            }
            else {
            $user_branch = User::where('id',$id)->value('branch_id');
            $user_other_branches = DB::table('user_locations')->where('user_id', $id)->value('location_id');
          //  $user_other_branches = DB::table('user_locations')->where('user_id', $id)->get();
            $Decoded_user_other_branches = json_decode($user_other_branches, true);
            $branches = array($user_branch);

            if(count($Decoded_user_other_branches) > 0){

                foreach($Decoded_user_other_branches as $key=>$other_branches) {
                    array_push($branches, $other_branches['value']);
                   }
            }


            $client_details = DB::table('clients')
             ->leftJoin('users', 'clients.default_user', '=', 'users.id')
             ->leftJoin('client_groups', 'clients.id', '=', 'client_groups.client_id')
             ->leftJoin('visamgr_groups', 'client_groups.group_id', '=', 'visamgr_groups.GROUP_ID')
             ->leftJoin('visamgr_branches', 'clients.client_office', '=', 'visamgr_branches.id')
             ->select('clients.*','visamgr_groups.GROUP_ID', 'visamgr_groups.GROUP_NAME', 'visamgr_branches.LOCATION_CODE', 'users.first_name AS users_firstname')
             ->whereIn('clients.client_office', $branches) ///->toSql() ;
             ->get();

             $data = $this->convert_from_latin1_to_utf8_recursively($client_details);
             return  $data;
             /*  $response =([
                 'data' => $client_details,
                 'user_branch' => $user_branch,
                 'user_other_branches' => $user_other_branches,
                 'Decoded_user_other_branches' => $Decoded_user_other_branches,
                 'branches' => $branches
             ]);

             return response($response, 200);  */


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


    public function generateOTP()
    {
        do {
            $otp = random_int(100000, 999999);
        } while (Client::where("otp", "=", $otp)->first());

        return $otp;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

       //Validate Entery request
       $request->validate([
        'first_name'=>'required|string',
        'last_name'=>'required|string',
        'email'=>'required|string',
        'password'=>'required|string|confirmed',
        'phone'=>'required|string|unique:clients,phone',
        //phone number
    ]);

    //Populate DB
   // $token= Str::random(64);
   $otp = $this->generateOTP();

   $email_verification = Str::uuid()->toString();

   $client = Client::create([

        'first_name'=>$request->first_name,
        'middle_name'=>$request->middle_name,
        'last_name'=>$request->last_name,
        'email'=>$request->email,
        'password'=>Hash::make($request->password),
        'phone'=>$request->phone,
        'country'=>$request->country,
        'OTP'=>$otp,
        'email_token'=>$email_verification,
        'agent_reference'=>$request->agent_reference,
        //'defaultbranch'=>$request->defaultbranch,
        'created_by'=>'CLIENT'
        //'remember_token'=>$token
      ]);

    //  $token  = $client->createToken($request->first_name)->plainTextToken;


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

    $client_office = Location::where('COUNTRY',$client_location)->first();

    $client->location()->attach($client_office);


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

//prepend country code
if($request->country=='Ghana'||$request->country=='GHANA'||$request->country=='ghana'){
    $new_client_phone = preg_replace('~^(?:0|\+?233)?~', '+233', $client_phone);
}

if($request->country=='United Kingdom'||$request->country=='Uk'||$request->country=='uk'){
$new_client_phone = preg_replace('~^(?:0|\+?44)?~', '+44', $client_phone);

}


    $params = array(
        'credentials' => [
            'key'    => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
        ],
        'region' => env('AWS_DEFAULT_REGION'),
        'version' => 'latest'
    );
    $sns = new \Aws\Sns\SnsClient($params);

    try {
    $args = array(
            "MessageAttributes" =>[



            'AWS.SNS.SMS.SMSType'=>[
                'DataType' => 'String',
                'StringValue' =>'Transactional'
                ]

            ],
        "Message" => "This is your OTP: ".$otp,
        "PhoneNumber" => $new_client_phone
    );

   //$results = $sns->publish($args);

   $sns->publish($args);

   if($sns->publish($args)[
    'message'
   ]){

    throw new Exception('SNS failed.');

   }

}

catch(Exception $e){


    DB::table('clients')->where('id',$client_id)->delete();

    DB::table('pre_screenings')->where('CLIENT_ID', $client_id)->delete();

    $response =([
        'message' =>"Something went wrong, please contact Administrator ",
        'snsError' => $e->getMessage() . PHP_EOL,
      //  'message' =>"Something went wrong, please contact Administrator "
    ]);

    return response($response, 400);

}

$toAddress = Client::where('id', $client_id)
        ->value('email');


$client_name = Client::where('id', $client_id)
        ->value('first_name');


$client = Client::findOrFail($client_id);
$client->notify(new VerifyEmailNotification($client, $client_name, $toAddress, $email_verification));



    return response([
        'message'=>'Kindly Confirm OTP',
        'id'=>$client_id,
        //'token'=>$token
    ], 201);


    }





    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
       // return Client::find($id);

       return $client_details = DB::table('clients')
       ->join('client_groups', 'clients.id', '=', 'client_groups.client_id')
       ->join('visamgr_groups', 'client_groups.group_id', '=', 'visamgr_groups.GROUP_ID')
       ->join('visamgr_branches', 'clients.client_office', '=', 'visamgr_branches.id')
       ->select('clients.*','visamgr_groups.GROUP_ID', 'visamgr_groups.GROUP_NAME','visamgr_branches.LOCATION_CODE')
       ->where('clients.id', $id)
       ->get();
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
        $id = $request->client_id;

        $client = Client::find($id);
        // if($request){

        // }
        $client->update($request->all());

        return $client;
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
            ['APPSTATUS', '!=', '3']
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


    public function clientDetailsPerApplicationandPrescreening(Request $request){
        $client_id = $request->client_id;

        $client_details = Client::where('id',$client_id)->get();

        $client_prescreening = PreScreening::where('client_id',$client_id)->get();

//$client_application = visamgr_applications::where('client_id',$client_id)->get();

       $client_application = DB::select('SELECT COUNT(`CLIENT_ID`) total_count,
        COUNT(DISTINCT CASE WHEN `APPSTATUS` = 1   THEN `CLIENT_ID` END) draft_count,
        COUNT(DISTINCT CASE WHEN `APPSTATUS` = 2 THEN `CLIENT_ID` END) review_count,
        COUNT(DISTINCT CASE WHEN `APPSTATUS` = 3 THEN `CLIENT_ID` END) approved_count
        FROM `visamgr_applications` WHERE `CLIENT_ID` = ?',[ $client_id]);

        $response = [
            'message'=> 'great!',
            'client_details'=>$client_details,
            'client_prescreening'=>$client_prescreening,
            'client_application'=>$client_application
            ];


            return response($response, 200);
    }



    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return Client::destroy($id);
    }

     /**
     * Search User by name
     *
     * @param  str  $name
     * @return \Illuminate\Http\Response
     */
    public function search($search , $id)
    {

        // return Client::where('email','like','%'.$search.'%',
        // 'or', 'first_name','like','%'.$search.'%','or',
        // 'last_name','like','%'.$search.'%', 'or',
        // 'client_office','like','%'.$search.'%')
        //  ->get();

        if (is_numeric($id)) {

            if($id == 1){

                $search_item = DB::table('clients')
                ->leftJoin('users', 'clients.default_user', '=', 'users.id')
                ->leftJoin('client_groups', 'clients.id', '=', 'client_groups.client_id')
                ->leftJoin('visamgr_groups', 'client_groups.group_id', '=', 'visamgr_groups.GROUP_ID')
                ->leftJoin('visamgr_branches', 'clients.client_office', '=', 'visamgr_branches.id')
                ->select('clients.*','visamgr_groups.GROUP_ID', 'visamgr_groups.GROUP_NAME', 'visamgr_branches.LOCATION_CODE', 'users.first_name AS users_firstname')
                ->where(function ($query) use($search) {
                    $query->where('clients.email', 'like', '%' . $search . '%')
                       ->orWhere('clients.first_name', 'like', '%' . $search . '%')
                       ->orWhere('clients.last_name', 'like', '%' . $search . '%')
                       ->orWhere('visamgr_branches.LOCATION_CODE', 'like', '%' . $search . '%')
                       ->orWhere('visamgr_groups.GROUP_NAME', 'like', '%' . $search . '%');
                    })->get();


                    return $this->convert_from_latin1_to_utf8_recursively($search_item);

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

      if(count($branches) > 0) {
        $search_item = DB::table('clients')
        ->leftJoin('users', 'clients.default_user', '=', 'users.id')
        ->leftJoin('client_groups', 'clients.id', '=', 'client_groups.client_id')
        ->leftJoin('visamgr_groups', 'client_groups.group_id', '=', 'visamgr_groups.GROUP_ID')
        ->leftJoin('visamgr_branches', 'clients.client_office', '=', 'visamgr_branches.id')
        ->select('clients.*','visamgr_groups.GROUP_ID', 'visamgr_groups.GROUP_NAME', 'visamgr_branches.LOCATION_CODE', 'users.first_name AS users_firstname')
        ->whereIn('clients.client_office', $branches)
        ->where(function ($query) use($search) {
            $query->where('clients.email', 'like', '%' . $search . '%')
               ->orWhere('clients.first_name', 'like', '%' . $search . '%')
               ->orWhere('clients.last_name', 'like', '%' . $search . '%')
               ->orWhere('visamgr_branches.LOCATION_CODE', 'like', '%' . $search . '%')
               ->orWhere('visamgr_groups.GROUP_NAME', 'like', '%' . $search . '%');
            })->get();


            return $this->convert_from_latin1_to_utf8_recursively($search_item);

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

   /* public function search($search)
    {

        // return Client::where('email','like','%'.$search.'%',
        // 'or', 'first_name','like','%'.$search.'%','or',
        // 'last_name','like','%'.$search.'%', 'or',
        // 'client_office','like','%'.$search.'%')
        //  ->get();


        $search_item = DB::table('clients')
        ->leftJoin('client_groups', 'clients.id', '=', 'client_groups.client_id')
        ->leftJoin('visamgr_branches', 'clients.client_office', '=', 'visamgr_branches.id')
        ->select('pre_screenings.*','visamgr_branches.LOCATION_CODE')
        ->leftJoin('visamgr_groups', 'client_groups.group_id', '=', 'visamgr_groups.GROUP_ID')
        ->select('clients.*','visamgr_groups.GROUP_ID', 'visamgr_groups.GROUP_NAME','visamgr_branches.LOCATION_CODE')
        ->where(function ($query) use($search) {
            $query->where('clients.email', 'like', '%' . $search . '%')
               ->orWhere('clients.first_name', 'like', '%' . $search . '%')
               ->orWhere('clients.last_name', 'like', '%' . $search . '%')
               ->orWhere('visamgr_branches.LOCATION_CODE', 'like', '%' . $search . '%')
               ->orWhere('visamgr_groups.GROUP_NAME', 'like', '%' . $search . '%');
            })->get();


            return $this->convert_from_latin1_to_utf8_recursively($search_item);

    }*/

    public function clientbyLocation(Request $request){
        $client_office = $request->client_office;


        $query = DB::table('clients')
        ->leftJoin('users', 'clients.default_user', '=', 'users.id')
        ->leftJoin('client_groups', 'clients.id', '=', 'client_groups.client_id')
        ->leftJoin('visamgr_groups', 'client_groups.group_id', '=', 'visamgr_groups.GROUP_ID')
        ->leftJoin('visamgr_branches', 'clients.client_office', '=', 'visamgr_branches.id')
        ->select('clients.*','visamgr_groups.GROUP_ID', 'visamgr_groups.GROUP_NAME', 'visamgr_branches.LOCATION_CODE', 'users.first_name AS users_firstname')
        ->where(function ($query) use($client_office) {
            $query->where('visamgr_branches.LOCATION_CODE', 'like', '%' . $client_office . '%');
            })->get();

        $response = [
            'message'=> 'great!',
            'attributes'=>$this->convert_from_latin1_to_utf8_recursively($query)
            ];

            return response($response, 200);



    }

    public function updateCLientOffice(Request $request)
    {
        $client_id = $request->client_id;

        $new_office = $request->office;

        DB::table('clients')
        ->where('id', $client_id)
        ->update(['client_office' => $new_office]);

        DB::table('pre_screenings')
        ->where('client_id', $client_id)
        ->update(['client_office' => $new_office]);

        $response = [
            'message'=> 'Client Office Updated!',
            ];

            return response($response, 200);

    }

    public function top30Clients($id){

        // $clients  = DB::select('SELECT * FROM clients ORDER BY last_name DESC LIMIT 30 ');


        //  $response = [
        //      'message'=> 'great!',
        //      'attributes'=>$clients
        //      ];

        //$id = $request->userid;

        if (is_numeric($id)) {

            if($id == 1){
                $top = DB::table('clients')
                ->leftJoin('users', 'clients.default_user', '=', 'users.id')
                ->leftJoin('client_groups', 'clients.id', '=', 'client_groups.client_id')
                ->leftJoin('visamgr_groups', 'client_groups.group_id', '=', 'visamgr_groups.GROUP_ID')
                ->leftJoin('visamgr_branches', 'clients.client_office', '=', 'visamgr_branches.id')
                ->select('clients.*','visamgr_groups.GROUP_ID', 'visamgr_groups.GROUP_NAME', 'visamgr_branches.LOCATION_CODE', 'users.first_name AS users_firstname')
                ->groupBy('clients.id')
                ->orderBy('clients.created_at','desc')
                ->take(30)->get();

                $response = [
                'message'=> 'total Top 30 Clients are '. count($top),
                'attributes'=>$this->convert_from_latin1_to_utf8_recursively($top)
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
                $top = DB::table('clients')
                ->leftJoin('users', 'clients.default_user', '=', 'users.id')
                ->leftJoin('client_groups', 'clients.id', '=', 'client_groups.client_id')
                ->leftJoin('visamgr_groups', 'client_groups.group_id', '=', 'visamgr_groups.GROUP_ID')
                ->leftJoin('visamgr_branches', 'clients.client_office', '=', 'visamgr_branches.id')
                ->select('clients.*','visamgr_groups.GROUP_ID', 'visamgr_groups.GROUP_NAME', 'visamgr_branches.LOCATION_CODE', 'users.first_name AS users_firstname')
                ->whereIn('clients.client_office', $branches)
                ->groupBy('clients.id')
                ->orderBy('clients.created_at','desc')
                ->take(30)->get();

                $response = [
                'message'=> 'total Top 30 Clients are '. count($top),
                'attributes'=>$this->convert_from_latin1_to_utf8_recursively($top)
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


      public function verifiedCLients($id){


        if (is_numeric($id)) {

            if($id == 1){
                $verified_clients = DB::table('clients')
                ->leftJoin('users', 'clients.default_user', '=', 'users.id')
                ->leftJoin('client_groups', 'clients.id', '=', 'client_groups.client_id')
                ->leftJoin('visamgr_groups', 'client_groups.group_id', '=', 'visamgr_groups.GROUP_ID')
                ->leftJoin('visamgr_branches', 'clients.client_office', '=', 'visamgr_branches.id')
                ->select('clients.*','visamgr_groups.GROUP_ID', 'visamgr_groups.GROUP_NAME', 'visamgr_branches.LOCATION_CODE', 'users.first_name AS users_firstname')
                //->whereIn('clients.client_office', $branches)
                ->where('clients.sms_verified','=',1)
                ->where('clients.email_token', '!=' , "")
                ->get();

                $response = [
                    'message'=> 'great!',
                    'attributes'=>$this->convert_from_latin1_to_utf8_recursively($verified_clients)
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



        $verified_clients = DB::table('clients')
        ->leftJoin('users', 'clients.default_user', '=', 'users.id')
        ->leftJoin('client_groups', 'clients.id', '=', 'client_groups.client_id')
        ->leftJoin('visamgr_groups', 'client_groups.group_id', '=', 'visamgr_groups.GROUP_ID')
        ->leftJoin('visamgr_branches', 'clients.client_office', '=', 'visamgr_branches.id')
        ->select('clients.*','visamgr_groups.GROUP_ID', 'visamgr_groups.GROUP_NAME', 'visamgr_branches.LOCATION_CODE', 'users.first_name AS users_firstname')
        ->whereIn('clients.client_office', $branches)
        ->where('clients.sms_verified','=',1)
        ->where('clients.email_token', '!=' , "")
        ->get();

        $response = [
            'message'=> 'great!',
            'attributes'=>$this->convert_from_latin1_to_utf8_recursively($verified_clients)
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


      public function updateGroup(Request $request)
    {

        $request->validate([
            'group'=>'required|integer',
            'clientid'=>'required|integer',
           ]);

        $clientid = $request->clientid;

        $group_mapping_exist = DB::table('client_groups')
                ->where('client_id', '=', $clientid)
                ->get();

        if($group_mapping_exist->isEmpty()){
            DB::table('client_groups')->insert([
                'client_id' => $clientid,
                'group_id' => $request->group,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            $response = [
                'message'=> 'Client Added to Group Successfully!'
                ];
          return response($response, 200);
        }
        else{

            DB::table('client_groups')
            ->where('client_id', $clientid)
            ->update([
                'group_id' =>$request->group
            ]);

            $response = [
                'message'=> 'Client Group updated Successfully'
                ];
          return response($response, 200);

        }

    }


    public function updateUser(Request $request)
    {

       $request->validate([
        'agent'=>'required|integer',
        'client_id'=>'required|integer',
       ]);

        $clientid = $request->client_id;



        DB::table('clients')
            ->where('id', $clientid)
            ->update([
                'default_user' =>$request->agent
            ]);


            $response = [

                'message'=> 'Agent assigned successfully!'

                ];

                return response($response, 200);

    }


     /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */


    // Client Login
    public function login(Request $request){
        $fields = $request->validate([
            'email'=>'required|email',
            'password'=>'required|string'
        ]);

        //Check Email
        $client = Client::where('email',$fields['email'])->first();

        if(!$client){

            return response([
                'message'=>'User not found!'
            ], 404);
        }

        $prescreening_status = PreScreening::where('client_id',$client->id)->value('prescreened_status');
        //Check if client exists




        //Check Password
        if(!$client || !Hash::check($fields['password'], $client->password)){
            return response([
                'message'=>'Invalid Credentials!'
            ], 401);

        }


        $email_verified= Client::where('id',$client->id)->value('email_verified_at');
        $phone_verified= Client::where('id',$client->id)->value('sms_verified');



        //Check Client Validation Status

        if($email_verified==null){
            return response([
                'message'=>'Kindly verify your email!'
            ], 401);
        }

        if($phone_verified==null){
            return response([
                'message'=>'Kindly verify your phone number!'
            ], 401);
        }

        //  $client_id = Client::where('email',$fields['email'])->value('id');
        //  //Store id in session
        // session()->put('client_id', $client_id);


       // $client_token = DB::table('personal_access_tokens')->where('tokenable_id',$client_id)->value('token');
       $client_token =  $client->createToken($client->first_name)->plainTextToken;




        $response = [
            'message'=>'Login Successful',
            'id'=>$client->id,
            'email'=>$client->email,
            'first_name'=> $client->first_name,
            'middle_name'=>$client->middle_name,
            'last_name'=>$client->last_name,
            'phone'=>$client->phone,
            'session_id'=>session()->get('client_id'),
            'prescreened_status'=>$prescreening_status,
            'token'=>$client_token
        ];

        return response($response, 201);
    }

     /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

     //Client Logout
    public function logout(Request $request){

        $request->bearerToken();

        $client_id = $request->id;

        $client = Client::find($client_id);

         $tokenId = $client_id;


       $client->tokens()->where('tokenable_id', $tokenId)->delete();


         return response([
            'message'=>'Logout Successful'
        ], 200);
    }


}
