<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Client;
use App\User;
use App\Location;
use App\user_location;
use App\visamgr_branches;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Location::all();
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
            'COUNTRY'=>'string|required',
          //  'DEFAULT_LOCATION'=>'string|required',
            // 'ADDRESS1'=>'string',
            // 'ADDRESS2'=>'string',
            // 'TOWN'=>'string',
            // 'COUNTY'=>'string',
            // 'POSTCODE'=>'string',
            // 'TELEPHONE'=>'string',
            // 'FAX'=>'string',
            // 'EMAIL'=>'string',
            // 'COUNTRY_PREFIX'=>'string',
            // 'VAT_RATE'=>'double'
        ]);

        DB::table('locations')->insert([
             'COUNTRY'=>$request->COUNTRY,
            // 'DEFAULT_LOCATION'=>$request->DEFAULT_LOCATION,
             'BRANCH_ID'=>$request->BRANCH_ID,
            //  'LOCATION_CODE'=>$request->LOCATION_CODE,
            //  'ADDRESS1'=>$request->ADDRESS1,
            //  'ADDRESS2'=>$request->ADDRESS2,
            //  'TOWN'=>$request->TOWN,
            //  'COUNTY'=>$request->COUNTY,
            //  'POSTCODE'=>$request->POSTCODE,
            //  'TELEPHONE'=>$request->TELEPHONE,
            //  'FAX'=>$request->FAX,
            //  'EMAIL'=>$request->EMAIL,
            //  'COUNTRY_PREFIX'=>$request->COUNTRY_PREFIX,
            //  'VAT_RATE'=>$request->VAT_RATE,
             'USER'=>$username,
             'created_at' => Carbon::now(),
             'updated_at' => Carbon::now(),
        ]);

        return response([
            'message'=>'Country added successfully',
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showLocationDetails($id)
{
    return DB::table('locations')
    ->join('visamgr_branches', 'locations.BRANCH_ID', '=', 'visamgr_branches.id')
    ->join('users', 'locations.USER_ID', '=', 'users.id')
    ->select('locations.*', 'visamgr_branches.LOCATION_CODE', 'visamgr_branches.DEFAULT_USER', 'users.first_name')
    ->where("locations.id",$id)
    ->get();
}


    public function showClientLocation($client_id)
    {
       $country_id =  DB::table('client_location')->where('client_id',$client_id)->value('location_id');

       $branch_id = Location::where('id',$country_id)->value('BRANCH_ID');

      // $default_location = visamgr_branches::where('id',$branch_id)->value('LOCATION_NAME');

        return $branch_id;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request )
    {
        $id = $request->id;
         /* $location = Location::find($id);

        $location->update($request->all());

        return $location; */

        $request->validate([
            //Personal Information
            'id'=>'numeric|required',
            'BRANCH_ID'=>'numeric|required',
        ]);

        DB::table('locations')
        ->where('id', $id)
        ->update(['BRANCH_ID' => $request->BRANCH_ID,
                  'USER_ID' => $request->USER_ID,
                 // 'DEFAULT_USER'=>$request->DEFAULT_USER,
        'updated_at' => Carbon::now()
        ]);
    }


    public function updateUserLocation(Request $request){

        DB::table('user_locations')
                ->where('user_id', $request->user_id)
                ->update(['location_id' => $request->group_id,
                'updated_at' => Carbon::now()
                ]);


         return response([
              'message'=>'User has been successfully added to a location',
          ], 201);

    }

    public function LocationBranch()
{
    return DB::table('locations')
            ->join('visamgr_branches', 'locations.BRANCH_ID', '=', 'visamgr_branches.id')
            ->join('users', 'locations.USER_ID', '=', 'users.id')
            ->select('locations.*', 'visamgr_branches.LOCATION_CODE', 'users.first_name')
            ->get();
}



 public function search($search)
 {

    return DB::table('locations')
    ->join('visamgr_branches', 'locations.BRANCH_ID', '=', 'visamgr_branches.id')
    ->select('locations.*', 'visamgr_branches.LOCATION_CODE', 'visamgr_branches.DEFAULT_USER')
    ->where(function ($query) use($search) {
    $query->where('locations.COUNTRY', 'like', '%' . $search . '%')
       ->orWhere('visamgr_branches.LOCATION_CODE', 'like', '%' . $search . '%')
       ->orWhere('visamgr_branches.DEFAULT_USER', 'like', '%' . $search . '%');
 })->get();



}


    public function updateClientLocation(Request $request)
    {
        $user_id = $request->id;
        $client_id =$request->client_id;

        $client_location =  DB::table('client_location')->find($client_id);


        $client_location->update($request->all());

        return $client_location;

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
