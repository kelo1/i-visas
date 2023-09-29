<?php

namespace App\Http\Controllers;

use App\visamgr_applications;
use App\visamgr_qualifications;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class QualificationController extends Controller
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

        $check_application = visamgr_qualifications::where('APPLICATION_ID',$application_id)->first();

        if($check_application){

            DB::table('visamgr_qualifications')
            ->where('APPLICATION_ID', $application_id)
            ->update(['QUALIFICATION' => $request->QUALIFICATION,
                      'created_at' => Carbon::now(),
                      'updated_at' => Carbon::now(),
                    ]);

        }

        else{

            DB::table('visamgr_qualifications')->insert([
                'APPLICATION_ID'=>$application_id,
                 'QUALIFICATION'=>$request->QUALIFICATION,
                 'created_at' => Carbon::now(),
                 'updated_at' => Carbon::now(),
            ]);
        }
    }


    public function updateQualification(Request $request){

        $application_id = $request->APPTYPE_ID;

       // $client_details = visamgr_characters::where('APPLICATION_ID',$application_id)->get();

       DB::table('visamgr_qualifications')
       ->where('APPLICATION_ID', $application_id)
       ->update(['QUALIFICATION' => $request->QUALIFICATION,
                 'created_at' => Carbon::now(),
                 'updated_at' => Carbon::now(),
               ]);


               $response = [
                'message'=> 'membership details have successfully updated!',
                ];

                return response($response, 200);

    }


    public function getQualification(Request $request){

        $application_id = $request->APPTYPE_ID;

        $client_qualification = visamgr_qualifications::where('APPLICATION_ID',$application_id)->get();


        $response = [
            'message'=> 'Membership Details',
            'client_details'=>$client_qualification
            ];

            return response($response, 200);
    }


    public function destroy($application_id)
    {
        return visamgr_qualifications::destroy($application_id);
    }

}
