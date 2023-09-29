<?php

namespace App\Http\Controllers;

use App\PreviousMarriage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PreviousMarriageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $application_id = $request->APPTYPE_ID;

        $check_application = PreviousMarriage::where('APPLICATION_ID',$application_id)->first();

        if($check_application){

            DB::table('previous_marriages')
            ->where('APPLICATION_ID', $application_id)
            ->update(['PREVIOUS_MARRIAGE' => $request->PREVIOUS_MARRIAGE,
                      'created_at' => Carbon::now(),
                      'updated_at' => Carbon::now(),
                    ]);

        }

        else{

            DB::table('previous_marriages')->insert([
                'APPLICATION_ID'=>$application_id,
                 'PREVIOUS_MARRIAGE'=>$request->PREVIOUS_MARRIAGE,
                 'created_at' => Carbon::now(),
                 'updated_at' => Carbon::now(),
            ]);
        }


        $response = [
            'message'=> 'Details saved successfully',
            ];

            return response($response, 200);
        }


    public function updateprevious_marriages(Request $request){

        $application_id = $request->APPTYPE_ID;

       // $client_details = visamgr_characters::where('APPLICATION_ID',$application_id)->get();

       DB::table('previous_marriages')
       ->where('APPLICATION_ID', $application_id)
       ->update(['PREVIOUS_MARRIAGE' => $request->PREVIOUS_MARRIAGE,
                 'created_at' => Carbon::now(),
                 'updated_at' => Carbon::now(),
               ]);


               $response = [
                'message'=> 'client previous marriagee details have successfully updated!',
                ];

                return response($response, 200);

    }


    public function getprevious_marriages(Request $request){

        $application_id = $request->APPTYPE_ID;

        $client_previous_marriages = PreviousMarriage::where('APPLICATION_ID',$application_id)->get();


        $response = [
            'message'=> 'client_previous_marriages',
            'client_details'=>$client_previous_marriages
            ];

            return response($response, 200);
    }


    public function destroy($application_id)
    {
        return PreviousMarriage::destroy($application_id);
    }
}
