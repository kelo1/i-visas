<?php

namespace App\Http\Controllers;

use App\Location;
use Illuminate\Http\Request;
use App\User;
use App\visamgr_branches;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\BranchReference;
use PhpParser\Node\Stmt\Return_;

class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return DB::table('visamgr_branches')
        ->leftJoin('users', 'visamgr_branches.DEFAULT_USER', '=', 'users.id')
        ->leftJoin('branch_references', 'branch_references.branch_id', '=', 'visamgr_branches.id')
        ->select('visamgr_branches.*','users.first_name', 'users.last_name', 'branch_references.branch_reference_no')
        ->get();
    }

    public function branchbyuserid($id)
    {
        if (is_numeric($id)) {

            if($id == 1){

                return DB::table('visamgr_branches')
                ->leftJoin('users', 'visamgr_branches.DEFAULT_USER', '=', 'users.id')
                ->leftJoin('branch_references', 'branch_references.branch_id', '=', 'visamgr_branches.id')
                ->select('visamgr_branches.*','users.first_name', 'users.last_name', 'branch_references.branch_reference_no')

               // ->whereIn('visamgr_branches.id', $branches)
                ->get();
            }

            else{

            $user_branch = User::where('id',$id)->value('branch_id');
            $user_other_branches = DB::table('user_locations')->where('user_id', $id)->value('location_id');
            $Decoded_user_other_branches = json_decode($user_other_branches, true);

            $branches = array($user_branch);

            if(count($Decoded_user_other_branches)>0){
                foreach($Decoded_user_other_branches as $key=>$other_branches) {
                    array_push($branches, $other_branches['value']);
                   }
            }


            if(count($branches) > 0) {
                return DB::table('visamgr_branches')
                ->leftJoin('users', 'visamgr_branches.DEFAULT_USER', '=', 'users.id')
                ->leftJoin('branch_references', 'branch_references.branch_id', '=', 'visamgr_branches.id')
                ->select('visamgr_branches.*','users.first_name', 'users.last_name', 'branch_references.branch_reference_no')
                ->whereIn('visamgr_branches.id', $branches)
                ->get();
            }
                else {
                    return $branches;
                }
            }
        }

        else {
            $response =([
                'Message' => 'Invalid User ID'
            ]);

            return response($response, 400);
        }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user_id = $request->id;
      //  $user_id = User::where('id',$user_id)->value('first_name');
      //  $location_name = $request->location;

      $branch_reference = $request->branch_reference_no;

        $request->validate([
            'COUNTRY'=>'string|required',
            'LOCATION_NAME'=>'string|required',
            'LOCATION_CODE'=>'string|required',
            'ADDRESS1'=>'string|required',
            'ADDRESS2'=>'string|required',
            'TOWN'=>'string|required',
            'COUNTY'=>'string|required',
            //'POSTCODE'=>'string|required',
            'TELEPHONE'=>'string|required',
            //'DEFAULT_USER'=>'string',
            //'FAX'=>'string|required',
            'EMAIL'=>'string|required',
            'VAT_RATE'=>'required',
            'STATUS'=>'string|required',
           // 'branch_reference_no'=>'string|unique:branch_references'
        ]);



        $branch = visamgr_branches::insert([
             'COUNTRY'=>$request->COUNTRY,
             'LOCATION_CODE'=>$request->LOCATION_CODE,
             'LOCATION_NAME'=>$request->LOCATION_NAME,
             'ADDRESS1'=>$request->ADDRESS1,
             'ADDRESS2'=>$request->ADDRESS2,
             'TOWN'=>$request->TOWN,
             'COUNTY'=>$request->COUNTY,
             'POSTCODE'=>$request->POSTCODE,
             'TELEPHONE'=>$request->TELEPHONE,
             'FAX'=>$request->FAX,
             'EMAIL'=>$request->EMAIL,
             'VAT_RATE'=>$request->VAT_RATE,
             'STATUS'=>$request->STATUS,
             'DEFAULT_USER'=>$request->DEFAULT_USER,
             'USER'=>$user_id,
             'created_at' => Carbon::now(),
             'updated_at' => Carbon::now(),
        ]);

        $branch_id = DB::getPdo()->lastInsertId();

        DB::table('visamgr_user_branches')->insert([
            'user_id'=>$request->DEFAULT_USER,
            'branch_id'=>$branch_id,
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()
        ]);


        if($branch_reference!=NULL){

            $request->validate(['branch_reference_no'=>'string|unique:branch_references'
         ]);

         DB::table('branch_references')->insert([
            'branch_id'=>$branch_id,
            'branch_reference_no'=>$branch_reference,
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()
        ]);

        }


       // $branch = Location::where('COUNTRY',$location_name)->value('id');
        //$user_id = User::where('first_name')


       // $user->branch()->attach($branch);

        return response([
            'message'=>'Branch added successfully',
        ], 201);


    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
           //return visamgr_branches::find($id);
           return DB::table('visamgr_branches')
           ->leftJoin('users', 'visamgr_branches.DEFAULT_USER', '=', 'users.id')
           ->leftJoin('branch_references', 'branch_references.branch_id', '=', 'visamgr_branches.id')
           ->select('visamgr_branches.*','users.first_name', 'users.last_name', 'branch_references.branch_reference_no')
           ->where('visamgr_branches.id', $id)->get();
    }

    public function search($search)
    {


        return visamgr_branches::where(function ($query) use($search) {
            $query->where('LOCATION_NAME', 'like', '%' . $search . '%')
               ->orWhere('LOCATION_CODE', 'like', '%' . $search . '%')
               ->orWhere('EMAIL', 'like', '%' . $search . '%')
               ->orWhere('VAT_RATE', 'like', '%' . $search . '%')
               ->orWhere('TELEPHONE', 'like', '%' . $search . '%')
               ->orWhere('STATUS', 'like', '%' . $search . '%');
            })->get();

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
        $user_id = $request->user_id;

        $id = $request->id;

        $branch_reference = $request->branch_reference_no;

       // $user_id = User::where('id',$user_id)->value('first_name');

        $branch = visamgr_branches::find($id);

        $user = User::find($user_id);
        // if($request){

        // }


        $branch->update($request->all());


        $check_branch_reference = BranchReference::where('branch_id',$id)->first();


$is_branch_refence_no =   BranchReference::where('branch_reference_no',$request->branch_reference_no)->where('branch_id',$id)->first();


if($branch_reference!=NULL){

    if($is_branch_refence_no){

    }

    else{


      //  $check_branch_reference = BranchReference::where('branch_id',$id)->first();

        if($check_branch_reference){

            $request->validate([
                'branch_reference_no'=>'string|unique:branch_references',
              //  'user_id'=>'int|'
            ]);

            DB::table('branch_references')
            ->where('branch_id', $id)
           ->update(['branch_reference_no' => $branch_reference,
                     'updated_at' => Carbon::now()
            ]);
        }

        else{

            $request->validate([
                'branch_reference_no'=>'string|unique:branch_references',
              //  'user_id'=>'int|'
            ]);

            DB::table('branch_references')->insert([
                     'branch_id'=>$id,
                     'branch_reference_no' => $branch_reference,
                     'created_at' => Carbon::now(),
                     'updated_at' => Carbon::now()
            ]);
        }


    }



}


$response = [
    'message'=> 'Branch details successfully updated!'
    ];

    return response($response, 200);


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return visamgr_branches::destroy($id);
    }

}
