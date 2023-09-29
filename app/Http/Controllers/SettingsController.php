<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Settings;
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $setting = DB::table('settings')
        ->select('id','COMPANY_NAME','COMPANY_PHONE','COMPANY_EMAIL')
        ->get();

        $logoPath = "";
          // $fileUrl = "";

          $logoPath = DB::table('settings')->value('COMPANY_LOGO');

        if($logoPath !== "" && Storage::disk('s3')->exists($logoPath)){

            // $baseurl = env('APP_URL')."/storage"."/";
            // $fileUrl = $baseurl.$logoPath;

         //   $baseURL = env('baseURL',''); ////Note, you can place the base URL in your env file.
         //   $fileUrl = $baseURL.urlencode($logoPath);

            $s3client = new S3Client([
                'credentials' => [
                    'key'    => env('AWS_ACCESS_KEY_ID'),
                    'secret' => env('AWS_SECRET_ACCESS_KEY'),
                ],
                'region' => env('AWS_DEFAULT_REGION'),
                'version' => 'latest',
            ]);


            $bucket_name = env('AWS_BUCKET');


    try {
        // Get the object.
        $result = $s3client->getObject([
            'Bucket' => $bucket_name,
            'Key'    => $logoPath
        ]);

    $mimetype = $result['ContentType'] . "\n";
    $data = base64_encode($result['Body']);

    $blob = 'data:'.$mimetype.';base64,'.$data;

    $response =([
        'URL' => $blob,
        'contentType' => $result['ContentType'],
        'Info' => $setting
    ]);

    return response($response, 200);


    } catch (S3Exception $e) {
        echo $e->getMessage() . PHP_EOL;
    }


 }
 else {
    $response =([
        'URL' => null,
        'Info' => $setting
    ]);

    return response($response, 200);
 }


        // $response =([
        //     'URL' => $fileUrl,
        //     'Info' => $setting,
        //    // 'APP_URL'=>env('APP_URL')
        //  ]);

        // return response($response, 200);

}

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $request->validate([
            'COMPANY_NAME'=>'string|required',
            'COMPANY_PHONE'=>'string|required',
            'COMPANY_EMAIL'=>'required|email',
            //'file'=>'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        $filePath = "";

        if($request->file){

            $request->validate([
                'file'=>'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
            ]);

            $file = $request->file;
            $filePath ='company_logo/'.$file->getClientOriginalName();
            //$filePath =$file->getClientOriginalName();
            Storage::disk('s3')->put($filePath, file_get_contents($file));
        }

        DB::table('settings')->insert([
            'COMPANY_NAME'=>$request->COMPANY_NAME,
             'COMPANY_PHONE'=>$request->COMPANY_PHONE,
             'COMPANY_EMAIL'=>$request->COMPANY_EMAIL,
             'COMPANY_LOGO'=>$filePath,
             'created_at' => Carbon::now(),
             'updated_at' => Carbon::now(),

        ]);

        return response([
            'message'=>'Company details added successfully',
            //'token'=>$token
        ], 201);
       /* $request->validate([
            'COMPANY_NAME'=>'string|required',
            'COMPANY_PHONE'=>'string|required',
            'COMPANY_EMAIL'=>'required|email',
            'file'=>'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        $file = $request->file;
        $filePath ='company_logo/'.$file->getClientOriginalName();
        //$filePath =$file->getClientOriginalName();
        Storage::disk('s3')->put($filePath, file_get_contents($file));



        DB::table('settings')->insert([
            'COMPANY_NAME'=>$request->COMPANY_NAME,
             'COMPANY_PHONE'=>$request->COMPANY_PHONE,
             'COMPANY_EMAIL'=>$request->COMPANY_EMAIL,
             'COMPANY_LOGO'=>$filePath,
             'created_at' => Carbon::now(),
             'updated_at' => Carbon::now(),

        ]);

        return response([
            'message'=>'Company details added successfully',
            //'token'=>$token
        ], 201); */
    }



    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */


    public function retrieveLogo(Request $request){

        $path = $request->path;

        if(Storage::disk('s3')->exists($path)){

            //$fileUrl = Storage::url($path);
        //     $baseURL = env('baseURL',''); ////Note, you can place the base URL in your env file.
        //     $fileUrl = $baseURL.urlencode($path);

        //     $response =([
        //         'URL' => $fileUrl
        //       ]);

        //    return response($response, 200);

        $s3client = new S3Client([
            'credentials' => [
                'key'    => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
            'region' => env('AWS_DEFAULT_REGION'),
            'version' => 'latest',
        ]);


        $bucket_name = env('AWS_BUCKET');


        try {
            // Get the object.
            $result = $s3client->getObject([
                'Bucket' => $bucket_name,
                'Key'    => $path
            ]);

        $mimetype = $result['ContentType'] . "\n";
        $data = base64_encode($result['Body']);

        $blob = 'data:'.$mimetype.';base64,'.$data;

        $response =([
            'URL' => $blob,
            'contentType' => $result['ContentType'],

        ]);

        return response($response, 200);


        } catch (S3Exception $e) {
            echo $e->getMessage() . PHP_EOL;
        }



    }


    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $id = $request->id;

       if($request->file!=''){
        $file = $request->file;
        $filePath ='company_logo/'.$file->getClientOriginalName();
        Storage::disk('s3')->put($filePath, file_get_contents($file));

        DB::table('settings')
        ->where('id', $id)
        ->update(['COMPANY_LOGO'=>$filePath,
                  'updated_at' => Carbon::now(),
                ]);

       }

        DB::table('settings')
        ->where('id', $id)
        ->update(['COMPANY_NAME' => $request->COMPANY_NAME,
                  'COMPANY_PHONE'=>$request->COMPANY_PHONE,
                  'COMPANY_EMAIL'=>$request->COMPANY_EMAIL,
                  'updated_at' => Carbon::now(),
                ]);



         $response = [
            'message'=> 'Company details successfully updated!',
                    ];
          return response($response, 200);

    }

    public function getCompanyDetails(Request $request){

        $id = $request->id;

        return Settings::where('id',$id)->get();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return Settings::destroy($id);
    }
}
