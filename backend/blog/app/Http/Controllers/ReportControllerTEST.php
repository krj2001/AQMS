<?php

namespace App\Http\Controllers;
use App\Http\Controllers\UTILITY\ReportsDataUtilityController;

use App\Http\Controllers\UtilityController;
use App\Exports\ReportExport;
use App\Exports\BumpTestReportExport;
use App\Exports\LimitEditLogsExport;
use App\Exports\serverUtilizationReportExport;
use App\Exports\SensorStatusReportExport;
use App\Exports\FirmwareVersionExport;
use App\Exports\AqiReportExport;
use App\Exports\DeviceModelLogExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AlertCron;
use App\Models\Device;
use App\Models\DeviceModelLog;
use App\Http\Controllers\UTILITY\DataUtilityController;
use App\Http\Controllers\UTILITY\AppDataUtilityController;
use DateTime;
use DatePeriod;
use DateInterval;
use Maatwebsite\Excel\Excel as BaseExcel;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailNotify;


class ReportControllerTEST extends Controller
{

 public function AqiReportMailExcelFile(Request $request) 
    {
        // $startDate = date("Y-m-d",strtotime($request->input(key:'fromDate')));
        // $endDate = date("Y-m-d", strtotime($request->input(key:'toDate')));
        $startDate = date("Y-m-d",strtotime($request->fromDate));
        $endDate = date("Y-m-d", strtotime($request->toDate));
        
        if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !="" && $request->lab_id !=""){

        $summaryAqiValue = DB::table('customers as c')
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
                    ->Join('Aqi_values_per_device as aqi', function($join){
                        $join->on('c.customerId', '=', 'd.companyCode')
                            ->on('l.id', '=', 'aqi.locationId')
                            ->on('b.id', '=', 'aqi.branchId')
                            ->on('f.id','=','aqi.facilityId')
                            ->on('bl.id','=','aqi.buildingId')
                            ->on('fl.id','=','aqi.floorId')
                            ->on('lb.id','=','aqi.labId')
                            ->on('d.id','=','aqi.deviceId');
                    })
                ->select(DB::raw('DATE_FORMAT(sampled_date_time, "%d-%b-%Y") as date'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName', 'fl.floorName','lb.labDepName','d.deviceName', DB::raw('ROUND(MAX(AqiValue),2) as AqiValue'))
                         ->where('customerId','=',$this->companyCode)
                         ->where('labId','=',$request->lab_id)
                         ->where('floorId','=',$request->floor_id)
                         ->where('buildingId','=',$request->building_id)
                          ->where('facilityId','=',$request->facility_id)
                          ->where('branchId','=',$request->branch_id)
                          ->where('locationId','=',$request->location_id)
                         ->groupBy(DB::raw('Date(sampled_date_time)'));

                //  $summaryAqiValue = DB::table('Aqi_values_per_device')
                //                      ->select( DB::raw('MAX(AqiValue) as AqiValue , DATE(sampled_date_time) as date,deviceId'))
                //                      ->where('labId','=',$request->lab_id)
                //                      ->groupBy(DB::raw('Date(sampled_date_time)'))
                //                      ->get();
                    
        }
        else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !=""){
                        $summaryAqiValue = DB::table('customers as c')
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
                    ->Join('Aqi_values_per_device as aqi', function($join){
                        $join->on('c.customerId', '=', 'd.companyCode')
                            ->on('l.id', '=', 'aqi.locationId')
                            ->on('b.id', '=', 'aqi.branchId')
                            ->on('f.id','=','aqi.facilityId')
                            ->on('bl.id','=','aqi.buildingId')
                            ->on('fl.id','=','aqi.floorId')
                            ->on('lb.id','=','aqi.labId')
                            ->on('d.id','=','aqi.deviceId');
                    })
                ->select(DB::raw('DATE_FORMAT(sampled_date_time, "%d-%b-%Y") as date'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName', 'fl.floorName','lb.labDepName','d.deviceName', DB::raw('ROUND(MAX(AqiValue),2) as AqiValue'))
                         ->where('customerId','=',$this->companyCode)
                        //  ->where('companyCode','=',$this->companyCode)
                         ->where('floorId','=',$request->floor_id)
                         ->where('buildingId','=',$request->building_id)
                          ->where('facilityId','=',$request->facility_id)
                          ->where('branchId','=',$request->branch_id)
                          ->where('locationId','=',$request->location_id)
                         ->groupBy(DB::raw('Date(sampled_date_time)'));

                //  $summaryAqiValue = DB::table('Aqi_values_per_device')
                //                      ->select( DB::raw('MAX(AqiValue) as AqiValue , DATE(sampled_date_time) as date,deviceId'))
                //                      ->where('labId','=',$request->lab_id)
                //                      ->groupBy(DB::raw('Date(sampled_date_time)'))
                //                      ->get();
            
        }
        else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !=""){
                $summaryAqiValue = DB::table('customers as c')
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
                    ->Join('Aqi_values_per_device as aqi', function($join){
                        $join->on('c.customerId', '=', 'd.companyCode')
                            ->on('l.id', '=', 'aqi.locationId')
                            ->on('b.id', '=', 'aqi.branchId')
                            ->on('f.id','=','aqi.facilityId')
                            ->on('bl.id','=','aqi.buildingId')
                            ->on('fl.id','=','aqi.floorId')
                            ->on('lb.id','=','aqi.labId')
                            ->on('d.id','=','aqi.deviceId');
                    })
                ->select(DB::raw('DATE_FORMAT(sampled_date_time, "%d-%b-%Y") as date'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName', 'fl.floorName','lb.labDepName','d.deviceName', DB::raw('ROUND(MAX(AqiValue),2) as AqiValue'))
                         ->where('customerId','=',$this->companyCode)
                         ->where('buildingId','=',$request->building_id)
                          ->where('facilityId','=',$request->facility_id)
                          ->where('branchId','=',$request->branch_id)
                          ->where('locationId','=',$request->location_id)
                         ->groupBy(DB::raw('Date(sampled_date_time)'));

                //  $summaryAqiValue = DB::table('Aqi_values_per_device')
                //                      ->select( DB::raw('MAX(AqiValue) as AqiValue , DATE(sampled_date_time) as date,deviceId'))
                //                      ->where('labId','=',$request->lab_id)
                //                      ->groupBy(DB::raw('Date(sampled_date_time)'))
                //                      ->get();
                      
                  }
              else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""){
                $summaryAqiValue = DB::table('customers as c')
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
                    ->Join('Aqi_values_per_device as aqi', function($join){
                        $join->on('c.customerId', '=', 'd.companyCode')
                            ->on('l.id', '=', 'aqi.locationId')
                            ->on('b.id', '=', 'aqi.branchId')
                            ->on('f.id','=','aqi.facilityId')
                            ->on('bl.id','=','aqi.buildingId')
                            ->on('fl.id','=','aqi.floorId')
                            ->on('lb.id','=','aqi.labId')
                            ->on('d.id','=','aqi.deviceId');
                    })
              ->select(DB::raw('DATE_FORMAT(sampled_date_time, "%d-%b-%Y") as date'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName', 'fl.floorName','lb.labDepName','d.deviceName', DB::raw('ROUND(MAX(AqiValue),2) as AqiValue'))
                          ->where('customerId','=',$this->companyCode)
                          ->where('facilityId','=',$request->facility_id)
                          ->where('branchId','=',$request->branch_id)
                          ->where('locationId','=',$request->location_id)
                         ->groupBy(DB::raw('Date(sampled_date_time)'));

                //  $summaryAqiValue = DB::table('Aqi_values_per_device')
                //                      ->select( DB::raw('MAX(AqiValue) as AqiValue , DATE(sampled_date_time) as date,deviceId'))
                //                      ->where('labId','=',$request->lab_id)
                //                      ->groupBy(DB::raw('Date(sampled_date_time)'))
                //                      ->get();
                  
              }
            else if($request->location_id != "" && $request->branch_id != ""){ 
                  $summaryAqiValue = DB::table('customers as c')
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
                    ->Join('Aqi_values_per_device as aqi', function($join){
                        $join->on('c.customerId', '=', 'd.companyCode')
                            ->on('l.id', '=', 'aqi.locationId')
                            ->on('b.id', '=', 'aqi.branchId')
                            ->on('f.id','=','aqi.facilityId')
                            ->on('bl.id','=','aqi.buildingId')
                            ->on('fl.id','=','aqi.floorId')
                            ->on('lb.id','=','aqi.labId')
                            ->on('d.id','=','aqi.deviceId');
                    })
                ->select(DB::raw('DATE_FORMAT(sampled_date_time, "%d-%b-%Y") as date'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName', 'fl.floorName','lb.labDepName','d.deviceName', DB::raw('ROUND(MAX(AqiValue),2) as AqiValue'))
                         ->where('customerId','=',$this->companyCode)
                          ->where('branchId','=',$request->branch_id)
                          ->where('locationId','=',$request->location_id)
                         ->groupBy(DB::raw('Date(sampled_date_time)'));

                //  $summaryAqiValue = DB::table('Aqi_values_per_device')
                //                      ->select( DB::raw('MAX(AqiValue) as AqiValue , DATE(sampled_date_time) as date,deviceId'))
                //                      ->where('labId','=',$request->lab_id)
                //                      ->groupBy(DB::raw('Date(sampled_date_time)'))
                //                      ->get();
            }
         else if($request->location_id != ""){  
             $summaryAqiValue = DB::table('customers as c')
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
                    ->Join('Aqi_values_per_device as aqi', function($join){
                        $join->on('c.customerId', '=', 'd.companyCode')
                            ->on('l.id', '=', 'aqi.locationId')
                            ->on('b.id', '=', 'aqi.branchId')
                            ->on('f.id','=','aqi.facilityId')
                            ->on('bl.id','=','aqi.buildingId')
                            ->on('fl.id','=','aqi.floorId')
                            ->on('lb.id','=','aqi.labId')
                            ->on('d.id','=','aqi.deviceId');
                    })
                 ->select(DB::raw('DATE_FORMAT(sampled_date_time, "%d-%b-%Y") as date'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName', 'fl.floorName','lb.labDepName','d.deviceName', DB::raw('ROUND(MAX(AqiValue),2) as AqiValue'))
                         ->where('customerId','=',$this->companyCode)
                          ->where('locationId','=',$request->location_id)
                         ->groupBy(DB::raw('Date(sampled_date_time)'));
        }
        else{
             $summaryAqiValue = DB::table('customers as c')
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
                    ->Join('Aqi_values_per_device as aqi', function($join){
                        $join->on('c.customerId', '=', 'd.companyCode')
                            ->on('l.id', '=', 'aqi.locationId')
                            ->on('b.id', '=', 'aqi.branchId')
                            ->on('f.id','=','aqi.facilityId')
                            ->on('bl.id','=','aqi.buildingId')
                            ->on('fl.id','=','aqi.floorId')
                            ->on('lb.id','=','aqi.labId')
                            ->on('d.id','=','aqi.deviceId');
                    })
                ->select(DB::raw('DATE_FORMAT(sampled_date_time, "%d-%b-%Y") as date'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName', 'fl.floorName','lb.labDepName','d.deviceName', DB::raw('ROUND(MAX(AqiValue),2) as AqiValue'))
                    //   ->where('customerId','=',$this->companyCode)
                      ->groupBy(DB::raw('Date(sampled_date_time)'));
        }

        if($startDate === $endDate){
            $summaryAqiValue->whereDate('aqi.sampled_date_time','=',$startDate); 
        }
        else {
            $summaryAqiValue->whereDate('aqi.sampled_date_time','>=',$startDate)
                            ->whereDate('aqi.sampled_date_time','<=',$endDate);    
        }
        
          $getData = new ReportsDataUtilityController($request,$summaryAqiValue);
        //   $getData = $getData->data;
          
                $response = [
                    "data"=>$getData->getData()
                ];
                $status = 200;
                return response($response,$status);
        
    }

}