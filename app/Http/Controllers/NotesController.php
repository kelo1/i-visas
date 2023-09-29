<?php

namespace App\Http\Controllers;

use App\Notes;
use App\visamgr_applications;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\User;
use Svg\Tag\Rect;

class NotesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Notes::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {   $user_id = $request->id;
        $application_id = $request->APPLICATION_ID;

        //$user = User::where('id',$user_id)->value('first_name');


        $request->validate([
            'DESCRIPTION'=>'required|string'

        ]);

        DB::table('notes')->insert([
            'APPLICATION_ID'=>$application_id,
            'DESCRIPTION'=>$request->DESCRIPTION,
            'USER'=>$user_id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
         ]);

         $response = [
            'message'=> 'Note added successfully!',
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
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
       $application_id = $request->APPLICATION_ID;

       return Notes::destroy($application_id);
    }
}
