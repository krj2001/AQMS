<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\UTILITY\DataUtilityController;

use Illuminate\Support\Facades\DB;

class BumpTestReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function BumpTestReport(Request $request)
    {
         $bumpTest = DB::table('customers as c')
         ->join('locations as l', 'c.customerId', '=', 'l.companyCode')
         ->Join('branches as b', function($join){
             $join->on('l.id', '=', 'b.location_id')
                  ->on('c.customerId', '=', 'b.companyCode');
         })
         ->Join('facilities as f', function($join){
             $join->on('c.customerId', '=', 'f.companyCode')
                 ->on('l.id', '=', 'f.location_id')
                 ->on('b.id', '=', 'f.branch_id');
         })
         ->Join('buildings as bl', function($join){
             $join->on('c.customerId', '=', 'bl.companyCode')
                 ->on('l.id', '=', 'bl.location_id')
                 ->on('b.id', '=', 'bl.branch_id')
                 ->on('f.id','=','bl.facility_id');
         })
         ->Join('floors as fl', function($join){
             $join->on('c.customerId', '=', 'fl.companyCode')
                 ->on('l.id', '=', 'fl.location_id')
                 ->on('b.id', '=', 'fl.branch_id')
                 ->on('f.id','=','fl.facility_id')
                 ->on('bl.id','=','fl.building_id');
         })
         ->Join('lab_departments as lb', function($join){
             $join->on('c.customerId', '=', 'lb.companyCode')
                 ->on('l.id', '=', 'lb.location_id')
                 ->on('b.id', '=', 'lb.branch_id')
                 ->on('f.id','=','lb.facility_id')
                 ->on('bl.id','=','lb.building_id')
                 ->on('fl.id','=','lb.floor_id');
         })
         ->Join('devices as d', function($join){
             $join->on('c.customerId', '=', 'd.companyCode')
                 ->on('l.id', '=', 'd.location_id')
                 ->on('b.id', '=', 'd.branch_id')
                 ->on('f.id','=','d.facility_id')
                 ->on('bl.id','=','d.building_id')
                 ->on('fl.id','=','d.floor_id')
                 ->on('lb.id','=','d.lab_id');
         })
         ->Join('sensors as s', function($join){
             $join->on('c.customerId', '=', 'd.companyCode')
                 ->on('l.id', '=', 's.location_id')
                 ->on('b.id', '=', 's.branch_id')
                 ->on('f.id','=','s.facility_id')
                 ->on('bl.id','=','s.building_id')
                 ->on('fl.id','=','s.floor_id')
                 ->on('lb.id','=','s.lab_id')
                 ->on('d.id','=','s.deviceid');
         })
         ->select('c.customerId', 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorNameUnit','s.sensorTag')
         ->WHERE('customerId','=','A-TEST')
         ->WHERE('sensorTag','=',$request->sensorTagName)
         ->first(); 

         //$getData = new DataUtilityController($request,$bumpTest);
            
         $response = [
              "data"=>$bumpTest
         ];
         $status = 200;

         return response($response,$status);
    }



    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
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
