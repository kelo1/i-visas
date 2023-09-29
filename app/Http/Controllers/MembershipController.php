<?php

namespace App\Http\Controllers;
use App\visamgr_applications;
use App\visamgr_memberships;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MembershipController extends Controller
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

        $check_application = visamgr_memberships::where('APPLICATION_ID',$application_id)->first();

        if($check_application){

            DB::table('visamgr_memberships')
            ->where('APPLICATION_ID', $application_id)
            ->update(['ATTRIBUTES' => $request->MEMBERSHIP,
                      'created_at' => Carbon::now(),
                      'updated_at' => Carbon::now(),
                    ]);

        }

        else{

            DB::table('visamgr_memberships')->insert([
                'APPLICATION_ID'=>$application_id,
                 'ATTRIBUTES'=>$request->MEMBERSHIP,
                 'created_at' => Carbon::now(),
                 'updated_at' => Carbon::now(),
            ]);
        }
    }

    public function updateMembership(Request $request){

        $application_id = $request->APPTYPE_ID;

       // $client_details = visamgr_characters::where('APPLICATION_ID',$application_id)->get();

       DB::table('visamgr_memberships')
       ->where('APPLICATION_ID', $application_id)
       ->update(['ATTRIBUTES' => $request->MEMBERSHIP,
                 'created_at' => Carbon::now(),
                 'updated_at' => Carbon::now(),
               ]);


               $response = [
                'message'=> 'membership details have successfully updated!',
                ];

                return response($response, 200);

    }


    public function getMembership(Request $request){

        $application_id = $request->APPTYPE_ID;

        $client_employment = visamgr_memberships::where('APPLICATION_ID',$application_id)->get();


        $response = [
            'message'=> 'Membership Details',
            'client_details'=>$client_employment
            ];

            return response($response, 200);
    }


    public function destroy($application_id)
    {
        return visamgr_memberships::destroy($application_id);
    }
}
