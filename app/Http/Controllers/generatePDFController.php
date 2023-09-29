<?php

namespace App\Http\Controllers;
use App\Client;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\PreScreening;
use App\visamgr_applications;
//use Barryvdh\DomPDF\PDF as DomPDFPDF;
use Barryvdh\DomPDF\Facade as PDF;


class generatePDFController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function generatePrescreeningPDF(Request $request)
    {
        $client_id = $request->client_id;

        $client_prescreening = PreScreening::where('client_id',$client_id)->get();

        $client_email = Client::where('id',$client_id)->value('email');

        $prescreeningdata = ['client_prescreening'=>$client_prescreening ];

        $pdf = PDF::loadView('generateprescreeningpdf', array('clientprescreeningdata'=>$prescreeningdata));

       //$pdf = PDF::output(array('clientprescreeningdata'=>$prescreeningdata));

        $pdf->setOptions(['isPhpEnabled' => true,'isRemoteEnabled' => true]);

        $filename = $client_email.'.pdf';

        // Save file to the directory
        $pdf->save('public/Prescreening'.$filename);

        //Download Pdf
        return $pdf->download($filename);

        // Or return to view pdf
        //return view('pdfview');

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function generateApplicationPDF(Request $request)
    {
        $application_id = $request->APPLICATION_ID;

        $client_application = visamgr_applications::where('APPTYPE_ID',$application_id)->get();

        $applicationdata = ['client_application'=>$client_application ];

        $pdf = PDF::loadView('generateapplicationpdf', array('clientapplicationdata'=>$applicationdata));

        $pdf->setOptions(['isPhpEnabled' => true,'isRemoteEnabled' => true]);

        $filename = $application_id.'.pdf';

        // Save file to the directory
        $pdf->save('public/Application'.$filename);

        //Download Pdf
        return $pdf->download($filename);

        // Or return to view pdf
        //return view('pdfview');

    }

}
