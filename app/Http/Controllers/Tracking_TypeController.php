<?php

namespace App\Http\Controllers;

use App\User;
use App\visamgr_tracking;
use App\visamgr_tracking_type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Application;

class Tracking_TypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return visamgr_tracking_type::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       $user_id = $request->user_id;

        $request->validate([
            'TRACK_CODE'=>'string|required',
            'TRACK_URL'=>'string|required',
            'STATUS'=>'string|required',

        ]);

        DB::table('visamgr_tracking_types')->insert([
            'TRACK_CODE'=>$request->TRACK_CODE,
             'TRACK_URL'=>$request->TRACK_URL,
             'STATUS'=>$request->STATUS,
             'USER'=>$user_id,
             'created_at' => Carbon::now(),
             'updated_at' => Carbon::now(),

        ]);

        $response = [
            'message'=> 'Courrier service added Successfully!',

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
        return DB::table('visamgr_tracking_types')->Where('RECID',$id)->get();//return visamgr_tracking_type::find($id);
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
        $id = $request->id;
        $request->validate([
            'TRACK_CODE'=>'string|required',
            'TRACK_URL'=>'string|required',
            'STATUS'=>'string|required',

        ]);


        DB::table('visamgr_tracking_types')
              ->where('RECID',$id)
              ->update([
                'TRACK_CODE'=>$request->TRACK_CODE,
                 'TRACK_URL'=>$request->TRACK_URL,
                 'STATUS'=>$request->STATUS,
                 'USER'=>$request->USER,
            ]);

        $response = [
            'message'=> 'Courrier details updated Successfully!',
            ];


            return response($response, 201);
    }

    public function search($search)
    {

            $query = DB::table('visamgr_tracking_types')
                     ->where(function ($query) use($search) {
                        $query->where('TRACK_CODE','like','%'.$search.'%')
                        ->orWhere('TRACK_URL','like','%'.$search.'%')
                        ->orWhere('STATUS','like','%'.$search.'%');
                     })->get();

                     $response = [
                        'message'=> 'great!',
                        'attributes'=>$query
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
        return visamgr_tracking_type::destroy($id);
    }
}
