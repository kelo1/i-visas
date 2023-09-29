<?php

namespace App\Http\Controllers;
use App\visamgr_applications;
use App\visamgr_other_nationality;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OtherNationalityController extends Controller
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

        $check_application = visamgr_other_nationality::where('APPLICATION_ID',$application_id)->first();

        if($check_application){

            DB::table('visamgr_other_nationalities')
            ->where('APPLICATION_ID', $application_id)
            ->update(['ATTRIBUTES' => $request->OTHER_NATIONALITY,
                      'created_at' => Carbon::now(),
                      'updated_at' => Carbon::now(),
                    ]);

        }

        else{

            DB::table('visamgr_other_nationalities')->insert([
                'APPLICATION_ID'=>$application_id,
                 'ATTRIBUTES'=>$request->OTHER_NATIONALITY,
                 'created_at' => Carbon::now(),
                 'updated_at' => Carbon::now(),
            ]);
        }
    }

    public function updateOtherNationality(Request $request){

        $application_id = $request->APPTYPE_ID;

       // $client_details = visamgr_characters::where('APPLICATION_ID',$application_id)->get();

       DB::table('visamgr_other_nationalities')
       ->where('APPLICATION_ID', $application_id)
       ->update(['ATTRIBUTES' => $request->OTHER_NATIONALITY,
                 'created_at' => Carbon::now(),
                 'updated_at' => Carbon::now(),
               ]);


               $response = [
                'message'=> 'other nationality details have successfully updated!',
                ];

                return response($response, 200);

    }


    public function getOtherNationality(Request $request){

        $application_id = $request->APPTYPE_ID;

        $client_nationality = visamgr_other_nationality::where('APPLICATION_ID',$application_id)->get();


        $response = [
            'message'=> 'Membership Details',
            'client_details'=>$client_nationality
            ];

            return response($response, 200);
    }


    public function destroy($application_id)
    {
        return visamgr_other_nationality::destroy($application_id);
    }

}
