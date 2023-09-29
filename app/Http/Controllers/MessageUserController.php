<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Messages;
use App\User;
use App\Client;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Notifications\MessageUserNotification;
use Illuminate\Support\Str;

class MessageUserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Messages::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function sendUserMessage(Request $request)
    {
        //$user_id = $request->session()->get('id');
        $user_id = $request->id;
        $id = $request->MESSAGE_TO;
      /*  if(session()->get('id')!=$user_id){

         $response =([
             'Message' => 'Re-login'
         ]);
         return response($response, 401);

       }*/

     //  $fromAddress='notify@i-visas.com';

        // $fromAddress = User::where('id',$user_id)
        // ->value('email');

        $toAddress = Client::where('id', $id)
        ->value('email');


        $client_name = Client::where('id', $id)
        ->value('first_name');

        $client_id =  Client::where('id',$id)
        ->value('id');

        $uuid = Str::uuid()->toString();



        $request->validate([
            'MESSAGE'=>'required|string',
            'MESSAGE_SUBJECT'=>'required|string',
            'MESSAGE_TAG'=>'required|string',
        ]);

        $subject =$request->MESSAGE_SUBJECT;

        DB::table('Messages')->insert([
            'MESSAGE'=>$request->MESSAGE,
            'MESSAGE_SUBJECT'=>$request->MESSAGE_SUBJECT,
            'MESSAGE_TAG'=>$request->MESSAGE_TAG,
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

         //Send broadcast notification to user and update notification table
         $message = Messages::find($itemId);
         $client = Client::findOrFail($id);
         $client->notify(new MessageUserNotification($client, $message, $client_name, $toAddress, $subject));

         //Store Message notification in database
           DB::table('notifications')->insert([
            'DATA'=>$request->MESSAGE,
            'MESSAGE_ID'=>$itemId,
            'CONVERSATION_ID'=>$uuid,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
         $response =([
             'Message' => 'Message sent successfully!',
            //  'CONVERSATION_ID'=>$uuid,
            //  'MESSAGE_ID'=>$itemId
         ]);

         return response($response, 201);

    }

    public function viewUserMessage(Request $request){
        // $user_id = $request->session()->get('id');
         $user_id = $request->id;
         $CONVERSATION_ID = $request->CONVERSATION_ID;
         //update Message table

         $Message = Messages::where('CONVERSATION_ID',$CONVERSATION_ID)->latest()->first();


         //Check Password  !$Message && $Message->RECEIPIENT === 'i-visas'
        if($Message->RECEIPIENT == 'i-visas'){


         DB::table('messages')
         ->where('CONVERSATION_ID', $CONVERSATION_ID)
         ->update([
             'MESSAGE_STATUS'=>'read',
             'USER_ID'=>$user_id
         ]);

          //update Notifications table
          DB::table('notifications')
          ->where('CONVERSATION_ID', $CONVERSATION_ID)
          ->update([
             'READ_AT' => Carbon::now(),
          ]);

         }

          $response =([
             'Message' => 'Message sent successfully!',
             "attributes"=>  Messages::where('CONVERSATION_ID',$CONVERSATION_ID)->get()
         ]);

         return response($response, 201);
        // var_dump($Message);
       // }
     }

     public function searchReceviedUser($id, $keyword){

        $user_id = $id;
        $search = $keyword;

        if($id == 1){
           /*  return DB::table('messages')->select('*')->where('RECEIPIENT','i-visas')->where('USER_DELETE', '!=', "deleted")
            ->where(function ($query) use($keyword) {
              $query->where('MESSAGE_SUBJECT', 'like', '%'.$keyword.'%')
                 ->orWhere('MESSAGE_TAG', 'like', '%'.$keyword.'%')
                 ->orWhere('MESSAGE', 'like', '%' . $keyword . '%');
                 //->andWhere('USER_DELETE', '!=', 1);
              })->get(); */

              return DB::table('messages')
              ->join('clients', 'messages.SENDER', '=', DB::raw('CAST(clients.id AS CHAR)'))
              ->select('messages.*', 'clients.first_name', 'clients.middle_name', 'clients.last_name')
              ->where('messages.RECEIPIENT','i-visas')
               ->Where('messages.USER_DELETE', '=', null)
               ->where(function ($query) use($search) {
                $query->where('messages.MESSAGE_SUBJECT', 'like', '%' . $search . '%')
                   ->orWhere('messages.MESSAGE_TAG', 'like', '%' . $search . '%')
                   ->orWhere('messages.MESSAGE', 'like', '%' . $search . '%');
                })->get();
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

        $all_clients_in_decoded_branches_object = DB::table('clients')->select('id')->whereIn('id', $branches)->get();

        $all_clients_array = array();

        if(count($all_clients_in_decoded_branches_object)>0){

            foreach($all_clients_in_decoded_branches_object as $cid) {
                array_push($all_clients_array, $cid->id);
             }
        }

        /* return DB::table('messages')->select('*')->where('RECEIPIENT','i-visas')
        ->where('USER_DELETE', '!=', "deleted")
        ->whereIn('SENDER', $all_clients_array)
           ->where(function ($query) use($keyword) {
             $query->where('MESSAGE_SUBJECT', 'like', '%'.$keyword.'%')
                ->orWhere('MESSAGE_TAG', 'like', '%'.$keyword.'%')
                ->orWhere('MESSAGE', 'like', '%' . $keyword . '%');
                //->andWhere('USER_DELETE', '!=', 1);
             })->get(); */

             return DB::table('messages')
              ->join('clients', 'messages.SENDER', '=', DB::raw('CAST(clients.id AS CHAR)'))
              ->select('messages.*', 'clients.first_name', 'clients.middle_name', 'clients.last_name')
               ->where('messages.RECEIPIENT','i-visas')
               ->Where('messages.USER_DELETE', '=', null)
               ->whereIn('messages.SENDER', $all_clients_array)
               ->where(function ($query) use($search) {
                $query->where('messages.MESSAGE_SUBJECT', 'like', '%' . $search . '%')
                   ->orWhere('messages.MESSAGE_TAG', 'like', '%' . $search . '%')
                   ->orWhere('messages.MESSAGE', 'like', '%' . $search . '%');
                })->get();

         //    dd($final);
         //    dump($final);

        /*  $response =([
            'Branches' => $branches,
            'all_clients_in_decoded_branches_object' => $all_clients_in_decoded_branches_object,
            'user_other_branches' => $user_other_branches,
            'Decoded_user_other_branches' => $Decoded_user_other_branches,
            'Client_array' => $all_clients_array,
            'ID' => $id,
            'Keyword'=> $search,
            'final'=> $final
        ]);

        return response($response, 400);  */
     }
    }

     public function searchSentUser($id, $keyword){

         $user_id = $id;
         $search = $keyword;
         /*
         return Messages::where('USER_ID',$user_id)->Where('USER_DELETE', '!=', "deleted")
           ->where(function ($query) use($search) {
             $query->where('MESSAGE_SUBJECT', 'like', '%' . $search . '%')
                ->orWhere('MESSAGE_TAG', 'like', '%' . $search . '%')
                ->orWhere('MESSAGE', 'like', '%' . $search . '%');
                //->andWhere('USER_DELETE', '!=', 1);
             })->get();
          */


            return DB::table('messages')
                   ->join('clients', 'messages.RECEIPIENT', '=', DB::raw('CAST(clients.id AS CHAR)'))
                   ->select('messages.*', 'clients.first_name', 'clients.middle_name', 'clients.last_name')
                   ->where('messages.USER_ID',$user_id)
                    ->Where('messages.USER_DELETE', '=', null)
                    ->where(function ($query) use($search) {
                     $query->where('messages.MESSAGE_SUBJECT', 'like', '%' . $search . '%')
                        ->orWhere('messages.MESSAGE_TAG', 'like', '%' . $search . '%')
                        ->orWhere('messages.MESSAGE', 'like', '%' . $search . '%');
                     })->get();



     }

    public function replyClientMessage(Request $request)
    {
       // $user_id = $request->session()->get('id');

       $user_id = $request->id;



       //$client_id = $request->MESSAGE_TO;



       $CONVERSATION_ID = $request->CONVERSATION_ID;



      /* if(session()->get('id')!=$user_id){



        $response =([

            'Message' => 'Re-login'

        ]);

        return response($response, 401);



      }*/



       $client_id =  Messages::where([

           ['CONVERSATION_ID','=',$CONVERSATION_ID],

           ['SENDER','!=','i-visas']

       ])

       ->value('SENDER');



       $client_name = Client::where('id', $client_id)

       ->value('first_name');



       $MESSAGE_SUBJECT = Messages::where('CONVERSATION_ID',$CONVERSATION_ID)

       ->value('MESSAGE_SUBJECT');



       $MESSAGE_TAG = Messages::where('CONVERSATION_ID',$CONVERSATION_ID)

       ->value('MESSAGE_TAG');



       $uuid = $CONVERSATION_ID; //Messages::where('CONVERSATION_ID',$CONVERSATION_ID)->value('CONVERSATION_ID');





       $toAddress = Client::where('id', $client_id)

       ->value('email');



       $request->validate([

           'MESSAGE'=>'required|string',

           'CONVERSATION_ID'=>'required|string'

       ]);



       DB::table('messages')->insert([

           'MESSAGE'=>$request->MESSAGE,

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

        $client->notify(new MessageUserNotification($client, $message,$client_name, $toAddress, $MESSAGE_SUBJECT));



        DB::table('notifications')->insert([

           'DATA'=>$request->MESSAGE,

           'MESSAGE_ID'=>$itemId,

           'CONVERSATION_ID'=>$uuid,

           'created_at' => Carbon::now(),

           'updated_at' => Carbon::now(),

       ]);

        $response =([

            'Message' => 'Message sent successfully!'

        ]);



        return response($response, 201);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showUserMessage($CONVERSATION_ID)
    {

        return Messages::find($CONVERSATION_ID);
    }


    public function showMessagesbyUserID($user_id){

        if (is_numeric($user_id)) {
        //return Messages::where("USER_ID",$user_id)->where('USER_DELETE', '!=', 1)->get();
        //return DB::select('SELECT MAX(MESSAGE_ID) AS MESSAGE_ID, CONVERSATION_ID, MAX(SENDER) AS SENDER, MAX(MESSAGE) AS MESSAGE, MAX(MESSAGE_SUBJECT) AS MESSAGE_SUBJECT, MAX(MESSAGE_TAG) AS MESSAGE_TAG, MAX(USER_ID) AS USER_ID, MAX(RECEIPIENT) AS RECEIPIENT, MAX(MESSAGE_STATUS) AS MESSAGE_STATUS, MAX(updated_at) AS updated_at, COUNT(*) AS CNT from messages WHERE USER_ID = ? AND USER_DELETE !="deleted" GROUP BY CONVERSATION_ID ORDER BY CNT',[$user_id]);
          return DB::select('SELECT MAX(MESSAGE_ID) AS MESSAGE_ID, CONVERSATION_ID, MAX(SENDER) AS SENDER, MAX(MESSAGE) AS MESSAGE, MAX(MESSAGE_SUBJECT) AS MESSAGE_SUBJECT, MAX(MESSAGE_TAG) AS MESSAGE_TAG, MAX(USER_ID) AS USER_ID, MAX(RECEIPIENT) AS RECEIPIENT, MAX(MESSAGE_STATUS) AS MESSAGE_STATUS, MAX(updated_at) AS updated_at, MAX(first_name) AS first_name, MAX(middle_name) AS middle_name, MAX(last_name) AS last_name, COUNT(*) AS CNT from (SELECT a.MESSAGE_ID, a.CONVERSATION_ID, a.SENDER, a.MESSAGE, a.MESSAGE_SUBJECT, a.MESSAGE_TAG, a.USER_ID, a.RECEIPIENT, a.MESSAGE_STATUS, a.updated_at, b.first_name, b.middle_name, b.last_name from messages as a INNER JOIN clients as b ON a.RECEIPIENT = CAST(b.id as char(255)) WHERE a.USER_ID = ? AND USER_DELETE !="deleted") as msg GROUP BY CONVERSATION_ID ORDER BY CNT',[$user_id]);
        }

        else {
            $response =([
                'Message' => 'Invalid User ID'
            ]);

            return response($response, 400);
        }
    }

    public function showMessagesFromAllClients($id){

        if (is_numeric($id)) {
            $sender = 'i-visas';

            $user_id = $id;

            if($id == 1){
                //return Messages::where('SENDER', 'NOT LIKE', $sender.'%')->Where('USER_DELETE', '!=', 1)->get();

                //return DB::select('SELECT MAX(MESSAGE_ID) AS MESSAGE_ID, CONVERSATION_ID, MAX(SENDER) AS SENDER, MAX(MESSAGE) AS MESSAGE, MAX(MESSAGE_SUBJECT) AS MESSAGE_SUBJECT, MAX(MESSAGE_TAG) AS MESSAGE_TAG, MAX(USER_ID) AS USER_ID, MAX(RECEIPIENT) AS RECEIPIENT, MAX(MESSAGE_STATUS) AS MESSAGE_STATUS, MAX(updated_at) AS updated_at, COUNT(*) AS CNT from messages WHERE SENDER != ? AND USER_DELETE !="deleted" GROUP BY CONVERSATION_ID ORDER BY CNT',[$sender]);


                return DB::select('SELECT MAX(MESSAGE_ID) AS MESSAGE_ID, CONVERSATION_ID, MAX(SENDER) AS SENDER, MAX(MESSAGE) AS MESSAGE, MAX(MESSAGE_SUBJECT) AS MESSAGE_SUBJECT, MAX(MESSAGE_TAG) AS MESSAGE_TAG, MAX(USER_ID) AS USER_ID, MAX(RECEIPIENT) AS RECEIPIENT, MAX(MESSAGE_STATUS) AS MESSAGE_STATUS, MAX(updated_at) AS updated_at, MAX(first_name) AS first_name, MAX(middle_name) AS middle_name, MAX(last_name) AS last_name, COUNT(*) AS CNT from (SELECT a.MESSAGE_ID, a.CONVERSATION_ID, a.SENDER, a.MESSAGE, a.MESSAGE_SUBJECT, a.MESSAGE_TAG, a.USER_ID, a.RECEIPIENT, a.MESSAGE_STATUS, a.updated_at, b.first_name, b.middle_name, b.last_name from messages as a INNER JOIN clients as b ON a.SENDER = CAST(b.id as char(255)) WHERE a.SENDER != ? AND USER_DELETE !="deleted") as msg GROUP BY CONVERSATION_ID ORDER BY CNT',[$sender]);

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

                //else{

                $all_clients_in_decoded_branches_object = DB::table('clients')->select('id')->whereIn('client_office', $branches)->get();

                $all_clients_array = array();

                if(count($all_clients_in_decoded_branches_object)>0){

                    foreach($all_clients_in_decoded_branches_object as $cid) {
                        array_push($all_clients_array, $cid->id);

                        }
                }


                if(empty($all_clients_array)){
                         return [];
                 }
                    else{
                        //return DB::select('SELECT MAX(MESSAGE_ID) AS MESSAGE_ID, CONVERSATION_ID, MAX(SENDER) AS SENDER, MAX(MESSAGE) AS MESSAGE, MAX(MESSAGE_SUBJECT) AS MESSAGE_SUBJECT, MAX(MESSAGE_TAG) AS MESSAGE_TAG, MAX(USER_ID) AS USER_ID, MAX(RECEIPIENT) AS RECEIPIENT, MAX(MESSAGE_STATUS) AS MESSAGE_STATUS, MAX(updated_at) AS updated_at, COUNT(*) AS CNT from messages WHERE SENDER IN ('.implode(',', $all_clients_array).') AND USER_DELETE !="deleted" GROUP BY CONVERSATION_ID ORDER BY CNT',[]);
                        return DB::select('SELECT MAX(MESSAGE_ID) AS MESSAGE_ID, CONVERSATION_ID, MAX(SENDER) AS SENDER, MAX(MESSAGE) AS MESSAGE, MAX(MESSAGE_SUBJECT) AS MESSAGE_SUBJECT, MAX(MESSAGE_TAG) AS MESSAGE_TAG, MAX(USER_ID) AS USER_ID, MAX(RECEIPIENT) AS RECEIPIENT, MAX(MESSAGE_STATUS) AS MESSAGE_STATUS, MAX(updated_at) AS updated_at, MAX(first_name) AS first_name, MAX(middle_name) AS middle_name, MAX(last_name) AS last_name, COUNT(*) AS CNT from (SELECT a.MESSAGE_ID, a.CONVERSATION_ID, a.SENDER, a.MESSAGE, a.MESSAGE_SUBJECT, a.MESSAGE_TAG, a.USER_ID, a.RECEIPIENT, a.MESSAGE_STATUS, a.updated_at, b.first_name, b.middle_name, b.last_name from messages as a INNER JOIN clients as b ON a.SENDER = CAST(b.id as char(255)) WHERE a.SENDER IN ('.implode(',', $all_clients_array).') AND USER_DELETE !="deleted") as msg GROUP BY CONVERSATION_ID ORDER BY CNT',[]);
                }

                /*if($user_id == 1){
                //return Messages::where('SENDER', 'NOT LIKE', $sender.'%')->Where('USER_DELETE', '!=', 1)->get();
                return DB::select('SELECT MAX(MESSAGE_ID) AS MESSAGE_ID, CONVERSATION_ID, MAX(SENDER) AS SENDER, MAX(MESSAGE) AS MESSAGE, MAX(MESSAGE_SUBJECT) AS MESSAGE_SUBJECT, MAX(MESSAGE_TAG) AS MESSAGE_TAG, MAX(USER_ID) AS USER_ID, MAX(RECEIPIENT) AS RECEIPIENT, MAX(MESSAGE_STATUS) AS MESSAGE_STATUS, MAX(updated_at) AS updated_at, COUNT(*) AS CNT from messages WHERE SENDER != ? AND USER_DELETE != "deleted" GROUP BY CONVERSATION_ID ORDER BY CNT',[$sender]);
                }
                else {*/
                    // if(empty($all_clients_array)){
                    //     return [];
                    // }
                 //   else{
                       // return DB::select('SELECT MAX(MESSAGE_ID) AS MESSAGE_ID, CONVERSATION_ID, MAX(SENDER) AS SENDER, MAX(MESSAGE) AS MESSAGE, MAX(MESSAGE_SUBJECT) AS MESSAGE_SUBJECT, MAX(MESSAGE_TAG) AS MESSAGE_TAG, MAX(USER_ID) AS USER_ID, MAX(RECEIPIENT) AS RECEIPIENT, MAX(MESSAGE_STATUS) AS MESSAGE_STATUS, MAX(updated_at) AS updated_at, COUNT(*) AS CNT from messages WHERE SENDER IN ('.implode(',', $all_clients_array).') AND USER_DELETE !="deleted" GROUP BY CONVERSATION_ID ORDER BY CNT',[]);
                   // }

                //}
            //}

                }



         }

    else {
        $response =([
            'Message' => 'Invalid User ID'
        ]);

        return response($response, 400);
    }

    }

   /* public function showMessagesbyUserIDReceived($user_id){


          return Messages::where("RECEIPIENT",$user_id)->get();
    }*/




    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteUserMessage(Request $request)
    {
        $id = $request->id;

        $msgArray = $request->MESSAGE;

                if(is_array($msgArray)){

                    DB::table('messages')
                    ->wherein('CONVERSATION_ID', $msgArray)
                    ->update([
                        'USER_DELETE'=>'deleted',
                        'updated_at' => Carbon::now(),
                    ]);

                    $response = ([
                        'Message' => 'Messages deleted successfully!',
                    ]);

                    return response($response, 201);
                }
                else {
                        $response =([
                            'Message' => 'Invalid Conversation ID!',
                        ]);

                        return response($response, 400);
                }
    }


    public function retagUserMessage(Request $request)
    {
        $id = $request->id;

        $msgArray = $request->MESSAGE;

                if(is_array($msgArray)){

                    DB::table('messages')
                    ->wherein('CONVERSATION_ID', $msgArray)
                    ->update([
                        'MESSAGE_TAG'=>$request->retag,
                        'updated_at' => Carbon::now(),
                    ]);

                    $response = ([
                        'Message' => 'Messages re-tagged successfully!',
                    ]);

                    return response($response, 201);
                }
                else {
                        $response =([
                            'Message' => 'Invalid Conversation ID!',
                        ]);

                        return response($response, 400);
                }
    }
}
