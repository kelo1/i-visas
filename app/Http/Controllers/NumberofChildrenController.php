<?php

namespace App\Http\Controllers;
use App\visamgr_applications;
use App\visamgr_children;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class NumberofChildrenController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {  $application_id = $request->APPTYPE_ID;

        $check_application = visamgr_children::where('APPLICATION_ID',$application_id)->first();

        if($check_application){

            DB::table('visamgr_childrens')
            ->where('APPLICATION_ID', $application_id)
            ->update(['ATTRIBUTES' => $request->CHILDREN,
                      'created_at' => Carbon::now(),
                      'updated_at' => Carbon::now(),
                    ]);

        }

        else{

            DB::table('visamgr_childrens')->insert([
                'APPLICATION_ID'=>$application_id,
                 'ATTRIBUTES'=>$request->CHILDREN,
                 'created_at' => Carbon::now(),
                 'updated_at' => Carbon::now(),
            ]);
        }
    }


    public function updateChildren(Request $request){

        $application_id = $request->APPTYPE_ID;

       // $client_details = visamgr_characters::where('APPLICATION_ID',$application_id)->get();

       DB::table('visamgr_childrens')
       ->where('APPLICATION_ID', $application_id)
       ->update(['ATTRIBUTES' => $request->CHILDREN,
                 'created_at' => Carbon::now(),
                 'updated_at' => Carbon::now(),
               ]);


               $response = [
                'message'=> 'Children details have successfully updated!',
                ];

                return response($response, 200);

    }


    public function getChildren(Request $request){

        $application_id = $request->APPTYPE_ID;

        $client_children = visamgr_children::where('APPLICATION_ID',$application_id)->get();


        $response = [
            'message'=> 'Employment Details',
            'client_details'=>$client_children
            ];

            return response($response, 200);
    }


    public function destroy($application_id)
    {
        return visamgr_children::destroy($application_id);
    }

}
