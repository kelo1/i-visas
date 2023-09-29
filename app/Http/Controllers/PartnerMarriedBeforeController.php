<?php

namespace App\Http\Controllers;

use App\PartnerMarriedBefore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PartnerMarriedBeforeController extends Controller
{
    public function store(Request $request)
    {
        $application_id = $request->APPTYPE_ID;

        $check_application = PartnerMarriedBefore::where('APPLICATION_ID',$application_id)->first();

        if($check_application){

            DB::table('partner_married_befores')
            ->where('APPLICATION_ID', $application_id)
            ->update(['PARTNERS_EX' => $request->PARTNERS_EX,
                      'created_at' => Carbon::now(),
                      'updated_at' => Carbon::now(),
                    ]);

        }

        else{

            DB::table('partner_married_befores')->insert([
                'APPLICATION_ID'=>$application_id,
                 'PARTNERS_EX'=>$request->PARTNERS_EX,
                 'created_at' => Carbon::now(),
                 'updated_at' => Carbon::now(),
            ]);
        }

        $response = [
            'message'=> 'Details saved successfully',
            ];

            return response($response, 200);
    }


    public function updatePartnerMarriedBefore(Request $request){

        $application_id = $request->APPTYPE_ID;

       // $client_details = visamgr_characters::where('APPLICATION_ID',$application_id)->get();

       DB::table('partner_married_befores')
       ->where('APPLICATION_ID', $application_id)
       ->update(['PARTNERS_EX' => $request->PARTNERS_EX,
                 'created_at' => Carbon::now(),
                 'updated_at' => Carbon::now(),
               ]);


               $response = [
                'message'=> 'client partner married before details have successfully updated!',
                ];

                return response($response, 200);

    }


    public function getPartnerMarriedBefore(Request $request){

        $application_id = $request->APPTYPE_ID;

        $client_partners_married_before = PartnerMarriedBefore::where('APPLICATION_ID',$application_id)->get();


        $response = [
            'message'=> 'partner married before Details',
            'client_details'=>$client_partners_married_before
            ];

            return response($response, 200);
    }


    public function destroy($application_id)
    {
        return PartnerMarriedBefore::destroy($application_id);
    }
}
