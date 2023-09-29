<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\visamgr_applications;
use App\Client;
use App\Billing;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BillingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Billing::all();
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


        $request->validate([
            'DESCRIPTION'=>'required|string'

        ]);

        DB::table('billings')->insert([
            'DESCRIPTION'=>$request->DESCRIPTION,
            'VAT_APPLICABLE'=>$request->VAT_APPLICABLE,
            'isACTIVE'=>$request->isACTIVE,
            'USER'=>$user_id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
         ]);

         $response = [
            'message'=> 'Billing item added successfully!',
            ];

            return response($response, 200);
    }

public function ActiveBillItems()
    {
        return DB::table('billings')->where('isActive',1)->get();
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Billing::find($id);
    }

    public function search($search)
    {

        return Billing::where(function ($query) use($search) {
            $query->where('DESCRIPTION', 'like', '%' . $search . '%');
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
        $id =$request->id;

        $billing_item = Billing::find($id);
        // if($request){

        // }
        $billing_item->update($request->all());

        //return $billing_item;
        $response = [
            'message'=> 'Billing item updated successfully!',
            ];

            return response($response, 200);
    }




    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $id = $request->row_id;

        return Billing::destroy($id);
    }
}
