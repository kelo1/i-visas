<?php

namespace App\Http\Controllers;
use App\User;
use App\visamgr_tracking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Application;

class TrackingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return visamgr_tracking::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {    $application_id = $request->APPTYPE_ID;

        $request->validate([
            'TRACKING_TYPE_ID'=>'integer|required',
            'DIRECTION'=>'string|required',
            'TRACKING_DATE'=>'date|required',
            'TRACKING_ID'=>'string|required',
        ]);
        DB::table('visamgr_trackings')->insert([
            'APPLICATION_ID'=>$application_id,
            'CLIENT_ID'=>$request->CLIENT_ID,
             'TRACKING_TYPE_ID'=>$request->TRACKING_TYPE_ID,
             'DIRECTION'=>$request->DIRECTION,
             'TRACKING_DATE'=>$request->TRACKING_DATE,
             'TRACKING_NOTE'=>$request->TRACKING_NOTE,
             'TRACKING_ID'=>$request->TRACKING_ID,
             'created_at' => Carbon::now(),
             'updated_at' => Carbon::now(),

        ]);

        $response = [
            'message'=> 'Tracking added Successfully!',

            ];

            return response($response, 201);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */


    public function showTrackingbyClientID($client_id)
    {
      return  visamgr_tracking::where('CLIENT_ID',$client_id)->get();
    }

    public function showTrackingbyApplicationID($application_id)
    {
        return  visamgr_tracking::where('APPLICATION_ID',$application_id)->get();
    }

    public function showTrackingbyID($id)
    {
         return visamgr_tracking::join('visamgr_tracking_types', 'visamgr_tracking_types.RECID', '=', 'visamgr_trackings.TRACKING_TYPE_ID')->where('visamgr_trackings.id',$id)->get(['visamgr_trackings.*', 'visamgr_tracking_types.TRACK_CODE', 'visamgr_tracking_types.TRACK_URL']);
    }

    public function search($search)
    {

            return DB::table('visamgr_trackings')
                     ->where(function ($query) use($search) {
                        $query->where('TRACKING_ID','like','%'.$search.'%')
                        ->orWhere('APPLICATION_ID','like','%'.$search.'%');
                     })->get();

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
        $application_id = $request->APPTYPE_ID;

        $id = visamgr_tracking::where('APPLICATION_ID',$application_id)->value('id');
        $tracking = visamgr_tracking::find($id);

        $tracking->update($request->all());

        $response = [
            'message'=> 'Tracking details updated Successfully!',
            'attributes'=> visamgr_tracking::where('APPLICATION_ID',$application_id)->get()

            ];

            return response($response, 201);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return visamgr_tracking::destroy($id);
    }
}
