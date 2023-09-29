<?php

namespace App\Http\Controllers;

use App\visamgr_applications;
use App\visamgr_dependants;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PeopleAtHomeController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $application_id = $request->APPTYPE_ID;

        $check_application = visamgr_dependants::where('APPLICATION_ID',$application_id)->first();

        if($check_application){

            DB::table('visamgr_dependants')
            ->where('APPLICATION_ID', $application_id)
            ->update(['ATTRIBUTES' => $request->FAMILY_IN_HOME_COUNTRY,
                      'created_at' => Carbon::now(),
                      'updated_at' => Carbon::now(),
                    ]);

        }

        else{

            DB::table('visamgr_dependants')->insert([
                'APPLICATION_ID'=>$application_id,
                 'ATTRIBUTES'=>$request->FAMILY_IN_HOME_COUNTRY,
                 'created_at' => Carbon::now(),
                 'updated_at' => Carbon::now(),
            ]);
        }
    }

    public function updatePeopleAtHome(Request $request){

        $application_id = $request->APPTYPE_ID;

       // $client_details = visamgr_characters::where('APPLICATION_ID',$application_id)->get();

       DB::table('visamgr_dependants')
       ->where('APPLICATION_ID', $application_id)
       ->update(['ATTRIBUTES' => $request->FAMILY_IN_HOME_COUNTRY,
                 'created_at' => Carbon::now(),
                 'updated_at' => Carbon::now(),
               ]);


               $response = [
                'message'=> 'membership details have successfully updated!',
                ];

                return response($response, 200);

    }


    public function getPeopleAtHome(Request $request){

        $application_id = $request->APPTYPE_ID;

        $client_dependants = visamgr_dependants::where('APPLICATION_ID',$application_id)->get();


        $response = [
            'message'=> 'Membership Details',
            'client_details'=>$client_dependants
            ];

            return response($response, 200);
    }


    public function destroy($application_id)
    {
        return visamgr_dependants::destroy($application_id);
    }

}
