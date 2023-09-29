<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\UserReference;



class UserReferenceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return UserReference::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user_id= $request->user_id;

        $user_reference = $request->user_reference_no; //Agent Reference Code AG. eg: AG0001


        $request->validate([
                'user_id'=>'required',
                'user_reference'=>'string|unique:user_references'

            ]);


            $agent= UserReference::create([

                'user_id'=> $user_id,
                'user_reference'=>$user_reference,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            $response = [
                'Message'=>"Agent Reference created!",
            ];

            return response($response, 201);

    }




    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $allUserReference = DB::table('user_references')
       ->join('users', 'users.id', '=', 'user_references.user_id')
       ->select('users.id AS id','users.first_name', 'users.last_name', 'users.email', 'user_references.created_at', 'user_references.user_reference_no')
       ->where('users.id', $id)
       ->get();


       $response =([
        'Message' => 'User details',
        'user_details'=>$allUserReference,
    ]);

    return response($response, 200);

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

        $user_id = User::find($request->user_id);

        $user_reference = $request->user_reference_no;

        if($user_id){

            $request->validate([
                'user_reference_no'=>'string|unique:user_references',
            ]);

            DB::table('user_references')
            ->where('user_id', $request->user_id)
           ->update(['user_reference_no' => $user_reference,
                     'updated_at' => Carbon::now()
            ]);

        }

        $response =([
            'Message' => 'Agent reference successfully updated'
        ]);

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
        //
    }
}
