<?php

namespace App\Http\Controllers;

use App\visamgr_applications;
use App\visamgr_locations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TravelController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {    $application_id = $request->APPTYPE_ID;

        $check_application = visamgr_locations::where('APPLICATION_ID',$application_id)->first();

        if($check_application){

            DB::table('visamgr_locations')
            ->where('APPLICATION_ID', $application_id)
            ->update(['COUNTRIES_TRAVELLED' => $request->COUNTRIES_TRAVELLED,
                      'created_at' => Carbon::now(),
                      'updated_at' => Carbon::now(),
                    ]);

        }

        else{

            DB::table('visamgr_locations')->insert([
                'APPLICATION_ID'=>$application_id,
                 'COUNTRIES_TRAVELLED'=>$request->COUNTRIES_TRAVELLED,
                 'created_at' => Carbon::now(),
                 'updated_at' => Carbon::now(),
            ]);
        }
    }


    public function updateTravel(Request $request){

        $application_id = $request->APPTYPE_ID;

       // $client_details = visamgr_characters::where('APPLICATION_ID',$application_id)->get();

       DB::table('visamgr_locations')
       ->where('APPLICATION_ID', $application_id)
       ->update(['COUNTRIES_TRAVELLED' => $request->COUNTRIES_TRAVELLED,
                 'created_at' => Carbon::now(),
                 'updated_at' => Carbon::now(),
               ]);


               $response = [
                'message'=> 'travel details have successfully updated!',
                ];

                return response($response, 200);

    }


    public function getTravel(Request $request){

        $application_id = $request->APPTYPE_ID;

        $client_travel = visamgr_locations::where('APPLICATION_ID',$application_id)->get();


        $response = [
            'message'=> 'Travel Details',
            'client_details'=>$client_travel
            ];

            return response($response, 200);
    }


    public function destroy($application_id)
    {
        return visamgr_locations::destroy($application_id);
    }
}
