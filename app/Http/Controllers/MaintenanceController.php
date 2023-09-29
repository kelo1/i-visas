<?php

namespace App\Http\Controllers;
use App\visamgr_applications;
use App\visamgr_maintenance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MaintenanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)

    {

        $application_id = $request->APPTYPE_ID;

        $check_application = visamgr_maintenance::where('APPLICATION_ID',$application_id)->first();

        $request->validate([

            'BANK_NAME'=>'string|required',

            'REGISTERED'=>'string|required',

            'HELD_COUNTRY'=>'string|required',

            'HELD_CURRENCY'=>'string|required',

            'HELD_AMOUNT'=>'numeric|required',

            'HELD_DATE'=>'date|required'

        ]);



        if($check_application){
            DB::table('visamgr_maintenances')
            ->where('APPLICATION_ID', $application_id)
            ->update([

                'APPLICATION_ID'=>$application_id,

                'BANK_NAME'=>$request->BANK_NAME,

                'REGISTERED'=>$request->REGISTERED,

                'HELD_COUNTRY'=>$request->HELD_COUNTRY,

                'HELD_CURRENCY'=>$request->HELD_CURRENCY,

                'HELD_AMOUNT'=>$request->HELD_AMOUNT,

                'HELD_DATE'=>$request->HELD_DATE,

                'created_at' => Carbon::now(),

                'updated_at' => Carbon::now(),

            ]);

        }
        else{


            DB::table('visamgr_maintenances')->insert([

                'APPLICATION_ID'=>$application_id,

                 'BANK_NAME'=>$request->BANK_NAME,

                 'REGISTERED'=>$request->REGISTERED,

                 'HELD_COUNTRY'=>$request->HELD_COUNTRY,

                 'HELD_CURRENCY'=>$request->HELD_CURRENCY,

                 'HELD_AMOUNT'=>$request->HELD_AMOUNT,

                 'HELD_DATE'=>$request->HELD_DATE,

                 'created_at' => Carbon::now(),

                 'updated_at' => Carbon::now(),

            ]);
        }



    }

    public function update(Request $request){

        $application_id = $request->APPTYPE_ID;


        DB::table('visamgr_maintenances')
        ->where('APPLICATION_ID', $application_id)
        ->update([

            'APPLICATION_ID'=>$application_id,

            'BANK_NAME'=>$request->BANK_NAME,

            'REGISTERED'=>$request->REGISTERED,

            'HELD_COUNTRY'=>$request->HELD_COUNTRY,

            'HELD_CURRENCY'=>$request->HELD_CURRENCY,

            'HELD_AMOUNT'=>$request->HELD_AMOUNT,

            'HELD_DATE'=>$request->HELD_DATE,

            'created_at' => Carbon::now(),

            'updated_at' => Carbon::now(),

        ]);


        $response = [
            'message'=> 'Maintenance details have successfully updated!',
            ];

            return response($response, 200);
    }




    public function destroy($application_id)
    {
        return visamgr_maintenance::where('APPLICATION_ID', '=', $application_id)->delete();
        //visamgr_maintenance::destroy($application_id);
    }
}
