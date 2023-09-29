<?php

namespace App\Http\Controllers;

use App\visamgr_applications;
use App\visamgr_characters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CharacterController extends Controller
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

        $check_application = visamgr_characters::where('APPLICATION_ID',$application_id)->first();

        if($check_application){

            DB::table('visamgr_characters')
            ->where('APPLICATION_ID', $application_id)
            ->update(['CRIMINAL_OFFENCE_ANSWER' => $request->CRIMINAL_OFFENCE_ANSWER,
                      'PENDING_PERSECUTION' => $request->PENDING_PERSECUTION,
                      'DETAILS_OF_PROSECUTIONS' => $request->DETAILS_OF_PROSECUTIONS,
                      'TERRORIST_VIEW' => $request->TERRORIST_VIEW,
                      'DETAILS_OF_TERRORIST_CHARGES' => $request->DETAILS_OF_TERRORIST_CHARGES,
                     // 'GOVERNMENT_WORK' => $request->GOVERNMENT_WORK,
                      'WORKED_FOR_SECURITY' => $request->WORKED_FOR_SECURITY,
                      'DETAILS_OF_WORK' => $request->DETAILS_OF_WORK,
                      'created_at' => Carbon::now(),
                      'updated_at' => Carbon::now(),
                    ]);

        }

        else{

          /*  $request->validate([
                'CRIMINAL_OFFENCE_ANSWER'=>'required|string',
                'PENDING_PERSECUTION'=>'required|string',
                'DETAILS_OF_PROSECUTIONS'=>'required|string',
                'TERRORIST_VIEW'=>'required|string',
                'DETAILS_OF_TERRORIST_CHARGES'=>'required|string',
               // 'GOVERNMENT_WORK'=>'required|string',
                'WORKED_FOR_SECURITY'=>'required|string',
                'DETAILS_OF_WORK'=>'required|string',
               ]);*/

            DB::table('visamgr_characters')->insert([
                'APPLICATION_ID'=>$application_id,
                'CRIMINAL_OFFENCE_ANSWER' => $request->CRIMINAL_OFFENCE_ANSWER,
                'PENDING_PERSECUTION' => $request->PENDING_PERSECUTION,
                'DETAILS_OF_PROSECUTIONS' => $request->DETAILS_OF_PROSECUTIONS,
                'TERRORIST_VIEW' => $request->TERRORIST_VIEW,
                'DETAILS_OF_TERRORIST_CHARGES' => $request->DETAILS_OF_TERRORIST_CHARGES,
                //'GOVERNMENT_WORK' => $request->GOVERNMENT_WORK,
                'WORKED_FOR_SECURITY' => $request->WORKED_FOR_SECURITY,
                'DETAILS_OF_WORK' => $request->DETAILS_OF_WORK,
                 'created_at' => Carbon::now(),
                 'updated_at' => Carbon::now(),
            ]);
        }


        $result = visamgr_characters::where('APPLICATION_ID',$application_id)->get();

        $response = [
            'message'=> 'Character successfully added!',
            'client_details'=>$result
            ];

            return response($response, 200);

    }

    public function updateCharacter(Request $request){

        $application_id = $request->APPTYPE_ID;

       // $client_details = visamgr_characters::where('APPLICATION_ID',$application_id)->get();

       DB::table('visamgr_characters')
       ->where('APPLICATION_ID', $application_id)
       ->update(['CRIMINAL_OFFENCE_ANSWER' => $request->CRIMINAL_OFFENCE_ANSWER,
                'PENDING_PERSECUTION' => $request->PENDING_PERSECUTION,
                'DETAILS_OF_PROSECUTIONS' => $request->DETAILS_OF_PROSECUTIONS,
                'TERRORIST_VIEW' => $request->TERRORIST_VIEW,
                'DETAILS_OF_TERRORIST_CHARGES' => $request->DETAILS_OF_TERRORIST_CHARGES,
                //'GOVERNMENT_WORK' => $request->GOVERNMENT_WORK,
                'WORKED_FOR_SECURITY' => $request->WORKED_FOR_SECURITY,
                'DETAILS_OF_WORK' => $request->DETAILS_OF_WORK,
                 'created_at' => Carbon::now(),
                 'updated_at' => Carbon::now(),
               ]);


               $response = [
                'message'=> 'Character successfully updated!',
                ];

                return response($response, 200);

    }


    public function getCharacter(Request $request){

        $application_id = $request->APPTYPE_ID;

        $client_character = visamgr_characters::where('APPLICATION_ID',$application_id)->get();


        $response = [
            'message'=> 'Character details!',
            'client_details'=>$client_character
            ];

            return response($response, 200);
    }


    public function destroy($application_id)
    {
        return visamgr_characters::destroy($application_id);
    }



}
