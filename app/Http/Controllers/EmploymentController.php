<?php

namespace App\Http\Controllers;


use App\visamgr_applications;
use App\visamgr_employment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EmploymentController extends Controller
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

        $check_application = visamgr_employment::where('APPLICATION_ID',$application_id)->first();

        if($check_application){

            DB::table('visamgr_employments')
            ->where('APPLICATION_ID', $application_id)
            ->update(['EMPLOYER_NAME' => $request->EMPLOYER_NAME,
                    'EMPLOYER_PHONE' => $request->EMPLOYER_PHONE,
                    'EMPLOYER_EMAIL' => $request->EMPLOYER_EMAIL,
                    'EMPLOYMENT_STATUS' => $request->EMPLOYMENT_STATUS,//EMPLOYMENT_DATE
                    'EMPLOYMENT_DATE' => $request->EMPLOYMENT_DATE,
                    'EMPLOYER_ADDRESS1' => $request->EMPLOYER_ADDRESS1,
                    'EMPLOYER_ADDRESS2' => $request->EMPLOYER_ADDRESS2,
                    'EMPLOYER_LOCATION' => $request->EMPLOYER_LOCATION,
                    'EMPLOYER_LOCATION_CODE' => $request->EMPLOYER_LOCATION_CODE,
                    'EMPLOYER_TOWN' => $request->EMPLOYER_TOWN,
                    'EMPLOYER_COUNTRY' => $request->EMPLOYER_COUNTRY,
                    'EMPLOYER_POSTCODE' => $request->EMPLOYER_POSTCODE,
                    'EMPLOYER_COUNTRYPREFIX' => $request->EMPLOYER_COUNTRYPREFIX,
                    'EMPLOYER_COUNTY' => $request->EMPLOYER_COUNTY,
                    'EMPLOYER_FAX' => $request->EMPLOYER_FAX,
                    'EMPLOYER_VATRATE' => $request->EMPLOYER_VATRATE,
                    'JOB_TITLE' => $request->JOB_TITLE,
                    'JOB_END_DATE' => $request->JOB_END_DATE,
                    'SALARY' => $request->SALARY,
                    'SOC_CODE' => $request->SOC_CODE,
                    'SOC_BAND' => $request->SOC_BAND,
                    'ADD_USER' => $request->ADD_USER,
                      'created_at' => Carbon::now(),
                      'updated_at' => Carbon::now(),
                    ]);

        }

        else{

           /* $request->validate([
                'EMPLOYER_NAME'=>'required|string',
                'EMPLOYER_PHONE'=>'required|string',
                'EMPLOYER_EMAIL'=>'required|string',
                'EMPLOYMENT_STATUS'=>'required|string',
                'EMPLOYER_ADDRESS1'=>'required|string',
                // 'EMPLOYER_ADDRESS2'=>'required|string',
                // 'EMPLOYER_LOCATION'=>'required|string',
                // 'EMPLOYER_LOCATION_CODE'=>'required|string',
                // 'EMPLOYER_TOWN'=>'required|string',
                // 'EMPLOYER_COUNTRY'=>'required|string',
                // 'EMPLOYER_POSTCODE'=>'required|string',
                // 'EMPLOYER_COUNTRYPREFIX'=>'required|string',
                // 'EMPLOYER_COUNTY'=>'required|string',
                // 'EMPLOYER_FAX'=>'required|string',
                // 'EMPLOYER_VATRATE'=>'required|string',
              //  'SALARY'=>'required|integer',
               ]);*/

            DB::table('visamgr_employments')->insert([
                'APPLICATION_ID'=>$application_id,
                'EMPLOYER_NAME' => $request->EMPLOYER_NAME,
                 'EMPLOYER_PHONE' => $request->EMPLOYER_PHONE,
                 'EMPLOYER_EMAIL' => $request->EMPLOYER_EMAIL,
                 'EMPLOYMENT_STATUS' => $request->EMPLOYMENT_STATUS,
                 'EMPLOYMENT_DATE' => $request->EMPLOYMENT_DATE,
                 'EMPLOYER_ADDRESS1' => $request->EMPLOYER_ADDRESS1,
                 'EMPLOYER_ADDRESS2' => $request->EMPLOYER_ADDRESS2,
                 'EMPLOYER_LOCATION' => $request->EMPLOYER_LOCATION,
                 'EMPLOYER_LOCATION_CODE' => $request->EMPLOYER_LOCATION_CODE,
                 'EMPLOYER_TOWN' => $request->EMPLOYER_TOWN,
                 'EMPLOYER_COUNTRY' => $request->EMPLOYER_COUNTRY,
                 'EMPLOYER_POSTCODE' => $request->EMPLOYER_POSTCODE,
                 'EMPLOYER_COUNTRYPREFIX' => $request->EMPLOYER_COUNTRYPREFIX,
                 'EMPLOYER_COUNTY' => $request->EMPLOYER_COUNTY,
                 'EMPLOYER_FAX' => $request->EMPLOYER_FAX,
                 'EMPLOYER_VATRATE' => $request->EMPLOYER_VATRATE,
                 'JOB_TITLE' => $request->JOB_TITLE,
                 'JOB_END_DATE' => $request->JOB_END_DATE,
                 'SALARY' => $request->SALARY,
                 'SOC_CODE' => $request->SOC_CODE,
                 'SOC_BAND' => $request->SOC_BAND,
                 'ADD_USER' => $request->ADD_USER,
                 'created_at' => Carbon::now(),
                 'updated_at' => Carbon::now(),
            ]);
        }

    }




    public function updateEmployement(Request $request){

        $application_id = $request->APPTYPE_ID;

       // $client_details = visamgr_characters::where('APPLICATION_ID',$application_id)->get();

       DB::table('visamgr_employments')
       ->where('APPLICATION_ID', $application_id)
       ->update(['EMPLOYER_NAME' => $request->EMPLOYER_NAME,
                 'EMPLOYER_PHONE' => $request->EMPLOYER_PHONE,
                 'EMPLOYER_EMAIL' => $request->EMPLOYER_EMAIL,
                 'EMPLOYMENT_STATUS' => $request->EMPLOYMENT_STATUS,
                 'EMPLOYER_ADDRESS1' => $request->EMPLOYER_ADDRESS1,
                 'EMPLOYER_ADDRESS2' => $request->EMPLOYER_ADDRESS2,
                 'EMPLOYER_LOCATION' => $request->EMPLOYER_LOCATION,
                 'EMPLOYER_LOCATION_CODE' => $request->EMPLOYER_LOCATION_CODE,
                 'EMPLOYER_TOWN' => $request->EMPLOYER_TOWN,
                 'EMPLOYER_COUNTRY' => $request->EMPLOYER_COUNTRY,
                 'EMPLOYER_POSTCODE' => $request->EMPLOYER_POSTCODE,
                 'EMPLOYER_COUNTRYPREFIX' => $request->EMPLOYER_COUNTRYPREFIX,
                 'EMPLOYER_COUNTY' => $request->EMPLOYER_COUNTY,
                 'EMPLOYER_FAX' => $request->EMPLOYER_FAX,
                 'EMPLOYER_VATRATE' => $request->EMPLOYER_VATRATE,
                 'JOB_TITLE' => $request->JOB_TITLE,
                 'JOB_END_DATE' => $request->JOB_END_DATE,
                 'SALARY' => $request->SALARY,
                 'SOC_CODE' => $request->SOC_CODE,
                 'SOC_BAND' => $request->SOC_BAND,
                 'ADD_USER' => $request->ADD_USER,
                 'created_at' => Carbon::now(),
                 'updated_at' => Carbon::now(),
               ]);


               $response = [
                'message'=> 'Employment details have successfully updated!',
                ];

                return response($response, 200);

    }


    public function getEmployment(Request $request){

        $application_id = $request->APPTYPE_ID;

        $client_employment = visamgr_employment::where('APPLICATION_ID',$application_id)->get();


        $response = [
            'message'=> 'Employment Details',
            'client_details'=>$client_employment
            ];

            return response($response, 200);
    }


    public function destroy($application_id)
    {
        return visamgr_employment::destroy($application_id);
    }

}
