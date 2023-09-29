<?php

namespace App\Http\Controllers;
use App\visamgr_applications;
use App\visamgr_names_of_people_at_address;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;

class NameofPeopleatAddressController extends Controller
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

        $check_application = visamgr_names_of_people_at_address::where('APPLICATION_ID',$application_id)->first();

        if($check_application){

            DB::table('visamgr_names_of_people_at_addresses')
            ->where('APPLICATION_ID', $application_id)
            ->update(['ATTRIBUTES' => $request->FAMILY_IN_HOME_COUNTRY,
                      'created_at' => Carbon::now(),
                      'updated_at' => Carbon::now(),
                    ]);

        }

        else{

            DB::table('visamgr_names_of_people_at_addresses')->insert([
                'APPLICATION_ID'=>$application_id,
                 'ATTRIBUTES'=>$request->FAMILY_IN_HOME_COUNTRY,
                 'created_at' => Carbon::now(),
                 'updated_at' => Carbon::now(),
            ]);
        }
    }

    public function updateNameofPeopleatAddress(Request $request){

        $application_id = $request->APPTYPE_ID;

       // $client_details = visamgr_characters::where('APPLICATION_ID',$application_id)->get();

       DB::table('visamgr_names_of_people_at_addresses')
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


    public function getNameofPeopleatAddress(Request $request){

        $application_id = $request->APPTYPE_ID;

        $people_at_address = visamgr_names_of_people_at_address::where('APPLICATION_ID',$application_id)->get();


        $response = [
            'message'=> 'Membership Details',
            'client_details'=>$people_at_address
            ];

            return response($response, 200);
    }


    public function destroy($application_id)
    {
        return visamgr_names_of_people_at_address::destroy($application_id);
    }

}
