<?php

namespace App\Http\Controllers;

use App\User;
use Carbon\Carbon;
use App\BranchReference;
use App\visamgr_branches;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class BranchReferenceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return BranchReference::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $user_id= $request->user_id;

        $branch_reference = $request->branch_reference_no; //Branch Reference Code BG. eg: BG0001


        $request->validate([
                'user_id'=>'required',
                'branch_reference_no'=>'string|unique:branch_references'

            ]);


            $branch= BranchReference::create([

                'user_id'=> $user_id,
                'branch_reference_no'=>$branch_reference,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            $response = [
                'Message'=>"Branch Reference created!",
            ];

            return response($response, 201);


    }



    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $allBranchReference = DB::table('branch_references')
        ->join('visamgr_branches', 'visamgr_branches.id', '=',  'branch_references.user_id')
        ->select('visamgr_branches.*', 'branch_references.created_at', 'branch_references.branch_reference_no')
        ->where('users.id', $id)
        ->get();


        $response =([
         'Message' => 'Branch details',
         'branch_details'=>$allBranchReference,
     ]);

     return response($response, 200);
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
        $branch_id = visamgr_branches::find($request->id);

        $branch_reference = $request->branch_reference_no;

        if($branch_id){

            $request->validate([
                'branch_reference_no'=>'string|unique:branch_references',
            ]);

            DB::table('branch_references')
            ->where('branch_id', $request->id)
           ->update(['branch_reference_no' => $branch_reference,
                     'updated_at' => Carbon::now()
            ]);

        }

        $response =([
            'Message' => 'Branch reference successfully updated'
        ]);

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
        //
    }
}
