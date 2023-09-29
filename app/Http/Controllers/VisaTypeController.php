<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\visamgr_apptypes;
use App\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VisaTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return visamgr_apptypes::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user_id = $request->id;
        $username = User::where('id',$user_id)->value('first_name');

        $request->validate([
            'APPTYPE_NAME'=>'string|required'
        ]);

        DB::table('visamgr_apptypes')->insert([

            'APPTYPE_NAME'=>$request->APPTYPE_NAME,
            'APPSUBCAT_NAME'=>$request->APPSUBCAT_NAME,
            'STATUS'=>$request->STATUS,
            'USER'=>$username,
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()
        ]);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return visamgr_apptypes::where('id',$id)->get();
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
        $row_id = $request->id;

        $user_id = $request->user_id;

        $username = User::where('id',$user_id)->value('first_name');


        DB::table('visamgr_apptypes')
        ->where('id', $row_id)
        ->update(['APPTYPE_NAME' => $request->APPTYPE_NAME,
                  'APPSUBCAT_NAME' => $request->APPSUBCAT_NAME,
                  'STATUS' => $request->STATUS,
                  'USER' => $username,
                 // 'DEFAULT_USER'=>$request->DEFAULT_USER,
                 'updated_at' => Carbon::now()
        ]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $id = $request->id;

        return visamgr_apptypes::destroy($id);
    }
}
