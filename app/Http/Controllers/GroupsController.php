<?php

namespace App\Http\Controllers;
use App\visamgr_groups;
use App\client_group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class GroupsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return visamgr_groups::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user  = $request->USER;

        DB::table('visamgr_groups')->insert([
            'GROUP_NAME'=>$request->GROUP_NAME,
            'USER'=>$user,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
         ]);

      return response([
        'message'=>'Group added successfully',
    ], 201);

    }

    public function assignClientGroup(Request $request){
        $group_id  = $request->group_id;

        $client_id  = $request->client_id;


        DB::table('client_groups')->insert([
            'client_id'=>$client_id,
            'group_id'=>$group_id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
         ]);

         return response([
            'message'=>'Client successfully added to group',
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
        return visamgr_groups::where('GROUP_ID',$id)->get();
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

        $group = visamgr_groups::find($id);

        $group->update($request->all());

        return $group;
    }

    public function updateClientGroup(Request $request)
    {
        //$group_id  = $request->group_id;

        $client_id  = $request->client_id;


        DB::table('client_groups')
                ->where('client_id', $client_id)
                ->update(['group_id' => $request->group_id,
                'updated_at' => Carbon::now()
                ]);


         return response([
              'message'=>'Client group successfully updated',
          ], 201);

    }

    public function getClientGroup(Request $request){

        return DB::table('client_groups')
        ->join('visamgr_groups',  'client_groups.group_id', '=', 'visamgr_groups.GROUP_ID')
        ->select('visamgr_groups.*', 'client_groups.client_id')
        ->get();

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return visamgr_groups::destroy($id);
    }
}
