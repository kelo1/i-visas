<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Messages;
use App\Notifications\MessageNotification;
use App\User;
use App\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Str;
// use GuzzleHttp\Promise\Create;
// use GuzzleHttp\Psr7\Message;

class MessageController extends Controller
{
    //Enable middleware route for authentication
   /* public function __construct()
    {
        $this->middleware('auth');
        // $this->client =  Auth::client();
    }*/

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
    public function sendClientMessage(Request $request)
    {
        // $request->token;
        $client_id = $request->id;

       /* if (!Auth::check()) {
            // The user is logged in...

            $response =([
                'Message' => 'Re-login'
            ]);
            return response($response, 401);
        }*/

        // $fromAddress = Client::where('id',$client_id)
        // ->value('email');

        $user_fname ='i-visas';

        $subject = $request->MESSAGE_SUBJECT;
        $uuid = Str::uuid()->toString();

        $request->validate([
            'MESSAGE'=>'required|string',
            'MESSAGE_SUBJECT'=>'required|string',
            'MESSAGE_TAG'=>'required|string',
        ]);


       DB::table('Messages')->insert([
        'MESSAGE'=>$request->MESSAGE,
        'MESSAGE_SUBJECT'=>$request->MESSAGE_SUBJECT,
        'MESSAGE_TAG'=>$request->MESSAGE_TAG,
        'SENDER'=>$client_id,
        'CONVERSATION_ID'=>$uuid,
        'RECEIPIENT'=>$user_fname,
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
        ]);


    //Get recently inserted id
    $itemId = DB::getPdo()->lastInsertId();
   // dd($itemId);


        $user = User::findOrFail(1);
        $message = Messages::find($itemId);
        $user->notify(new MessageNotification($user,$message, $subject));

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
            //'MESSAGE_ID'=>$itemId,
           // 'CONVERSATION_ID'=>$uuid
        ]);

        return response($response, 201);
    }

    public function searchRecevied($id, $keyword){

        $client_id = $id;
        $search = $keyword;
         //return Messages::where("RECEIPIENT",$client_id)->get();
         return Messages::where('RECEIPIENT',$client_id)->where('CLIENT_DELETE', '!=', "deletedS")
         ->where(function ($query) use($search) {
             $query->where('MESSAGE_SUBJECT', 'like', '%' . $search . '%')
                ->orWhere('MESSAGE_TAG', 'like', '%' . $search . '%')
                ->orWhere('MESSAGE', 'like', '%' . $search . '%');
                //->andWhere('CLIENT_DELETE', '!=', 1);
             })->get();
     }


     public function searchSent($id, $keyword){


         //return Messages::where("SENDER",$client_id)->get();
         $client_id = $id;
         $search = $keyword;
         //return Messages::where("RECEIPIENT",$client_id)->get();
         return Messages::where('SENDER',$client_id)->where('CLIENT_DELETE', '!=', 1)
         ->where(function ($query) use($search) {
             $query->where('MESSAGE_SUBJECT', 'like', '%' . $search . '%')
                ->orWhere('MESSAGE_TAG', 'like', '%' . $search . '%')
                ->orWhere('MESSAGE', 'like', '%' . $search . '%');
                //->andWhere('CLIENT_DELETE', '!=', 1);
             })->get();
         /* return Messages::where('SENDER',$client_id)->where(function ($query) use($search) {
             $query->where('MESSAGE_SUBJECT', 'like', '%' . $search . '%')
                ->orWhere('MESSAGE_TAG', 'like', '%' . $search . '%')
                ->orWhere('MESSAGE', 'like', '%' . $search . '%')
                ->andWhere('CLIENT_DELETE', '!=', 1);
             })->get(); */
     }

     public function viewClientMessage(Request $request){
        // $user_id = $request->session()->get('id');
         $client_id = $request->id;
         $CONVERSATION_ID = $request->CONVERSATION_ID;
         //update Message table

         $Message = Messages::where('CONVERSATION_ID',$CONVERSATION_ID)->where('CLIENT_DELETE', '!=', 1)->latest()->first();





         DB::table('messages')
         ->where('CONVERSATION_ID', $CONVERSATION_ID)
         ->update([
             'MESSAGE_STATUS'=>'read'
         ]);

          //update Notifications table
          DB::table('notifications')
          ->where('CONVERSATION_ID', $CONVERSATION_ID)
          ->update([
             'READ_AT' => Carbon::now(),
          ]);



          $response =([
             'Message' => 'Message received successfully!',
             "attributes"=>  Messages::where('CONVERSATION_ID',$CONVERSATION_ID)->get()
         ]);

         return response($response, 201);
        // var_dump($Message);
       // }
     }


     public function viewClientReceivedMessage(Request $request){
        // $user_id = $request->session()->get('id');
         $client_id = $request->id;
         $CONVERSATION_ID = $request->CONVERSATION_ID;
         //update Message table

         $Message = Messages::where('CONVERSATION_ID',$CONVERSATION_ID)->where('CLIENT_DELETE', '!=', 'deleted')->latest()->first();

          DB::table('messages')
         ->where('CONVERSATION_ID', $CONVERSATION_ID)
         ->update([
             'MESSAGE_STATUS'=>'read'
         ]);

          //update Notifications table
          DB::table('notifications')
          ->where('CONVERSATION_ID', $CONVERSATION_ID)
          ->update([
             'READ_AT' => Carbon::now(),
          ]);



          $response =([
             'Message' => 'Message received successfully!',
             "attributes"=>  Messages::where('CONVERSATION_ID',$CONVERSATION_ID)->get()
         ]);

         return response($response, 201);
        // var_dump($Message);
       // }
     }



public function viewClientSentMessage(Request $request){
        // $user_id = $request->session()->get('id');
         $client_id = $request->id;
         $CONVERSATION_ID = $request->CONVERSATION_ID;
         //update Message table

         $Message = Messages::where('CONVERSATION_ID',$CONVERSATION_ID)->where('CLIENT_DELETE', '!=', 'deleted')->latest()->first();

          $response =([
             'Message' => 'Message received successfully!',
             "attributes"=>  Messages::where('CONVERSATION_ID',$CONVERSATION_ID)->get()
         ]);

         return response($response, 201);
        // var_dump($Message);
       // }
     }





    public function replyUserMessage(Request $request)
    {
        $client_id = $request->id;

        $CONVERSATION_ID = $request->CONVERSATION_ID;

       /* if(session()->get('id')!=$client_id){

         $response =([
             'Message' => 'Re-login'
         ]);
         return response($response, 401);

       }*/




        $user_fname = 'i-visas';

        $MESSAGE_SUBJECT = Messages::where('CONVERSATION_ID',$CONVERSATION_ID)
        ->value('MESSAGE_SUBJECT');

        $MESSAGE_TAG = Messages::where('CONVERSATION_ID',$CONVERSATION_ID)
        ->value('MESSAGE_TAG');

        $uuid = $CONVERSATION_ID; ///Messages::where('CONVERSATION_ID',$CONVERSATION_ID)->value('CONVERSATION_ID');

        // $fromAddress = Client::where('id',$client_id)
        // ->value('email');


        $request->validate([
            'MESSAGE'=>'required|string',
            'CONVERSATION_ID'=>'required|string',
        ]);




        DB::table('messages')->insert([
            'MESSAGE'=>$request->MESSAGE,
            'MESSAGE_SUBJECT'=>$MESSAGE_SUBJECT,
            'MESSAGE_TAG'=>$MESSAGE_TAG,
            'SENDER'=>$client_id,
            'CONVERSATION_ID'=>$uuid,
            'RECEIPIENT'=>$user_fname,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            ]);

            $itemId = DB::getPdo()->lastInsertId();

         //Send broadcast notification to user and update notification table
         $user = User::findOrFail(1);
        // $user->notify(new MessageNotification($user,$fromAddress, $MESSAGE_SUBJECT));
        $message = Messages::findOrFail($itemId);
        $user->notify(new MessageNotification($user,$message, $MESSAGE_SUBJECT));
         //Store Message notification in database


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
    public function showMessage($CONVERSATION_ID)
    {

        return Messages::find($CONVERSATION_ID);
    }


    public function showMessagesbyClientID($client_id){


        //return Messages::where("SENDER",$client_id)->where('CLIENT_DELETE', '!=', 1)->get();
         return DB::select('SELECT MAX(MESSAGE_ID) AS MESSAGE_ID, CONVERSATION_ID, MAX(SENDER) AS SENDER, MAX(MESSAGE) AS MESSAGE, MAX(MESSAGE_SUBJECT) AS MESSAGE_SUBJECT, MAX(MESSAGE_TAG) AS MESSAGE_TAG, MAX(USER_ID) AS USER_ID, MAX(RECEIPIENT) AS RECEIPIENT, MAX(MESSAGE_STATUS) AS MESSAGE_STATUS, MAX(updated_at) AS updated_at, COUNT(*) AS CNT from messages WHERE SENDER = ? AND CLIENT_DELETE != "deleted" GROUP BY CONVERSATION_ID ORDER BY CNT',[$client_id]);
    }

    public function showMessagesbyClientIDReceived($client_id){


          //return Messages::where("RECEIPIENT",$client_id)->where('CLIENT_DELETE', '!=', 1)->get();
          return DB::select('SELECT MAX(MESSAGE_ID) AS MESSAGE_ID, CONVERSATION_ID, MAX(SENDER) AS SENDER, MAX(MESSAGE) AS MESSAGE, MAX(MESSAGE_SUBJECT) AS MESSAGE_SUBJECT, MAX(MESSAGE_TAG) AS MESSAGE_TAG, MAX(USER_ID) AS USER_ID, MAX(RECEIPIENT) AS RECEIPIENT, MAX(MESSAGE_STATUS) AS MESSAGE_STATUS, MAX(updated_at) AS updated_at, COUNT(*) AS CNT from messages WHERE RECEIPIENT = ? AND CLIENT_DELETE != "deleted" GROUP BY CONVERSATION_ID ORDER BY CNT',[$client_id]);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteMessage(Request $request)
    {

    $id = $request->id;

    $msgArray = $request->MESSAGE;

            if(is_array($msgArray)){
            // foreach($msgArray as $value) {
                //return Messages::destroy($id);
                DB::table('messages')
                ->wherein('CONVERSATION_ID', $msgArray)
                ->update([
                    'CLIENT_DELETE'=>'deleted',
                    'updated_at' => Carbon::now(),
                ]);

            //  }

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
            }    }
}
