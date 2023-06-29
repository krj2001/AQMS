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


class ReportController extends Controller
{
    protected $companyCode = "";
    protected $table = "";
    protected $alertColor;

    function __construct(Request $request) {
        $getData = new UtilityController($request);
        $this->companyCode = $getData->getCompanyCode();
        $this->alertColor = $getData->getAlertColors();
        $this->userId = $getData->getUserId();
    }

    function fetchVerifiedEmailUsers($userEmail){

       $verifiedUser = DB::table('users')
                      ->where('email', '=' , $userEmail)
                      ->where('isverified', 1)
                      ->first();

        if($verifiedUser) {
            return $userEmail;
        }
        else {
            return $userEmail;
        }
    }


    public function DeviceAqiReport(Request $request){

        $startDate = date("Y-m-d",strtotime($request->fromDate));
        $endDate = date("Y-m-d", strtotime($request->toDate));

        if($request->deviceId != "")
        {
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
            ->select(DB::raw('ROUND(MAX(AqiValue),2) as AqiValue ,ROUND(MAX(AqiValue),2) as AqiStatus, DATE(sampled_date_time) as date'),'aqi.labId','d.deviceName','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','aqi.id')
                    ->where('customerId','=',$this->companyCode)
                    ->where('labId','=',$request->lab_id)
                    ->where('floorId','=',$request->floor_id)
                    ->where('buildingId','=',$request->building_id)
                     ->where('facilityId','=',$request->facility_id)
                     ->where('branchId','=',$request->branch_id)
                     ->where('locationId','=',$request->location_id)
                      ->where('deviceId','=',$request->deviceId)
                    ->groupBy(DB::raw('Date(sampled_date_time)'))
                    ->orderBy('AqiValue', 'desc');

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
            ->select(DB::raw('ROUND(MAX(AqiValue),2) as AqiValue ,ROUND(MAX(AqiValue),2) as AqiStatus, DATE(sampled_date_time) as date'),'aqi.labId','d.deviceName','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','aqi.id')
           // ->select(DB::raw('ROUND(MAX(AqiValue),2) as AqiValue , DATE(sampled_date_time) as date'),'aqi.labId','d.deviceName','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','aqi.id')
                    ->where('customerId','=',$this->companyCode)
                   //  ->where('companyCode','=',$this->companyCode)
                    ->where('floorId','=',$request->floor_id)
                    ->where('buildingId','=',$request->building_id)
                     ->where('facilityId','=',$request->facility_id)
                     ->where('branchId','=',$request->branch_id)
                     ->where('locationId','=',$request->location_id)
                       ->where('deviceId','=',$request->deviceId)
                    ->groupBy(DB::raw('Date(sampled_date_time)'))
                    ->orderBy('AqiValue', 'desc');

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
            ->select(DB::raw('ROUND(MAX(AqiValue),2) as AqiValue ,ROUND(MAX(AqiValue),2) as AqiStatus, DATE(sampled_date_time) as date'),'aqi.labId','d.deviceName','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','aqi.id')
           // ->select(DB::raw('ROUND(MAX(AqiValue),2) as AqiValue , DATE(sampled_date_time) as date'),'aqi.labId','d.deviceName','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','aqi.id')
                    ->where('customerId','=',$this->companyCode)
                    ->where('buildingId','=',$request->building_id)
                    ->where('facilityId','=',$request->facility_id)
                    ->where('branchId','=',$request->branch_id)
                    ->where('locationId','=',$request->location_id)
                    ->where('deviceId','=',$request->deviceId)
                    ->groupBy(DB::raw('Date(sampled_date_time)'))
                    ->orderBy('AqiValue', 'desc');

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
            ->select(DB::raw('ROUND(MAX(AqiValue),2) as AqiValue ,ROUND(MAX(AqiValue),2) as AqiStatus, DATE(sampled_date_time) as date'),'aqi.labId','d.deviceName','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','aqi.id')
                     ->where('customerId','=',$this->companyCode)
                     ->where('facilityId','=',$request->facility_id)
                     ->where('branchId','=',$request->branch_id)
                     ->where('locationId','=',$request->location_id)
                     ->where('deviceId','=',$request->deviceId)
                    ->groupBy(DB::raw('Date(sampled_date_time)'))
                    ->orderBy('AqiValue', 'desc');

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
            ->select(DB::raw('ROUND(MAX(AqiValue),2) as AqiValue ,ROUND(MAX(AqiValue),2) as AqiStatus, DATE(sampled_date_time) as date'),'aqi.labId','d.deviceName','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','aqi.id')
                    ->where('customerId','=',$this->companyCode)
                     ->where('branchId','=',$request->branch_id)
                     ->where('locationId','=',$request->location_id)
                     ->where('deviceId','=',$request->deviceId)
                    ->groupBy(DB::raw('Date(sampled_date_time)'))
                    ->orderBy('AqiValue', 'desc');

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
            ->select(DB::raw('ROUND(MAX(AqiValue),2) as AqiValue ,ROUND(MAX(AqiValue),2) as AqiStatus, DATE(sampled_date_time) as date'),'aqi.labId','d.deviceName','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','aqi.id')
                    ->where('customerId','=',$this->companyCode)
                     ->where('locationId','=',$request->location_id)
                     ->where('deviceId','=',$request->deviceId)
                    ->groupBy(DB::raw('Date(sampled_date_time)'))
                    ->orderBy('AqiValue', 'desc');

           //  $summaryAqiValue = DB::table('Aqi_values_per_device')
           //                      ->select( DB::raw('MAX(AqiValue) as AqiValue , DATE(sampled_date_time) as date,deviceId'))
           //                      ->where('labId','=',$request->lab_id)
           //                      ->groupBy(DB::raw('Date(sampled_date_time)'))
           //                      ->get();
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
            ->select(DB::raw('ROUND(MAX(AqiValue),2) as AqiValue ,ROUND(MAX(AqiValue),2) as AqiStatus, DATE(sampled_date_time) as date'),'aqi.labId','d.deviceName','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','aqi.id','aqi.companyCode')
                  ->where('customerId','=',$this->companyCode)
                 ->where('deviceId','=',$request->deviceId)
                   ->groupBy(DB::raw('Date(sampled_date_time)'))
                   ->orderBy('AqiValue', 'desc');
           }
        }
       else{

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
           //  ->select(DB::raw('ROUND(MAX(AqiValue),2) as AqiValue , DATE(sampled_date_time) as date'),'aqi.labId','d.deviceName','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','aqi.id')
           ->select(DB::raw('ROUND(MAX(AqiValue),2) as AqiValue ,ROUND(MAX(AqiValue),2) as AqiStatus, DATE(sampled_date_time) as date'),'l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','aqi.id')
                    ->where('customerId','=',$this->companyCode)
                    ->where('labId','=',$request->lab_id)
                    ->where('floorId','=',$request->floor_id)
                    ->where('buildingId','=',$request->building_id)
                     ->where('facilityId','=',$request->facility_id)
                     ->where('branchId','=',$request->branch_id)
                     ->where('locationId','=',$request->location_id)

                    ->groupBy(DB::raw('Date(sampled_date_time)'))
                    ->orderBy('AqiValue', 'desc');

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
           //  ->select(DB::raw('ROUND(MAX(AqiValue),2) as AqiValue , DATE(sampled_date_time) as date'),'aqi.labId','d.deviceName','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','aqi.id')
           ->select(DB::raw('ROUND(MAX(AqiValue),2) as AqiValue ,ROUND(MAX(AqiValue),2) as AqiStatus, DATE(sampled_date_time) as date'),'l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','aqi.id')
                    ->where('customerId','=',$this->companyCode)
                   //  ->where('companyCode','=',$this->companyCode)
                    ->where('floorId','=',$request->floor_id)
                    ->where('buildingId','=',$request->building_id)
                     ->where('facilityId','=',$request->facility_id)
                     ->where('branchId','=',$request->branch_id)
                     ->where('locationId','=',$request->location_id)

                    ->groupBy(DB::raw('Date(sampled_date_time)'))
                    ->orderBy('AqiValue', 'desc');

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
           //  ->select(DB::raw('ROUND(MAX(AqiValue),2) as AqiValue , DATE(sampled_date_time) as date'),'aqi.labId','d.deviceName','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','aqi.id')
           ->select(DB::raw('ROUND(MAX(AqiValue),2) as AqiValue ,ROUND(MAX(AqiValue),2) as AqiStatus, DATE(sampled_date_time) as date'),'l.stateName','b.branchName','f.facilityName','bl.buildingName','aqi.id')
                    ->where('customerId','=',$this->companyCode)
                    ->where('buildingId','=',$request->building_id)
                    ->where('facilityId','=',$request->facility_id)
                    ->where('branchId','=',$request->branch_id)
                    ->where('locationId','=',$request->location_id)

                    ->groupBy(DB::raw('Date(sampled_date_time)'))
                    ->orderBy('AqiValue', 'desc');

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
           //  ->select(DB::raw('ROUND(MAX(AqiValue),2) as AqiValue , DATE(sampled_date_time) as date'),'aqi.labId','d.deviceName','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','aqi.id')
           ->select(DB::raw('ROUND(MAX(AqiValue),2) as AqiValue ,ROUND(MAX(AqiValue),2) as AqiStatus, DATE(sampled_date_time) as date'),'l.stateName','b.branchName','f.facilityName','aqi.id')
                     ->where('customerId','=',$this->companyCode)
                     ->where('facilityId','=',$request->facility_id)
                     ->where('branchId','=',$request->branch_id)
                     ->where('locationId','=',$request->location_id)

                    ->groupBy(DB::raw('Date(sampled_date_time)'))
                    ->orderBy('AqiValue', 'desc');

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
           //  ->select(DB::raw('ROUND(MAX(AqiValue),2) as AqiValue , DATE(sampled_date_time) as date'),'aqi.labId','d.deviceName','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','aqi.id')
           ->select(DB::raw('ROUND(AVG(AqiValue), 2) as AqiValue,ROUND(AVG(AqiValue),2) as AqiStatus, DATE(sampled_date_time) as date, l.stateName, b.branchName, aqi.id'))
                    ->where('customerId','=',$this->companyCode)
                     ->where('branchId','=',$request->branch_id)
                     ->where('locationId','=',$request->location_id)

                    ->groupBy(DB::raw('Date(sampled_date_time)'))
                    ->orderBy('AqiValue', 'desc');

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
           //  ->select(DB::raw('ROUND(MAX(AqiValue),2) as AqiValue , DATE(sampled_date_time) as date'),'aqi.labId','d.deviceName','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','aqi.id')
           // ->where('customerId','=',$this->companyCode)
           //           ->where('locationId','=',$request->location_id)
           //          ->groupBy(DB::raw('Date(sampled_date_time)'));
           ->select(DB::raw('ROUND(MAX(AqiValue),2) as AqiValue ,ROUND(MAX(AqiValue),2) as AqiStatus, DATE(sampled_date_time) as date'),'l.stateName','aqi.id')
                    ->where('customerId','=',$this->companyCode)
                     ->where('locationId','=',$request->location_id)
                    ->groupBy(DB::raw('Date(sampled_date_time)'))
                    ->where('customerId','=',$this->companyCode)
                    ->where('locationId','=',$request->location_id)
                   ->groupBy(DB::raw('Date(sampled_date_time)'))
                   ->orderBy('AqiValue', 'desc');
           //  $summaryAqiValue = DB::table('Aqi_values_per_device')
           //                      ->select( DB::raw('MAX(AqiValue) as AqiValue , DATE(sampled_date_time) as date,deviceId'))
           //                      ->where('labId','=',$request->lab_id)
           //                      ->groupBy(DB::raw('Date(sampled_date_time)'))
           //                      ->get();
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
           //  ->select(DB::raw('ROUND(MAX(AqiValue),2) as AqiValue , DATE(sampled_date_time) as date'),'aqi.labId','d.deviceName','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','aqi.id','aqi.companyCode')
           // ->select(DB::raw(' DATE(sampled_date_time) as date'),'aqi.id','aqi.companyCode')
           ->select(DB::raw(' DATE(sampled_date_time) as date'),'aqi.id','aqi.companyCode')
                  ->where('customerId','=',$this->companyCode)

                   ->groupBy(DB::raw('Date(sampled_date_time)'));
                   // ->orderBy('AqiValue', 'desc');




           }

}

          if($startDate === $endDate){
               $summaryAqiValue->whereDate('aqi.sampled_date_time','=',$startDate);
           }
           else {
                 $summaryAqiValue->whereDate('aqi.sampled_date_time', '>=', $startDate)
                         ->whereDate('aqi.sampled_date_time', '<=', $endDate);
           }


           $getData = new ReportsDataUtilityController($request,$summaryAqiValue);

           $response = [
               "data"=>$getData->getData()
           ];
           $status = 200;
           return response($response,$status);

}


    public function reportBumpTest(Request $request){


        $startDate = date("Y-m-d",strtotime($request->fromDate));
        $endDate = date("Y-m-d", strtotime($request->toDate));


        if($request->device_id != "")
             {
                       if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !="" && $request->lab_id !=""){
                            $query = DB::table('customers as c')
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
                            ->Join('bump_test_results as btr', function($join){
                                $join->on('c.customerId', '=', 'btr.companyCode')
                                    ->on('d.id', '=', 'btr.device_id');

                            })
                            ->select('c.customerId', 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','btr.id','d.deviceName','btr.sensorTagName','btr.lastDueDate','btr.typeCheck','btr.percentageDeviation','btr.created_at','btr.result','btr.userEmail')
                            ->where('customerId','=',$this->companyCode)
                          ->where('lb.id','=',$request->lab_id)
                         ->where('fl.id','=',$request->floor_id)
                         ->where('bl.id','=',$request->building_id)
                          ->where('f.id','=',$request->facility_id)
                          ->where('b.id','=',$request->branch_id)
                          ->where('l.id','=',$request->location_id)
                           ->where('device_id','=',$request->device_id);
                       }
                       else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !=""){
                           $query = DB::table('customers as c')
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
                            ->Join('bump_test_results as btr', function($join){
                                $join->on('c.customerId', '=', 'btr.companyCode')
                                    ->on('d.id', '=', 'btr.device_id');

                            })
                            ->select('c.customerId', 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','btr.id','d.deviceName','btr.sensorTagName','btr.lastDueDate','btr.typeCheck','btr.percentageDeviation','btr.created_at','btr.result','btr.userEmail')
                             ->where('customerId','=',$this->companyCode)
                         ->where('fl.id','=',$request->floor_id)
                         ->where('bl.id','=',$request->building_id)
                          ->where('f.id','=',$request->facility_id)
                          ->where('b.id','=',$request->branch_id)
                          ->where('l.id','=',$request->location_id)
                           ->where('device_id','=',$request->device_id);
                       }
                        else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !=""){
                           $query = DB::table('customers as c')
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
                            ->Join('bump_test_results as btr', function($join){
                                $join->on('c.customerId', '=', 'btr.companyCode')
                                    ->on('d.id', '=', 'btr.device_id');

                            })
                            ->select('c.customerId', 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','btr.id','d.deviceName','btr.sensorTagName','btr.lastDueDate','btr.typeCheck','btr.percentageDeviation','btr.created_at','btr.result','btr.userEmail')
                            ->where('customerId','=',$this->companyCode)
                         ->where('bl.id','=',$request->building_id)
                          ->where('f.id','=',$request->facility_id)
                          ->where('b.id','=',$request->branch_id)
                          ->where('l.id','=',$request->location_id)
                           ->where('device_id','=',$request->device_id);
                       }
                        else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""){
                           $query = DB::table('customers as c')
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
                            ->Join('bump_test_results as btr', function($join){
                                $join->on('c.customerId', '=', 'btr.companyCode')
                                    ->on('d.id', '=', 'btr.device_id');

                            })
                            ->select('c.customerId', 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','btr.id','d.deviceName','btr.sensorTagName','btr.lastDueDate','btr.typeCheck','btr.percentageDeviation','btr.created_at','btr.result','btr.userEmail')
                            ->where('customerId','=',$this->companyCode)
                          ->where('f.id','=',$request->facility_id)
                          ->where('b.id','=',$request->branch_id)
                          ->where('l.id','=',$request->location_id)
                               ->where('device_id','=',$request->device_id);
                       }
                        else if($request->location_id != "" && $request->branch_id != "" ){
                           $query = DB::table('customers as c')
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
                            ->Join('bump_test_results as btr', function($join){
                                $join->on('c.customerId', '=', 'btr.companyCode')
                                    ->on('d.id', '=', 'btr.device_id');

                            })
                            ->select('c.customerId', 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','btr.id','d.deviceName','btr.sensorTagName','btr.lastDueDate','btr.typeCheck','btr.percentageDeviation','btr.created_at','btr.result','btr.userEmail')
                              ->where('customerId','=',$this->companyCode)
                          ->where('b.id','=',$request->branch_id)
                          ->where('l.id','=',$request->location_id)
                               ->where('device_id','=',$request->device_id);
                       }
                         else if($request->location_id != ""){
                           $query = DB::table('customers as c')
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
                            ->Join('bump_test_results as btr', function($join){
                                $join->on('c.customerId', '=', 'btr.companyCode')
                                    ->on('d.id', '=', 'btr.device_id');

                            })
                            ->select('c.customerId', 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','btr.id','d.deviceName','btr.sensorTagName','btr.lastDueDate','btr.typeCheck','btr.percentageDeviation','btr.created_at','btr.result','btr.userEmail')
                               ->where('customerId','=',$this->companyCode)
                          ->where('l.id','=',$request->location_id)
                               ->where('device_id','=',$request->device_id);
                       }
                       else{
                             $query = DB::table('customers as c')
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
                            ->Join('bump_test_results as btr', function($join){
                                $join->on('c.customerId', '=', 'btr.companyCode')
                                    ->on('d.id', '=', 'btr.device_id');

                            })
                            ->select('c.customerId', 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','btr.id','d.deviceName','btr.sensorTagName','btr.lastDueDate','btr.typeCheck','btr.percentageDeviation','btr.created_at','btr.result','btr.userEmail')
                               ->where('customerId','=',$this->companyCode)
                               ->where('device_id','=',$request->device_id);

                       }

            }
        else{

                       if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !="" && $request->lab_id !=""){
                            $query = DB::table('customers as c')
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
                            ->Join('bump_test_results as btr', function($join){
                                $join->on('c.customerId', '=', 'btr.companyCode')
                                    ->on('d.id', '=', 'btr.device_id');

                            })
                            ->select('c.customerId', 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','btr.id','d.deviceName','btr.sensorTagName','btr.lastDueDate','btr.typeCheck','btr.percentageDeviation','btr.created_at','btr.result','btr.userEmail')
                            ->where('customerId','=',$this->companyCode)
                             ->where('lb.id','=',$request->lab_id)
                             ->where('fl.id','=',$request->floor_id)
                             ->where('bl.id','=',$request->building_id)
                              ->where('f.id','=',$request->facility_id)
                              ->where('b.id','=',$request->branch_id)
                              ->where('l.id','=',$request->location_id);
                       }
                       else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !=""){
                           $query = DB::table('customers as c')
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
                            ->Join('bump_test_results as btr', function($join){
                                $join->on('c.customerId', '=', 'btr.companyCode')
                                    ->on('d.id', '=', 'btr.device_id');

                            })
                            ->select('c.customerId', 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','btr.id','d.deviceName','btr.sensorTagName','btr.lastDueDate','btr.typeCheck','btr.percentageDeviation','btr.created_at','btr.result','btr.userEmail')
                             ->where('customerId','=',$this->companyCode)
                         ->where('fl.id','=',$request->floor_id)
                         ->where('bl.id','=',$request->building_id)
                          ->where('f.id','=',$request->facility_id)
                          ->where('b.id','=',$request->branch_id)
                          ->where('l.id','=',$request->location_id);
                       }
                        else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !=""){
                           $query = DB::table('customers as c')
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
                            ->Join('bump_test_results as btr', function($join){
                                $join->on('c.customerId', '=', 'btr.companyCode')
                                    ->on('d.id', '=', 'btr.device_id');

                            })
                            ->select('c.customerId', 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','btr.id','d.deviceName','btr.sensorTagName','btr.lastDueDate','btr.typeCheck','btr.percentageDeviation','btr.created_at','btr.result','btr.userEmail')
                            ->where('customerId','=',$this->companyCode)
                         ->where('bl.id','=',$request->building_id)
                          ->where('f.id','=',$request->facility_id)
                          ->where('b.id','=',$request->branch_id)
                          ->where('l.id','=',$request->location_id);
                       }
                        else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""){
                           $query = DB::table('customers as c')
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
                            ->Join('bump_test_results as btr', function($join){
                                $join->on('c.customerId', '=', 'btr.companyCode')
                                    ->on('d.id', '=', 'btr.device_id');

                            })
                            ->select('c.customerId', 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','btr.id','d.deviceName','btr.sensorTagName','btr.lastDueDate','btr.typeCheck','btr.percentageDeviation','btr.created_at','btr.result','btr.userEmail')
                            ->where('customerId','=',$this->companyCode)
                          ->where('f.id','=',$request->facility_id)
                          ->where('b.id','=',$request->branch_id)
                          ->where('l.id','=',$request->location_id);
                       }
                        else if($request->location_id != "" && $request->branch_id != "" ){
                           $query = DB::table('customers as c')
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
                            ->Join('bump_test_results as btr', function($join){
                                $join->on('c.customerId', '=', 'btr.companyCode')
                                    ->on('d.id', '=', 'btr.device_id');

                            })
                            ->select('c.customerId', 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','btr.id','d.deviceName','btr.sensorTagName','btr.lastDueDate','btr.typeCheck','btr.percentageDeviation','btr.created_at','btr.result','btr.userEmail')
                              ->where('customerId','=',$this->companyCode)
                          ->where('b.id','=',$request->branch_id)
                          ->where('l.id','=',$request->location_id);
                       }
                         else if($request->location_id != ""){
                           $query = DB::table('customers as c')
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
                            ->Join('bump_test_results as btr', function($join){
                                $join->on('c.customerId', '=', 'btr.companyCode')
                                    ->on('d.id', '=', 'btr.device_id');

                            })
                            ->select('c.customerId', 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','btr.id','d.deviceName','btr.sensorTagName','btr.lastDueDate','btr.typeCheck','btr.percentageDeviation','btr.created_at','btr.result','btr.userEmail')
                               ->where('customerId','=',$this->companyCode)
                              ->where('l.id','=',$request->location_id);
                       }
                       else{
                             $query = DB::table('customers as c')
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
                            ->Join('bump_test_results as btr', function($join){
                                $join->on('c.customerId', '=', 'btr.companyCode')
                                    ->on('d.id', '=', 'btr.device_id');

                            })
                            ->select('c.customerId', 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','btr.id','d.deviceName','btr.sensorTagName','btr.lastDueDate','btr.typeCheck','btr.percentageDeviation','btr.created_at','btr.result','btr.userEmail')
                               ->where('customerId','=',$this->companyCode);
                       }
        }


        if($startDate === $endDate){
            $query->whereDate('btr.created_at','=',$startDate);
        }
        else{

             $query->whereDate('btr.created_at', '>=', $startDate)
                              ->whereDate('btr.created_at', '<=', $endDate);
        }

        $getData = new ReportsDataUtilityController($request,$query);

        $response = [
             "data"=>$getData->getData()

        ];

        $status = 200;
        return response($response,$status);
    }

    public function exportBumpTest(Request $request) {

        $startDate = date("Y-m-d",strtotime($request->fromDate));
        $endDate = date("Y-m-d", strtotime($request->toDate));

       if($request->device_id != "")
             {
                       if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !="" && $request->lab_id !=""){
                            $query = DB::table('customers as c')
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
                            ->Join('bump_test_results as btr', function($join){
                                $join->on('c.customerId', '=', 'btr.companyCode')
                                    ->on('d.id', '=', 'btr.device_id');

                            })
                       ->select(DB::raw('DATE_FORMAT(btr.created_at, "%d-%m-%Y") as date'),'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','btr.sensorTagName','btr.result','btr.percentageDeviation','btr.typeCheck','btr.lastDueDate')
                       ->where('customerId','=',$this->companyCode)
                          ->where('lb.id','=',$request->lab_id)
                         ->where('fl.id','=',$request->floor_id)
                         ->where('bl.id','=',$request->building_id)
                          ->where('f.id','=',$request->facility_id)
                          ->where('b.id','=',$request->branch_id)
                          ->where('l.id','=',$request->location_id)
                           ->where('device_id','=',$request->device_id);
                       }
                       else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !=""){
                           $query = DB::table('customers as c')
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
                            ->Join('bump_test_results as btr', function($join){
                                $join->on('c.customerId', '=', 'btr.companyCode')
                                    ->on('d.id', '=', 'btr.device_id');

                            })
                       ->select(DB::raw('DATE_FORMAT(btr.created_at, "%d-%m-%Y") as date'),'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','btr.sensorTagName','btr.result','btr.percentageDeviation','btr.typeCheck','btr.lastDueDate')
                             ->where('customerId','=',$this->companyCode)
                         ->where('fl.id','=',$request->floor_id)
                         ->where('bl.id','=',$request->building_id)
                          ->where('f.id','=',$request->facility_id)
                          ->where('b.id','=',$request->branch_id)
                          ->where('l.id','=',$request->location_id)
                           ->where('device_id','=',$request->device_id);
                       }
                        else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !=""){
                           $query = DB::table('customers as c')
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
                            ->Join('bump_test_results as btr', function($join){
                                $join->on('c.customerId', '=', 'btr.companyCode')
                                    ->on('d.id', '=', 'btr.device_id');

                            })
                       ->select(DB::raw('DATE_FORMAT(btr.created_at, "%d-%m-%Y") as date'),'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','btr.sensorTagName','btr.result','btr.percentageDeviation','btr.typeCheck','btr.lastDueDate')
                            ->where('customerId','=',$this->companyCode)
                           ->where('bl.id','=',$request->building_id)
                          ->where('f.id','=',$request->facility_id)
                          ->where('b.id','=',$request->branch_id)
                          ->where('l.id','=',$request->location_id)
                           ->where('device_id','=',$request->device_id);
                       }
                        else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""){
                           $query = DB::table('customers as c')
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
                            ->Join('bump_test_results as btr', function($join){
                                $join->on('c.customerId', '=', 'btr.companyCode')
                                    ->on('d.id', '=', 'btr.device_id');

                            })
                       ->select(DB::raw('DATE_FORMAT(btr.created_at, "%d-%m-%Y") as date'),'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','btr.sensorTagName','btr.result','btr.percentageDeviation','btr.typeCheck','btr.lastDueDate')
                            ->where('customerId','=',$this->companyCode)
                          ->where('f.id','=',$request->facility_id)
                          ->where('b.id','=',$request->branch_id)
                          ->where('l.id','=',$request->location_id)
                               ->where('device_id','=',$request->device_id);
                       }
                        else if($request->location_id != "" && $request->branch_id != "" ){
                           $query = DB::table('customers as c')
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
                            ->Join('bump_test_results as btr', function($join){
                                $join->on('c.customerId', '=', 'btr.companyCode')
                                    ->on('d.id', '=', 'btr.device_id');

                            })
                             ->select(DB::raw('DATE_FORMAT(btr.created_at, "%d-%m-%Y") as date'),'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','btr.sensorTagName','btr.result','btr.percentageDeviation','btr.typeCheck','btr.lastDueDate')
                              ->where('customerId','=',$this->companyCode)
                              ->where('b.id','=',$request->branch_id)
                              ->where('l.id','=',$request->location_id)
                               ->where('device_id','=',$request->device_id);
                       }
                         else if($request->location_id != ""){
                           $query = DB::table('customers as c')
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
                            ->Join('bump_test_results as btr', function($join){
                                $join->on('c.customerId', '=', 'btr.companyCode')
                                    ->on('d.id', '=', 'btr.device_id');

                            })
                       ->select(DB::raw('DATE_FORMAT(btr.created_at, "%d-%m-%Y") as date'),'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','btr.sensorTagName','btr.result','btr.percentageDeviation','btr.typeCheck','btr.lastDueDate')
                               ->where('customerId','=',$this->companyCode)
                               ->where('l.id','=',$request->location_id)
                               ->where('device_id','=',$request->device_id);
                       }
                       else{
                             $query = DB::table('customers as c')
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
                            ->Join('bump_test_results as btr', function($join){
                                $join->on('c.customerId', '=', 'btr.companyCode')
                                    ->on('d.id', '=', 'btr.device_id');

                            })
                       ->select(DB::raw('DATE_FORMAT(btr.created_at, "%d-%m-%Y") as date'),'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','btr.sensorTagName','btr.result','btr.percentageDeviation','btr.typeCheck','btr.lastDueDate')
                               ->where('customerId','=',$this->companyCode)
                               ->where('device_id','=',$request->device_id);

                       }

            }
        else{

                       if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !="" && $request->lab_id !=""){
                            $query = DB::table('customers as c')
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
                            ->Join('bump_test_results as btr', function($join){
                                $join->on('c.customerId', '=', 'btr.companyCode')
                                    ->on('d.id', '=', 'btr.device_id');

                            })
                       ->select(DB::raw('DATE_FORMAT(btr.created_at, "%d-%m-%Y") as date'),'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','btr.sensorTagName','btr.result','btr.percentageDeviation','btr.typeCheck','btr.lastDueDate')
                            ->where('customerId','=',$this->companyCode)
                             ->where('lb.id','=',$request->lab_id)
                             ->where('fl.id','=',$request->floor_id)
                             ->where('bl.id','=',$request->building_id)
                              ->where('f.id','=',$request->facility_id)
                              ->where('b.id','=',$request->branch_id)
                              ->where('l.id','=',$request->location_id);
                       }
                       else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !=""){
                           $query = DB::table('customers as c')
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
                            ->Join('bump_test_results as btr', function($join){
                                $join->on('c.customerId', '=', 'btr.companyCode')
                                    ->on('d.id', '=', 'btr.device_id');

                            })
                       ->select(DB::raw('DATE_FORMAT(btr.created_at, "%d-%m-%Y") as date'),'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','btr.sensorTagName','btr.result','btr.percentageDeviation','btr.typeCheck','btr.lastDueDate')
                             ->where('customerId','=',$this->companyCode)
                         ->where('fl.id','=',$request->floor_id)
                         ->where('bl.id','=',$request->building_id)
                          ->where('f.id','=',$request->facility_id)
                          ->where('b.id','=',$request->branch_id)
                          ->where('l.id','=',$request->location_id);
                       }
                        else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !=""){
                           $query = DB::table('customers as c')
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
                            ->Join('bump_test_results as btr', function($join){
                                $join->on('c.customerId', '=', 'btr.companyCode')
                                    ->on('d.id', '=', 'btr.device_id');

                            })
                       ->select(DB::raw('DATE_FORMAT(btr.created_at, "%d-%m-%Y") as date'),'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','btr.sensorTagName','btr.result','btr.percentageDeviation','btr.typeCheck','btr.lastDueDate')
                            ->where('customerId','=',$this->companyCode)
                         ->where('bl.id','=',$request->building_id)
                          ->where('f.id','=',$request->facility_id)
                          ->where('b.id','=',$request->branch_id)
                          ->where('l.id','=',$request->location_id);
                       }
                        else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""){
                           $query = DB::table('customers as c')
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
                            ->Join('bump_test_results as btr', function($join){
                                $join->on('c.customerId', '=', 'btr.companyCode')
                                    ->on('d.id', '=', 'btr.device_id');

                            })
                       ->select(DB::raw('DATE_FORMAT(btr.created_at, "%d-%m-%Y") as date'),'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','btr.sensorTagName','btr.result','btr.percentageDeviation','btr.typeCheck','btr.lastDueDate')
                            ->where('customerId','=',$this->companyCode)
                          ->where('f.id','=',$request->facility_id)
                          ->where('b.id','=',$request->branch_id)
                          ->where('l.id','=',$request->location_id);
                       }
                        else if($request->location_id != "" && $request->branch_id != "" ){
                           $query = DB::table('customers as c')
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
                            ->Join('bump_test_results as btr', function($join){
                                $join->on('c.customerId', '=', 'btr.companyCode')
                                    ->on('d.id', '=', 'btr.device_id');

                            })
                       ->select(DB::raw('DATE_FORMAT(btr.created_at, "%d-%m-%Y") as date'),'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','btr.sensorTagName','btr.result','btr.percentageDeviation','btr.typeCheck','btr.lastDueDate')
                              ->where('customerId','=',$this->companyCode)
                              ->where('b.id','=',$request->branch_id)
                              ->where('l.id','=',$request->location_id);
                       }
                         else if($request->location_id != ""){
                           $query = DB::table('customers as c')
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
                            ->Join('bump_test_results as btr', function($join){
                                $join->on('c.customerId', '=', 'btr.companyCode')
                                    ->on('d.id', '=', 'btr.device_id');

                            })
                       ->select(DB::raw('DATE_FORMAT(btr.created_at, "%d-%m-%Y") as date'),'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','btr.sensorTagName','btr.result','btr.percentageDeviation','btr.typeCheck','btr.lastDueDate')
                           ->where('customerId','=',$this->companyCode)
                          ->where('l.id','=',$request->location_id);
                       }
                       else{
                             $query = DB::table('customers as c')
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
                            ->Join('bump_test_results as btr', function($join){
                                $join->on('c.customerId', '=', 'btr.companyCode')
                                    ->on('d.id', '=', 'btr.device_id');

                            })
                       ->select(DB::raw('DATE_FORMAT(btr.created_at, "%d-%m-%Y") as date'),'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','btr.sensorTagName','btr.result','btr.percentageDeviation','btr.typeCheck','btr.lastDueDate')
                               ->where('customerId','=',$this->companyCode);
                       }
        }



            if($startDate === $endDate){
                $query->whereDate('btr.created_at','=',$startDate);
            }
            else{
                  $query->whereDate('btr.created_at', '>=', $startDate)
                              ->whereDate('btr.created_at', '<=', $endDate);
            }
        return Excel::download(new BumpTestReportExport($query), 'ReportBumpTest.xlsx');
    }


public function emailBumpTest(Request $request) {

               $startDate = date("Y-m-d",strtotime($request->fromDate));
               $endDate = date("Y-m-d", strtotime($request->toDate));

                if($request->device_id != "")
                  {
                       if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !="" && $request->lab_id !=""){
                            $query = DB::table('customers as c')
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
                            ->Join('bump_test_results as btr', function($join){
                                $join->on('c.customerId', '=', 'btr.companyCode')
                                    ->on('d.id', '=', 'btr.device_id');

                            })
                       ->select(DB::raw('DATE_FORMAT(btr.created_at, "%d-%m-%Y") as date'),'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','btr.sensorTagName','btr.result','btr.percentageDeviation','btr.typeCheck','btr.lastDueDate')
                       ->where('customerId','=',$this->companyCode)
                          ->where('lb.id','=',$request->lab_id)
                         ->where('fl.id','=',$request->floor_id)
                         ->where('bl.id','=',$request->building_id)
                          ->where('f.id','=',$request->facility_id)
                          ->where('b.id','=',$request->branch_id)
                          ->where('l.id','=',$request->location_id)
                           ->where('device_id','=',$request->device_id);
                       }
                       else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !=""){
                           $query = DB::table('customers as c')
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
                            ->Join('bump_test_results as btr', function($join){
                                $join->on('c.customerId', '=', 'btr.companyCode')
                                    ->on('d.id', '=', 'btr.device_id');

                            })
                       ->select(DB::raw('DATE_FORMAT(btr.created_at, "%d-%m-%Y") as date'),'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','btr.sensorTagName','btr.result','btr.percentageDeviation','btr.typeCheck','btr.lastDueDate')
                             ->where('customerId','=',$this->companyCode)
                         ->where('fl.id','=',$request->floor_id)
                         ->where('bl.id','=',$request->building_id)
                          ->where('f.id','=',$request->facility_id)
                          ->where('b.id','=',$request->branch_id)
                          ->where('l.id','=',$request->location_id)
                           ->where('device_id','=',$request->device_id);
                       }
                        else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !=""){
                           $query = DB::table('customers as c')
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
                            ->Join('bump_test_results as btr', function($join){
                                $join->on('c.customerId', '=', 'btr.companyCode')
                                    ->on('d.id', '=', 'btr.device_id');

                            })
                       ->select(DB::raw('DATE_FORMAT(btr.created_at, "%d-%m-%Y") as date'),'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','btr.sensorTagName','btr.result','btr.percentageDeviation','btr.typeCheck','btr.lastDueDate')
                            ->where('customerId','=',$this->companyCode)
                           ->where('bl.id','=',$request->building_id)
                          ->where('f.id','=',$request->facility_id)
                          ->where('b.id','=',$request->branch_id)
                          ->where('l.id','=',$request->location_id)
                           ->where('device_id','=',$request->device_id);
                       }
                        else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""){
                           $query = DB::table('customers as c')
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
                            ->Join('bump_test_results as btr', function($join){
                                $join->on('c.customerId', '=', 'btr.companyCode')
                                    ->on('d.id', '=', 'btr.device_id');

                            })
                       ->select(DB::raw('DATE_FORMAT(btr.created_at, "%d-%m-%Y") as date'),'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','btr.sensorTagName','btr.result','btr.percentageDeviation','btr.typeCheck','btr.lastDueDate')
                            ->where('customerId','=',$this->companyCode)
                          ->where('f.id','=',$request->facility_id)
                          ->where('b.id','=',$request->branch_id)
                          ->where('l.id','=',$request->location_id)
                               ->where('device_id','=',$request->device_id);
                       }
                        else if($request->location_id != "" && $request->branch_id != "" ){
                           $query = DB::table('customers as c')
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
                            ->Join('bump_test_results as btr', function($join){
                                $join->on('c.customerId', '=', 'btr.companyCode')
                                    ->on('d.id', '=', 'btr.device_id');

                            })
                             ->select(DB::raw('DATE_FORMAT(btr.created_at, "%d-%m-%Y") as date'),'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','btr.sensorTagName','btr.result','btr.percentageDeviation','btr.typeCheck','btr.lastDueDate')
                              ->where('customerId','=',$this->companyCode)
                              ->where('b.id','=',$request->branch_id)
                              ->where('l.id','=',$request->location_id)
                               ->where('device_id','=',$request->device_id);
                       }
                         else if($request->location_id != ""){
                           $query = DB::table('customers as c')
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
                            ->Join('bump_test_results as btr', function($join){
                                $join->on('c.customerId', '=', 'btr.companyCode')
                                    ->on('d.id', '=', 'btr.device_id');

                            })
                       ->select(DB::raw('DATE_FORMAT(btr.created_at, "%d-%m-%Y") as date'),'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','btr.sensorTagName','btr.result','btr.percentageDeviation','btr.typeCheck','btr.lastDueDate')
                               ->where('customerId','=',$this->companyCode)
                               ->where('l.id','=',$request->location_id)
                               ->where('device_id','=',$request->device_id);
                       }
                       else{
                             $query = DB::table('customers as c')
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
                            ->Join('bump_test_results as btr', function($join){
                                $join->on('c.customerId', '=', 'btr.companyCode')
                                    ->on('d.id', '=', 'btr.device_id');

                            })
                       ->select(DB::raw('DATE_FORMAT(btr.created_at, "%d-%m-%Y") as date'),'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','btr.sensorTagName','btr.result','btr.percentageDeviation','btr.typeCheck','btr.lastDueDate')
                               ->where('customerId','=',$this->companyCode)
                               ->where('device_id','=',$request->device_id);

                       }

            }
                else{

                       if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !="" && $request->lab_id !=""){
                            $query = DB::table('customers as c')
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
                            ->Join('bump_test_results as btr', function($join){
                                $join->on('c.customerId', '=', 'btr.companyCode')
                                    ->on('d.id', '=', 'btr.device_id');

                            })
                       ->select(DB::raw('DATE_FORMAT(btr.created_at, "%d-%m-%Y") as date'),'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','btr.sensorTagName','btr.result','btr.percentageDeviation','btr.typeCheck','btr.lastDueDate')
                            ->where('customerId','=',$this->companyCode)
                             ->where('lb.id','=',$request->lab_id)
                             ->where('fl.id','=',$request->floor_id)
                             ->where('bl.id','=',$request->building_id)
                              ->where('f.id','=',$request->facility_id)
                              ->where('b.id','=',$request->branch_id)
                              ->where('l.id','=',$request->location_id);
                       }
                       else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !=""){
                           $query = DB::table('customers as c')
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
                            ->Join('bump_test_results as btr', function($join){
                                $join->on('c.customerId', '=', 'btr.companyCode')
                                    ->on('d.id', '=', 'btr.device_id');

                            })
                       ->select(DB::raw('DATE_FORMAT(btr.created_at, "%d-%m-%Y") as date'),'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','btr.sensorTagName','btr.result','btr.percentageDeviation','btr.typeCheck','btr.lastDueDate')
                             ->where('customerId','=',$this->companyCode)
                         ->where('fl.id','=',$request->floor_id)
                         ->where('bl.id','=',$request->building_id)
                          ->where('f.id','=',$request->facility_id)
                          ->where('b.id','=',$request->branch_id)
                          ->where('l.id','=',$request->location_id);
                       }
                        else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !=""){
                           $query = DB::table('customers as c')
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
                            ->Join('bump_test_results as btr', function($join){
                                $join->on('c.customerId', '=', 'btr.companyCode')
                                    ->on('d.id', '=', 'btr.device_id');

                            })
                       ->select(DB::raw('DATE_FORMAT(btr.created_at, "%d-%m-%Y") as date'),'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','btr.sensorTagName','btr.result','btr.percentageDeviation','btr.typeCheck','btr.lastDueDate')
                            ->where('customerId','=',$this->companyCode)
                         ->where('bl.id','=',$request->building_id)
                          ->where('f.id','=',$request->facility_id)
                          ->where('b.id','=',$request->branch_id)
                          ->where('l.id','=',$request->location_id);
                       }
                        else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""){
                           $query = DB::table('customers as c')
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
                            ->Join('bump_test_results as btr', function($join){
                                $join->on('c.customerId', '=', 'btr.companyCode')
                                    ->on('d.id', '=', 'btr.device_id');

                            })
                       ->select(DB::raw('DATE_FORMAT(btr.created_at, "%d-%m-%Y") as date'),'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','btr.sensorTagName','btr.result','btr.percentageDeviation','btr.typeCheck','btr.lastDueDate')
                            ->where('customerId','=',$this->companyCode)
                          ->where('f.id','=',$request->facility_id)
                          ->where('b.id','=',$request->branch_id)
                          ->where('l.id','=',$request->location_id);
                       }
                        else if($request->location_id != "" && $request->branch_id != "" ){
                           $query = DB::table('customers as c')
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
                            ->Join('bump_test_results as btr', function($join){
                                $join->on('c.customerId', '=', 'btr.companyCode')
                                    ->on('d.id', '=', 'btr.device_id');

                            })
                       ->select(DB::raw('DATE_FORMAT(btr.created_at, "%d-%m-%Y") as date'),'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','btr.sensorTagName','btr.result','btr.percentageDeviation','btr.typeCheck','btr.lastDueDate')
                              ->where('customerId','=',$this->companyCode)
                              ->where('b.id','=',$request->branch_id)
                              ->where('l.id','=',$request->location_id);
                       }
                         else if($request->location_id != ""){
                           $query = DB::table('customers as c')
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
                            ->Join('bump_test_results as btr', function($join){
                                $join->on('c.customerId', '=', 'btr.companyCode')
                                    ->on('d.id', '=', 'btr.device_id');

                            })
                       ->select(DB::raw('DATE_FORMAT(btr.created_at, "%d-%m-%Y") as date'),'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','btr.sensorTagName','btr.result','btr.percentageDeviation','btr.typeCheck','btr.lastDueDate')
                           ->where('customerId','=',$this->companyCode)
                          ->where('l.id','=',$request->location_id);
                       }
                       else{
                             $query = DB::table('customers as c')
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
                            ->Join('bump_test_results as btr', function($join){
                                $join->on('c.customerId', '=', 'btr.companyCode')
                                    ->on('d.id', '=', 'btr.device_id');

                            })
                       ->select(DB::raw('DATE_FORMAT(btr.created_at, "%d-%m-%Y") as date'),'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','btr.sensorTagName','btr.result','btr.percentageDeviation','btr.typeCheck','btr.lastDueDate')
                               ->where('customerId','=',$this->companyCode);
                       }
        }




                if($startDate === $endDate) {
                    $query->whereDate('btr.created_at','=',$startDate);
                }
                else {
                      $query->whereDate('btr.created_at', '>=', $startDate)
                             ->whereDate('btr.created_at', '<=', $endDate);
                }




                $attachment =  Excel::raw(new BumpTestReportExport($query), BaseExcel::XLSX);

                $userEmail = $this->userId;
                $email = $request->header('Userid');
                $url = env('APPLICATION_URL');

                if($email == 0){
                    $response = [
                            "message"=>"Email IS NOT VERIFIED"
                        ];
                        $status = 401;
                     return response($response, $status);
                }else{

                    $data = [
                        'meassage' => 'Bumptest Reports',
                        'url' => $url
                    ];

                    Mail::send('BumptestReport',$data, function($messages) use ($email, $attachment){
                        $messages->to($email);
                        $messages->subject('Bumptest Reports');
                        $messages->attachData($attachment, 'ReportBumpTest.xlsx',[
                        ]);
                    });

                       $response = [
                            "message"=>"Reports data sent Successfully"
                        ];
                        $status = 200;

                     return response($response, $status);
                }





}




    /*

    public function exportBumpTest(Request $request) {

        $startDate = date("Y-m-d",strtotime($request->fromDate));
        $endDate = date("Y-m-d", strtotime($request->toDate));

        $query = DB::table('customers as c')
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
        ->Join('bump_test_results as btr', function($join){
            $join->on('c.customerId', '=', 'btr.companyCode')
                ->on('d.id', '=', 'btr.device_id');

        })
        ->select(DB::raw('DATE_FORMAT(btr.created_at, "%d-%m-%Y") as date'),'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','btr.sensorTagName','btr.result','btr.percentageDeviation','btr.typeCheck','btr.lastDueDate')
        ->WHERE('customerId','=',$this->companyCode)
        ->WHERE('btr.device_id','=',$request->deviceId);



            if($startDate === $endDate){
                $query->whereDate('btr.created_at','=',$startDate);
            }
            else{
                  $query->whereDate('btr.created_at', '>=', $startDate)
                              ->whereDate('btr.created_at', '<=', $endDate);
            }
        return Excel::download(new BumpTestReportExport($query), 'ReportBumpTest.xlsx');
    }

      */
      /*

     public function emailBumpTest(Request $request) {


               $startDate = date("Y-m-d",strtotime($request->fromDate));
               $endDate = date("Y-m-d", strtotime($request->toDate));

                $query = DB::table('customers as c')
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
                ->Join('bump_test_results as btr', function($join){
                    $join->on('c.customerId', '=', 'btr.companyCode')
                        ->on('d.id', '=', 'btr.device_id');

                })
                // ->select(DB::raw('*, DATE_FORMAT(btr.created_at,"%d-%m-%Y") as createdDate, TIME(btr.created_at) as createdTime'))
                ->select(DB::raw('DATE_FORMAT(btr.created_at, "%d-%m-%Y") as date'),'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','btr.sensorTagName','btr.result','btr.percentageDeviation','btr.typeCheck','btr.lastDueDate')
                 ->where('customerId','=',$this->companyCode)
                ->WHERE('btr.device_id','=',$request->deviceId);
                //->WHERE('sensorTagName','=',$request->sensorTagName)

                if($startDate === $endDate) {
                    $query->whereDate('btr.created_at','=',$startDate);
                }
                else {
                    // $query->whereBetween('btr.created_at', [$startDate, $endDate]);
                      $query->whereDate('btr.created_at', '>=', $startDate)
                              ->whereDate('btr.created_at', '<=', $endDate);
                }




                $attachment =  Excel::raw(new BumpTestReportExport($query), BaseExcel::XLSX);

                //   $email = "developer2@rdltech.in";

                $userEmail = $this->userId;
                $email = $this->fetchVerifiedEmailUsers($userEmail);
                if($email == 0){
                    $response = [
                            "message"=>"Email IS NOT VERIFIED"
                        ];
                        $status = 401;
                     return response($response, $status);
                }else{

                    $data = [
                        'userid'=>$email,
                        'body' =>"BumpTest Reports"
                    ];

                    Mail::send('BumptestReport',$data, function($messages) use ($email,$attachment){
                        $messages->to($email);
                        $messages->subject('BumpTest Reports');
                        $messages->attachData($attachment, 'ReportBumpTest.xlsx',[
                             ]);
                    });

                       $response = [
                            "message"=>"Reports data sent Successfully"
                        ];
                        $status = 200;
                     return response($response, $status);
                }
    }*/



    public function alarmReport(Request $request){

        $startDate = date("Y-m-d",strtotime($request->fromDate));
        $endDate = date("Y-m-d", strtotime($request->toDate));

        if( $request->deviceId !=""){

          if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !="" && $request->lab_id !="")
            {
                $query = DB::table('customers as c')
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
                ->Join('alert_crons as alarm', function($join){
                    $join->on('c.customerId', '=', 'alarm.companyCode')
                          ->on('d.id', '=', 'alarm.deviceId');
                })
                ->select('alarm.id','l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','alarm.deviceId','alarm.Reason','alarm.sensorTag','alarm.msg','alarm.alertStandardMessage','alarm.sensorId','alarm.a_date','alarm.a_time','alarm.alertTriggeredDuration','alarm.alarmClearedUser')
                        ->where('customerId','=',$this->companyCode)
                        ->where('l.id','=',$request->location_id)
                        ->where('b.id','=',$request->branch_id)
                        ->where('f.id','=',$request->facility_id)
                        ->where('bl.id','=',$request->building_id)
                        ->where('fl.id','=',$request->floor_id)
                        ->where('lb.id','=',$request->lab_id)
                        ->WHERE('deviceId','=',$request->deviceId)
                        ->orderBy('id', 'DESC');

            }
          else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !=""){
               $query = DB::table('customers as c')
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
                    ->Join('alert_crons as alarm', function($join){
                        $join->on('c.customerId', '=', 'alarm.companyCode')
                              ->on('d.id', '=', 'alarm.deviceId');
                    })
                    ->select('alarm.id','l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','alarm.deviceId','alarm.Reason','alarm.sensorTag','alarm.msg','alarm.alertStandardMessage','alarm.sensorId','alarm.a_date','alarm.a_time','alarm.alertTriggeredDuration','alarm.alarmClearedUser')
                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->where('b.id','=',$request->branch_id)
                            ->where('f.id','=',$request->facility_id)
                            ->where('bl.id','=',$request->building_id)
                            ->where('fl.id','=',$request->floor_id)
                            ->WHERE('deviceId','=',$request->deviceId)
                            ->orderBy('id', 'DESC');
            }
       else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !=""){
                  $query = DB::table('customers as c')
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
                        ->Join('alert_crons as alarm', function($join){
                            $join->on('c.customerId', '=', 'alarm.companyCode')
                                  ->on('d.id', '=', 'alarm.deviceId');
                        })
                        ->select('alarm.id','l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','alarm.deviceId','alarm.Reason','alarm.sensorTag','alarm.msg','alarm.alertStandardMessage','alarm.sensorId','alarm.a_date','alarm.a_time','alarm.alertTriggeredDuration','alarm.alarmClearedUser')
                                ->where('customerId','=',$this->companyCode)
                                ->where('l.id','=',$request->location_id)
                                ->where('b.id','=',$request->branch_id)
                                ->where('f.id','=',$request->facility_id)
                                ->where('bl.id','=',$request->building_id)
                                ->WHERE('deviceId','=',$request->deviceId)
                                ->orderBy('id', 'DESC');
           }
       else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""){
                             $query = DB::table('customers as c')
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
                                ->Join('alert_crons as alarm', function($join){
                                    $join->on('c.customerId', '=', 'alarm.companyCode')
                                          ->on('d.id', '=', 'alarm.deviceId');
                                })
                                ->select('alarm.id','l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','alarm.deviceId','alarm.Reason','alarm.sensorTag','alarm.msg','alarm.alertStandardMessage','alarm.sensorId','alarm.a_date','alarm.a_time','alarm.alertTriggeredDuration','alarm.alarmClearedUser')
                                        ->where('customerId','=',$this->companyCode)
                                        ->where('l.id','=',$request->location_id)
                                        ->where('b.id','=',$request->branch_id)
                                        ->where('f.id','=',$request->facility_id)
                                        ->WHERE('deviceId','=',$request->deviceId)
                                        ->orderBy('id', 'DESC');
       }
       else if($request->location_id != "" && $request->branch_id != ""){
                             $query = DB::table('customers as c')
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
                                ->Join('alert_crons as alarm', function($join){
                                    $join->on('c.customerId', '=', 'alarm.companyCode')
                                          ->on('d.id', '=', 'alarm.deviceId');
                                })
                                ->select('alarm.id','l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','alarm.deviceId','alarm.Reason','alarm.sensorTag','alarm.msg','alarm.alertStandardMessage','alarm.sensorId','alarm.a_date','alarm.a_time','alarm.alertTriggeredDuration','alarm.alarmClearedUser')
                                        ->where('customerId','=',$this->companyCode)
                                        ->where('l.id','=',$request->location_id)
                                        ->where('b.id','=',$request->branch_id)
                                        ->WHERE('deviceId','=',$request->deviceId)
                                        ->orderBy('id', 'DESC');
           }
        else if($request->location_id != ""){
                             $query = DB::table('customers as c')
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
                                ->Join('alert_crons as alarm', function($join){
                                    $join->on('c.customerId', '=', 'alarm.companyCode')
                                          ->on('d.id', '=', 'alarm.deviceId');
                                })
                                ->select('alarm.id','l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','alarm.deviceId','alarm.Reason','alarm.sensorTag','alarm.msg','alarm.alertStandardMessage','alarm.sensorId','alarm.a_date','alarm.a_time','alarm.alertTriggeredDuration','alarm.alarmClearedUser')
                                        ->where('customerId','=',$this->companyCode)
                                        ->where('l.id','=',$request->location_id)
                                        ->WHERE('deviceId','=',$request->deviceId)
                                        ->orderBy('id', 'DESC');
        }
         else{
                        $query = DB::table('customers as c')
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
                        ->Join('alert_crons as alarm', function($join){
                            $join->on('c.customerId', '=', 'alarm.companyCode')
                                  ->on('d.id', '=', 'alarm.deviceId');
                        })
                        ->select('alarm.id','l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','alarm.deviceId','alarm.Reason','alarm.sensorTag','alarm.msg','alarm.alertStandardMessage','alarm.sensorId','alarm.a_date','alarm.a_time','alarm.alertTriggeredDuration','alarm.alarmType','alarm.alarmClearedUser')
                                ->where('customerId','=',$this->companyCode)
                                ->WHERE('deviceId','=',$request->deviceId)
                                ->orderBy('id', 'DESC');
              }
        }else{

        if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !="" && $request->lab_id !="")
            {
                $query = DB::table('customers as c')
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
                ->Join('alert_crons as alarm', function($join){
                    $join->on('c.customerId', '=', 'alarm.companyCode')
                          ->on('d.id', '=', 'alarm.deviceId');
                })
                ->select('alarm.id','l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','alarm.deviceId','alarm.Reason','alarm.sensorTag','alarm.msg','alarm.alertStandardMessage','alarm.sensorId','alarm.a_date','alarm.a_time','alarm.alertTriggeredDuration','alarm.alarmType','alarm.alarmClearedUser')
                        ->where('customerId','=',$this->companyCode)
                        ->where('l.id','=',$request->location_id)
                        ->where('b.id','=',$request->branch_id)
                        ->where('f.id','=',$request->facility_id)
                        ->where('bl.id','=',$request->building_id)
                        ->where('fl.id','=',$request->floor_id)
                        ->where('lb.id','=',$request->lab_id)
                        // ->WHERE('deviceId','=',$request->deviceId)
                        ->orderBy('id', 'DESC');

            }
          else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !=""){
               $query = DB::table('customers as c')
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
                    ->Join('alert_crons as alarm', function($join){
                        $join->on('c.customerId', '=', 'alarm.companyCode')
                              ->on('d.id', '=', 'alarm.deviceId');
                    })
                    ->select('alarm.id','l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','alarm.deviceId','alarm.Reason','alarm.sensorTag','alarm.msg','alarm.alertStandardMessage','alarm.sensorId','alarm.a_date','alarm.a_time','alarm.alertTriggeredDuration','alarm.alarmType','alarm.alarmClearedUser')
                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->where('b.id','=',$request->branch_id)
                            ->where('f.id','=',$request->facility_id)
                            ->where('bl.id','=',$request->building_id)
                            ->where('fl.id','=',$request->floor_id)
                            // ->WHERE('deviceId','=',$request->deviceId)
                            ->orderBy('id', 'DESC');
            }
       else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !=""){
                  $query = DB::table('customers as c')
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
                        ->Join('alert_crons as alarm', function($join){
                            $join->on('c.customerId', '=', 'alarm.companyCode')
                                  ->on('d.id', '=', 'alarm.deviceId');
                        })
                        ->select('alarm.id','l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','alarm.deviceId','alarm.Reason','alarm.sensorTag','alarm.msg','alarm.alertStandardMessage','alarm.sensorId','alarm.a_date','alarm.a_time','alarm.alertTriggeredDuration','alarm.alarmType','alarm.alarmClearedUser')
                                ->where('customerId','=',$this->companyCode)
                                ->where('l.id','=',$request->location_id)
                                ->where('b.id','=',$request->branch_id)
                                ->where('f.id','=',$request->facility_id)
                                ->where('bl.id','=',$request->building_id)
                                // ->WHERE('deviceId','=',$request->deviceId)
                                ->orderBy('id', 'DESC');
           }
       else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""){
                             $query = DB::table('customers as c')
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
                                ->Join('alert_crons as alarm', function($join){
                                    $join->on('c.customerId', '=', 'alarm.companyCode')
                                          ->on('d.id', '=', 'alarm.deviceId');
                                })
                                ->select('alarm.id','l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','alarm.deviceId','alarm.Reason','alarm.sensorTag','alarm.msg','alarm.alertStandardMessage','alarm.sensorId','alarm.a_date','alarm.a_time','alarm.alertTriggeredDuration','alarm.alarmType','alarm.alarmClearedUser')
                                        ->where('customerId','=',$this->companyCode)
                                        ->where('l.id','=',$request->location_id)
                                        ->where('b.id','=',$request->branch_id)
                                        ->where('f.id','=',$request->facility_id)
                                        // ->WHERE('deviceId','=',$request->deviceId)
                                        ->orderBy('id', 'DESC');
       }
       else if($request->location_id != "" && $request->branch_id != ""){
                             $query = DB::table('customers as c')
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
                                ->Join('alert_crons as alarm', function($join){
                                    $join->on('c.customerId', '=', 'alarm.companyCode')
                                          ->on('d.id', '=', 'alarm.deviceId');
                                })
                                ->select('alarm.id','l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','alarm.deviceId','alarm.Reason','alarm.sensorTag','alarm.msg','alarm.alertStandardMessage','alarm.sensorId','alarm.a_date','alarm.a_time','alarm.alertTriggeredDuration','alarm.alarmType','alarm.alarmClearedUser')
                                        ->where('customerId','=',$this->companyCode)
                                        ->where('l.id','=',$request->location_id)
                                        ->where('b.id','=',$request->branch_id)
                                        // ->WHERE('deviceId','=',$request->deviceId)
                                        ->orderBy('id', 'DESC');
           }
        else if($request->location_id != ""){
                             $query = DB::table('customers as c')
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
                                ->Join('alert_crons as alarm', function($join){
                                    $join->on('c.customerId', '=', 'alarm.companyCode')
                                          ->on('d.id', '=', 'alarm.deviceId');
                                })
                                ->select('alarm.id','l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','alarm.deviceId','alarm.Reason','alarm.sensorTag','alarm.msg','alarm.alertStandardMessage','alarm.sensorId','alarm.a_date','alarm.a_time','alarm.alertTriggeredDuration','alarm.alarmType','alarm.alarmClearedUser')
                                        ->where('customerId','=',$this->companyCode)
                                        ->where('l.id','=',$request->location_id)
                                        // ->WHERE('deviceId','=',$request->deviceId)
                                        ->orderBy('id', 'DESC');
        }
         else{
                        $query = DB::table('customers as c')
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
                        ->Join('alert_crons as alarm', function($join){
                            $join->on('c.customerId', '=', 'alarm.companyCode')
                                  ->on('d.id', '=', 'alarm.deviceId');
                        })
                        ->select('alarm.id','l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','alarm.deviceId','alarm.Reason','alarm.sensorTag','alarm.msg','alarm.alertStandardMessage','alarm.sensorId','alarm.a_date','alarm.a_time','alarm.alertTriggeredDuration','alarm.alarmType','alarm.alarmClearedUser')
                                ->where('customerId','=',$this->companyCode)
                                // ->WHERE('deviceId','=',$request->deviceId)
                                ->orderBy('id', 'DESC');
              }
        }

        if($startDate === $endDate){
            $query->whereDate('alarm.a_date','=',$startDate);
        }
        else {
            $query->whereBetween('alarm.a_date', [$startDate, $endDate]);
        }

        $getData = new ReportsDataUtilityController($request,$query);

        $response = [
             "data"=>$getData->getData()
        ];

        $status = 200;

       return response($response,$status);
}



    public function exportAlarm(Request $request){


        $startDate = date("Y-m-d",strtotime($request->fromDate));
        $endDate = date("Y-m-d", strtotime($request->toDate));

    if( $request->deviceId !=""){

        if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !="" && $request->lab_id !=""){

                    $query = DB::table('customers as c')
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
                    ->Join('alert_crons as alarm', function($join){
                        $join->on('c.customerId', '=', 'alarm.companyCode')
                              ->on('d.id', '=', 'alarm.deviceId');
                    })
                    ->select(DB::raw('CONCAT(DATE_FORMAT(alarm.a_date, "%d-%m-%Y"), " | ", alarm.a_time) as concatenated_column'),'alarm.alertTriggeredDuration','l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','alarm.sensorTag','alarm.alertStandardMessage','alarm.msg','alarm.Reason')
                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->where('b.id','=',$request->branch_id)
                            ->where('f.id','=',$request->facility_id)
                            ->where('bl.id','=',$request->building_id)
                            ->where('fl.id','=',$request->floor_id)
                            ->where('lb.id','=',$request->lab_id)
                            ->WHERE('alarm.deviceId','=',$request->deviceId)
                             ->orderBy('alarm.id', 'DESC');

            }
        else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !=""){

          $query = DB::table('customers as c')
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
                    ->Join('alert_crons as alarm', function($join){
                        $join->on('c.customerId', '=', 'alarm.companyCode')
                              ->on('d.id', '=', 'alarm.deviceId');
                    })
                    ->select(DB::raw('CONCAT(DATE_FORMAT(alarm.a_date, "%d-%m-%Y"), " | ", alarm.a_time) as concatenated_column'),'alarm.alertTriggeredDuration','l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','alarm.sensorTag','alarm.alertStandardMessage','alarm.msg','alarm.Reason')
                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->where('b.id','=',$request->branch_id)
                            ->where('f.id','=',$request->facility_id)
                            ->where('bl.id','=',$request->building_id)
                            ->where('fl.id','=',$request->floor_id)
                            ->WHERE('alarm.deviceId','=',$request->deviceId)
                            ->orderBy('alarm.id', 'DESC');
            }
        else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !=""){
              $query = DB::table('customers as c')
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
                    ->Join('alert_crons as alarm', function($join){
                        $join->on('c.customerId', '=', 'alarm.companyCode')
                              ->on('d.id', '=', 'alarm.deviceId');
                    })
                  ->select(DB::raw('DATE_FORMAT(alarm.a_date, "%d-%b-%Y") as date'),'alarm.a_time','d.deviceName','l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','alarm.sensorTag','alarm.alertStandardMessage','alarm.msg','alarm.Reason','alarm.alertTriggeredDuration')
                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->where('b.id','=',$request->branch_id)
                            ->where('f.id','=',$request->facility_id)
                            ->where('bl.id','=',$request->building_id)
                            ->WHERE('alarm.deviceId','=',$request->deviceId)
                           ->orderBy('alarm.id', 'DESC');
        }
           else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""){
                 $query = DB::table('customers as c')
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
                    ->Join('alert_crons as alarm', function($join){
                        $join->on('c.customerId', '=', 'alarm.companyCode')
                              ->on('d.id', '=', 'alarm.deviceId');
                    })
                ->select(DB::raw('DATE_FORMAT(alarm.a_date, "%d-%b-%Y") as date'),'alarm.a_time','d.deviceName','l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','alarm.sensorTag','alarm.alertStandardMessage','alarm.msg','alarm.Reason','alarm.alertTriggeredDuration')
                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->where('b.id','=',$request->branch_id)
                            ->where('f.id','=',$request->facility_id)
                            ->WHERE('alarm.deviceId','=',$request->deviceId)
                           ->orderBy('alarm.id', 'DESC');
           }
           else if($request->location_id != "" && $request->branch_id != ""){
                 $query = DB::table('customers as c')
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
                    ->Join('alert_crons as alarm', function($join){
                        $join->on('c.customerId', '=', 'alarm.companyCode')
                              ->on('d.id', '=', 'alarm.deviceId');
                    })
                 ->select(DB::raw('DATE_FORMAT(alarm.a_date, "%d-%b-%Y") as date'),'alarm.a_time','d.deviceName','l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','alarm.sensorTag','alarm.alertStandardMessage','alarm.msg','alarm.Reason','alarm.alertTriggeredDuration')
                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->where('b.id','=',$request->branch_id)
                            ->WHERE('alarm.deviceId','=',$request->deviceId)
                            ->orderBy('alarm.id', 'DESC');
           }
            else if($request->location_id != ""){
                 $query = DB::table('customers as c')
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
                    ->Join('alert_crons as alarm', function($join){
                        $join->on('c.customerId', '=', 'alarm.companyCode')
                              ->on('d.id', '=', 'alarm.deviceId');
                    })
                   ->select(DB::raw('DATE_FORMAT(alarm.a_date, "%d-%b-%Y") as date'),'alarm.a_time','d.deviceName','l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','alarm.sensorTag','alarm.alertStandardMessage','alarm.msg','alarm.Reason','alarm.alertTriggeredDuration')
                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->WHERE('alarm.deviceId','=',$request->deviceId)
                             ->orderBy('alarm.id', 'DESC');
            }
              else{
                    $query = DB::table('customers as c')
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
                    ->Join('alert_crons as alarm', function($join){
                        $join->on('c.customerId', '=', 'alarm.companyCode')
                              ->on('d.id', '=', 'alarm.deviceId');
                    })
                    ->select(DB::raw('CONCAT(DATE_FORMAT(alarm.a_date, "%d-%m-%Y"), " | ", alarm.a_time) as concatenated_column'),'alarm.alertTriggeredDuration','l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','alarm.sensorTag','alarm.alertStandardMessage','alarm.msg','alarm.Reason')
                            ->where('c.customerId','=',$this->companyCode)
                            ->WHERE('alarm.deviceId','=',$request->deviceId)
                            ->orderBy('alarm.id', 'DESC');
              }

        }
        else{

             if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !="" && $request->lab_id !=""){

                    $query = DB::table('customers as c')
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
                    ->Join('alert_crons as alarm', function($join){
                        $join->on('c.customerId', '=', 'alarm.companyCode')
                              ->on('d.id', '=', 'alarm.deviceId');
                    })
                    ->select(DB::raw('CONCAT(DATE_FORMAT(alarm.a_date, "%d-%m-%Y"), " | ", alarm.a_time) as concatenated_column'),'alarm.alertTriggeredDuration','l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','alarm.sensorTag','alarm.alertStandardMessage','alarm.msg','alarm.Reason')
                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->where('b.id','=',$request->branch_id)
                            ->where('f.id','=',$request->facility_id)
                            ->where('bl.id','=',$request->building_id)
                            ->where('fl.id','=',$request->floor_id)
                            ->where('lb.id','=',$request->lab_id)
                            // ->WHERE('alarm.deviceId','=',$request->deviceId)
                             ->orderBy('alarm.id', 'DESC');

            }
        else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !=""){

          $query = DB::table('customers as c')
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
                    ->Join('alert_crons as alarm', function($join){
                        $join->on('c.customerId', '=', 'alarm.companyCode')
                              ->on('d.id', '=', 'alarm.deviceId');
                    })
                    ->select(DB::raw('CONCAT(DATE_FORMAT(alarm.a_date, "%d-%m-%Y"), " | ", alarm.a_time) as concatenated_column'),'alarm.alertTriggeredDuration','l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','alarm.sensorTag','alarm.alertStandardMessage','alarm.msg','alarm.Reason')
                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->where('b.id','=',$request->branch_id)
                            ->where('f.id','=',$request->facility_id)
                            ->where('bl.id','=',$request->building_id)
                            ->where('fl.id','=',$request->floor_id)
                            // ->WHERE('alarm.deviceId','=',$request->deviceId)
                            ->orderBy('alarm.id', 'DESC');
            }
        else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !=""){
              $query = DB::table('customers as c')
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
                    ->Join('alert_crons as alarm', function($join){
                        $join->on('c.customerId', '=', 'alarm.companyCode')
                              ->on('d.id', '=', 'alarm.deviceId');
                    })
                  ->select(DB::raw('DATE_FORMAT(alarm.a_date, "%d-%b-%Y") as date'),'alarm.a_time','d.deviceName','l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','alarm.sensorTag','alarm.alertStandardMessage','alarm.msg','alarm.Reason','alarm.alertTriggeredDuration')
                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->where('b.id','=',$request->branch_id)
                            ->where('f.id','=',$request->facility_id)
                            ->where('bl.id','=',$request->building_id)
                            // ->WHERE('alarm.deviceId','=',$request->deviceId)
                           ->orderBy('alarm.id', 'DESC');
        }
           else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""){
                 $query = DB::table('customers as c')
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
                    ->Join('alert_crons as alarm', function($join){
                        $join->on('c.customerId', '=', 'alarm.companyCode')
                              ->on('d.id', '=', 'alarm.deviceId');
                    })
                ->select(DB::raw('DATE_FORMAT(alarm.a_date, "%d-%b-%Y") as date'),'alarm.a_time','d.deviceName','l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','alarm.sensorTag','alarm.alertStandardMessage','alarm.msg','alarm.Reason','alarm.alertTriggeredDuration')
                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->where('b.id','=',$request->branch_id)
                            ->where('f.id','=',$request->facility_id)
                            // ->WHERE('alarm.deviceId','=',$request->deviceId)
                           ->orderBy('alarm.id', 'DESC');
           }
           else if($request->location_id != "" && $request->branch_id != ""){
                 $query = DB::table('customers as c')
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
                    ->Join('alert_crons as alarm', function($join){
                        $join->on('c.customerId', '=', 'alarm.companyCode')
                              ->on('d.id', '=', 'alarm.deviceId');
                    })
                 ->select(DB::raw('DATE_FORMAT(alarm.a_date, "%d-%b-%Y") as date'),'alarm.a_time','d.deviceName','l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','alarm.sensorTag','alarm.alertStandardMessage','alarm.msg','alarm.Reason','alarm.alertTriggeredDuration')
                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->where('b.id','=',$request->branch_id)
                            // ->WHERE('alarm.deviceId','=',$request->deviceId)
                            ->orderBy('alarm.id', 'DESC');
           }
            else if($request->location_id != ""){
                 $query = DB::table('customers as c')
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
                    ->Join('alert_crons as alarm', function($join){
                        $join->on('c.customerId', '=', 'alarm.companyCode')
                              ->on('d.id', '=', 'alarm.deviceId');
                    })
                   ->select(DB::raw('DATE_FORMAT(alarm.a_date, "%d-%b-%Y") as date'),'alarm.a_time','d.deviceName','l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','alarm.sensorTag','alarm.alertStandardMessage','alarm.msg','alarm.Reason','alarm.alertTriggeredDuration')
                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            // ->WHERE('alarm.deviceId','=',$request->deviceId)
                             ->orderBy('alarm.id', 'DESC');
            }
              else{
                    $query = DB::table('customers as c')
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
                    ->Join('alert_crons as alarm', function($join){
                        $join->on('c.customerId', '=', 'alarm.companyCode')
                              ->on('d.id', '=', 'alarm.deviceId');
                    })
                    ->select(DB::raw('CONCAT(DATE_FORMAT(alarm.a_date, "%d-%m-%Y"), " | ", alarm.a_time) as concatenated_column'),'alarm.alertTriggeredDuration','l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','alarm.sensorTag','alarm.alertStandardMessage','alarm.msg','alarm.Reason')
                            ->where('c.customerId','=',$this->companyCode)
                            // ->WHERE('alarm.deviceId','=',$request->deviceId)
                            ->orderBy('alarm.id', 'DESC');
              }


        }

                            if($startDate === $endDate){
                                $query->whereDate('alarm.a_date','=',$startDate);
                            }
                            else {
                                $query->whereBetween('alarm.a_date', [$startDate, $endDate]);
                            }

                        return Excel::download(new ReportExport($query), 'ReportAlarm.xlsx');
}




   public function alarmReportExcelFile(Request $request){

            $startDate = date("Y-m-d",strtotime($request->fromDate));
            $endDate = date("Y-m-d", strtotime($request->toDate));


    if( $request->deviceId !=""){
       if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !="" && $request->lab_id !=""){

            $query = DB::table('customers as c')
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
            ->Join('alert_crons as alarm', function($join){
                $join->on('c.customerId', '=', 'alarm.companyCode')
                      ->on('d.id', '=', 'alarm.deviceId');
            })
            ->select(DB::raw('DATE_FORMAT(alarm.a_date, "%d-%b-%Y") as date'),'alarm.a_time','d.deviceName','l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','alarm.sensorTag','alarm.alertStandardMessage','alarm.Reason','alarm.alertTriggeredDuration')
                    ->where('customerId','=',$this->companyCode)
                    ->where('l.id','=',$request->location_id)
                    ->where('b.id','=',$request->branch_id)
                    ->where('f.id','=',$request->facility_id)
                    ->where('bl.id','=',$request->building_id)
                    ->where('fl.id','=',$request->floor_id)
                    ->where('lb.id','=',$request->lab_id)
                    ->WHERE('alarm.deviceId','=',$request->deviceId)
                    ->orderBy('alarm.id', 'DESC');

    }
else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !=""){

  $query = DB::table('customers as c')
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
            ->Join('alert_crons as alarm', function($join){
                $join->on('c.customerId', '=', 'alarm.companyCode')
                      ->on('d.id', '=', 'alarm.deviceId');
            })
            ->select(DB::raw('DATE_FORMAT(alarm.a_date, "%d-%b-%Y") as date'),'alarm.a_time','d.deviceName','l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','alarm.sensorTag','alarm.alertStandardMessage','alarm.Reason','alarm.alertTriggeredDuration')
                    ->where('customerId','=',$this->companyCode)
                    ->where('l.id','=',$request->location_id)
                    ->where('b.id','=',$request->branch_id)
                    ->where('f.id','=',$request->facility_id)
                    ->where('bl.id','=',$request->building_id)
                    ->where('fl.id','=',$request->floor_id)
                    ->WHERE('alarm.deviceId','=',$request->deviceId)
                    ->orderBy('alarm.id', 'DESC');
    }
else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !=""){
      $query = DB::table('customers as c')
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
            ->Join('alert_crons as alarm', function($join){
                $join->on('c.customerId', '=', 'alarm.companyCode')
                      ->on('d.id', '=', 'alarm.deviceId');
            })
            ->select(DB::raw('DATE_FORMAT(alarm.a_date, "%d-%b-%Y") as date'),'alarm.a_time','d.deviceName','l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','alarm.sensorTag','alarm.alertStandardMessage','alarm.Reason','alarm.alertTriggeredDuration')
                    ->where('customerId','=',$this->companyCode)
                    ->where('l.id','=',$request->location_id)
                    ->where('b.id','=',$request->branch_id)
                    ->where('f.id','=',$request->facility_id)
                    ->where('bl.id','=',$request->building_id)
                    ->WHERE('alarm.deviceId','=',$request->deviceId)
                    ->orderBy('alarm.id', 'DESC');
}
   else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""){
         $query = DB::table('customers as c')
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
            ->Join('alert_crons as alarm', function($join){
                $join->on('c.customerId', '=', 'alarm.companyCode')
                      ->on('d.id', '=', 'alarm.deviceId');
            })
            ->select(DB::raw('DATE_FORMAT(alarm.a_date, "%d-%b-%Y") as date'),'alarm.a_time','d.deviceName','l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','alarm.sensorTag','alarm.alertStandardMessage','alarm.Reason','alarm.alertTriggeredDuration')
                    ->where('customerId','=',$this->companyCode)
                    ->where('l.id','=',$request->location_id)
                    ->where('b.id','=',$request->branch_id)
                    ->where('f.id','=',$request->facility_id)
                    ->WHERE('alarm.deviceId','=',$request->deviceId)
                    ->orderBy('alarm.id', 'DESC');
   }
   else if($request->location_id != "" && $request->branch_id != ""){
         $query = DB::table('customers as c')
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
            ->Join('alert_crons as alarm', function($join){
                $join->on('c.customerId', '=', 'alarm.companyCode')
                      ->on('d.id', '=', 'alarm.deviceId');
            })
            ->select(DB::raw('DATE_FORMAT(alarm.a_date, "%d-%b-%Y") as date'),'alarm.a_time','d.deviceName','l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','alarm.sensorTag','alarm.alertStandardMessage','alarm.Reason','alarm.alertTriggeredDuration')
                    ->where('customerId','=',$this->companyCode)
                    ->where('l.id','=',$request->location_id)
                    ->where('b.id','=',$request->branch_id)
                    ->WHERE('alarm.deviceId','=',$request->deviceId)
                    ->orderBy('alarm.id', 'DESC');
   }
    else if($request->location_id != ""){
         $query = DB::table('customers as c')
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
            ->Join('alert_crons as alarm', function($join){
                $join->on('c.customerId', '=', 'alarm.companyCode')
                      ->on('d.id', '=', 'alarm.deviceId');
            })
            ->select(DB::raw('DATE_FORMAT(alarm.a_date, "%d-%b-%Y") as date'),'alarm.a_time','d.deviceName','l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','alarm.sensorTag','alarm.alertStandardMessage','alarm.Reason','alarm.alertTriggeredDuration')
                    ->where('customerId','=',$this->companyCode)
                    ->where('l.id','=',$request->location_id)
                    ->WHERE('alarm.deviceId','=',$request->deviceId)
                    ->orderBy('alarm.id', 'DESC');
    }
      else{
            $query = DB::table('customers as c')
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
            ->Join('alert_crons as alarm', function($join){
                $join->on('c.customerId', '=', 'alarm.companyCode')
                      ->on('d.id', '=', 'alarm.deviceId');
            })
            ->select(DB::raw('DATE_FORMAT(alarm.a_date, "%d-%m-%Y") as date'),'alarm.a_time','d.deviceName','l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','alarm.sensorTag','alarm.alertStandardMessage','alarm.Reason','alarm.alertTriggeredDuration')
                    ->where('customerId','=',$this->companyCode)
                    ->WHERE('alarm.deviceId','=',$request->deviceId)
                    ->orderBy('alarm.id', 'DESC');
      }

    }
    else{

         if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !="" && $request->lab_id !=""){

            $query = DB::table('customers as c')
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
            ->Join('alert_crons as alarm', function($join){
                $join->on('c.customerId', '=', 'alarm.companyCode')
                      ->on('d.id', '=', 'alarm.deviceId');
            })
            ->select(DB::raw('DATE_FORMAT(alarm.a_date, "%d-%b-%Y") as date'),'alarm.a_time','d.deviceName','l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','alarm.sensorTag','alarm.alertStandardMessage','alarm.Reason','alarm.alertTriggeredDuration')
                    ->where('customerId','=',$this->companyCode)
                    ->where('l.id','=',$request->location_id)
                    ->where('b.id','=',$request->branch_id)
                    ->where('f.id','=',$request->facility_id)
                    ->where('bl.id','=',$request->building_id)
                    ->where('fl.id','=',$request->floor_id)
                    ->where('lb.id','=',$request->lab_id)
                    // ->WHERE('alarm.deviceId','=',$request->deviceId)
                    ->orderBy('alarm.id', 'DESC');

    }
else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !=""){

  $query = DB::table('customers as c')
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
            ->Join('alert_crons as alarm', function($join){
                $join->on('c.customerId', '=', 'alarm.companyCode')
                      ->on('d.id', '=', 'alarm.deviceId');
            })
            ->select(DB::raw('DATE_FORMAT(alarm.a_date, "%d-%b-%Y") as date'),'alarm.a_time','d.deviceName','l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','alarm.sensorTag','alarm.alertStandardMessage','alarm.Reason','alarm.alertTriggeredDuration')
                    ->where('customerId','=',$this->companyCode)
                    ->where('l.id','=',$request->location_id)
                    ->where('b.id','=',$request->branch_id)
                    ->where('f.id','=',$request->facility_id)
                    ->where('bl.id','=',$request->building_id)
                    ->where('fl.id','=',$request->floor_id)
                    // ->WHERE('alarm.deviceId','=',$request->deviceId)
                    ->orderBy('alarm.id', 'DESC');
    }
else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !=""){
      $query = DB::table('customers as c')
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
            ->Join('alert_crons as alarm', function($join){
                $join->on('c.customerId', '=', 'alarm.companyCode')
                      ->on('d.id', '=', 'alarm.deviceId');
            })
            ->select(DB::raw('DATE_FORMAT(alarm.a_date, "%d-%b-%Y") as date'),'alarm.a_time','d.deviceName','l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','alarm.sensorTag','alarm.alertStandardMessage','alarm.Reason','alarm.alertTriggeredDuration')
                    ->where('customerId','=',$this->companyCode)
                    ->where('l.id','=',$request->location_id)
                    ->where('b.id','=',$request->branch_id)
                    ->where('f.id','=',$request->facility_id)
                    ->where('bl.id','=',$request->building_id)
                    // ->WHERE('alarm.deviceId','=',$request->deviceId)
                    ->orderBy('alarm.id', 'DESC');
}
   else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""){
         $query = DB::table('customers as c')
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
            ->Join('alert_crons as alarm', function($join){
                $join->on('c.customerId', '=', 'alarm.companyCode')
                      ->on('d.id', '=', 'alarm.deviceId');
            })
            ->select(DB::raw('DATE_FORMAT(alarm.a_date, "%d-%b-%Y") as date'),'alarm.a_time','d.deviceName','l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','alarm.sensorTag','alarm.alertStandardMessage','alarm.Reason','alarm.alertTriggeredDuration')
                    ->where('customerId','=',$this->companyCode)
                    ->where('l.id','=',$request->location_id)
                    ->where('b.id','=',$request->branch_id)
                    ->where('f.id','=',$request->facility_id)
                    // ->WHERE('alarm.deviceId','=',$request->deviceId)
                    ->orderBy('alarm.id', 'DESC');
   }
   else if($request->location_id != "" && $request->branch_id != ""){
         $query = DB::table('customers as c')
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
            ->Join('alert_crons as alarm', function($join){
                $join->on('c.customerId', '=', 'alarm.companyCode')
                      ->on('d.id', '=', 'alarm.deviceId');
            })
            ->select(DB::raw('DATE_FORMAT(alarm.a_date, "%d-%b-%Y") as date'),'alarm.a_time','d.deviceName','l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','alarm.sensorTag','alarm.alertStandardMessage','alarm.Reason','alarm.alertTriggeredDuration')
                    ->where('customerId','=',$this->companyCode)
                    ->where('l.id','=',$request->location_id)
                    ->where('b.id','=',$request->branch_id)
                    // ->WHERE('alarm.deviceId','=',$request->deviceId)
                    ->orderBy('alarm.id', 'DESC');
   }
    else if($request->location_id != ""){
         $query = DB::table('customers as c')
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
            ->Join('alert_crons as alarm', function($join){
                $join->on('c.customerId', '=', 'alarm.companyCode')
                      ->on('d.id', '=', 'alarm.deviceId');
            })
            ->select(DB::raw('DATE_FORMAT(alarm.a_date, "%d-%b-%Y") as date'),'alarm.a_time','d.deviceName','l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','alarm.sensorTag','alarm.alertStandardMessage','alarm.Reason','alarm.alertTriggeredDuration')
                    ->where('customerId','=',$this->companyCode)
                    ->where('l.id','=',$request->location_id)
                    // ->WHERE('alarm.deviceId','=',$request->deviceId)
                    ->orderBy('alarm.id', 'DESC');
    }
      else{
            $query = DB::table('customers as c')
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
            ->Join('alert_crons as alarm', function($join){
                $join->on('c.customerId', '=', 'alarm.companyCode')
                      ->on('d.id', '=', 'alarm.deviceId');
            })
            ->select(DB::raw('DATE_FORMAT(alarm.a_date, "%d-%m-%Y") as date'),'alarm.a_time','d.deviceName','l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','alarm.sensorTag','alarm.alertStandardMessage','alarm.Reason','alarm.alertTriggeredDuration')
                    ->where('customerId','=',$this->companyCode)
                    // ->WHERE('alarm.deviceId','=',$request->deviceId)
                    ->orderBy('alarm.id', 'DESC');
      }


    }

                    if($startDate === $endDate){
                        $query->whereDate('alarm.a_date','=',$startDate);
                    }
                    else {
                        $query->whereBetween('alarm.a_date', [$startDate, $endDate]);
                    }


            $attachment =  Excel::raw(new ReportExport($query), BaseExcel::XLSX);

            $userEmail = $this->userId;
            $email = $this->fetchVerifiedEmailUsers($userEmail);
            // $email = 'vaishakkpoojary@gmail.com';
            $url = env('APPLICATION_URL');

            $data = [
                'userid'=>$email,
                'body' =>"Alarm Data",
                'url' => $url
            ];

            Mail::send('alarmReport',$data, function($messages) use ($email,$attachment){
                $messages->to($email);
                $messages->subject('Alarm Reports');
                $messages->attachData($attachment, 'ReportAlarm.xlsx',[
                     ]);
            });

               $response = [
                    "message"=>"Reports data sent Successfully"
                    // "email"=>$email
                ];
                $status = 200;
             return response($response, $status);

     }

/*

public function SensorLog(Request $request) {
        $startDate = date("Y-m-d",strtotime($request->fromDate));
        $endDate = date("Y-m-d", strtotime($request->toDate));

        $query = DB::table('customers as c')
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
            $join->on('c.customerId', '=', 's.companyCode')
                ->on('l.id', '=', 's.location_id')
                ->on('b.id', '=', 's.branch_id')
                ->on('f.id','=','s.facility_id')
                ->on('bl.id','=','s.building_id')
                ->on('fl.id','=','s.floor_id')
                ->on('lb.id','=','s.lab_id')
                ->on('d.id','=','s.deviceId');
        })
        ->Join('sensor_limit_change_logs as slc', function($join){
            $join->on('c.customerId', '=', 'slc.companyCode')
                ->on('s.id', '=', 'slc.sensor_id');
        })

        ->select('slc.companyCode','slc.created_at','slc.id', 'slc.email','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorTag','slc.criticalMinValue','slc.criticalMaxValue','slc.warningMinValue','slc.warningMaxValue','slc.outofrangeMinValue','slc.outofrangeMaxValue')
        ->WHERE('customerId','=',$this->companyCode)
        ->WHERE('device_id','=',$request->deviceId)
        ->orderBy('id', 'DESC');

        if($startDate === $endDate){
            $query->whereDate('slc.created_at','=',$startDate);
        }
        else {
             $query->whereDate('slc.created_at', '>=', $startDate)
                    ->whereDate('slc.created_at', '<=', $endDate);
        }

        $getData = new ReportsDataUtilityController($request,$query);

        $response = [
                        "data"=>$getData->getData()
                     ];
        $status = 200;
        return response($response,$status);
}

  */

public function SensorLog(Request $request) {
        $startDate = date("Y-m-d",strtotime($request->fromDate));
        $endDate = date("Y-m-d", strtotime($request->toDate));

    if( $request->deviceId !=""){

        if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !="" && $request->lab_id !=""){


        $query = DB::table('customers as c')
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
            $join->on('c.customerId', '=', 's.companyCode')
                ->on('l.id', '=', 's.location_id')
                ->on('b.id', '=', 's.branch_id')
                ->on('f.id','=','s.facility_id')
                ->on('bl.id','=','s.building_id')
                ->on('fl.id','=','s.floor_id')
                ->on('lb.id','=','s.lab_id')
                ->on('d.id','=','s.deviceId');
        })
        ->Join('sensor_limit_change_logs as slc', function($join){
            $join->on('c.customerId', '=', 'slc.companyCode')
                ->on('s.id', '=', 'slc.sensor_id');
        })

        ->select('slc.companyCode','slc.created_at','slc.id', 'slc.email','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorTag','slc.criticalMinValue','slc.criticalMaxValue','slc.warningMinValue','slc.warningMaxValue','slc.outofrangeMinValue','slc.outofrangeMaxValue')
        ->WHERE('customerId','=',$this->companyCode)
                    ->where('l.id','=',$request->location_id)
                    ->where('b.id','=',$request->branch_id)
                    ->where('f.id','=',$request->facility_id)
                    ->where('bl.id','=',$request->building_id)
                    ->where('fl.id','=',$request->floor_id)
                    ->where('lb.id','=',$request->lab_id)
        ->WHERE('device_id','=',$request->deviceId)
        ->orderBy('id', 'DESC');
}
    else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !=""){
     $query = DB::table('customers as c')
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
            $join->on('c.customerId', '=', 's.companyCode')
                ->on('l.id', '=', 's.location_id')
                ->on('b.id', '=', 's.branch_id')
                ->on('f.id','=','s.facility_id')
                ->on('bl.id','=','s.building_id')
                ->on('fl.id','=','s.floor_id')
                ->on('lb.id','=','s.lab_id')
                ->on('d.id','=','s.deviceId');
        })
        ->Join('sensor_limit_change_logs as slc', function($join){
            $join->on('c.customerId', '=', 'slc.companyCode')
                ->on('s.id', '=', 'slc.sensor_id');
        })

        ->select('slc.companyCode','slc.created_at','slc.id', 'slc.email','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorTag','slc.criticalMinValue','slc.criticalMaxValue','slc.warningMinValue','slc.warningMaxValue','slc.outofrangeMinValue','slc.outofrangeMaxValue')
        ->WHERE('customerId','=',$this->companyCode)
                    ->where('l.id','=',$request->location_id)
                    ->where('b.id','=',$request->branch_id)
                    ->where('f.id','=',$request->facility_id)
                    ->where('bl.id','=',$request->building_id)
                    ->where('fl.id','=',$request->floor_id)
        ->WHERE('device_id','=',$request->deviceId)
        ->orderBy('id', 'DESC');
       }
 else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !=""){
    $query = DB::table('customers as c')
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
            $join->on('c.customerId', '=', 's.companyCode')
                ->on('l.id', '=', 's.location_id')
                ->on('b.id', '=', 's.branch_id')
                ->on('f.id','=','s.facility_id')
                ->on('bl.id','=','s.building_id')
                ->on('fl.id','=','s.floor_id')
                ->on('lb.id','=','s.lab_id')
                ->on('d.id','=','s.deviceId');
        })
        ->Join('sensor_limit_change_logs as slc', function($join){
            $join->on('c.customerId', '=', 'slc.companyCode')
                ->on('s.id', '=', 'slc.sensor_id');
        })

        ->select('slc.companyCode','slc.created_at','slc.id', 'slc.email','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorTag','slc.criticalMinValue','slc.criticalMaxValue','slc.warningMinValue','slc.warningMaxValue','slc.outofrangeMinValue','slc.outofrangeMaxValue')
        ->WHERE('customerId','=',$this->companyCode)
                    ->where('l.id','=',$request->location_id)
                    ->where('b.id','=',$request->branch_id)
                    ->where('f.id','=',$request->facility_id)
                    ->where('bl.id','=',$request->building_id)
        ->WHERE('device_id','=',$request->deviceId)
        ->orderBy('id', 'DESC');
       }
       else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""){
               $query = DB::table('customers as c')
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
            $join->on('c.customerId', '=', 's.companyCode')
                ->on('l.id', '=', 's.location_id')
                ->on('b.id', '=', 's.branch_id')
                ->on('f.id','=','s.facility_id')
                ->on('bl.id','=','s.building_id')
                ->on('fl.id','=','s.floor_id')
                ->on('lb.id','=','s.lab_id')
                ->on('d.id','=','s.deviceId');
        })
        ->Join('sensor_limit_change_logs as slc', function($join){
            $join->on('c.customerId', '=', 'slc.companyCode')
                ->on('s.id', '=', 'slc.sensor_id');
        })

        ->select('slc.companyCode','slc.created_at','slc.id', 'slc.email','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorTag','slc.criticalMinValue','slc.criticalMaxValue','slc.warningMinValue','slc.warningMaxValue','slc.outofrangeMinValue','slc.outofrangeMaxValue')
        ->WHERE('customerId','=',$this->companyCode)
                    ->where('l.id','=',$request->location_id)
                    ->where('b.id','=',$request->branch_id)
                    ->where('f.id','=',$request->facility_id)
        ->WHERE('device_id','=',$request->deviceId)
        ->orderBy('id', 'DESC');
       }
       else if($request->location_id != "" && $request->branch_id != ""){
                          $query = DB::table('customers as c')
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
            $join->on('c.customerId', '=', 's.companyCode')
                ->on('l.id', '=', 's.location_id')
                ->on('b.id', '=', 's.branch_id')
                ->on('f.id','=','s.facility_id')
                ->on('bl.id','=','s.building_id')
                ->on('fl.id','=','s.floor_id')
                ->on('lb.id','=','s.lab_id')
                ->on('d.id','=','s.deviceId');
        })
        ->Join('sensor_limit_change_logs as slc', function($join){
            $join->on('c.customerId', '=', 'slc.companyCode')
                ->on('s.id', '=', 'slc.sensor_id');
        })

        ->select('slc.companyCode','slc.created_at','slc.id', 'slc.email','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorTag','slc.criticalMinValue','slc.criticalMaxValue','slc.warningMinValue','slc.warningMaxValue','slc.outofrangeMinValue','slc.outofrangeMaxValue')
        ->WHERE('customerId','=',$this->companyCode)
                    ->where('l.id','=',$request->location_id)
                    ->where('b.id','=',$request->branch_id)
        ->WHERE('device_id','=',$request->deviceId)
        ->orderBy('id', 'DESC');
       }
        else if($request->location_id != ""){

               $query = DB::table('customers as c')
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
            $join->on('c.customerId', '=', 's.companyCode')
                ->on('l.id', '=', 's.location_id')
                ->on('b.id', '=', 's.branch_id')
                ->on('f.id','=','s.facility_id')
                ->on('bl.id','=','s.building_id')
                ->on('fl.id','=','s.floor_id')
                ->on('lb.id','=','s.lab_id')
                ->on('d.id','=','s.deviceId');
        })
        ->Join('sensor_limit_change_logs as slc', function($join){
            $join->on('c.customerId', '=', 'slc.companyCode')
                ->on('s.id', '=', 'slc.sensor_id');
        })

        ->select('slc.companyCode','slc.created_at','slc.id', 'slc.email','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorTag','slc.criticalMinValue','slc.criticalMaxValue','slc.warningMinValue','slc.warningMaxValue','slc.outofrangeMinValue','slc.outofrangeMaxValue')
        ->WHERE('customerId','=',$this->companyCode)
                    ->where('l.id','=',$request->location_id)
        ->WHERE('device_id','=',$request->deviceId)
        ->orderBy('id', 'DESC');

        }
        else{
                           $query = DB::table('customers as c')
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
            $join->on('c.customerId', '=', 's.companyCode')
                ->on('l.id', '=', 's.location_id')
                ->on('b.id', '=', 's.branch_id')
                ->on('f.id','=','s.facility_id')
                ->on('bl.id','=','s.building_id')
                ->on('fl.id','=','s.floor_id')
                ->on('lb.id','=','s.lab_id')
                ->on('d.id','=','s.deviceId');
        })
        ->Join('sensor_limit_change_logs as slc', function($join){
            $join->on('c.customerId', '=', 'slc.companyCode')
                ->on('s.id', '=', 'slc.sensor_id');
        })

        ->select('slc.companyCode','slc.created_at','slc.id', 'slc.email','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorTag','slc.criticalMinValue','slc.criticalMaxValue','slc.warningMinValue','slc.warningMaxValue','slc.outofrangeMinValue','slc.outofrangeMaxValue')
        ->WHERE('customerId','=',$this->companyCode)
        ->WHERE('device_id','=',$request->deviceId)
        ->orderBy('id', 'DESC');
        }
    }
    else{
        if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !="" && $request->lab_id !=""){
              $query = DB::table('customers as c')
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
            $join->on('c.customerId', '=', 's.companyCode')
                ->on('l.id', '=', 's.location_id')
                ->on('b.id', '=', 's.branch_id')
                ->on('f.id','=','s.facility_id')
                ->on('bl.id','=','s.building_id')
                ->on('fl.id','=','s.floor_id')
                ->on('lb.id','=','s.lab_id')
                ->on('d.id','=','s.deviceId');
        })
        ->Join('sensor_limit_change_logs as slc', function($join){
            $join->on('c.customerId', '=', 'slc.companyCode')
                ->on('s.id', '=', 'slc.sensor_id');
        })

        ->select('slc.companyCode','slc.created_at','slc.id', 'slc.email','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorTag','slc.criticalMinValue','slc.criticalMaxValue','slc.warningMinValue','slc.warningMaxValue','slc.outofrangeMinValue','slc.outofrangeMaxValue')
        ->WHERE('customerId','=',$this->companyCode)
                    ->where('l.id','=',$request->location_id)
                    ->where('b.id','=',$request->branch_id)
                    ->where('f.id','=',$request->facility_id)
                    ->where('bl.id','=',$request->building_id)
                    ->where('fl.id','=',$request->floor_id)
                    ->where('lb.id','=',$request->lab_id)
                   ->orderBy('id', 'DESC');
}
       else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !=""){
                         $query = DB::table('customers as c')
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
            $join->on('c.customerId', '=', 's.companyCode')
                ->on('l.id', '=', 's.location_id')
                ->on('b.id', '=', 's.branch_id')
                ->on('f.id','=','s.facility_id')
                ->on('bl.id','=','s.building_id')
                ->on('fl.id','=','s.floor_id')
                ->on('lb.id','=','s.lab_id')
                ->on('d.id','=','s.deviceId');
        })
        ->Join('sensor_limit_change_logs as slc', function($join){
            $join->on('c.customerId', '=', 'slc.companyCode')
                ->on('s.id', '=', 'slc.sensor_id');
        })

        ->select('slc.companyCode','slc.created_at','slc.id', 'slc.email','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorTag','slc.criticalMinValue','slc.criticalMaxValue','slc.warningMinValue','slc.warningMaxValue','slc.outofrangeMinValue','slc.outofrangeMaxValue')
        ->WHERE('customerId','=',$this->companyCode)
                    ->where('l.id','=',$request->location_id)
                    ->where('b.id','=',$request->branch_id)
                    ->where('f.id','=',$request->facility_id)
                    ->where('bl.id','=',$request->building_id)
                    ->where('fl.id','=',$request->floor_id)
                   ->orderBy('id', 'DESC');
       }
       else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !=""){
        $query = DB::table('customers as c')
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
            $join->on('c.customerId', '=', 's.companyCode')
                ->on('l.id', '=', 's.location_id')
                ->on('b.id', '=', 's.branch_id')
                ->on('f.id','=','s.facility_id')
                ->on('bl.id','=','s.building_id')
                ->on('fl.id','=','s.floor_id')
                ->on('lb.id','=','s.lab_id')
                ->on('d.id','=','s.deviceId');
        })
        ->Join('sensor_limit_change_logs as slc', function($join){
            $join->on('c.customerId', '=', 'slc.companyCode')
                ->on('s.id', '=', 'slc.sensor_id');
        })

        ->select('slc.companyCode','slc.created_at','slc.id', 'slc.email','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorTag','slc.criticalMinValue','slc.criticalMaxValue','slc.warningMinValue','slc.warningMaxValue','slc.outofrangeMinValue','slc.outofrangeMaxValue')
        ->WHERE('customerId','=',$this->companyCode)
                    ->where('l.id','=',$request->location_id)
                    ->where('b.id','=',$request->branch_id)
                    ->where('f.id','=',$request->facility_id)
                    ->where('bl.id','=',$request->building_id)
                   ->orderBy('id', 'DESC');
       }
       else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""){
                   $query = DB::table('customers as c')
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
            $join->on('c.customerId', '=', 's.companyCode')
                ->on('l.id', '=', 's.location_id')
                ->on('b.id', '=', 's.branch_id')
                ->on('f.id','=','s.facility_id')
                ->on('bl.id','=','s.building_id')
                ->on('fl.id','=','s.floor_id')
                ->on('lb.id','=','s.lab_id')
                ->on('d.id','=','s.deviceId');
        })
        ->Join('sensor_limit_change_logs as slc', function($join){
            $join->on('c.customerId', '=', 'slc.companyCode')
                ->on('s.id', '=', 'slc.sensor_id');
        })

        ->select('slc.companyCode','slc.created_at','slc.id', 'slc.email','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorTag','slc.criticalMinValue','slc.criticalMaxValue','slc.warningMinValue','slc.warningMaxValue','slc.outofrangeMinValue','slc.outofrangeMaxValue')
        ->WHERE('customerId','=',$this->companyCode)
                    ->where('l.id','=',$request->location_id)
                    ->where('b.id','=',$request->branch_id)
                    ->where('f.id','=',$request->facility_id)
                   ->orderBy('id', 'DESC');
       }
       else if($request->location_id != "" && $request->branch_id != ""){
             $query = DB::table('customers as c')
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
            $join->on('c.customerId', '=', 's.companyCode')
                ->on('l.id', '=', 's.location_id')
                ->on('b.id', '=', 's.branch_id')
                ->on('f.id','=','s.facility_id')
                ->on('bl.id','=','s.building_id')
                ->on('fl.id','=','s.floor_id')
                ->on('lb.id','=','s.lab_id')
                ->on('d.id','=','s.deviceId');
        })
        ->Join('sensor_limit_change_logs as slc', function($join){
            $join->on('c.customerId', '=', 'slc.companyCode')
                ->on('s.id', '=', 'slc.sensor_id');
        })

        ->select('slc.companyCode','slc.created_at','slc.id', 'slc.email','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorTag','slc.criticalMinValue','slc.criticalMaxValue','slc.warningMinValue','slc.warningMaxValue','slc.outofrangeMinValue','slc.outofrangeMaxValue')
        ->WHERE('customerId','=',$this->companyCode)
                    ->where('l.id','=',$request->location_id)
                    ->where('b.id','=',$request->branch_id)
                   ->orderBy('id', 'DESC');
       }
        else if($request->location_id != ""){
                         $query = DB::table('customers as c')
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
            $join->on('c.customerId', '=', 's.companyCode')
                ->on('l.id', '=', 's.location_id')
                ->on('b.id', '=', 's.branch_id')
                ->on('f.id','=','s.facility_id')
                ->on('bl.id','=','s.building_id')
                ->on('fl.id','=','s.floor_id')
                ->on('lb.id','=','s.lab_id')
                ->on('d.id','=','s.deviceId');
        })
        ->Join('sensor_limit_change_logs as slc', function($join){
            $join->on('c.customerId', '=', 'slc.companyCode')
                ->on('s.id', '=', 'slc.sensor_id');
        })

        ->select('slc.companyCode','slc.created_at','slc.id', 'slc.email','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorTag','slc.criticalMinValue','slc.criticalMaxValue','slc.warningMinValue','slc.warningMaxValue','slc.outofrangeMinValue','slc.outofrangeMaxValue')
        ->WHERE('customerId','=',$this->companyCode)
                    ->where('l.id','=',$request->location_id)
                   ->orderBy('id', 'DESC');
        }
        else{
         $query = DB::table('customers as c')
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
            $join->on('c.customerId', '=', 's.companyCode')
                ->on('l.id', '=', 's.location_id')
                ->on('b.id', '=', 's.branch_id')
                ->on('f.id','=','s.facility_id')
                ->on('bl.id','=','s.building_id')
                ->on('fl.id','=','s.floor_id')
                ->on('lb.id','=','s.lab_id')
                ->on('d.id','=','s.deviceId');
        })
        ->Join('sensor_limit_change_logs as slc', function($join){
            $join->on('c.customerId', '=', 'slc.companyCode')
                ->on('s.id', '=', 'slc.sensor_id');
        })

        ->select('slc.companyCode','slc.created_at','slc.id', 'slc.email','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorTag','slc.criticalMinValue','slc.criticalMaxValue','slc.warningMinValue','slc.warningMaxValue','slc.outofrangeMinValue','slc.outofrangeMaxValue')
        ->WHERE('customerId','=',$this->companyCode)
                   ->orderBy('id', 'DESC');
        }

    }


        if($startDate === $endDate){
            $query->whereDate('slc.created_at','=',$startDate);
        }
        else {
             $query->whereDate('slc.created_at', '>=', $startDate)
                    ->whereDate('slc.created_at', '<=', $endDate);
        }

        $getData = new ReportsDataUtilityController($request,$query);

        $response = [
                        "data"=>$getData->getData()
                     ];
        $status = 200;
        return response($response,$status);
}


  public function exportSensorLog(Request $request) {

        $startDate = date("Y-m-d",strtotime($request->fromDate));
        $endDate = date("Y-m-d", strtotime($request->toDate));

          if( $request->deviceId !=""){

        if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !="" && $request->lab_id !=""){


        $query = DB::table('customers as c')
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
            $join->on('c.customerId', '=', 's.companyCode')
                ->on('l.id', '=', 's.location_id')
                ->on('b.id', '=', 's.branch_id')
                ->on('f.id','=','s.facility_id')
                ->on('bl.id','=','s.building_id')
                ->on('fl.id','=','s.floor_id')
                ->on('lb.id','=','s.lab_id')
                ->on('d.id','=','s.deviceId');
        })
        ->Join('sensor_limit_change_logs as slc', function($join){
            $join->on('c.customerId', '=', 'slc.companyCode')
                ->on('s.id', '=', 'slc.sensor_id');
        })

->select(DB::raw('DATE_FORMAT(slc.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(slc.created_at) as time'),'slc.email','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorTag','slc.criticalMinValue','slc.criticalMaxValue','slc.warningMinValue','slc.warningMaxValue','slc.outofrangeMinValue','slc.outofrangeMaxValue')        ->WHERE('customerId','=',$this->companyCode)
                    ->where('l.id','=',$request->location_id)
                    ->where('b.id','=',$request->branch_id)
                    ->where('f.id','=',$request->facility_id)
                    ->where('bl.id','=',$request->building_id)
                    ->where('fl.id','=',$request->floor_id)
                    ->where('lb.id','=',$request->lab_id)
        ->WHERE('device_id','=',$request->deviceId)
        ->orderBy('slc.id', 'DESC');
}
    else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !=""){
     $query = DB::table('customers as c')
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
            $join->on('c.customerId', '=', 's.companyCode')
                ->on('l.id', '=', 's.location_id')
                ->on('b.id', '=', 's.branch_id')
                ->on('f.id','=','s.facility_id')
                ->on('bl.id','=','s.building_id')
                ->on('fl.id','=','s.floor_id')
                ->on('lb.id','=','s.lab_id')
                ->on('d.id','=','s.deviceId');
        })
        ->Join('sensor_limit_change_logs as slc', function($join){
            $join->on('c.customerId', '=', 'slc.companyCode')
                ->on('s.id', '=', 'slc.sensor_id');
        })

->select(DB::raw('DATE_FORMAT(slc.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(slc.created_at) as time'),'slc.email','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorTag','slc.criticalMinValue','slc.criticalMaxValue','slc.warningMinValue','slc.warningMaxValue','slc.outofrangeMinValue','slc.outofrangeMaxValue')        ->WHERE('customerId','=',$this->companyCode)
                    ->where('l.id','=',$request->location_id)
                    ->where('b.id','=',$request->branch_id)
                    ->where('f.id','=',$request->facility_id)
                    ->where('bl.id','=',$request->building_id)
                    ->where('fl.id','=',$request->floor_id)
        ->WHERE('device_id','=',$request->deviceId)
        ->orderBy('slc.id', 'DESC');
       }
 else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !=""){
    $query = DB::table('customers as c')
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
            $join->on('c.customerId', '=', 's.companyCode')
                ->on('l.id', '=', 's.location_id')
                ->on('b.id', '=', 's.branch_id')
                ->on('f.id','=','s.facility_id')
                ->on('bl.id','=','s.building_id')
                ->on('fl.id','=','s.floor_id')
                ->on('lb.id','=','s.lab_id')
                ->on('d.id','=','s.deviceId');
        })
        ->Join('sensor_limit_change_logs as slc', function($join){
            $join->on('c.customerId', '=', 'slc.companyCode')
                ->on('s.id', '=', 'slc.sensor_id');
        })

->select(DB::raw('DATE_FORMAT(slc.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(slc.created_at) as time'),'slc.email','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorTag','slc.criticalMinValue','slc.criticalMaxValue','slc.warningMinValue','slc.warningMaxValue','slc.outofrangeMinValue','slc.outofrangeMaxValue')        ->WHERE('customerId','=',$this->companyCode)
                    ->where('l.id','=',$request->location_id)
                    ->where('b.id','=',$request->branch_id)
                    ->where('f.id','=',$request->facility_id)
                    ->where('bl.id','=',$request->building_id)
        ->WHERE('device_id','=',$request->deviceId)
        ->orderBy('slc.id', 'DESC');
       }
       else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""){
               $query = DB::table('customers as c')
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
            $join->on('c.customerId', '=', 's.companyCode')
                ->on('l.id', '=', 's.location_id')
                ->on('b.id', '=', 's.branch_id')
                ->on('f.id','=','s.facility_id')
                ->on('bl.id','=','s.building_id')
                ->on('fl.id','=','s.floor_id')
                ->on('lb.id','=','s.lab_id')
                ->on('d.id','=','s.deviceId');
        })
        ->Join('sensor_limit_change_logs as slc', function($join){
            $join->on('c.customerId', '=', 'slc.companyCode')
                ->on('s.id', '=', 'slc.sensor_id');
        })

->select(DB::raw('DATE_FORMAT(slc.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(slc.created_at) as time'),'slc.email','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorTag','slc.criticalMinValue','slc.criticalMaxValue','slc.warningMinValue','slc.warningMaxValue','slc.outofrangeMinValue','slc.outofrangeMaxValue')        ->WHERE('customerId','=',$this->companyCode)
                    ->where('l.id','=',$request->location_id)
                    ->where('b.id','=',$request->branch_id)
                    ->where('f.id','=',$request->facility_id)
        ->WHERE('device_id','=',$request->deviceId)
         ->orderBy('slc.id', 'DESC');
       }
       else if($request->location_id != "" && $request->branch_id != ""){
                          $query = DB::table('customers as c')
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
            $join->on('c.customerId', '=', 's.companyCode')
                ->on('l.id', '=', 's.location_id')
                ->on('b.id', '=', 's.branch_id')
                ->on('f.id','=','s.facility_id')
                ->on('bl.id','=','s.building_id')
                ->on('fl.id','=','s.floor_id')
                ->on('lb.id','=','s.lab_id')
                ->on('d.id','=','s.deviceId');
        })
        ->Join('sensor_limit_change_logs as slc', function($join){
            $join->on('c.customerId', '=', 'slc.companyCode')
                ->on('s.id', '=', 'slc.sensor_id');
        })

->select(DB::raw('DATE_FORMAT(slc.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(slc.created_at) as time'),'slc.email','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorTag','slc.criticalMinValue','slc.criticalMaxValue','slc.warningMinValue','slc.warningMaxValue','slc.outofrangeMinValue','slc.outofrangeMaxValue')        ->WHERE('customerId','=',$this->companyCode)
                    ->where('l.id','=',$request->location_id)
                    ->where('b.id','=',$request->branch_id)
        ->WHERE('device_id','=',$request->deviceId)
        ->orderBy('slc.id', 'DESC');
       }
        else if($request->location_id != ""){

               $query = DB::table('customers as c')
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
            $join->on('c.customerId', '=', 's.companyCode')
                ->on('l.id', '=', 's.location_id')
                ->on('b.id', '=', 's.branch_id')
                ->on('f.id','=','s.facility_id')
                ->on('bl.id','=','s.building_id')
                ->on('fl.id','=','s.floor_id')
                ->on('lb.id','=','s.lab_id')
                ->on('d.id','=','s.deviceId');
        })
        ->Join('sensor_limit_change_logs as slc', function($join){
            $join->on('c.customerId', '=', 'slc.companyCode')
                ->on('s.id', '=', 'slc.sensor_id');
        })

->select(DB::raw('DATE_FORMAT(slc.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(slc.created_at) as time'),'slc.email','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorTag','slc.criticalMinValue','slc.criticalMaxValue','slc.warningMinValue','slc.warningMaxValue','slc.outofrangeMinValue','slc.outofrangeMaxValue')        ->WHERE('customerId','=',$this->companyCode)
                    ->where('l.id','=',$request->location_id)
        ->WHERE('device_id','=',$request->deviceId)
       ->orderBy('slc.id', 'DESC');

        }
        else{
                           $query = DB::table('customers as c')
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
            $join->on('c.customerId', '=', 's.companyCode')
                ->on('l.id', '=', 's.location_id')
                ->on('b.id', '=', 's.branch_id')
                ->on('f.id','=','s.facility_id')
                ->on('bl.id','=','s.building_id')
                ->on('fl.id','=','s.floor_id')
                ->on('lb.id','=','s.lab_id')
                ->on('d.id','=','s.deviceId');
        })
        ->Join('sensor_limit_change_logs as slc', function($join){
            $join->on('c.customerId', '=', 'slc.companyCode')
                ->on('s.id', '=', 'slc.sensor_id');
        })

->select(DB::raw('DATE_FORMAT(slc.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(slc.created_at) as time'),'slc.email','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorTag','slc.criticalMinValue','slc.criticalMaxValue','slc.warningMinValue','slc.warningMaxValue','slc.outofrangeMinValue','slc.outofrangeMaxValue')        ->WHERE('customerId','=',$this->companyCode)
        ->WHERE('device_id','=',$request->deviceId)
       ->orderBy('slc.id', 'DESC');
        }
    }
    else{
        if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !="" && $request->lab_id !=""){
              $query = DB::table('customers as c')
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
            $join->on('c.customerId', '=', 's.companyCode')
                ->on('l.id', '=', 's.location_id')
                ->on('b.id', '=', 's.branch_id')
                ->on('f.id','=','s.facility_id')
                ->on('bl.id','=','s.building_id')
                ->on('fl.id','=','s.floor_id')
                ->on('lb.id','=','s.lab_id')
                ->on('d.id','=','s.deviceId');
        })
        ->Join('sensor_limit_change_logs as slc', function($join){
            $join->on('c.customerId', '=', 'slc.companyCode')
                ->on('s.id', '=', 'slc.sensor_id');
        })

->select(DB::raw('DATE_FORMAT(slc.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(slc.created_at) as time'),'slc.email','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorTag','slc.criticalMinValue','slc.criticalMaxValue','slc.warningMinValue','slc.warningMaxValue','slc.outofrangeMinValue','slc.outofrangeMaxValue')
->WHERE('customerId','=',$this->companyCode)
                    ->where('l.id','=',$request->location_id)
                    ->where('b.id','=',$request->branch_id)
                    ->where('f.id','=',$request->facility_id)
                    ->where('bl.id','=',$request->building_id)
                    ->where('fl.id','=',$request->floor_id)
                    ->where('lb.id','=',$request->lab_id)
                   ->orderBy('slc.id', 'DESC');
}
       else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !=""){
                         $query = DB::table('customers as c')
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
            $join->on('c.customerId', '=', 's.companyCode')
                ->on('l.id', '=', 's.location_id')
                ->on('b.id', '=', 's.branch_id')
                ->on('f.id','=','s.facility_id')
                ->on('bl.id','=','s.building_id')
                ->on('fl.id','=','s.floor_id')
                ->on('lb.id','=','s.lab_id')
                ->on('d.id','=','s.deviceId');
        })
        ->Join('sensor_limit_change_logs as slc', function($join){
            $join->on('c.customerId', '=', 'slc.companyCode')
                ->on('s.id', '=', 'slc.sensor_id');
        })

->select(DB::raw('DATE_FORMAT(slc.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(slc.created_at) as time'),'slc.email','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorTag','slc.criticalMinValue','slc.criticalMaxValue','slc.warningMinValue','slc.warningMaxValue','slc.outofrangeMinValue','slc.outofrangeMaxValue')        ->WHERE('customerId','=',$this->companyCode)
                    ->where('l.id','=',$request->location_id)
                    ->where('b.id','=',$request->branch_id)
                    ->where('f.id','=',$request->facility_id)
                    ->where('bl.id','=',$request->building_id)
                    ->where('fl.id','=',$request->floor_id)
                  ->orderBy('slc.id', 'DESC');
       }
       else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !=""){
        $query = DB::table('customers as c')
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
            $join->on('c.customerId', '=', 's.companyCode')
                ->on('l.id', '=', 's.location_id')
                ->on('b.id', '=', 's.branch_id')
                ->on('f.id','=','s.facility_id')
                ->on('bl.id','=','s.building_id')
                ->on('fl.id','=','s.floor_id')
                ->on('lb.id','=','s.lab_id')
                ->on('d.id','=','s.deviceId');
        })
        ->Join('sensor_limit_change_logs as slc', function($join){
            $join->on('c.customerId', '=', 'slc.companyCode')
                ->on('s.id', '=', 'slc.sensor_id');
        })

->select(DB::raw('DATE_FORMAT(slc.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(slc.created_at) as time'),'slc.email','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorTag','slc.criticalMinValue','slc.criticalMaxValue','slc.warningMinValue','slc.warningMaxValue','slc.outofrangeMinValue','slc.outofrangeMaxValue')        ->WHERE('customerId','=',$this->companyCode)
                    ->where('l.id','=',$request->location_id)
                    ->where('b.id','=',$request->branch_id)
                    ->where('f.id','=',$request->facility_id)
                    ->where('bl.id','=',$request->building_id)
                 ->orderBy('slc.id', 'DESC');
       }
       else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""){
                   $query = DB::table('customers as c')
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
            $join->on('c.customerId', '=', 's.companyCode')
                ->on('l.id', '=', 's.location_id')
                ->on('b.id', '=', 's.branch_id')
                ->on('f.id','=','s.facility_id')
                ->on('bl.id','=','s.building_id')
                ->on('fl.id','=','s.floor_id')
                ->on('lb.id','=','s.lab_id')
                ->on('d.id','=','s.deviceId');
        })
        ->Join('sensor_limit_change_logs as slc', function($join){
            $join->on('c.customerId', '=', 'slc.companyCode')
                ->on('s.id', '=', 'slc.sensor_id');
        })

->select(DB::raw('DATE_FORMAT(slc.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(slc.created_at) as time'),'slc.email','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorTag','slc.criticalMinValue','slc.criticalMaxValue','slc.warningMinValue','slc.warningMaxValue','slc.outofrangeMinValue','slc.outofrangeMaxValue')        ->WHERE('customerId','=',$this->companyCode)
                    ->where('l.id','=',$request->location_id)
                    ->where('b.id','=',$request->branch_id)
                    ->where('f.id','=',$request->facility_id)
                   ->orderBy('slc.id', 'DESC');
       }
       else if($request->location_id != "" && $request->branch_id != ""){
             $query = DB::table('customers as c')
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
            $join->on('c.customerId', '=', 's.companyCode')
                ->on('l.id', '=', 's.location_id')
                ->on('b.id', '=', 's.branch_id')
                ->on('f.id','=','s.facility_id')
                ->on('bl.id','=','s.building_id')
                ->on('fl.id','=','s.floor_id')
                ->on('lb.id','=','s.lab_id')
                ->on('d.id','=','s.deviceId');
        })
        ->Join('sensor_limit_change_logs as slc', function($join){
            $join->on('c.customerId', '=', 'slc.companyCode')
                ->on('s.id', '=', 'slc.sensor_id');
        })

->select(DB::raw('DATE_FORMAT(slc.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(slc.created_at) as time'),'slc.email','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorTag','slc.criticalMinValue','slc.criticalMaxValue','slc.warningMinValue','slc.warningMaxValue','slc.outofrangeMinValue','slc.outofrangeMaxValue')        ->WHERE('customerId','=',$this->companyCode)
                    ->where('l.id','=',$request->location_id)
                    ->where('b.id','=',$request->branch_id)
                   ->orderBy('slc.id', 'DESC');
       }
        else if($request->location_id != ""){
                         $query = DB::table('customers as c')
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
            $join->on('c.customerId', '=', 's.companyCode')
                ->on('l.id', '=', 's.location_id')
                ->on('b.id', '=', 's.branch_id')
                ->on('f.id','=','s.facility_id')
                ->on('bl.id','=','s.building_id')
                ->on('fl.id','=','s.floor_id')
                ->on('lb.id','=','s.lab_id')
                ->on('d.id','=','s.deviceId');
        })
        ->Join('sensor_limit_change_logs as slc', function($join){
            $join->on('c.customerId', '=', 'slc.companyCode')
                ->on('s.id', '=', 'slc.sensor_id');
        })

->select(DB::raw('DATE_FORMAT(slc.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(slc.created_at) as time'),'slc.email','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorTag','slc.criticalMinValue','slc.criticalMaxValue','slc.warningMinValue','slc.warningMaxValue','slc.outofrangeMinValue','slc.outofrangeMaxValue')
                   ->WHERE('customerId','=',$this->companyCode)
                    ->where('l.id','=',$request->location_id)
                   ->orderBy('slc.id', 'DESC');
        }
        else{
         $query = DB::table('customers as c')
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
            $join->on('c.customerId', '=', 's.companyCode')
                ->on('l.id', '=', 's.location_id')
                ->on('b.id', '=', 's.branch_id')
                ->on('f.id','=','s.facility_id')
                ->on('bl.id','=','s.building_id')
                ->on('fl.id','=','s.floor_id')
                ->on('lb.id','=','s.lab_id')
                ->on('d.id','=','s.deviceId');
        })
        ->Join('sensor_limit_change_logs as slc', function($join){
            $join->on('c.customerId', '=', 'slc.companyCode')
                ->on('s.id', '=', 'slc.sensor_id');
        })

->select(DB::raw('DATE_FORMAT(slc.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(slc.created_at) as time'),'slc.email','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorTag','slc.criticalMinValue','slc.criticalMaxValue','slc.warningMinValue','slc.warningMaxValue','slc.outofrangeMinValue','slc.outofrangeMaxValue')
        ->WHERE('customerId','=',$this->companyCode)
                  ->orderBy('slc.id', 'DESC');
        }

    }




        if($startDate === $endDate){
            $query->whereDate('slc.created_at','=',$startDate);
        }
        else {
            $query->whereBetween('slc.created_at', [$startDate, $endDate]);
        }

        return Excel::download(new LimitEditLogsExport($query), 'Limiteditlogs.xlsx');
}


public function emailDeviceLog(Request $request)
    {

        $startDate = date("Y-m-d",strtotime($request->fromDate));
        $endDate = date("Y-m-d", strtotime($request->toDate));

           if( $request->deviceId !=""){
        if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !="" && $request->lab_id !=""){


        $query = DB::table('customers as c')
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
            $join->on('c.customerId', '=', 's.companyCode')
                ->on('l.id', '=', 's.location_id')
                ->on('b.id', '=', 's.branch_id')
                ->on('f.id','=','s.facility_id')
                ->on('bl.id','=','s.building_id')
                ->on('fl.id','=','s.floor_id')
                ->on('lb.id','=','s.lab_id')
                ->on('d.id','=','s.deviceId');
        })
        ->Join('sensor_limit_change_logs as slc', function($join){
            $join->on('c.customerId', '=', 'slc.companyCode')
                ->on('s.id', '=', 'slc.sensor_id');
        })

->select(DB::raw('DATE_FORMAT(slc.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(slc.created_at) as time'),'slc.email','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorTag','slc.criticalMinValue','slc.criticalMaxValue','slc.warningMinValue','slc.warningMaxValue','slc.outofrangeMinValue','slc.outofrangeMaxValue')        ->WHERE('customerId','=',$this->companyCode)
                    ->where('l.id','=',$request->location_id)
                    ->where('b.id','=',$request->branch_id)
                    ->where('f.id','=',$request->facility_id)
                    ->where('bl.id','=',$request->building_id)
                    ->where('fl.id','=',$request->floor_id)
                    ->where('lb.id','=',$request->lab_id)
        ->WHERE('device_id','=',$request->deviceId)
        ->orderBy('slc.id', 'DESC');
}
    else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !=""){
     $query = DB::table('customers as c')
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
            $join->on('c.customerId', '=', 's.companyCode')
                ->on('l.id', '=', 's.location_id')
                ->on('b.id', '=', 's.branch_id')
                ->on('f.id','=','s.facility_id')
                ->on('bl.id','=','s.building_id')
                ->on('fl.id','=','s.floor_id')
                ->on('lb.id','=','s.lab_id')
                ->on('d.id','=','s.deviceId');
        })
        ->Join('sensor_limit_change_logs as slc', function($join){
            $join->on('c.customerId', '=', 'slc.companyCode')
                ->on('s.id', '=', 'slc.sensor_id');
        })

->select(DB::raw('DATE_FORMAT(slc.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(slc.created_at) as time'),'slc.email','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorTag','slc.criticalMinValue','slc.criticalMaxValue','slc.warningMinValue','slc.warningMaxValue','slc.outofrangeMinValue','slc.outofrangeMaxValue')        ->WHERE('customerId','=',$this->companyCode)
                    ->where('l.id','=',$request->location_id)
                    ->where('b.id','=',$request->branch_id)
                    ->where('f.id','=',$request->facility_id)
                    ->where('bl.id','=',$request->building_id)
                    ->where('fl.id','=',$request->floor_id)
        ->WHERE('device_id','=',$request->deviceId)
        ->orderBy('slc.id', 'DESC');
       }
 else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !=""){
    $query = DB::table('customers as c')
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
            $join->on('c.customerId', '=', 's.companyCode')
                ->on('l.id', '=', 's.location_id')
                ->on('b.id', '=', 's.branch_id')
                ->on('f.id','=','s.facility_id')
                ->on('bl.id','=','s.building_id')
                ->on('fl.id','=','s.floor_id')
                ->on('lb.id','=','s.lab_id')
                ->on('d.id','=','s.deviceId');
        })
        ->Join('sensor_limit_change_logs as slc', function($join){
            $join->on('c.customerId', '=', 'slc.companyCode')
                ->on('s.id', '=', 'slc.sensor_id');
        })

->select(DB::raw('DATE_FORMAT(slc.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(slc.created_at) as time'),'slc.email','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorTag','slc.criticalMinValue','slc.criticalMaxValue','slc.warningMinValue','slc.warningMaxValue','slc.outofrangeMinValue','slc.outofrangeMaxValue')        ->WHERE('customerId','=',$this->companyCode)
                    ->where('l.id','=',$request->location_id)
                    ->where('b.id','=',$request->branch_id)
                    ->where('f.id','=',$request->facility_id)
                    ->where('bl.id','=',$request->building_id)
        ->WHERE('device_id','=',$request->deviceId)
        ->orderBy('slc.id', 'DESC');
       }
       else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""){
               $query = DB::table('customers as c')
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
            $join->on('c.customerId', '=', 's.companyCode')
                ->on('l.id', '=', 's.location_id')
                ->on('b.id', '=', 's.branch_id')
                ->on('f.id','=','s.facility_id')
                ->on('bl.id','=','s.building_id')
                ->on('fl.id','=','s.floor_id')
                ->on('lb.id','=','s.lab_id')
                ->on('d.id','=','s.deviceId');
        })
        ->Join('sensor_limit_change_logs as slc', function($join){
            $join->on('c.customerId', '=', 'slc.companyCode')
                ->on('s.id', '=', 'slc.sensor_id');
        })

->select(DB::raw('DATE_FORMAT(slc.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(slc.created_at) as time'),'slc.email','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorTag','slc.criticalMinValue','slc.criticalMaxValue','slc.warningMinValue','slc.warningMaxValue','slc.outofrangeMinValue','slc.outofrangeMaxValue')        ->WHERE('customerId','=',$this->companyCode)
                    ->where('l.id','=',$request->location_id)
                    ->where('b.id','=',$request->branch_id)
                    ->where('f.id','=',$request->facility_id)
        ->WHERE('device_id','=',$request->deviceId)
         ->orderBy('slc.id', 'DESC');
       }
       else if($request->location_id != "" && $request->branch_id != ""){
                          $query = DB::table('customers as c')
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
            $join->on('c.customerId', '=', 's.companyCode')
                ->on('l.id', '=', 's.location_id')
                ->on('b.id', '=', 's.branch_id')
                ->on('f.id','=','s.facility_id')
                ->on('bl.id','=','s.building_id')
                ->on('fl.id','=','s.floor_id')
                ->on('lb.id','=','s.lab_id')
                ->on('d.id','=','s.deviceId');
        })
        ->Join('sensor_limit_change_logs as slc', function($join){
            $join->on('c.customerId', '=', 'slc.companyCode')
                ->on('s.id', '=', 'slc.sensor_id');
        })

->select(DB::raw('DATE_FORMAT(slc.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(slc.created_at) as time'),'slc.email','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorTag','slc.criticalMinValue','slc.criticalMaxValue','slc.warningMinValue','slc.warningMaxValue','slc.outofrangeMinValue','slc.outofrangeMaxValue')        ->WHERE('customerId','=',$this->companyCode)
                    ->where('l.id','=',$request->location_id)
                    ->where('b.id','=',$request->branch_id)
        ->WHERE('device_id','=',$request->deviceId)
        ->orderBy('slc.id', 'DESC');
       }
        else if($request->location_id != ""){

               $query = DB::table('customers as c')
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
            $join->on('c.customerId', '=', 's.companyCode')
                ->on('l.id', '=', 's.location_id')
                ->on('b.id', '=', 's.branch_id')
                ->on('f.id','=','s.facility_id')
                ->on('bl.id','=','s.building_id')
                ->on('fl.id','=','s.floor_id')
                ->on('lb.id','=','s.lab_id')
                ->on('d.id','=','s.deviceId');
        })
        ->Join('sensor_limit_change_logs as slc', function($join){
            $join->on('c.customerId', '=', 'slc.companyCode')
                ->on('s.id', '=', 'slc.sensor_id');
        })

->select(DB::raw('DATE_FORMAT(slc.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(slc.created_at) as time'),'slc.email','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorTag','slc.criticalMinValue','slc.criticalMaxValue','slc.warningMinValue','slc.warningMaxValue','slc.outofrangeMinValue','slc.outofrangeMaxValue')        ->WHERE('customerId','=',$this->companyCode)
                    ->where('l.id','=',$request->location_id)
        ->WHERE('device_id','=',$request->deviceId)
       ->orderBy('slc.id', 'DESC');

        }
        else{
                           $query = DB::table('customers as c')
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
            $join->on('c.customerId', '=', 's.companyCode')
                ->on('l.id', '=', 's.location_id')
                ->on('b.id', '=', 's.branch_id')
                ->on('f.id','=','s.facility_id')
                ->on('bl.id','=','s.building_id')
                ->on('fl.id','=','s.floor_id')
                ->on('lb.id','=','s.lab_id')
                ->on('d.id','=','s.deviceId');
        })
        ->Join('sensor_limit_change_logs as slc', function($join){
            $join->on('c.customerId', '=', 'slc.companyCode')
                ->on('s.id', '=', 'slc.sensor_id');
        })

->select(DB::raw('DATE_FORMAT(slc.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(slc.created_at) as time'),'slc.email','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorTag','slc.criticalMinValue','slc.criticalMaxValue','slc.warningMinValue','slc.warningMaxValue','slc.outofrangeMinValue','slc.outofrangeMaxValue')        ->WHERE('customerId','=',$this->companyCode)
        ->WHERE('device_id','=',$request->deviceId)
       ->orderBy('slc.id', 'DESC');
        }
    }
    else{
        if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !="" && $request->lab_id !=""){
              $query = DB::table('customers as c')
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
            $join->on('c.customerId', '=', 's.companyCode')
                ->on('l.id', '=', 's.location_id')
                ->on('b.id', '=', 's.branch_id')
                ->on('f.id','=','s.facility_id')
                ->on('bl.id','=','s.building_id')
                ->on('fl.id','=','s.floor_id')
                ->on('lb.id','=','s.lab_id')
                ->on('d.id','=','s.deviceId');
        })
        ->Join('sensor_limit_change_logs as slc', function($join){
            $join->on('c.customerId', '=', 'slc.companyCode')
                ->on('s.id', '=', 'slc.sensor_id');
        })

->select(DB::raw('DATE_FORMAT(slc.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(slc.created_at) as time'),'slc.email','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorTag','slc.criticalMinValue','slc.criticalMaxValue','slc.warningMinValue','slc.warningMaxValue','slc.outofrangeMinValue','slc.outofrangeMaxValue')
->WHERE('customerId','=',$this->companyCode)
                    ->where('l.id','=',$request->location_id)
                    ->where('b.id','=',$request->branch_id)
                    ->where('f.id','=',$request->facility_id)
                    ->where('bl.id','=',$request->building_id)
                    ->where('fl.id','=',$request->floor_id)
                    ->where('lb.id','=',$request->lab_id)
                   ->orderBy('slc.id', 'DESC');
}
       else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !=""){
                         $query = DB::table('customers as c')
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
            $join->on('c.customerId', '=', 's.companyCode')
                ->on('l.id', '=', 's.location_id')
                ->on('b.id', '=', 's.branch_id')
                ->on('f.id','=','s.facility_id')
                ->on('bl.id','=','s.building_id')
                ->on('fl.id','=','s.floor_id')
                ->on('lb.id','=','s.lab_id')
                ->on('d.id','=','s.deviceId');
        })
        ->Join('sensor_limit_change_logs as slc', function($join){
            $join->on('c.customerId', '=', 'slc.companyCode')
                ->on('s.id', '=', 'slc.sensor_id');
        })

->select(DB::raw('DATE_FORMAT(slc.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(slc.created_at) as time'),'slc.email','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorTag','slc.criticalMinValue','slc.criticalMaxValue','slc.warningMinValue','slc.warningMaxValue','slc.outofrangeMinValue','slc.outofrangeMaxValue')        ->WHERE('customerId','=',$this->companyCode)
                    ->where('l.id','=',$request->location_id)
                    ->where('b.id','=',$request->branch_id)
                    ->where('f.id','=',$request->facility_id)
                    ->where('bl.id','=',$request->building_id)
                    ->where('fl.id','=',$request->floor_id)
                  ->orderBy('slc.id', 'DESC');
       }
       else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !=""){
        $query = DB::table('customers as c')
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
            $join->on('c.customerId', '=', 's.companyCode')
                ->on('l.id', '=', 's.location_id')
                ->on('b.id', '=', 's.branch_id')
                ->on('f.id','=','s.facility_id')
                ->on('bl.id','=','s.building_id')
                ->on('fl.id','=','s.floor_id')
                ->on('lb.id','=','s.lab_id')
                ->on('d.id','=','s.deviceId');
        })
        ->Join('sensor_limit_change_logs as slc', function($join){
            $join->on('c.customerId', '=', 'slc.companyCode')
                ->on('s.id', '=', 'slc.sensor_id');
        })

->select(DB::raw('DATE_FORMAT(slc.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(slc.created_at) as time'),'slc.email','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorTag','slc.criticalMinValue','slc.criticalMaxValue','slc.warningMinValue','slc.warningMaxValue','slc.outofrangeMinValue','slc.outofrangeMaxValue')        ->WHERE('customerId','=',$this->companyCode)
                    ->where('l.id','=',$request->location_id)
                    ->where('b.id','=',$request->branch_id)
                    ->where('f.id','=',$request->facility_id)
                    ->where('bl.id','=',$request->building_id)
                 ->orderBy('slc.id', 'DESC');
       }
       else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""){
                   $query = DB::table('customers as c')
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
            $join->on('c.customerId', '=', 's.companyCode')
                ->on('l.id', '=', 's.location_id')
                ->on('b.id', '=', 's.branch_id')
                ->on('f.id','=','s.facility_id')
                ->on('bl.id','=','s.building_id')
                ->on('fl.id','=','s.floor_id')
                ->on('lb.id','=','s.lab_id')
                ->on('d.id','=','s.deviceId');
        })
        ->Join('sensor_limit_change_logs as slc', function($join){
            $join->on('c.customerId', '=', 'slc.companyCode')
                ->on('s.id', '=', 'slc.sensor_id');
        })

->select(DB::raw('DATE_FORMAT(slc.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(slc.created_at) as time'),'slc.email','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorTag','slc.criticalMinValue','slc.criticalMaxValue','slc.warningMinValue','slc.warningMaxValue','slc.outofrangeMinValue','slc.outofrangeMaxValue')        ->WHERE('customerId','=',$this->companyCode)
                    ->where('l.id','=',$request->location_id)
                    ->where('b.id','=',$request->branch_id)
                    ->where('f.id','=',$request->facility_id)
                   ->orderBy('slc.id', 'DESC');
       }
       else if($request->location_id != "" && $request->branch_id != ""){
             $query = DB::table('customers as c')
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
            $join->on('c.customerId', '=', 's.companyCode')
                ->on('l.id', '=', 's.location_id')
                ->on('b.id', '=', 's.branch_id')
                ->on('f.id','=','s.facility_id')
                ->on('bl.id','=','s.building_id')
                ->on('fl.id','=','s.floor_id')
                ->on('lb.id','=','s.lab_id')
                ->on('d.id','=','s.deviceId');
        })
        ->Join('sensor_limit_change_logs as slc', function($join){
            $join->on('c.customerId', '=', 'slc.companyCode')
                ->on('s.id', '=', 'slc.sensor_id');
        })

->select(DB::raw('DATE_FORMAT(slc.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(slc.created_at) as time'),'slc.email','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorTag','slc.criticalMinValue','slc.criticalMaxValue','slc.warningMinValue','slc.warningMaxValue','slc.outofrangeMinValue','slc.outofrangeMaxValue')        ->WHERE('customerId','=',$this->companyCode)
                    ->where('l.id','=',$request->location_id)
                    ->where('b.id','=',$request->branch_id)
                   ->orderBy('slc.id', 'DESC');
       }
        else if($request->location_id != ""){
                         $query = DB::table('customers as c')
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
            $join->on('c.customerId', '=', 's.companyCode')
                ->on('l.id', '=', 's.location_id')
                ->on('b.id', '=', 's.branch_id')
                ->on('f.id','=','s.facility_id')
                ->on('bl.id','=','s.building_id')
                ->on('fl.id','=','s.floor_id')
                ->on('lb.id','=','s.lab_id')
                ->on('d.id','=','s.deviceId');
        })
        ->Join('sensor_limit_change_logs as slc', function($join){
            $join->on('c.customerId', '=', 'slc.companyCode')
                ->on('s.id', '=', 'slc.sensor_id');
        })

->select(DB::raw('DATE_FORMAT(slc.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(slc.created_at) as time'),'slc.email','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorTag','slc.criticalMinValue','slc.criticalMaxValue','slc.warningMinValue','slc.warningMaxValue','slc.outofrangeMinValue','slc.outofrangeMaxValue')
                   ->WHERE('customerId','=',$this->companyCode)
                    ->where('l.id','=',$request->location_id)
                   ->orderBy('slc.id', 'DESC');
        }
        else{
         $query = DB::table('customers as c')
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
            $join->on('c.customerId', '=', 's.companyCode')
                ->on('l.id', '=', 's.location_id')
                ->on('b.id', '=', 's.branch_id')
                ->on('f.id','=','s.facility_id')
                ->on('bl.id','=','s.building_id')
                ->on('fl.id','=','s.floor_id')
                ->on('lb.id','=','s.lab_id')
                ->on('d.id','=','s.deviceId');
        })
        ->Join('sensor_limit_change_logs as slc', function($join){
            $join->on('c.customerId', '=', 'slc.companyCode')
                ->on('s.id', '=', 'slc.sensor_id');
        })

->select(DB::raw('DATE_FORMAT(slc.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(slc.created_at) as time'),'slc.email','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorTag','slc.criticalMinValue','slc.criticalMaxValue','slc.warningMinValue','slc.warningMaxValue','slc.outofrangeMinValue','slc.outofrangeMaxValue')
        ->WHERE('customerId','=',$this->companyCode)
                  ->orderBy('slc.id', 'DESC');
        }

    }


        if($startDate === $endDate){
            $query->whereDate('slc.created_at','=',$startDate);
        }
        else {
            $query->whereBetween('slc.created_at', [$startDate, $endDate]);
        }

       $attachment =  Excel::raw(new LimitEditLogsExport($query), BaseExcel::XLSX);

        // $email = "vaishakkpoojary@gmail.com";
        $userEmail = $this->userId;
        $email = $this->fetchVerifiedEmailUsers($userEmail);
        $url = env('APPLICATION_URL');

        $data = [
            'userid'=>$email,
            'body' =>"Limit Edit Logs",
            'url' => $url
        ];

        Mail::send('LimitEditLogs', $data, function($messages) use ($email,$attachment){
            $messages->to($email);
            $messages->subject('Limit edit logs');
            $messages->attachData($attachment, 'LimitEditLogs.xlsx',[
                 ]);
        });

       $response = [
            "message"=>"Reports data sent Successfully"
        ];
        $status = 200;

         return response($response, $status);
    }





/*

public function exportSensorLog(Request $request) {

        $startDate = date("Y-m-d",strtotime($request->fromDate));
        $endDate = date("Y-m-d", strtotime($request->toDate));

        $query = DB::table('customers as c')
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
            $join->on('c.customerId', '=', 's.companyCode')
                ->on('l.id', '=', 's.location_id')
                ->on('b.id', '=', 's.branch_id')
                ->on('f.id','=','s.facility_id')
                ->on('bl.id','=','s.building_id')
                ->on('fl.id','=','s.floor_id')
                ->on('lb.id','=','s.lab_id')
                ->on('d.id','=','s.deviceId');
        })
        ->Join('sensor_limit_change_logs as slc', function($join){
            $join->on('c.customerId', '=', 'slc.companyCode')
                ->on('s.id', '=', 'slc.sensor_id');
        })

        ->select(DB::raw('DATE_FORMAT(slc.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(slc.created_at) as time'),'slc.email','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorTag','slc.criticalMinValue','slc.criticalMaxValue','slc.warningMinValue','slc.warningMaxValue','slc.outofrangeMinValue','slc.outofrangeMaxValue')
        ->WHERE('customerId','=',$this->companyCode)
        ->WHERE('device_id','=',$request->deviceId);

        if($startDate === $endDate){
            $query->whereDate('slc.created_at','=',$startDate);
        }
        else {
            $query->whereBetween('slc.created_at', [$startDate, $endDate]);
        }

        return Excel::download(new SensorLogReport($query), 'Limiteditlogs.xlsx');
}

*/





/*

public function emailDeviceLog(Request $request)
    {

        $startDate = date("Y-m-d",strtotime($request->fromDate));
        $endDate = date("Y-m-d", strtotime($request->toDate));

        $query = DB::table('customers as c')
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
            $join->on('c.customerId', '=', 's.companyCode')
                ->on('l.id', '=', 's.location_id')
                ->on('b.id', '=', 's.branch_id')
                ->on('f.id','=','s.facility_id')
                ->on('bl.id','=','s.building_id')
                ->on('fl.id','=','s.floor_id')
                ->on('lb.id','=','s.lab_id')
                ->on('d.id','=','s.deviceId');
        })
        ->Join('sensor_limit_change_logs as slc', function($join){
            $join->on('c.customerId', '=', 'slc.companyCode')
                ->on('s.id', '=', 'slc.sensor_id');
        })

        ->select(DB::raw('DATE_FORMAT(slc.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(slc.created_at) as time'),'slc.email','l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorTag','slc.criticalMinValue','slc.criticalMaxValue','slc.warningMinValue','slc.warningMaxValue','slc.outofrangeMinValue','slc.outofrangeMaxValue')
        ->WHERE('customerId','=',$this->companyCode)
        ->WHERE('device_id','=',$request->deviceId);

        if($startDate === $endDate){
            $query->whereDate('slc.created_at','=',$startDate);
        }
        else {
            $query->whereBetween('slc.created_at', [$startDate, $endDate]);
        }

        // return Excel::download(new SensorLogReport($query), 'SensorLogReport.xlsx');


            $attachment =  Excel::raw(new LimitEditLogs($query), BaseExcel::XLSX);

//   $email = "developer2@rdltech.in";

$userEmail = $this->userId;
$email = $this->fetchVerifiedEmailUsers($userEmail);



    $data = [
        'userid'=>$email,
        'body' =>"Limit Edit Logs"
    ];

    Mail::send('LimitEditLogs',$data, function($messages) use ($email,$attachment){
        $messages->to($email);
        $messages->subject('Limit edit logs data');
        $messages->attachData($attachment, 'LimitEditLogs.xlsx',[
             ]);
    });

       $response = [
            "message"=>"Reports data sent Successfully"
        ];
        $status = 200;
     return response($response, $status);



}



*/


public function SiteDeviceReport(Request $request){

$query = DB::table('customers as c')
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
            $join->on('c.customerId', '=', 'bl.d')
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
        ->select('l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','d.id')
        ->WHERE('customerId','=',$this->companyCode)
        ->where('l.id','=',$request->location_id)
        ->where('b.id','=',$request->branch_id)
        ->where('f.id','=',$request->facility_id)
        ->where('bl.id','=',$request->building_id)
        ->where('fl.id','=',$request->floor_id)
        ->where('lb.id','=',$request->lab_id);
        //->WHERE('deviceId','=',$deviceId)
        //->get();

        $data = $query->get();

        $deviceData = array();

        $deviceCount = count($data);

        for($x=0;$x<$deviceCount;$x++){
              $deviceStatusCount = DB::table('sampled_sensor_data_details')
                                    ->selectRaw('alertType,sample_date_time')
                                    ->where('device_id','=',$data[$x]->id)
                                    ->orderBy('id','desc')
                                    ->first();

              if($deviceStatusCount){
                $alertTypeVal = $deviceStatusCount->alertType;
                $dateTime = $deviceStatusCount->sample_date_time;

                if($alertTypeVal === "Critical"){
                    $alertColor = $this->alertColor['CRITICAL'];
                    $alertLightColor = $this->alertColor['CRITICALLIGHTCOLOR'];
                }
                if($alertTypeVal === "Warning"){
                    $alertColor = $this->alertColor['WARNING'];
                     $alertLightColor = $this->alertColor['WARNINGLIGHTCOLOR'];
                }
                if($alertTypeVal === "outOfRange"){
                    $alertColor = $this->alertColor['CRITICAL'];
                    $alertLightColor = $this->alertColor['CRITICALLIGHTCOLOR'];
                }
                if($alertTypeVal === "NORMAL"){
                    $alertColor = $this->alertColor['NORMAL'];
                    $alertLightColor = $this->alertColor['NORMALLIGHTCOLOR'];
                }


              }else{
                  $alertTypeVal = "NA";
                  $alertColor = "NA";
                  $alertLightColor = "NA";
                  $dateTime = "NA";
              }
              $data[$x]->alertType = $alertTypeVal;
              $data[$x]->alertColor =$alertColor;
              $data[$x]->alertLightColor = $alertLightColor;
              $data[$x]->sample_date_time = $dateTime;
              $deviceData[] = $data[$x];
        }

          $response = [
             "data"=>$deviceData
        ];
        $status = 200;
        return response($response,$status);
}


public function ExportAqiReport(Request $request)
{

        $startDate = date("Y-m-d",strtotime($request->input(key:'fromDate')));
        $endDate = date("Y-m-d", strtotime($request->input(key:'toDate')));


        if($request->deviceId != "")
         {
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
                //->select(DB::raw('DATE_FORMAT(sampled_date_time, "%d-%b-%Y") as date'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName', 'fl.floorName','lb.labDepName','d.deviceName', DB::raw('ROUND(MAX(AqiValue),2) as AqiValue'))
                ->select(
                    DB::raw('DATE_FORMAT(sampled_date_time, "%d-%b-%Y") as date'),'l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName',DB::raw('ROUND(MAX(AqiValue),2) as AqiValue')
                )
                ->where('customerId','=',$this->companyCode)
                     ->where('labId','=',$request->lab_id)
                     ->where('floorId','=',$request->floor_id)
                     ->where('buildingId','=',$request->building_id)
                      ->where('facilityId','=',$request->facility_id)
                      ->where('branchId','=',$request->branch_id)
                      ->where('locationId','=',$request->location_id)
                       ->where('deviceId','=',$request->deviceId)
                       ->orderBy('AqiValue','desc')
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
                // ->select(DB::raw('DATE_FORMAT(sampled_date_time, "%d-%b-%Y") as date'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName', 'fl.floorName','lb.labDepName','d.deviceName', DB::raw('ROUND(MAX(AqiValue),2) as AqiValue'))
                //->select(DB::raw('DATE_FORMAT(sampled_date_time, "%d-%b-%Y") as date'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName', 'fl.floorName','d.deviceName', DB::raw('ROUND(MAX(AqiValue),2) as AqiValue'))
                ->select(
                    DB::raw('DATE_FORMAT(sampled_date_time, "%d-%b-%Y") as date'),'l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName',DB::raw('"-" as labDepName'),'d.deviceName',DB::raw('ROUND(MAX(AqiValue),2) as AqiValue')
                )
                ->where('customerId','=',$this->companyCode)
                    //  ->where('companyCode','=',$this->companyCode)
                     ->where('floorId','=',$request->floor_id)
                     ->where('buildingId','=',$request->building_id)
                      ->where('facilityId','=',$request->facility_id)
                      ->where('branchId','=',$request->branch_id)
                      ->where('locationId','=',$request->location_id)
                        ->where('deviceId','=',$request->deviceId)
                        ->orderBy('AqiValue','desc')
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
                // ->select(DB::raw('DATE_FORMAT(sampled_date_time, "%d-%b-%Y") as date'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName', 'fl.floorName','lb.labDepName','d.deviceName', DB::raw('ROUND(MAX(AqiValue),2) as AqiValue'))
               // ->select(DB::raw('DATE_FORMAT(sampled_date_time, "%d-%b-%Y") as date'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName', 'd.deviceName', DB::raw('ROUND(MAX(AqiValue),2) as AqiValue'))
                ->select(
                    DB::raw('DATE_FORMAT(sampled_date_time, "%d-%b-%Y") as date'),'l.stateName','b.branchName','f.facilityName','bl.buildingName',DB::raw('"-"as floorName'),DB::raw('"-" as labDepName'),'d.deviceName',DB::raw('ROUND(MAX(AqiValue),2) as AqiValue')
                )
                ->where('customerId','=',$this->companyCode)
                     ->where('buildingId','=',$request->building_id)
                     ->where('facilityId','=',$request->facility_id)
                     ->where('branchId','=',$request->branch_id)
                     ->where('locationId','=',$request->location_id)
                     ->where('deviceId','=',$request->deviceId)
                     ->orderBy('AqiValue','desc')
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
                //->select(DB::raw('DATE_FORMAT(sampled_date_time, "%d-%b-%Y") as date'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName', 'fl.floorName','lb.labDepName','d.deviceName', DB::raw('ROUND(MAX(AqiValue),2) as AqiValue'))
                //->select(DB::raw('DATE_FORMAT(sampled_date_time, "%d-%b-%Y") as date'),'l.stateName', 'b.branchName', 'f.facilityName','d.deviceName', DB::raw('ROUND(MAX(AqiValue),2) as AqiValue'))
                ->select(
                    DB::raw('DATE_FORMAT(sampled_date_time, "%d-%b-%Y") as date'),'l.stateName','b.branchName','f.facilityName',DB::raw('"-" as buildingName'),DB::raw('"-" as floorName'),DB::raw('"-" as labDepName'),'d.deviceName',DB::raw('ROUND(MAX(AqiValue),2) as AqiValue')
                )
                ->where('customerId','=',$this->companyCode)
                      ->where('facilityId','=',$request->facility_id)
                      ->where('branchId','=',$request->branch_id)
                      ->where('locationId','=',$request->location_id)
                      ->where('deviceId','=',$request->deviceId)
                      ->orderBy('AqiValue','desc')
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
                //->select(DB::raw('DATE_FORMAT(sampled_date_time, "%d-%b-%Y") as date'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName', 'fl.floorName','lb.labDepName','d.deviceName', DB::raw('ROUND(MAX(AqiValue),2) as AqiValue'))
                //->select(DB::raw('DATE_FORMAT(sampled_date_time, "%d-%b-%Y") as date'),'l.stateName', 'b.branchName', 'd.deviceName', DB::raw('ROUND(AVG(AqiValue),2) as AqiValue'))
                ->select(
                    DB::raw('DATE_FORMAT(sampled_date_time, "%d-%b-%Y") as date'),'l.stateName','b.branchName',DB::raw('"-" as facilityName'),DB::raw('"-" as buildingName'),DB::raw('"-" as floorName'),DB::raw('"-" as labDepName'),'d.deviceName',DB::raw('ROUND(AVG(AqiValue),2) as AqiValue')
                )
                ->where('customerId','=',$this->companyCode)
                      ->where('branchId','=',$request->branch_id)
                      ->where('locationId','=',$request->location_id)
                      ->where('deviceId','=',$request->deviceId)
                      ->orderBy('AqiValue','desc')
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
                //->select(DB::raw('DATE_FORMAT(sampled_date_time, "%d-%b-%Y") as date'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName', 'fl.floorName','lb.labDepName','d.deviceName', DB::raw('ROUND(MAX(AqiValue),2) as AqiValue'))
                //->select(DB::raw('DATE_FORMAT(sampled_date_time, "%d-%b-%Y") as date'),'l.stateName','d.deviceName', DB::raw('ROUND(MAX(AqiValue),2) as AqiValue'))
                ->select(
                    DB::raw('DATE_FORMAT(sampled_date_time, "%d-%b-%Y") as date'),'l.stateName',DB::raw('"-" as branchName'),DB::raw('"-" as facilityName'),DB::raw('"-" as buildingName'),DB::raw('"-" as floorName'),DB::raw('"-" as labDepName'),'d.deviceName',DB::raw('ROUND(MAX(AqiValue),2) as AqiValue')
                )
                ->where('customerId','=',$this->companyCode)
                      ->where('locationId','=',$request->location_id)
                      ->where('deviceId','=',$request->deviceId)
                      ->orderBy('AqiValue','desc')
                     ->groupBy(DB::raw('Date(sampled_date_time)'));

            //  $summaryAqiValue = DB::table('Aqi_values_per_device')
            //                      ->select( DB::raw('MAX(AqiValue) as AqiValue , DATE(sampled_date_time) as date,deviceId'))
            //                      ->where('labId','=',$request->lab_id)
            //                      ->groupBy(DB::raw('Date(sampled_date_time)'))
            //                      ->get();
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
               // ->select(DB::raw('DATE_FORMAT(sampled_date_time, "%d-%b-%Y") as date'),'l.stateName','d.deviceName', DB::raw('ROUND(MAX(AqiValue),2) as AqiValue'))
                // ->select(DB::raw('DATE_FORMAT(sampled_date_time, "%d-%b-%Y") as date'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName', 'fl.floorName','lb.labDepName','d.deviceName', DB::raw('ROUND(MAX(AqiValue),2) as AqiValue'))
                ->select(
                    DB::raw('DATE_FORMAT(sampled_date_time, "%d-%b-%Y") as date'),'l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName',DB::raw('ROUND(MAX(AqiValue),2) as AqiValue')
                )
                ->where('customerId','=',$this->companyCode)
                  ->where('deviceId','=',$request->deviceId)
                  ->orderBy('AqiValue','desc')
                    ->groupBy(DB::raw('Date(sampled_date_time)'));
            }
         }
        else{

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
                // ->select(DB::raw('DATE_FORMAT(sampled_date_time, "%d-%b-%Y") as date'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName', 'fl.floorName','lb.labDepName','d.deviceName', DB::raw('ROUND(MAX(AqiValue),2) as AqiValue'))
                //->select(DB::raw('DATE_FORMAT(sampled_date_time, "%d-%b-%Y") as date'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName', 'fl.floorName','lb.labDepName', DB::raw('ROUND(MAX(AqiValue),2) as AqiValue'))
                ->select(
                    DB::raw('DATE_FORMAT(sampled_date_time, "%d-%b-%Y") as date'),'l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName',DB::raw('"-" as deviceName'),DB::raw('ROUND(MAX(AqiValue),2) as AqiValue')
                )
                ->where('customerId','=',$this->companyCode)
                     ->where('labId','=',$request->lab_id)
                     ->where('floorId','=',$request->floor_id)
                     ->where('buildingId','=',$request->building_id)
                      ->where('facilityId','=',$request->facility_id)
                      ->where('branchId','=',$request->branch_id)
                      ->where('locationId','=',$request->location_id)
                      ->orderBy('AqiValue','desc')
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
               // ->select(DB::raw('DATE_FORMAT(sampled_date_time, "%d-%b-%Y") as date'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName', 'fl.floorName', DB::raw('ROUND(MAX(AqiValue),2) as AqiValue'))
                // ->select(DB::raw('DATE_FORMAT(sampled_date_time, "%d-%b-%Y") as date'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName', 'fl.floorName','lb.labDepName','d.deviceName', DB::raw('ROUND(MAX(AqiValue),2) as AqiValue'))
                ->select(
                    DB::raw('DATE_FORMAT(sampled_date_time, "%d-%b-%Y") as date'),'l.stateName','b.branchName','f.facilityName','bl.buildingName','fl.floorName',DB::raw('"-" as labDepName'),DB::raw('"-" as deviceName'),DB::raw('ROUND(MAX(AqiValue),2) as AqiValue')
                )
                ->where('customerId','=',$this->companyCode)
                    //  ->where('companyCode','=',$this->companyCode)
                     ->where('floorId','=',$request->floor_id)
                     ->where('buildingId','=',$request->building_id)
                      ->where('facilityId','=',$request->facility_id)
                      ->where('branchId','=',$request->branch_id)
                      ->where('locationId','=',$request->location_id)
                      ->orderBy('AqiValue','desc')
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
               // ->select(DB::raw('DATE_FORMAT(sampled_date_time, "%d-%b-%Y") as date'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName', 'fl.floorName','lb.labDepName','d.deviceName', DB::raw('ROUND(MAX(AqiValue),2) as AqiValue'))
               // ->select(DB::raw('DATE_FORMAT(sampled_date_time, "%d-%b-%Y") as date'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName', DB::raw('ROUND(MAX(AqiValue),2) as AqiValue'))
                ->select(
                    DB::raw('DATE_FORMAT(sampled_date_time, "%d-%b-%Y") as date'),'l.stateName','b.branchName','f.facilityName','bl.buildingName',DB::raw('"-" as floorName'),DB::raw('"-" as labDepName'),DB::raw('"-" as deviceName'),DB::raw('"-" as labDepName'),DB::raw('ROUND(MAX(AqiValue),2) as AqiValue')
                )
                ->where('customerId','=',$this->companyCode)
                     ->where('buildingId','=',$request->building_id)
                     ->where('facilityId','=',$request->facility_id)
                     ->where('branchId','=',$request->branch_id)
                     ->where('locationId','=',$request->location_id)
                     ->orderBy('AqiValue','desc')
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
                //->select(DB::raw('DATE_FORMAT(sampled_date_time, "%d-%b-%Y") as date'),'l.stateName', 'b.branchName', 'f.facilityName',DB::raw('ROUND(MAX(AqiValue),2) as AqiValue'))
                ->select(
                    DB::raw('DATE_FORMAT(sampled_date_time, "%d-%b-%Y") as date'),'l.stateName','b.branchName','f.facilityName',DB::raw('"-" as buildingName'),DB::raw('"-" as floorName'),DB::raw('"-" as labDepName'),DB::raw('"-" as deviceName'),DB::raw('ROUND(MAX(AqiValue),2) as AqiValue')
                )
                //  ->select(DB::raw('DATE_FORMAT(sampled_date_time, "%d-%b-%Y") as date'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName', 'fl.floorName','lb.labDepName','d.deviceName', DB::raw('ROUND(MAX(AqiValue),2) as AqiValue'))
                ->where('customerId','=',$this->companyCode)
                      ->where('facilityId','=',$request->facility_id)
                      ->where('branchId','=',$request->branch_id)
                      ->where('locationId','=',$request->location_id)
                      ->orderBy('AqiValue', 'desc')
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
                // ->select(DB::raw('DATE_FORMAT(sampled_date_time, "%d-%b-%Y") as date'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName', 'fl.floorName','lb.labDepName','d.deviceName', DB::raw('ROUND(MAX(AqiValue),2) as AqiValue'))
                ->select(
                    DB::raw('DATE_FORMAT(sampled_date_time, "%d-%b-%Y") as date'),'l.stateName','b.branchName',DB::raw('"-" as facilityName'),DB::raw('"-" as buildingName'),DB::raw('"-" as floorName'),DB::raw('"-" as labDepName'),DB::raw('"-" as deviceName'),DB::raw('ROUND(AVG(AqiValue),2) as AqiValue')
                )
                ->where('customerId','=',$this->companyCode)
                      ->where('branchId','=',$request->branch_id)
                      ->where('locationId','=',$request->location_id)
                      ->orderBy('AqiValue', 'desc')
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
->join('branches as b', function ($join) {
    $join->on('l.id', '=', 'b.location_id')
        ->on('c.customerId', '=', 'b.companyCode');
})
->join('facilities as f', function ($join) {
    $join->on('c.customerId', '=', 'f.companyCode')
        ->on('l.id', '=', 'f.location_id')
        ->on('b.id', '=', 'f.branch_id');
})
->join('buildings as bl', function ($join) {
    $join->on('c.customerId', '=', 'bl.companyCode')
        ->on('l.id', '=', 'bl.location_id')
        ->on('b.id', '=', 'bl.branch_id')
        ->on('f.id', '=', 'bl.facility_id');
})
->join('floors as fl', function ($join) {
    $join->on('c.customerId', '=', 'fl.companyCode')
        ->on('l.id', '=', 'fl.location_id')
        ->on('b.id', '=', 'fl.branch_id')
        ->on('f.id', '=', 'fl.facility_id')
        ->on('bl.id', '=', 'fl.building_id');
})
->join('lab_departments as lb', function ($join) {
    $join->on('c.customerId', '=', 'lb.companyCode')
        ->on('l.id', '=', 'lb.location_id')
        ->on('b.id', '=', 'lb.branch_id')
        ->on('f.id', '=', 'lb.facility_id')
        ->on('bl.id', '=', 'lb.building_id')
        ->on('fl.id', '=', 'lb.floor_id');
})
->join('devices as d', function ($join) {
    $join->on('c.customerId', '=', 'd.companyCode')
        ->on('l.id', '=', 'd.location_id')
        ->on('b.id', '=', 'd.branch_id')
        ->on('f.id', '=', 'd.facility_id')
        ->on('bl.id', '=', 'd.building_id')
        ->on('fl.id', '=', 'd.floor_id')
        ->on('lb.id', '=', 'd.lab_id');
})
->join('Aqi_values_per_device as aqi', function ($join) {
    $join->on('c.customerId', '=', 'd.companyCode')
        ->on('l.id', '=', 'aqi.locationId')
        ->on('b.id', '=', 'aqi.branchId')
        ->on('f.id', '=', 'aqi.facilityId')
        ->on('bl.id', '=', 'aqi.buildingId')
        ->on('fl.id', '=', 'aqi.floorId')
        ->on('lb.id', '=', 'aqi.labId')
        ->on('d.id', '=', 'aqi.deviceId');
})
//->select(DB::raw('DATE_FORMAT(sampled_date_time, "%d-%b-%Y") as date'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName', 'fl.floorName','lb.labDepName','d.deviceName', DB::raw('ROUND(MAX(AqiValue),2) as AqiValue'))
->select(
    DB::raw('DATE_FORMAT(sampled_date_time, "%d-%b-%Y") as date'),'l.stateName',DB::raw('"-" as branchName'),DB::raw('"-" as facilityName'),DB::raw('"-" as buildingName'),DB::raw('"-" as floorName'),DB::raw('"-" as labDepName'),DB::raw('"-" as deviceName'),DB::raw('ROUND(MAX(AqiValue),2) as AqiValue')
)
->where('customerId', '=', $this->companyCode)
->where('locationId', '=', $request->location_id)
->orderBy('AqiValue','desc')
->groupBy(DB::raw('Date(sampled_date_time)'));


            //  $summaryAqiValue = DB::table('Aqi_values_per_device')
            //                      ->select( DB::raw('MAX(AqiValue) as AqiValue , DATE(sampled_date_time) as date,deviceId'))
            //                      ->where('labId','=',$request->lab_id)
            //                      ->groupBy(DB::raw('Date(sampled_date_time)'))
            //                      ->get();
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
                ->where('customerId','=',$this->companyCode)

                    ->groupBy(DB::raw('Date(sampled_date_time)'));
            }

}


          if($startDate === $endDate){
                $summaryAqiValue->whereDate('aqi.sampled_date_time','=',$startDate);
            }
            else {
                $summaryAqiValue->whereDate('aqi.sampled_date_time','>=',$startDate)
                                ->whereDate('aqi.sampled_date_time','<=',$endDate);
            }

        return Excel::download(new AqiReportExport($summaryAqiValue), 'AqiReportExport.xlsx');

}


            public function FirmwareVersionReport(Request $request)
                {

                if( $request->deviceId !=""){

                    if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !="" && $request->lab_id !=""){
                         $query = DB::table('customers as c')
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
                            ->Join('firmware_version_reports as fvr', function($join){
                                $join->on('c.customerId', '=', 'fvr.companyCode')
                                    ->on('d.id', '=', 'fvr.device_id');

                            })
                            ->select('fvr.*','c.customerId','fvr.companyCode','fvr.device_id','fvr.created_at','fvr.updated_at','fvr.deviceName','fvr.firmwareVersion')
                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->where('b.id','=',$request->branch_id)
                            ->where('f.id','=',$request->facility_id)
                            ->where('bl.id','=',$request->building_id)
                            ->where('fl.id','=',$request->floor_id)
                            ->where('lb.id','=',$request->lab_id)
                            ->where('device_id','=',$request->deviceId)
                            ->orderBy('id', 'asc');
                    }
                       else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !="")
                        {
                          $query = DB::table('customers as c')
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
                        ->Join('firmware_version_reports as fvr', function($join){
                            $join->on('c.customerId', '=', 'fvr.companyCode')
                                ->on('d.id', '=', 'fvr.device_id');

                        })
                        ->select('fvr.*','c.customerId','fvr.companyCode','fvr.device_id','fvr.created_at','fvr.updated_at','fvr.deviceName','fvr.firmwareVersion')
                        ->where('customerId','=',$this->companyCode)
                        ->where('l.id','=',$request->location_id)
                        ->where('b.id','=',$request->branch_id)
                        ->where('f.id','=',$request->facility_id)
                        ->where('bl.id','=',$request->building_id)
                        ->where('fl.id','=',$request->floor_id)
                        ->where('device_id','=',$request->deviceId)
                        ->orderBy('id', 'asc');
                        }
                        else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !=""){
                            $query = DB::table('customers as c')
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
                        ->Join('firmware_version_reports as fvr', function($join){
                            $join->on('c.customerId', '=', 'fvr.companyCode')
                                ->on('d.id', '=', 'fvr.device_id');

                        })
                        ->select('fvr.*','c.customerId','fvr.companyCode','fvr.device_id','fvr.created_at','fvr.updated_at','fvr.deviceName','fvr.firmwareVersion')
                        ->where('customerId','=',$this->companyCode)
                        ->where('l.id','=',$request->location_id)
                        ->where('b.id','=',$request->branch_id)
                        ->where('f.id','=',$request->facility_id)
                        ->where('bl.id','=',$request->building_id)
                        ->where('device_id','=',$request->deviceId)
                        ->orderBy('id', 'asc');
                        }
                        else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""){
                             $query = DB::table('customers as c')
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
                        ->Join('firmware_version_reports as fvr', function($join){
                            $join->on('c.customerId', '=', 'fvr.companyCode')
                                ->on('d.id', '=', 'fvr.device_id');

                        })
                        ->select('fvr.*','c.customerId','fvr.companyCode','fvr.device_id','fvr.created_at','fvr.updated_at','fvr.deviceName','fvr.firmwareVersion')
                        ->where('customerId','=',$this->companyCode)
                        ->where('l.id','=',$request->location_id)
                        ->where('b.id','=',$request->branch_id)
                        ->where('f.id','=',$request->facility_id)
                        ->where('device_id','=',$request->deviceId)
                        ->orderBy('id', 'asc');
                        }
                        else if($request->location_id != "" && $request->branch_id != ""){
                            $query = DB::table('customers as c')
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
                        ->Join('firmware_version_reports as fvr', function($join){
                            $join->on('c.customerId', '=', 'fvr.companyCode')
                                ->on('d.id', '=', 'fvr.device_id');

                        })
                        ->select('fvr.*','c.customerId','fvr.companyCode','fvr.device_id','fvr.created_at','fvr.updated_at','fvr.deviceName','fvr.firmwareVersion')
                        ->where('customerId','=',$this->companyCode)
                        ->where('l.id','=',$request->location_id)
                        ->where('b.id','=',$request->branch_id)
                        ->where('device_id','=',$request->deviceId)
                        ->orderBy('id', 'asc');
                        }
                        else if($request->location_id != ""){
                            $query = DB::table('customers as c')
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
                        ->Join('firmware_version_reports as fvr', function($join){
                            $join->on('c.customerId', '=', 'fvr.companyCode')
                                ->on('d.id', '=', 'fvr.device_id');

                        })
                        ->select('fvr.*','c.customerId','fvr.companyCode','fvr.device_id','fvr.created_at','fvr.updated_at','fvr.deviceName','fvr.firmwareVersion')
                        ->where('customerId','=',$this->companyCode)
                        ->where('l.id','=',$request->location_id)
                        ->where('device_id','=',$request->deviceId)
                        ->orderBy('id', 'asc');
                        }
                         else{
                               $query = DB::table('customers as c')
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
                        ->Join('firmware_version_reports as fvr', function($join){
                            $join->on('c.customerId', '=', 'fvr.companyCode')
                                ->on('d.id', '=', 'fvr.device_id');

                        })
                        ->select('fvr.*','c.customerId','fvr.companyCode','fvr.device_id','fvr.created_at','fvr.updated_at','fvr.deviceName','fvr.firmwareVersion')
                        ->where('customerId','=',$this->companyCode)
                        ->where('device_id','=',$request->deviceId)
                        ->orderBy('id', 'asc');
                         }

                }
                else
                    {

                            if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !="" && $request->lab_id !="")
                            {
                                $query = DB::table('customers as c')
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
                                ->Join('firmware_version_reports as fvr', function($join){
                                    $join->on('c.customerId', '=', 'fvr.companyCode')
                                        ->on('d.id', '=', 'fvr.device_id');

                                })
                                ->select('fvr.*','c.customerId','fvr.companyCode','fvr.device_id','fvr.created_at','fvr.updated_at','fvr.deviceName','fvr.firmwareVersion')
                                ->where('customerId','=',$this->companyCode)
                                ->where('l.id','=',$request->location_id)
                                ->where('b.id','=',$request->branch_id)
                                ->where('f.id','=',$request->facility_id)
                                ->where('bl.id','=',$request->building_id)
                                ->where('fl.id','=',$request->floor_id)
                                ->where('lb.id','=',$request->lab_id)
                                ->orderBy('id', 'asc');
                        }
                        else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !="")
                            {
                                    $query = DB::table('customers as c')
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
                                ->Join('firmware_version_reports as fvr', function($join){
                                    $join->on('c.customerId', '=', 'fvr.companyCode')
                                        ->on('d.id', '=', 'fvr.device_id');

                                })
                                ->select('fvr.*','c.customerId','fvr.companyCode','fvr.device_id','fvr.created_at','fvr.updated_at','fvr.deviceName','fvr.firmwareVersion')
                                ->where('customerId','=',$this->companyCode)
                                ->where('l.id','=',$request->location_id)
                                ->where('b.id','=',$request->branch_id)
                                ->where('f.id','=',$request->facility_id)
                                ->where('bl.id','=',$request->building_id)
                                ->where('fl.id','=',$request->floor_id)
                                ->orderBy('id', 'asc');


                            }
                            else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !=""){
                                    $query = DB::table('customers as c')
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
                                ->Join('firmware_version_reports as fvr', function($join){
                                    $join->on('c.customerId', '=', 'fvr.companyCode')
                                        ->on('d.id', '=', 'fvr.device_id');

                                })
                                ->select('fvr.*','c.customerId','fvr.companyCode','fvr.device_id','fvr.created_at','fvr.updated_at','fvr.deviceName','fvr.firmwareVersion')
                                ->where('customerId','=',$this->companyCode)
                                ->where('l.id','=',$request->location_id)
                                ->where('b.id','=',$request->branch_id)
                                ->where('f.id','=',$request->facility_id)
                                ->where('bl.id','=',$request->building_id)
                                ->orderBy('id', 'asc');

                            }
                            else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""){
                                $query = DB::table('customers as c')
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
                                ->Join('firmware_version_reports as fvr', function($join){
                                    $join->on('c.customerId', '=', 'fvr.companyCode')
                                        ->on('d.id', '=', 'fvr.device_id');

                                })
                                ->select('fvr.*','c.customerId','fvr.companyCode','fvr.device_id','fvr.created_at','fvr.updated_at','fvr.deviceName','fvr.firmwareVersion')
                                ->where('customerId','=',$this->companyCode)
                                ->where('l.id','=',$request->location_id)
                                ->where('b.id','=',$request->branch_id)
                                ->where('f.id','=',$request->facility_id)
                                ->orderBy('id', 'asc');

                            }
                            else if($request->location_id != "" && $request->branch_id != ""){
                                    $query = DB::table('customers as c')
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
                                ->Join('firmware_version_reports as fvr', function($join){
                                    $join->on('c.customerId', '=', 'fvr.companyCode')
                                        ->on('d.id', '=', 'fvr.device_id');

                                    })
                                    ->select('fvr.*','c.customerId','fvr.companyCode','fvr.device_id','fvr.created_at','fvr.updated_at','fvr.deviceName','fvr.firmwareVersion')
                                    ->where('customerId','=',$this->companyCode)
                                    ->where('l.id','=',$request->location_id)
                                    ->where('b.id','=',$request->branch_id)
                                    ->orderBy('id', 'asc');

                            }
                            else if($request->location_id != ""){
                                $query = DB::table('customers as c')
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
                                ->Join('firmware_version_reports as fvr', function($join){
                                    $join->on('c.customerId', '=', 'fvr.companyCode')
                                        ->on('d.id', '=', 'fvr.device_id');

                                })
                                ->select('fvr.*','c.customerId','fvr.companyCode','fvr.device_id','fvr.created_at','fvr.updated_at','fvr.deviceName','fvr.firmwareVersion')
                                ->where('customerId','=',$this->companyCode)
                                ->where('l.id','=',$request->location_id)
                                ->orderBy('id', 'asc');

                            }
                            else{
                                    $query = DB::table('customers as c')
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
                                ->Join('firmware_version_reports as fvr', function($join){
                                    $join->on('c.customerId', '=', 'fvr.companyCode')
                                        ->on('d.id', '=', 'fvr.device_id');

                                })
                                ->select('fvr.*','c.customerId','fvr.companyCode','fvr.device_id','fvr.created_at','fvr.updated_at','fvr.deviceName','fvr.firmwareVersion')
                                ->where('customerId','=',$this->companyCode)
                                ->orderBy('id', 'asc');
                            }
                    }



                    $getData = new ReportsDataUtilityController($request,$query);
                    $response = [
                                   "data"=>$getData->getData()
                                ];
                    $status = 200;

                    return response($response,$status);
                }


                public function FirmwareVersionExport(Request $request)
                {
                            // $startDate = date("Y-m-d",strtotime($request->input(key:'fromDate')));
                            // $endDate = date("Y-m-d", strtotime($request->input(key:'toDate')));

                    if( $request->deviceId !=""){

                    if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !="" && $request->lab_id !="")
                        {
                         $query = DB::table('customers as c')
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
                            ->Join('firmware_version_reports as fvr', function($join){
                                $join->on('c.customerId', '=', 'fvr.companyCode')
                                    ->on('d.id', '=', 'fvr.device_id');

                            })
                            ->select(DB::raw('DATE_FORMAT(fvr.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(fvr.created_at) as time'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName', 'fl.floorName','lb.labDepName','fvr.deviceName','fvr.firmwareVersion', 'fvr.userEmail', 'fvr.status')
                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->where('b.id','=',$request->branch_id)
                            ->where('f.id','=',$request->facility_id)
                            ->where('bl.id','=',$request->building_id)
                            ->where('fl.id','=',$request->floor_id)
                            ->where('lb.id','=',$request->lab_id)
                            ->where('device_id','=',$request->deviceId)
                            ->orderBy('fvr.id', 'asc');
                    }
                       else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !="")
                        {
                          $query = DB::table('customers as c')
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
                        ->Join('firmware_version_reports as fvr', function($join){
                            $join->on('c.customerId', '=', 'fvr.companyCode')
                                ->on('d.id', '=', 'fvr.device_id');

                        })
                        ->select(DB::raw('DATE_FORMAT(fvr.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(fvr.created_at) as time'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName', 'fl.floorName','lb.labDepName','fvr.deviceName','fvr.firmwareVersion', 'fvr.userEmail', 'fvr.status')
                        ->where('customerId','=',$this->companyCode)
                        ->where('l.id','=',$request->location_id)
                        ->where('b.id','=',$request->branch_id)
                        ->where('f.id','=',$request->facility_id)
                        ->where('bl.id','=',$request->building_id)
                        ->where('fl.id','=',$request->floor_id)
                        ->where('device_id','=',$request->deviceId)
                       ->orderBy('fvr.id', 'asc');
                        }
                        else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !=""){
                            $query = DB::table('customers as c')
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
                        ->Join('firmware_version_reports as fvr', function($join){
                            $join->on('c.customerId', '=', 'fvr.companyCode')
                                ->on('d.id', '=', 'fvr.device_id');

                        })
                                ->select(DB::raw('DATE_FORMAT(fvr.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(fvr.created_at) as time'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName', 'fl.floorName','lb.labDepName','fvr.deviceName','fvr.firmwareVersion', 'fvr.userEmail', 'fvr.status')
                        ->where('customerId','=',$this->companyCode)
                        ->where('l.id','=',$request->location_id)
                        ->where('b.id','=',$request->branch_id)
                        ->where('f.id','=',$request->facility_id)
                        ->where('bl.id','=',$request->building_id)
                        ->where('device_id','=',$request->deviceId)
                        ->orderBy('fvr.id', 'asc');
                        }
                        else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""){
                             $query = DB::table('customers as c')
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
                        ->Join('firmware_version_reports as fvr', function($join){
                            $join->on('c.customerId', '=', 'fvr.companyCode')
                                ->on('d.id', '=', 'fvr.device_id');

                        })
                                ->select(DB::raw('DATE_FORMAT(fvr.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(fvr.created_at) as time'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName', 'fl.floorName','lb.labDepName','fvr.deviceName','fvr.firmwareVersion', 'fvr.userEmail', 'fvr.status')
                        ->where('customerId','=',$this->companyCode)
                        ->where('l.id','=',$request->location_id)
                        ->where('b.id','=',$request->branch_id)
                        ->where('f.id','=',$request->facility_id)
                        ->where('device_id','=',$request->deviceId)
                        ->orderBy('fvr.id', 'asc');
                        }
                        else if($request->location_id != "" && $request->branch_id != ""){
                            $query = DB::table('customers as c')
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
                        ->Join('firmware_version_reports as fvr', function($join){
                            $join->on('c.customerId', '=', 'fvr.companyCode')
                                ->on('d.id', '=', 'fvr.device_id');

                        })
                         ->select(DB::raw('DATE_FORMAT(fvr.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(fvr.created_at) as time'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName', 'fl.floorName','lb.labDepName','fvr.deviceName','fvr.firmwareVersion', 'fvr.userEmail', 'fvr.status')
                        ->where('customerId','=',$this->companyCode)
                        ->where('l.id','=',$request->location_id)
                        ->where('b.id','=',$request->branch_id)
                        ->where('device_id','=',$request->deviceId)
                        ->orderBy('fvr.id', 'asc');
                        }
                        else if($request->location_id != ""){
                            $query = DB::table('customers as c')
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
                        ->Join('firmware_version_reports as fvr', function($join){
                            $join->on('c.customerId', '=', 'fvr.companyCode')
                                ->on('d.id', '=', 'fvr.device_id');

                        })
                        ->select(DB::raw('DATE_FORMAT(fvr.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(fvr.created_at) as time'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName','fl.floorName','lb.labDepName','fvr.deviceName','fvr.firmwareVersion', 'fvr.userEmail', 'fvr.status')
                        ->where('customerId','=',$this->companyCode)
                        ->where('l.id','=',$request->location_id)
                        ->where('device_id','=',$request->deviceId)
                        ->orderBy('fvr.id', 'asc');
                        }
                         else{
                               $query = DB::table('customers as c')
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
                        ->Join('firmware_version_reports as fvr', function($join){
                            $join->on('c.customerId', '=', 'fvr.companyCode')
                                ->on('d.id', '=', 'fvr.device_id');

                        })
                        ->select(DB::raw('DATE_FORMAT(fvr.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(fvr.created_at) as time'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName', 'fl.floorName','lb.labDepName','fvr.deviceName','fvr.firmwareVersion', 'fvr.userEmail', 'fvr.status')
                        ->where('customerId','=',$this->companyCode)
                        ->where('device_id','=',$request->deviceId)
                        ->orderBy('fvr.id', 'asc');
                         }
                    }else{

                        if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !="" && $request->lab_id !="")
                        {
                      $query = DB::table('customers as c')
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
                        ->Join('firmware_version_reports as fvr', function($join){
                            $join->on('c.customerId', '=', 'fvr.companyCode')
                                ->on('d.id', '=', 'fvr.device_id');

                        })
                        ->select(DB::raw('DATE_FORMAT(fvr.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(fvr.created_at) as time'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName', 'fl.floorName','lb.labDepName','fvr.deviceName','fvr.firmwareVersion', 'fvr.userEmail', 'fvr.status')
                        ->where('customerId','=',$this->companyCode)
                        ->where('l.id','=',$request->location_id)
                        ->where('b.id','=',$request->branch_id)
                        ->where('f.id','=',$request->facility_id)
                        ->where('bl.id','=',$request->building_id)
                        ->where('fl.id','=',$request->floor_id)
                        ->where('lb.id','=',$request->lab_id)
                        ->orderBy('fvr.id', 'asc');
                    }
                       else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !="")
                        {
                            $query = DB::table('customers as c')
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
                        ->Join('firmware_version_reports as fvr', function($join){
                            $join->on('c.customerId', '=', 'fvr.companyCode')
                                ->on('d.id', '=', 'fvr.device_id');

                        })
                    ->select(DB::raw('DATE_FORMAT(fvr.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(fvr.created_at) as time'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName', 'fl.floorName','lb.labDepName','fvr.deviceName','fvr.firmwareVersion', 'fvr.userEmail', 'fvr.status')
                        ->where('customerId','=',$this->companyCode)
                        ->where('l.id','=',$request->location_id)
                        ->where('b.id','=',$request->branch_id)
                        ->where('f.id','=',$request->facility_id)
                        ->where('bl.id','=',$request->building_id)
                        ->where('fl.id','=',$request->floor_id)
                        ->orderBy('fvr.id', 'asc');


                        }
                        else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !=""){
                            $query = DB::table('customers as c')
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
                        ->Join('firmware_version_reports as fvr', function($join){
                            $join->on('c.customerId', '=', 'fvr.companyCode')
                                ->on('d.id', '=', 'fvr.device_id');

                        })
                                ->select(DB::raw('DATE_FORMAT(fvr.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(fvr.created_at) as time'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName', 'fl.floorName','lb.labDepName','fvr.deviceName','fvr.firmwareVersion', 'fvr.userEmail', 'fvr.status')
                        ->where('customerId','=',$this->companyCode)
                        ->where('l.id','=',$request->location_id)
                        ->where('b.id','=',$request->branch_id)
                        ->where('f.id','=',$request->facility_id)
                        ->where('bl.id','=',$request->building_id)
                       ->orderBy('fvr.id', 'asc');

                        }
                        else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""){
                         $query = DB::table('customers as c')
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
                        ->Join('firmware_version_reports as fvr', function($join){
                            $join->on('c.customerId', '=', 'fvr.companyCode')
                                ->on('d.id', '=', 'fvr.device_id');

                        })
                        ->select(DB::raw('DATE_FORMAT(fvr.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(fvr.created_at) as time'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName', 'fl.floorName','lb.labDepName','fvr.deviceName','fvr.firmwareVersion', 'fvr.userEmail', 'fvr.status')
                        ->where('customerId','=',$this->companyCode)
                        ->where('l.id','=',$request->location_id)
                        ->where('b.id','=',$request->branch_id)
                        ->where('f.id','=',$request->facility_id)
                         ->orderBy('fvr.id', 'asc');

                        }
                        else if($request->location_id != "" && $request->branch_id != ""){
                            $query = DB::table('customers as c')
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
                        ->Join('firmware_version_reports as fvr', function($join){
                            $join->on('c.customerId', '=', 'fvr.companyCode')
                                ->on('d.id', '=', 'fvr.device_id');

                        })
                        ->select(DB::raw('DATE_FORMAT(fvr.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(fvr.created_at) as time'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName', 'fl.floorName','lb.labDepName','fvr.deviceName','fvr.firmwareVersion', 'fvr.userEmail', 'fvr.status')
                        ->where('customerId','=',$this->companyCode)
                        ->where('l.id','=',$request->location_id)
                        ->where('b.id','=',$request->branch_id)
                         ->orderBy('fvr.id', 'asc');

                        }
                        else if($request->location_id != ""){
                        $query = DB::table('customers as c')
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
                        ->Join('firmware_version_reports as fvr', function($join){
                            $join->on('c.customerId', '=', 'fvr.companyCode')
                                ->on('d.id', '=', 'fvr.device_id');

                        })
                        ->select(DB::raw('DATE_FORMAT(fvr.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(fvr.created_at) as time'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName', 'fl.floorName','lb.labDepName','fvr.deviceName','fvr.firmwareVersion', 'fvr.userEmail', 'fvr.status')
                        ->where('customerId','=',$this->companyCode)
                        ->where('l.id','=',$request->location_id)
                        ->orderBy('fvr.id', 'asc');

                        }
                         else{
                               $query = DB::table('customers as c')
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
                        ->Join('firmware_version_reports as fvr', function($join){
                            $join->on('c.customerId', '=', 'fvr.companyCode')
                                ->on('d.id', '=', 'fvr.device_id');

                        })
                                ->select(DB::raw('DATE_FORMAT(fvr.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(fvr.created_at) as time'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName', 'fl.floorName','lb.labDepName','fvr.deviceName','fvr.firmwareVersion', 'fvr.userEmail', 'fvr.status')
                        ->where('customerId','=',$this->companyCode)
                        ->orderBy('fvr.id', 'asc');
                         }
                        }



                    // if($startDate === $endDate){
                    //     $query->whereDate('fvr.created_at','=',$startDate);
                    // }
                    // else {
                    //     $query->whereBetween('fvr.created_at', [$startDate, $endDate]);
                    // }


                        return Excel::download(new FirmwareVersionExport($query), 'firmware.xlsx');
                 }





    public function EmailFirmwareVersion(Request $request)
    {


                if( $request->deviceId !=""){

                    if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !="" && $request->lab_id !="")
                        {
                         $query = DB::table('customers as c')
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
                            ->Join('firmware_version_reports as fvr', function($join){
                                $join->on('c.customerId', '=', 'fvr.companyCode')
                                    ->on('d.id', '=', 'fvr.device_id');

                            })
                            ->select(DB::raw('DATE_FORMAT(fvr.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(fvr.created_at) as time'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName','fl.floorName','lb.labDepName','fvr.deviceName','fvr.firmwareVersion', 'fvr.userEmail', 'fvr.status')
                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->where('b.id','=',$request->branch_id)
                            ->where('f.id','=',$request->facility_id)
                            ->where('bl.id','=',$request->building_id)
                            ->where('fl.id','=',$request->floor_id)
                            ->where('lb.id','=',$request->lab_id)
                            ->where('device_id','=',$request->deviceId)
                            ->orderBy('fvr.id', 'asc');
                    }
                       else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !="")
                        {
                          $query = DB::table('customers as c')
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
                        ->Join('firmware_version_reports as fvr', function($join){
                            $join->on('c.customerId', '=', 'fvr.companyCode')
                                ->on('d.id', '=', 'fvr.device_id');

                        })
                        ->select(DB::raw('DATE_FORMAT(fvr.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(fvr.created_at) as time'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName','fl.floorName','lb.labDepName','fvr.deviceName','fvr.firmwareVersion', 'fvr.userEmail', 'fvr.status')
                        ->where('customerId','=',$this->companyCode)
                        ->where('l.id','=',$request->location_id)
                        ->where('b.id','=',$request->branch_id)
                        ->where('f.id','=',$request->facility_id)
                        ->where('bl.id','=',$request->building_id)
                        ->where('fl.id','=',$request->floor_id)
                        ->where('device_id','=',$request->deviceId)
                       ->orderBy('fvr.id', 'asc');
                        }
                        else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !=""){
                            $query = DB::table('customers as c')
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
                        ->Join('firmware_version_reports as fvr', function($join){
                            $join->on('c.customerId', '=', 'fvr.companyCode')
                                ->on('d.id', '=', 'fvr.device_id');

                        })
                        ->select(DB::raw('DATE_FORMAT(fvr.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(fvr.created_at) as time'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName','fl.floorName','lb.labDepName','fvr.deviceName','fvr.firmwareVersion', 'fvr.userEmail', 'fvr.status')
                        ->where('customerId','=',$this->companyCode)
                        ->where('l.id','=',$request->location_id)
                        ->where('b.id','=',$request->branch_id)
                        ->where('f.id','=',$request->facility_id)
                        ->where('bl.id','=',$request->building_id)
                        ->where('device_id','=',$request->deviceId)
                        ->orderBy('fvr.id', 'asc');
                        }
                        else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""){
                             $query = DB::table('customers as c')
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
                        ->Join('firmware_version_reports as fvr', function($join){
                            $join->on('c.customerId', '=', 'fvr.companyCode')
                                ->on('d.id', '=', 'fvr.device_id');

                        })
                        ->select(DB::raw('DATE_FORMAT(fvr.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(fvr.created_at) as time'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName','fl.floorName','lb.labDepName','fvr.deviceName','fvr.firmwareVersion', 'fvr.userEmail', 'fvr.status')
                        ->where('customerId','=',$this->companyCode)
                        ->where('l.id','=',$request->location_id)
                        ->where('b.id','=',$request->branch_id)
                        ->where('f.id','=',$request->facility_id)
                        ->where('device_id','=',$request->deviceId)
                        ->orderBy('fvr.id', 'asc');
                        }
                        else if($request->location_id != "" && $request->branch_id != ""){
                            $query = DB::table('customers as c')
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
                        ->Join('firmware_version_reports as fvr', function($join){
                            $join->on('c.customerId', '=', 'fvr.companyCode')
                                ->on('d.id', '=', 'fvr.device_id');

                        })
                        ->select(DB::raw('DATE_FORMAT(fvr.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(fvr.created_at) as time'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName','fl.floorName','lb.labDepName','fvr.deviceName','fvr.firmwareVersion', 'fvr.userEmail', 'fvr.status')
                        ->where('customerId','=',$this->companyCode)
                        ->where('l.id','=',$request->location_id)
                        ->where('b.id','=',$request->branch_id)
                        ->where('device_id','=',$request->deviceId)
                        ->orderBy('fvr.id', 'asc');
                        }
                        else if($request->location_id != ""){
                            $query = DB::table('customers as c')
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
                        ->Join('firmware_version_reports as fvr', function($join){
                            $join->on('c.customerId', '=', 'fvr.companyCode')
                                ->on('d.id', '=', 'fvr.device_id');

                        })
                        ->select(DB::raw('DATE_FORMAT(fvr.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(fvr.created_at) as time'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName','fl.floorName','lb.labDepName','fvr.deviceName','fvr.firmwareVersion', 'fvr.userEmail', 'fvr.status')
                        ->where('customerId','=',$this->companyCode)
                        ->where('l.id','=',$request->location_id)
                        ->where('device_id','=',$request->deviceId)
                        ->orderBy('fvr.id', 'asc');
                        }
                         else{
                               $query = DB::table('customers as c')
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
                        ->Join('firmware_version_reports as fvr', function($join){
                            $join->on('c.customerId', '=', 'fvr.companyCode')
                                ->on('d.id', '=', 'fvr.device_id');

                        })
                        ->select(DB::raw('DATE_FORMAT(fvr.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(fvr.created_at) as time'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName','fl.floorName','lb.labDepName','fvr.deviceName','fvr.firmwareVersion', 'fvr.userEmail', 'fvr.status')
                        ->where('customerId','=',$this->companyCode)
                        ->where('device_id','=',$request->deviceId)
                        ->orderBy('fvr.id', 'asc');
                         }
                    }else{

                        if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !="" && $request->lab_id !="")
                        {
                      $query = DB::table('customers as c')
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
                        ->Join('firmware_version_reports as fvr', function($join){
                            $join->on('c.customerId', '=', 'fvr.companyCode')
                                ->on('d.id', '=', 'fvr.device_id');

                        })
                        ->select(DB::raw('DATE_FORMAT(fvr.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(fvr.created_at) as time'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName','fl.floorName','lb.labDepName','fvr.deviceName','fvr.firmwareVersion', 'fvr.userEmail', 'fvr.status')
                        ->where('customerId','=',$this->companyCode)
                        ->where('l.id','=',$request->location_id)
                        ->where('b.id','=',$request->branch_id)
                        ->where('f.id','=',$request->facility_id)
                        ->where('bl.id','=',$request->building_id)
                        ->where('fl.id','=',$request->floor_id)
                        ->where('lb.id','=',$request->lab_id)
                        ->orderBy('fvr.id', 'asc');
                    }
                       else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !="")
                        {
                            $query = DB::table('customers as c')
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
                        ->Join('firmware_version_reports as fvr', function($join){
                            $join->on('c.customerId', '=', 'fvr.companyCode')
                                ->on('d.id', '=', 'fvr.device_id');

                        })
                        ->select(DB::raw('DATE_FORMAT(fvr.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(fvr.created_at) as time'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName','fl.floorName','lb.labDepName','fvr.deviceName','fvr.firmwareVersion', 'fvr.userEmail', 'fvr.status')
                        ->where('customerId','=',$this->companyCode)
                        ->where('l.id','=',$request->location_id)
                        ->where('b.id','=',$request->branch_id)
                        ->where('f.id','=',$request->facility_id)
                        ->where('bl.id','=',$request->building_id)
                        ->where('fl.id','=',$request->floor_id)
                        ->orderBy('fvr.id', 'asc');


                        }
                        else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !=""){
                            $query = DB::table('customers as c')
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
                        ->Join('firmware_version_reports as fvr', function($join){
                            $join->on('c.customerId', '=', 'fvr.companyCode')
                                ->on('d.id', '=', 'fvr.device_id');

                        })
                        ->select(DB::raw('DATE_FORMAT(fvr.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(fvr.created_at) as time'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName','fl.floorName','lb.labDepName','fvr.deviceName','fvr.firmwareVersion', 'fvr.userEmail', 'fvr.status')
                        ->where('customerId','=',$this->companyCode)
                        ->where('l.id','=',$request->location_id)
                        ->where('b.id','=',$request->branch_id)
                        ->where('f.id','=',$request->facility_id)
                        ->where('bl.id','=',$request->building_id)
                       ->orderBy('fvr.id', 'asc');

                        }
                        else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""){
                         $query = DB::table('customers as c')
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
                        ->Join('firmware_version_reports as fvr', function($join){
                            $join->on('c.customerId', '=', 'fvr.companyCode')
                                ->on('d.id', '=', 'fvr.device_id');

                        })
                        ->select(DB::raw('DATE_FORMAT(fvr.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(fvr.created_at) as time'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName','fl.floorName','lb.labDepName','fvr.deviceName','fvr.firmwareVersion', 'fvr.userEmail', 'fvr.status')
                        ->where('customerId','=',$this->companyCode)
                        ->where('l.id','=',$request->location_id)
                        ->where('b.id','=',$request->branch_id)
                        ->where('f.id','=',$request->facility_id)
                         ->orderBy('fvr.id', 'asc');

                        }
                        else if($request->location_id != "" && $request->branch_id != ""){
                            $query = DB::table('customers as c')
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
                        ->Join('firmware_version_reports as fvr', function($join){
                            $join->on('c.customerId', '=', 'fvr.companyCode')
                                ->on('d.id', '=', 'fvr.device_id');

                        })
                        ->select(DB::raw('DATE_FORMAT(fvr.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(fvr.created_at) as time'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName','fl.floorName','lb.labDepName','fvr.deviceName','fvr.firmwareVersion', 'fvr.userEmail', 'fvr.status')
                        ->where('customerId','=',$this->companyCode)
                        ->where('l.id','=',$request->location_id)
                        ->where('b.id','=',$request->branch_id)
                         ->orderBy('fvr.id', 'asc');

                        }
                        else if($request->location_id != ""){
                        $query = DB::table('customers as c')
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
                        ->Join('firmware_version_reports as fvr', function($join){
                            $join->on('c.customerId', '=', 'fvr.companyCode')
                                ->on('d.id', '=', 'fvr.device_id');

                        })
                        ->select(DB::raw('DATE_FORMAT(fvr.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(fvr.created_at) as time'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName','fl.floorName','lb.labDepName','fvr.deviceName','fvr.firmwareVersion', 'fvr.userEmail', 'fvr.status')
                        ->where('customerId','=',$this->companyCode)
                        ->where('l.id','=',$request->location_id)
                        ->orderBy('fvr.id', 'asc');

                        }
                         else{
                               $query = DB::table('customers as c')
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
                        ->Join('firmware_version_reports as fvr', function($join){
                            $join->on('c.customerId', '=', 'fvr.companyCode')
                                ->on('d.id', '=', 'fvr.device_id');

                        })
                        ->select(DB::raw('DATE_FORMAT(fvr.created_at, "%d-%b-%Y") as date'),DB::raw('TIME(fvr.created_at) as time'),'l.stateName', 'b.branchName', 'f.facilityName', 'bl.buildingName','fl.floorName','lb.labDepName','fvr.deviceName','fvr.firmwareVersion', 'fvr.userEmail', 'fvr.status')
                        ->where('customerId','=',$this->companyCode)
                        ->orderBy('fvr.id', 'asc');
                         }
                        }


                    $attachment =  Excel::raw(new FirmwareVersionExport($query), BaseExcel::XLSX);

                    $url = env('APPLICATION_URL');
                    $email = $request->header('Userid');
                    $data = [
                        'meassage' => 'Firmware Version Reports',
                        'url' => $url
                    ];

                    Mail::send('FirmwareVersion',$data, function($messages) use ($email,$attachment){
                        $messages->to($email);
                        $messages->subject('Firmware Version Reports');
                        $messages->attachData($attachment, 'firmware.xlsx',[
                             ]);
                    });

                       $response = [
                            "message"=>"Reports data sent Successfully",
                            "data" => $query
                        ];

                        $status = 200;

                     return response($response, $status);

                 }




        public function serverUsageReport(Request $request)
            {

                 $startDate = date("Y-m-d",strtotime($request->fromDate));
                 $endDate = date("Y-m-d", strtotime($request->toDate));

                    $query = DB::table('server_usage_statitics');

                    if($startDate === $endDate){
                        $query->whereDate('date','=',$startDate);
                    }
                    else {
                        $query->whereBetween('date', [$startDate, $endDate]);
                    }

                    $getData = new ReportsDataUtilityController($request,$query);

                    $response = [
                                    "data"=>$getData->getData()
                              ];
                    $status = 200;
                    return response($response,$status);
            }

    // download server utilization
    public function ServerUtilizationExport(Request $request)
    {
        $startDate = date("Y-m-d",strtotime($request->fromDate));
        $endDate = date("Y-m-d", strtotime($request->toDate));

        $query = DB::table('server_usage_statitics')
                    ->select(DB::raw('DATE_FORMAT(date, "%d-%m-%Y") as date'),'time','perc_memory_usage','disk_usage','avg_cpu_load');

        if($startDate === $endDate){
            $query->whereDate('date','=',$startDate);
        }
        else {
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        return Excel::download(new serverUtilizationReportExport($query), 'serverUtiliExport.xlsx');
    }

    public function EmailServerUtilization(Request $request)
    {
        $startDate = date("Y-m-d",strtotime($request->fromDate));
        $endDate = date("Y-m-d", strtotime($request->toDate));

        $query = DB::table('server_usage_statitics')
                ->select(DB::raw('DATE_FORMAT(date, "%d-%m-%Y") as date'),'time','perc_memory_usage','disk_usage','avg_cpu_load');

        if($startDate === $endDate){
            $query->whereDate('date','=',$startDate);
        }
        else {
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        $attachment =  Excel::raw(new serverUtilizationReportExport($query), BaseExcel::XLSX);

        //$email = "developer2@rdltech.in";
        // $userEmail = $this->userId;
        // $email = $this->fetchVerifiedEmailUsers($userEmail);

        // $data = [
        //     'userid'=>$email,
        //     'body' =>"Server Utilization Reports"
        // ];

        $url = env('APPLICATION_URL');
        $email = $request->header('Userid');
        $data = [
            'meassage' => 'Server Utilization',
            'url' => $url
        ];

        Mail::send('ServerUtilization', $data, function($messages) use ($email,$attachment){
            $messages->to($email);
            $messages->subject('Server Utilization Reports');
            $messages->attachData($attachment, 'serverUtiliExport.xlsx',[
            ]);
        });

        $response = [
            "message"=>"Reports data sent Successfully"
        ];
        $status = 200;

        return response($response, $status);
    }



    // report sensor status
        public function SensorStatusReport(Request $request){

            $deviceId = $request->deviceId;

            $sensorTagList = DB::table('sensors')
                            ->select('sensorTag', 'id')
                            ->where('deviceId','=',$deviceId)
                            ->get();

            $gassCollectionData = array();
            $datas = array();
            $data = array();
            $sensorList = array();
            $sensorIdList = array();

            $fromDate = date("Y-m-d",strtotime($request->input(key:'fromDate')));
            $toDate = date("Y-m-d", strtotime($request->input(key:'toDate')));

            // $fromDate = "2022-10-10";
            // $toDate = "2022-10-14";

            function getDatesFromRange($start, $end, $format = 'Y-m-d') {
                $array = array();
                $interval = new DateInterval('P1D');

                $realEnd = new DateTime($end);
                $realEnd->add($interval);

                $period = new DatePeriod(new DateTime($start), $interval, $realEnd);

                foreach($period as $date) {
                    $array[] = $date->format($format);
                }

                return $array;
            }

            $dates = getDatesFromRange($fromDate, $toDate);

            // filter data
             $arrlength2 = count($dates);

            $filterDate =array();

            for($x=0;$x<$arrlength2;$x++) {
                    	    $dateQuery = DB::table('sampled_sensor_data_details_MinMaxAvg')
                    	                  ->select('current_date_time')
                                          ->where ('device_id','=',$deviceId)
                                          ->whereDate('current_date_time','=',$dates[$x])
                                          ->first();
                            if($dateQuery){

                              $filterDate[] = date("Y-m-d",strtotime($dateQuery->current_date_time));
                            }
              }



            // end filter data
            //sensor List as gasscollection
            foreach($sensorTagList as $sensorTag => $tagValue) {
                $sensorList[] = $tagValue->sensorTag;
                $sensorIdList[] = $tagValue->id;
            }

            $datas["gasCollection"] = $sensorList; //sensorList

            $results = array("min","max","avg","status");
            // $dates = array("2022-10-14","2022-10-11");
            $arrlength = count($filterDate);

            for($x=0;$x<$arrlength;$x++) {
              	$resultCount = count($results);
                for($j=0;$j<$resultCount;$j++){

                    $sensorCnt = count($sensorIdList);
                    for($y=0;$y<$sensorCnt;$y++){

                    	if($results[$j] == "min"){
                    	    $dateQuery = DB::table('sampled_sensor_data_details_MinMaxAvg')
                                          ->selectRaw('MIN(avg_val) as minVal')
                                          ->where('sensor_id','=',$sensorIdList[$y])
                                          ->where ('device_id','=',$deviceId)
                                          ->whereDate('current_date_time','=',$filterDate[$x])
                                          //->groupBy(DB::raw('CAST(sample_date_time AS DATE)'))
                                          ->get();
                            $minVAL = $dateQuery[0]->minVal;

                            if(!is_null($minVAL)){
                                $data[$filterDate[$x]][$results[$j]][] = number_format($minVAL,2);
                            }else{
                                $data[$filterDate[$x]][$results[$j]][] = "NA";
                            }
                        }

                        if($results[$j] == "max"){
                        	$dateQuery = DB::table('sampled_sensor_data_details_MinMaxAvg')
                                          ->selectRaw('MAX(avg_val) as maxVal')
                                          ->where('sensor_id','=',$sensorIdList[$y])
                                          ->where ('device_id','=',$deviceId)
                                          ->whereDate('current_date_time','=',$filterDate[$x])
                                          //->groupBy(DB::raw('CAST(sample_date_time AS DATE)'))
                                          ->get();
                            $maxVAL = $dateQuery[0]->maxVal;

                            if(!is_null($maxVAL)){
                                $data[$filterDate[$x]][$results[$j]][] = number_format($maxVAL,2);
                            }else{
                                $data[$filterDate[$x]][$results[$j]][] = "NA";
                            }
                        }
                        if($results[$j] == "avg"){
                        	$dateQuery = DB::table('sampled_sensor_data_details_MinMaxAvg')
                                          ->selectRaw('AVG(avg_val) as avgVal')
                                          ->where('sensor_id','=',$sensorIdList[$y])
                                          ->where ('device_id','=',$deviceId)
                                          ->whereDate('current_date_time','=',$filterDate[$x])
                                          //->groupBy(DB::raw('CAST(sample_date_time AS DATE)'))
                                          ->get();
                            $avgVAL = $dateQuery[0]->avgVal;

                            if(!is_null($avgVAL)){
                                $data[$filterDate[$x]][$results[$j]][] = number_format($avgVAL,2);
                            }else{
                                $data[$filterDate[$x]][$results[$j]][] = "NA";
                            }
                        }
                         if($results[$j] == "status"){

                            $dateQuery = DB::table('sampled_sensor_data_details_MinMaxAvg')
                                          ->selectRaw('distinct(alertType)')
                                          ->where('sensor_id','=',$sensorIdList[$y])
                                          ->where ('device_id','=',$deviceId)
                                          ->whereDate('current_date_time','=',$filterDate[$x])
                                          ->get();

                            if($dateQuery == null){
                                $status = [
                                   "alertType"=>null,
                                   "alertColor"=>null
                               ];
                                $data[$filterDate[$x]][$results[$j]][] = $status;

                            }else{
                                $alertList = array();
                                $cnt = count($dateQuery);

                                //Warning, outOfRange, NORMAL, Critical
                                for($i=0;$i<$cnt;$i++){
                                    if($dateQuery[$i]->alertType === "Warning"){
                                        $alertList["W"] = "Warning";
                                    }

                                    if($dateQuery[$i]->alertType === "outOfRange"){
                                        $alertList["O"] = "Out Of Range";
                                    }

                                    if($dateQuery[$i]->alertType === "NORMAL"){
                                        $alertList["N"] = "Normal";
                                    }

                                    if($dateQuery[$i]->alertType === "Critical"){
                                        $alertList["C"] = "Critical";
                                    }
                                }

                                $alert = array();
                                if (array_key_exists("C",$alertList))
                                {
                                  	$alert["alertType"] =  $alertList["C"];
                                  	$alert["alertColor"] =  "red";
                                }
                                else if (array_key_exists("W",$alertList))
                                {
                                  	$alert["alertType"] =  $alertList["W"];
                                  	$alert["alertColor"] = "#FFBF00";
                                }
                                else if (array_key_exists("O",$alertList))
                                {
                                  	$alert["alertType"] =  $alertList["O"];
                                  	$alert["alertColor"] = "Purple";

                                }
                                else if (array_key_exists("N",$alertList))
                                {
                                  	$alert["alertType"] =  $alertList["N"];
                                  	$alert["alertColor"] = "green";
                                }
                                else
                                {
                                  	$alert["alertType"] =  "NA";
                                  	$alert["alertColor"] = "NA";
                                }

                                $data[$filterDate[$x]][$results[$j]][] = $alert;
                            }






                        }
                    }
                }
            }

            $response = [
                          "headerItem"=>$datas,
                          "data"=>$data,
                          "date"=>$dates,
                          "filterData"=>$filterDate
            ];
            $status = 200;
            return response($response,$status);
        }


//export sensorStatusReport
/*
    public function sensorStatusReportExport(Request $request){

            $deviceId = $request->deviceId;
            $sensorTagList = DB::table('sensors')
                            ->select('sensorTag', 'id')
                            ->where('deviceId','=',$deviceId)
                            ->get();

            $gassCollectionData = array();
            $datas = array();
            $data = array();
            $sensorList = array();
            $sensorIdList = array();

            $fromDate = date("Y-m-d",strtotime($request->input(key:'fromDate')));
            $toDate = date("Y-m-d", strtotime($request->input(key:'toDate')));

            // $fromDate = "2022-10-10";
            // $toDate = "2022-10-14";

            function getDatesFromRange($start, $end, $format = 'Y-m-d') {
                $array = array();
                $interval = new DateInterval('P1D');

                $realEnd = new DateTime($end);
                $realEnd->add($interval);

                $period = new DatePeriod(new DateTime($start), $interval, $realEnd);

                foreach($period as $date) {
                    $array[] = $date->format($format);
                }
                return $array;
            }


            $dates = getDatesFromRange($fromDate, $toDate);

            //sensor List as gasscollection
            foreach($sensorTagList as $sensorTag => $tagValue) {
                $sensorList[] = $tagValue->sensorTag;
                $sensorIdList[] = $tagValue->id;
            }

            $datas["gasCollection"] = $sensorList; //sensorList

            $results = array("min","max","avg","status");
            // $dates = array("2022-10-14","2022-10-11");
            $arrlength = count($dates);

            for($x=0;$x<$arrlength;$x++) {
              	$resultCount = count($results);
                for($j=0;$j<$resultCount;$j++){

                    $sensorCnt = count($sensorIdList);
                    for($y=0;$y<$sensorCnt;$y++){

                    	if($results[$j] == "min"){
                    	    $dateQuery = DB::table('sampled_sensor_data_details_MinMaxAvg')
                                          ->selectRaw('MIN(avg_val) as minVal')
                                          ->where('sensor_id','=',$sensorIdList[$y])
                                          ->where ('device_id','=',$deviceId)
                                          ->whereDate('current_date_time','=',$dates[$x])
                                          //->groupBy(DB::raw('CAST(sample_date_time AS DATE)'))
                                          ->get();
                            $minVAL = $dateQuery[0]->minVal;

                            if($minVAL!=null){
                                $data[$dates[$x]][$results[$j]][] = number_format($minVAL,2);
                            }else{
                                $data[$dates[$x]][$results[$j]][] = "NA";
                            }
                        }

                        if($results[$j] == "max"){
                        	$dateQuery = DB::table('sampled_sensor_data_details_MinMaxAvg')
                                          ->selectRaw('MAX(avg_val) as maxVal')
                                          ->where('sensor_id','=',$sensorIdList[$y])
                                          ->where ('device_id','=',$deviceId)
                                          ->whereDate('current_date_time','=',$dates[$x])
                                          //->groupBy(DB::raw('CAST(sample_date_time AS DATE)'))
                                          ->get();
                            $maxVAL = $dateQuery[0]->maxVal;

                            if($maxVAL!=null){
                                $data[$dates[$x]][$results[$j]][] = number_format($maxVAL,2);
                            }else{
                                $data[$dates[$x]][$results[$j]][] = "NA";
                            }
                        }
                        if($results[$j] == "avg"){
                        	$dateQuery = DB::table('sampled_sensor_data_details_MinMaxAvg')
                                          ->selectRaw('AVG(avg_val) as avgVal')
                                          ->where('sensor_id','=',$sensorIdList[$y])
                                          ->where ('device_id','=',$deviceId)
                                          ->whereDate('current_date_time','=',$dates[$x])
                                          //->groupBy(DB::raw('CAST(sample_date_time AS DATE)'))
                                          ->get();
                            $avgVAL = $dateQuery[0]->avgVal;

                            if($avgVAL!=null){
                                $data[$dates[$x]][$results[$j]][] = number_format($avgVAL,2);
                            }else{
                                $data[$dates[$x]][$results[$j]][] = "NA";
                            }
                        }
                         if($results[$j] == "status"){

                            $dateQuery = DB::table('sampled_sensor_data_details_MinMaxAvg')
                                          ->selectRaw('distinct(alertType)')
                                          ->where('sensor_id','=',$sensorIdList[$y])
                                          ->where ('device_id','=',$deviceId)
                                          ->whereDate('current_date_time','=',$dates[$x])
                                          ->get();

                            if($dateQuery == null){
                                $status = [
                                  "alertType"=>null,
                                  "alertColor"=>null
                              ];
                                $data[$dates[$x]][$results[$j]][] = $status;

                            }else{
                                $alertList = array();
                                $cnt = count($dateQuery);

                                //Warning, outOfRange, NORMAL, Critical
                                for($i=0;$i<$cnt;$i++){
                                    if($dateQuery[$i]->alertType === "Warning"){
                                        $alertList["W"] = "Warning";
                                    }

                                    if($dateQuery[$i]->alertType === "outOfRange"){
                                        $alertList["O"] = "outOfRange";
                                    }

                                    if($dateQuery[$i]->alertType === "NORMAL"){
                                        $alertList["N"] = "NORMAL";
                                    }

                                    if($dateQuery[$i]->alertType === "Critical"){
                                        $alertList["C"] = "Critical";
                                    }
                                }

                                $alert = array();
                                if (array_key_exists("C",$alertList))
                                {
                                  	$alert["alertType"] =  $alertList["C"];
                                //   	$alert["alertColor"] =  "red";
                                }
                                else if (array_key_exists("W",$alertList))
                                {
                                  	$alert["alertType"] =  $alertList["W"];
                                //   	$alert["alertColor"] = "#FFBF00";
                                }
                                else if (array_key_exists("O",$alertList))
                                {
                                  	$alert["alertType"] =  $alertList["O"];
                                //   	$alert["alertColor"] = "Purple";

                                }
                                else if (array_key_exists("N",$alertList))
                                {
                                  	$alert["alertType"] =  $alertList["N"];
                                //   	$alert["alertColor"] = "green";
                                }
                                else
                                {
                                  	$alert["alertType"] =  "NA";
                                //   	$alert["alertColor"] = "NA";
                                }

                                $data[$dates[$x]][$results[$j]][] = $alert["alertType"];
                            }
                        }
                    }
                }
            }

            $query =   [
                          "headerItem"=>$datas,
                          "data"=>$data,
                          "dateSet"=>$dates
                      ];

            //  $response = [
            //   "headerItem"=>$datas,
            //   "data"=>$data,
            // ];
            // $status = 200;
            // return response($query,$status);

          return Excel::download(new SensorStatusReportExport($query), 'sensorStatusReportExport.xlsx');

        }
    */




    public function sensorStatusReportExport(Request $request)
    {
        $deviceId = $request->deviceId;
        $sensorTagList = DB::table('sensors')
                        ->select('sensorTag', 'id')
                        ->where('deviceId','=',$deviceId)
                        ->get();

        $gassCollectionData = array();
        $datas = array();
        $data = array();
        $sensorList = array();
        $sensorIdList = array();

        $fromDate = date("Y-m-d",strtotime($request->input(key:'fromDate')));
        $toDate = date("Y-m-d", strtotime($request->input(key:'toDate')));

        // $fromDate = "2022-10-10";
        // $toDate = "2022-10-14";

        function getDatesFromRange($start, $end, $format = 'Y-m-d') {
            $array = array();
            $interval = new DateInterval('P1D');

            $realEnd = new DateTime($end);
            $realEnd->add($interval);

            $period = new DatePeriod(new DateTime($start), $interval, $realEnd);

            foreach($period as $date) {
                $array[] = $date->format($format);
            }
            return $array;
        }


        $dates = getDatesFromRange($fromDate, $toDate);

            $arrlength2 = count($dates);

        $filterDate =array();

        for($x=0;$x<$arrlength2;$x++) {
                        $dateQuery = DB::table('sampled_sensor_data_details_MinMaxAvg')
                                        ->select('current_date_time')
                                        ->where ('device_id','=',$deviceId)
                                        ->whereDate('current_date_time','=',$dates[$x])
                                        ->first();
                        if($dateQuery){

                            $filterDate[] = date("Y-m-d",strtotime($dateQuery->current_date_time));
                        }
            }


        //sensor List as gasscollection
        foreach($sensorTagList as $sensorTag => $tagValue) {
            $sensorList[] = $tagValue->sensorTag;
            $sensorIdList[] = $tagValue->id;
        }

        $datas["gasCollection"] = $sensorList; //sensorList

        $results = array("min","max","avg","status");
        // $dates = array("2022-10-14","2022-10-11");
        $arrlength = count($filterDate);

        for($x=0;$x<$arrlength;$x++) {
            $resultCount = count($results);
            for($j=0;$j<$resultCount;$j++){

                $sensorCnt = count($sensorIdList);
                for($y=0;$y<$sensorCnt;$y++){

                    if($results[$j] == "min"){
                        $dateQuery = DB::table('sampled_sensor_data_details_MinMaxAvg')
                                        ->selectRaw('MIN(avg_val) as minVal')
                                        ->where('sensor_id','=',$sensorIdList[$y])
                                        ->where ('device_id','=',$deviceId)
                                        ->whereDate('current_date_time','=',$filterDate[$x])
                                        //->groupBy(DB::raw('CAST(sample_date_time AS DATE)'))
                                        ->get();
                        $minVAL = $dateQuery[0]->minVal;
                        if($y == 0){
                            $data[$x][$results[$j]][] = " ";
                            $data[$x][$results[$j]][] = "MIN";
                        }
                        if($minVAL!=null){
                            $data[$x][$results[$j]][] = number_format($minVAL,2);
                        }else{
                            $data[$x][$results[$j]][] = "NA";
                        }
                    }

                    if($results[$j] == "max"){
                        $dateQuery = DB::table('sampled_sensor_data_details_MinMaxAvg')
                                        ->selectRaw('MAX(avg_val) as maxVal')
                                        ->where('sensor_id','=',$sensorIdList[$y])
                                        ->where ('device_id','=',$deviceId)
                                        ->whereDate('current_date_time','=',$filterDate[$x])
                                        //->groupBy(DB::raw('CAST(sample_date_time AS DATE)'))
                                        ->get();
                        $maxVAL = $dateQuery[0]->maxVal;

                        if($y == 0){
                            $data[$x][$results[$j]][] = " ";
                            $data[$x][$results[$j]][] ="MAX";
                        }

                        if($maxVAL!=null){
                            $data[$x][$results[$j]][] = number_format($maxVAL,2);

                        }else{
                            $data[$x][$results[$j]][] = "NA";
                        }
                    }
                    if($results[$j] == "avg"){
                        $dateQuery = DB::table('sampled_sensor_data_details_MinMaxAvg')
                                        ->selectRaw('AVG(avg_val) as avgVal')
                                        ->where('sensor_id','=',$sensorIdList[$y])
                                        ->where ('device_id','=',$deviceId)
                                        ->whereDate('current_date_time','=',$filterDate[$x])
                                        //->groupBy(DB::raw('CAST(sample_date_time AS DATE)'))
                                        ->get();
                        $avgVAL = $dateQuery[0]->avgVal;

                            if($y == 0){
                            $data[$x][$results[$j]][] = " ";
                            $data[$x][$results[$j]][] ="AVG";
                        }

                        if($avgVAL!=null){
                            $data[$x][$results[$j]][] = number_format($avgVAL,2);
                        }else{
                            $data[$x][$results[$j]][] = "NA";
                        }
                    }
                        if($results[$j] == "status"){

                        $dateQuery = DB::table('sampled_sensor_data_details_MinMaxAvg')
                                        ->selectRaw('distinct(alertType)')
                                        ->where('sensor_id','=',$sensorIdList[$y])
                                        ->where ('device_id','=',$deviceId)
                                        ->whereDate('current_date_time','=',$filterDate[$x])
                                        ->get();

                        if($dateQuery == null){
                            $status = [
                                "alertType"=>null,
                                "alertColor"=>null
                            ];
                            $data[$filterDate[$x]][$results[$j]][] = $status;

                        }else{
                            $alertList = array();
                            $cnt = count($dateQuery);

                            //Warning, outOfRange, NORMAL, Critical
                            for($i=0;$i<$cnt;$i++){
                                if($dateQuery[$i]->alertType === "Warning"){
                                    $alertList["W"] = "Warning";
                                }

                                if($dateQuery[$i]->alertType === "outOfRange"){
                                    $alertList["O"] = "Out Of Range";
                                }

                                if($dateQuery[$i]->alertType === "NORMAL"){
                                    $alertList["N"] = "Normal";
                                }

                                if($dateQuery[$i]->alertType === "Critical"){
                                    $alertList["C"] = "Critical";
                                }
                            }

                            $alert = array();
                            if (array_key_exists("C",$alertList))
                            {
                                $alert["alertType"] =  $alertList["C"];
                            //   	$alert["alertColor"] =  "red";
                            }
                            else if (array_key_exists("W",$alertList))
                            {
                                $alert["alertType"] =  $alertList["W"];
                            //   	$alert["alertColor"] = "#FFBF00";
                            }
                            else if (array_key_exists("O",$alertList))
                            {
                                $alert["alertType"] =  $alertList["O"];
                            //   	$alert["alertColor"] = "Purple";

                            }
                            else if (array_key_exists("N",$alertList))
                            {
                                $alert["alertType"] =  $alertList["N"];
                            //   	$alert["alertColor"] = "green";
                            }
                            else
                            {
                                $alert["alertType"] =  "NA";
                            //   	$alert["alertColor"] = "NA";
                            }
                                if($y == 0){
                                $data[$x][$results[$j]][] = " ";
                                $data[$x][$results[$j]][] ="STATUS";
                            }

                            $data[$x][$results[$j]][] = $alert["alertType"];
                        }
                    }
                }
            }
        }

        $data_temp=array();

        for($i=0;$i<(count($data));$i++)
        {

            $date_data=array();


                $date_data[0]=$filterDate[$i];

                for($k=1;$k<count($data[$i][$results[0]]);$k++)
                {
                    $date_data[$k]="";
                }

                $data_temp[$i*5] = $date_data;

            for($j=1;$j<5;$j++)
            {
                $data_temp[$i*5+$j]= $data[$i][$results[$j-1]];
            }
        }


        $query =   [
            "headerItem"=>$datas,
            "data"=>$data_temp,
            "dateSet"=>$filterDate
        ];

        $locationName = DB::table('locations')->where('id',$request->location_id)->get();
        $branchName = DB::table('branches')->where('id',$request->branch_id)->get();
        $facilityName = DB::table('facilities')->where('id',$request->facility_id)->get();
        $buildingName = DB::table('buildings')->where('id',$request->building_id)->get();
        $floorName = DB::table('floors')->where('id',$request->floor_id)->get();
        $labDepName = DB::table('lab_departments')->where('id',$request->lab_id)->get();
        $deviceName = DB::table('devices')->where('id',$request->deviceId)->get();

        if($deviceName){
            $locationName = DB::table('devices')->where('devices.id',$request->deviceId)
                    ->join('locations','locations.id','=','devices.location_id')
                    ->join('branches','branches.id','=','devices.branch_id')
                    ->join('facilities','facilities.id','=','devices.facility_id')
                    ->join('buildings','buildings.id','=','devices.building_id')
                    ->join('floors','floors.id','=','devices.floor_id')
                    ->join('lab_departments','lab_departments.id','=','devices.lab_id')
                    ->select('locations.stateName as location','branches.branchName as branch','facilities.facilityName as facility','buildings.buildingName as building',
                        'floors.floorName as floor','lab_departments.labDepName as zone','devices.deviceName as device')
                    ->first();

                $locationDetail = [
                    'location' => $locationName->location,
                    'branch' => $locationName->branch,
                    'facility' => $locationName->facility,
                    'building' => $locationName->building,
                    'floor' => $locationName->floor,
                    'zone' => $locationName->zone,
                    'device' => $locationName->device
                ];

        }else{
            if(count($locationName)>0){
                $locationName = DB::table('locations')->where('id',$request->location_id)->first();
                $locationDetail['location'] = $locationName->stateName;
            }else{
                $locationDetail['location'] = 'NA';
            }

            if(count($branchName)>0){
                $branchName = DB::table('branches')->where('id',$request->branch_id)->first();
                $locationDetail['branch'] = $branchName->branchName;
            }else{
                $locationDetail['branch'] = 'NA';
            }

            if(count($facilityName)>0){
                $facilityName = DB::table('facilities')->where('id',$request->facility_id)->first();
                $locationDetail['facility'] = $facilityName->facilityName;
            }else{
                $locationDetail['facility'] = 'NA';
            }

            if(count($buildingName)>0){
                $buildingName = DB::table('buildings')->where('id',$request->building_id)->first();
                $locationDetail['building'] = $buildingName->buildingName;
            }else{
                $locationDetail['building'] = 'NA';
            }

            if(count($floorName)>0){
                $floorName = DB::table('floors')->where('id',$request->floor_id)->first();
                $locationDetail['floor'] = $floorName->floorName;
            }else{
                $locationDetail['floor'] = 'NA';
            }

            if(count($labDepName)>0){
                $labDepName = DB::table('lab_departments')->where('id',$request->lab_id)->first();
                $locationDetail['zone'] = $labDepName->labDepName;
            }else{
                $locationDetail['zone'] = 'NA';
            }

            if(count($deviceName)>0){
                $deviceName = DB::table('devices')->where('id',$request->deviceId)->first();
                $locationDetail['device'] = $deviceName->deviceName;
            }else{
                $locationDetail['device'] = 'NA';
            }
        }

        // $locationDetail = [
        //     "location" => "Kerala",
        //     "branch" => "Kochi",
        //     "facility" => "Kochi Fort",
        //     "building" => "Building 1",
        //     "floor" => "Floor1",
        //     "zone" => "Chem lab"
        // ];

       return Excel::download(new SensorStatusReportExport($query, $locationDetail), 'sensorStatusReportExport.xlsx');
    }




    public function EmailsensorStatusReport(Request $request)
    {
        $deviceId = $request->deviceId;
        $sensorTagList = DB::table('sensors')
                        ->select('sensorTag', 'id')
                        ->where('deviceId','=',$deviceId)
                        ->get();

        $gassCollectionData = array();
        $datas = array();
        $data = array();
        $sensorList = array();
        $sensorIdList = array();

        $fromDate = date("Y-m-d",strtotime($request->input(key:'fromDate')));
        $toDate = date("Y-m-d", strtotime($request->input(key:'toDate')));

        // $fromDate = "2022-10-10";
        // $toDate = "2022-10-14";

        function getDatesFromRange($start, $end, $format = 'Y-m-d') {
            $array = array();
            $interval = new DateInterval('P1D');

            $realEnd = new DateTime($end);
            $realEnd->add($interval);

            $period = new DatePeriod(new DateTime($start), $interval, $realEnd);

            foreach($period as $date) {
                $array[] = $date->format($format);
            }
            return $array;
        }


        $dates = getDatesFromRange($fromDate, $toDate);

        $arrlength2 = count($dates);

        $filterDate =array();

        for($x=0;$x<$arrlength2;$x++) {
                        $dateQuery = DB::table('sampled_sensor_data_details_MinMaxAvg')
                                        ->select('current_date_time')
                                        ->where ('device_id','=',$deviceId)
                                        ->whereDate('current_date_time','=',$dates[$x])
                                        ->first();
                        if($dateQuery){

                            $filterDate[] = date("Y-m-d",strtotime($dateQuery->current_date_time));
                        }
            }




        //sensor List as gasscollection
        foreach($sensorTagList as $sensorTag => $tagValue) {
            $sensorList[] = $tagValue->sensorTag;
            $sensorIdList[] = $tagValue->id;
        }

        $datas["gasCollection"] = $sensorList; //sensorList

        $results = array("min","max","avg","status");
        // $dates = array("2022-10-14","2022-10-11");
        $arrlength = count($filterDate);

        for($x=0;$x<$arrlength;$x++) {
            $resultCount = count($results);
            for($j=0;$j<$resultCount;$j++){

                $sensorCnt = count($sensorIdList);
                for($y=0;$y<$sensorCnt;$y++){

                    if($results[$j] == "min"){
                        $dateQuery = DB::table('sampled_sensor_data_details_MinMaxAvg')
                                        ->selectRaw('MIN(avg_val) as minVal')
                                        ->where('sensor_id','=',$sensorIdList[$y])
                                        ->where ('device_id','=',$deviceId)
                                        ->whereDate('current_date_time','=',$filterDate[$x])
                                        //->groupBy(DB::raw('CAST(sample_date_time AS DATE)'))
                                        ->get();
                        $minVAL = $dateQuery[0]->minVal;

                        if($minVAL!=null){
                            $data[$x][$results[$j]][] = number_format($minVAL,2);
                        }else{
                            $data[$x][$results[$j]][] = "NA";
                        }
                    }

                    if($results[$j] == "max"){
                        $dateQuery = DB::table('sampled_sensor_data_details_MinMaxAvg')
                                        ->selectRaw('MAX(avg_val) as maxVal')
                                        ->where('sensor_id','=',$sensorIdList[$y])
                                        ->where ('device_id','=',$deviceId)
                                        ->whereDate('current_date_time','=',$filterDate[$x])
                                        //->groupBy(DB::raw('CAST(sample_date_time AS DATE)'))
                                        ->get();
                        $maxVAL = $dateQuery[0]->maxVal;

                        if($maxVAL!=null){
                            $data[$x][$results[$j]][] = number_format($maxVAL,2);
                        }else{
                            $data[$x][$results[$j]][] = "NA";
                        }
                    }
                    if($results[$j] == "avg"){
                        $dateQuery = DB::table('sampled_sensor_data_details_MinMaxAvg')
                                        ->selectRaw('AVG(avg_val) as avgVal')
                                        ->where('sensor_id','=',$sensorIdList[$y])
                                        ->where ('device_id','=',$deviceId)
                                        ->whereDate('current_date_time','=',$filterDate[$x])
                                        //->groupBy(DB::raw('CAST(sample_date_time AS DATE)'))
                                        ->get();
                        $avgVAL = $dateQuery[0]->avgVal;

                        if($avgVAL!=null){
                            $data[$x][$results[$j]][] = number_format($avgVAL,2);
                        }else{
                            $data[$x][$results[$j]][] = "NA";
                        }
                    }
                        if($results[$j] == "status"){

                        $dateQuery = DB::table('sampled_sensor_data_details_MinMaxAvg')
                                        ->selectRaw('distinct(alertType)')
                                        ->where('sensor_id','=',$sensorIdList[$y])
                                        ->where ('device_id','=',$deviceId)
                                        ->whereDate('current_date_time','=',$filterDate[$x])
                                        ->get();

                        if($dateQuery == null){
                            $status = [
                                "alertType"=>null,
                                "alertColor"=>null
                            ];
                            $data[$filterDate[$x]][$results[$j]][] = $status;

                        }else{
                            $alertList = array();
                            $cnt = count($dateQuery);

                            //Warning, outOfRange, NORMAL, Critical
                            for($i=0;$i<$cnt;$i++){
                                if($dateQuery[$i]->alertType === "Warning"){
                                    $alertList["W"] = "Warning";
                                }

                                if($dateQuery[$i]->alertType === "outOfRange"){
                                    $alertList["O"] = "Out Of Range";
                                }

                                if($dateQuery[$i]->alertType === "NORMAL"){
                                    $alertList["N"] = "Normal";
                                }

                                if($dateQuery[$i]->alertType === "Critical"){
                                    $alertList["C"] = "Critical";
                                }
                            }

                            $alert = array();
                            if (array_key_exists("C",$alertList))
                            {
                                $alert["alertType"] =  $alertList["C"];
                            //   	$alert["alertColor"] =  "red";
                            }
                            else if (array_key_exists("W",$alertList))
                            {
                                $alert["alertType"] =  $alertList["W"];
                            //   	$alert["alertColor"] = "#FFBF00";
                            }
                            else if (array_key_exists("O",$alertList))
                            {
                                $alert["alertType"] =  $alertList["O"];
                            //   	$alert["alertColor"] = "Purple";

                            }
                            else if (array_key_exists("N",$alertList))
                            {
                                $alert["alertType"] =  $alertList["N"];
                            //   	$alert["alertColor"] = "green";
                            }
                            else
                            {
                                $alert["alertType"] =  "NA";
                            //   	$alert["alertColor"] = "NA";
                            }

                            $data[$x][$results[$j]][] = $alert["alertType"];
                        }
                    }
                }
            }
        }


        $data_temp=array();

        for($i=0;$i<(count($data));$i++)
        {

            $date_data=array();


                $date_data[0]=$filterDate[$i];

                for($k=1;$k<count($data[$i][$results[0]]);$k++)
                {
                    $date_data[$k]="";
                }


                $data_temp[$i*5]= $date_data;

            for($j=1;$j<5;$j++)
            {
                $data_temp[$i*5+$j]= $data[$i][$results[$j-1]];
            }
        }

        $query = [
            "headerItem"=>$datas,
            "data"=>$data_temp,
            "dateSet"=>$filterDate
        ];

        $locationName = DB::table('locations')->where('id',$request->location_id)->get();
        $branchName = DB::table('branches')->where('id',$request->branch_id)->get();
        $facilityName = DB::table('facilities')->where('id',$request->facility_id)->get();
        $buildingName = DB::table('buildings')->where('id',$request->building_id)->get();
        $floorName = DB::table('floors')->where('id',$request->floor_id)->get();
        $labDepName = DB::table('lab_departments')->where('id',$request->lab_id)->get();
        $deviceName = DB::table('devices')->where('id',$request->deviceId)->get();

        if(count($locationName)>0){
            $locationName = DB::table('locations')->where('id',$request->location_id)->first();
            $locationDetail['location'] = $locationName->stateName;
        }else{
            $locationDetail['location'] = 'NA';
        }

        if(count($branchName)>0){
            $branchName = DB::table('branches')->where('id',$request->branch_id)->first();
            $locationDetail['branch'] = $branchName->branchName;
        }else{
            $locationDetail['branch'] = 'NA';
        }

        if(count($facilityName)>0){
            $facilityName = DB::table('facilities')->where('id',$request->facility_id)->first();
            $locationDetail['facility'] = $facilityName->facilityName;
        }else{
            $locationDetail['facility'] = 'NA';
        }

        if(count($buildingName)>0){
            $buildingName = DB::table('buildings')->where('id',$request->building_id)->first();
            $locationDetail['building'] = $buildingName->buildingName;
        }else{
            $locationDetail['building'] = 'NA';
        }

        if(count($floorName)>0){
            $floorName = DB::table('floors')->where('id',$request->floor_id)->first();
            $locationDetail['floor'] = $floorName->floorName;
        }else{
            $locationDetail['floor'] = 'NA';
        }

        if(count($labDepName)>0){
            $labDepName = DB::table('lab_departments')->where('id',$request->lab_id)->first();
            $locationDetail['zone'] = $labDepName->labDepName;
        }else{
            $locationDetail['zone'] = 'NA';
        }

        if(count($deviceName)>0){
            $deviceName = DB::table('devices')->where('id',$request->deviceId)->first();
            $locationDetail['device'] = $deviceName->deviceName;
        }else{
            $locationDetail['device'] = 'NA';
        }


        // return Excel::download(new SensorStatusReportExport($query), 'sensorStatusReportExport.xlsx');
        $attachment =  Excel::raw(new SensorStatusReportExport($query, $locationDetail), BaseExcel::XLSX);

        $url = env('APPLICATION_URL');
        $email = $request->header('Userid');
        $data = [
            'meassage' => 'Sensor Status Reports',
            'url' => $url
        ];

        Mail::send('sensorStatus', $data, function($messages) use ($email,$attachment){
            $messages->to($email);
            $messages->subject('Sensor Status Reports');
            $messages->attachData($attachment, 'sensorStatusReport.xlsx',[
            ]);
        });

        $response = [
            "message"=>"Reports data sent Successfully"
        ];
        $status = 200;

        return response($response, $status);
    }


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
                      ->where('customerId','=',$this->companyCode)
                      ->groupBy(DB::raw('Date(sampled_date_time)'));
        }

        if(!$request->deviceId){
            if($startDate === $endDate){
                $summaryAqiValue->whereDate('aqi.sampled_date_time','=',$startDate);
            }
            else {
                $summaryAqiValue->whereDate('aqi.sampled_date_time','>=',$startDate)
                                ->whereDate('aqi.sampled_date_time','<=',$endDate);
            }

        }else{
            if($startDate === $endDate){
                $summaryAqiValue->whereDate('aqi.sampled_date_time','=',$startDate)
                                ->where('aqi.deviceId',$request->deviceId);
            }
            else {
                $summaryAqiValue->whereDate('aqi.sampled_date_time','>=',$startDate)
                                ->whereDate('aqi.sampled_date_time','<=',$endDate)
                                ->where('aqi.deviceId',$request->deviceId);
            }
        }


        $attachment =  Excel::raw(new AqiReportExport($summaryAqiValue), BaseExcel::XLSX);

        $url = env('APPLICATION_URL');
        // $email = "vaishakkpoojary@gmail.com";
        $userEmail = $this->userId;
        $email = $this->fetchVerifiedEmailUsers($userEmail);

        $data = [
            'userid'=>$email,
            'url' => $url
        ];

        Mail::send('AqiReport', $data, function($messages) use ($email,$attachment){
            $messages->to($email);
            $messages->subject('Air quality Index Report');
            $messages->attachData($attachment, 'AirQualityIndexReports.xlsx',[
            ]);
        });

        $response = [
            "message"=>"Reports data sent Successfully"
        ];
        $status = 200;

        return response($response, $status);
    }












            public function DeviceModelLogsReports(Request $request)
                    {

                         if( $request->deviceId !=""){
                             if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !="" && $request->lab_id !=""){
                     $query = DB::table('customers as c')
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
                        ->Join('device_model_logs as dml', function($join){
                            $join->on('c.customerId', '=', 'dml.companyName')
                                ->on('d.id', '=', 'dml.deviceId');

                        })
                        ->select(DB::raw('DATE_FORMAT(dml.created_at, "%d-%m-%Y") as date'),DB::raw('TIME(dml.created_at) as time'), 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName' ,'dml.deviceModel','dml.id')
                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->where('b.id','=',$request->branch_id)
                            ->where('f.id','=',$request->facility_id)
                            ->where('bl.id','=',$request->building_id)
                            ->where('fl.id','=',$request->floor_id)
                            ->where('lb.id','=',$request->lab_id)
                            ->where('deviceId','=',$request->deviceId)
                            ->orderBy('dml.id', 'asc');
                             }
                    else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !=""){
                             $query = DB::table('customers as c')
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
                        ->Join('device_model_logs as dml', function($join){
                            $join->on('c.customerId', '=', 'dml.companyName')
                                ->on('d.id', '=', 'dml.deviceId');

                        })
                        ->select(DB::raw('DATE_FORMAT(dml.created_at, "%d-%m-%Y") as date'),DB::raw('TIME(dml.created_at) as time'), 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName' ,'dml.deviceModel','dml.id')
                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->where('b.id','=',$request->branch_id)
                            ->where('f.id','=',$request->facility_id)
                            ->where('bl.id','=',$request->building_id)
                            ->where('fl.id','=',$request->floor_id)
                            ->where('deviceId','=',$request->deviceId)
                           ->orderBy('dml.id', 'asc');
                    }
                     else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !=""){
                           $query = DB::table('customers as c')
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
                        ->Join('device_model_logs as dml', function($join){
                            $join->on('c.customerId', '=', 'dml.companyName')
                                ->on('d.id', '=', 'dml.deviceId');

                        })
                        ->select(DB::raw('DATE_FORMAT(dml.created_at, "%d-%m-%Y") as date'),DB::raw('TIME(dml.created_at) as time'), 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName' ,'dml.deviceModel','dml.id')
                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->where('b.id','=',$request->branch_id)
                            ->where('f.id','=',$request->facility_id)
                            ->where('bl.id','=',$request->building_id)
                            ->where('deviceId','=',$request->deviceId)
                           ->orderBy('dml.id', 'asc');
                     }
                     else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""){
                         $query = DB::table('customers as c')
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
                        ->Join('device_model_logs as dml', function($join){
                            $join->on('c.customerId', '=', 'dml.companyName')
                                ->on('d.id', '=', 'dml.deviceId');

                        })
                        ->select(DB::raw('DATE_FORMAT(dml.created_at, "%d-%m-%Y") as date'),DB::raw('TIME(dml.created_at) as time'), 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName' ,'dml.deviceModel','dml.id')
                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->where('b.id','=',$request->branch_id)
                            ->where('f.id','=',$request->facility_id)
                            ->where('deviceId','=',$request->deviceId)
                           ->orderBy('dml.id', 'asc');
                     }
                     else if($request->location_id != "" && $request->branch_id != ""){
                         $query = DB::table('customers as c')
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
                        ->Join('device_model_logs as dml', function($join){
                            $join->on('c.customerId', '=', 'dml.companyName')
                                ->on('d.id', '=', 'dml.deviceId');

                        })
                        ->select(DB::raw('DATE_FORMAT(dml.created_at, "%d-%m-%Y") as date'),DB::raw('TIME(dml.created_at) as time'), 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName' ,'dml.deviceModel','dml.id')
                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->where('b.id','=',$request->branch_id)
                            ->where('deviceId','=',$request->deviceId)
                           ->orderBy('dml.id', 'asc');
                     }
                     else if($request->location_id != ""){
                         $query = DB::table('customers as c')
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
                        ->Join('device_model_logs as dml', function($join){
                            $join->on('c.customerId', '=', 'dml.companyName')
                                ->on('d.id', '=', 'dml.deviceId');

                        })
                        ->select(DB::raw('DATE_FORMAT(dml.created_at, "%d-%m-%Y") as date'),DB::raw('TIME(dml.created_at) as time'), 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName' ,'dml.deviceModel','dml.id')
                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->where('deviceId','=',$request->deviceId)
                           ->orderBy('dml.id', 'asc');
                     }
                     else{
                          $query = DB::table('customers as c')
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
                        ->Join('device_model_logs as dml', function($join){
                            $join->on('c.customerId', '=', 'dml.companyName')
                                ->on('d.id', '=', 'dml.deviceId');

                        })
                        ->select(DB::raw('DATE_FORMAT(dml.created_at, "%d-%m-%Y") as date'),DB::raw('TIME(dml.created_at) as time'), 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName' ,'dml.deviceModel','dml.id')
                            ->where('customerId','=',$this->companyCode)
                            ->where('deviceId','=',$request->deviceId)
                           ->orderBy('dml.id', 'asc');
                     }
                         }else{
                              if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !="" && $request->lab_id !=""){
                         $query = DB::table('customers as c')
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
                        ->Join('device_model_logs as dml', function($join){
                            $join->on('c.customerId', '=', 'dml.companyName')
                                ->on('d.id', '=', 'dml.deviceId');

                        })
                        ->select(DB::raw('DATE_FORMAT(dml.created_at, "%d-%m-%Y") as date'),DB::raw('TIME(dml.created_at) as time'), 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName' ,'dml.deviceModel','dml.id')
                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->where('b.id','=',$request->branch_id)
                            ->where('f.id','=',$request->facility_id)
                            ->where('bl.id','=',$request->building_id)
                            ->where('fl.id','=',$request->floor_id)
                            ->where('lb.id','=',$request->lab_id)
                            ->orderBy('dml.id', 'asc');
                             }
                    else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !=""){
                         $query = DB::table('customers as c')
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
                        ->Join('device_model_logs as dml', function($join){
                            $join->on('c.customerId', '=', 'dml.companyName')
                                ->on('d.id', '=', 'dml.deviceId');

                        })
                        ->select(DB::raw('DATE_FORMAT(dml.created_at, "%d-%m-%Y") as date'),DB::raw('TIME(dml.created_at) as time'), 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName' ,'dml.deviceModel','dml.id')
                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->where('b.id','=',$request->branch_id)
                            ->where('f.id','=',$request->facility_id)
                            ->where('bl.id','=',$request->building_id)
                            ->where('fl.id','=',$request->floor_id)
                            ->orderBy('dml.id', 'asc');

                    }
                     else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !=""){
                          $query = DB::table('customers as c')
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
                        ->Join('device_model_logs as dml', function($join){
                            $join->on('c.customerId', '=', 'dml.companyName')
                                ->on('d.id', '=', 'dml.deviceId');

                        })
                        ->select(DB::raw('DATE_FORMAT(dml.created_at, "%d-%m-%Y") as date'),DB::raw('TIME(dml.created_at) as time'), 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName' ,'dml.deviceModel','dml.id')
                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->where('b.id','=',$request->branch_id)
                            ->where('f.id','=',$request->facility_id)
                            ->where('bl.id','=',$request->building_id)
                            ->orderBy('dml.id', 'asc');
                     }
                     else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""){
                         $query = DB::table('customers as c')
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
                        ->Join('device_model_logs as dml', function($join){
                            $join->on('c.customerId', '=', 'dml.companyName')
                                ->on('d.id', '=', 'dml.deviceId');

                        })
                        ->select(DB::raw('DATE_FORMAT(dml.created_at, "%d-%m-%Y") as date'),DB::raw('TIME(dml.created_at) as time'), 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName' ,'dml.deviceModel','dml.id')
                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->where('b.id','=',$request->branch_id)
                            ->where('f.id','=',$request->facility_id)
                            ->orderBy('dml.id', 'asc');
                     }
                     else if($request->location_id != "" && $request->branch_id != ""){
                           $query = DB::table('customers as c')
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
                        ->Join('device_model_logs as dml', function($join){
                            $join->on('c.customerId', '=', 'dml.companyName')
                                ->on('d.id', '=', 'dml.deviceId');

                        })
                        ->select(DB::raw('DATE_FORMAT(dml.created_at, "%d-%m-%Y") as date'),DB::raw('TIME(dml.created_at) as time'), 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName' ,'dml.deviceModel','dml.id')
                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->where('b.id','=',$request->branch_id)
                            ->orderBy('dml.id', 'asc');

                     }
                     else if($request->location_id != ""){
                             $query = DB::table('customers as c')
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
                        ->Join('device_model_logs as dml', function($join){
                            $join->on('c.customerId', '=', 'dml.companyName')
                                ->on('d.id', '=', 'dml.deviceId');

                        })
                        ->select(DB::raw('DATE_FORMAT(dml.created_at, "%d-%m-%Y") as date'),DB::raw('TIME(dml.created_at) as time'), 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName' ,'dml.deviceModel','dml.id')
                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->orderBy('dml.id', 'asc');
                     }
                     else{
                            $query = DB::table('customers as c')
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
                        ->Join('device_model_logs as dml', function($join){
                            $join->on('c.customerId', '=', 'dml.companyName')
                                ->on('d.id', '=', 'dml.deviceId');

                        })
                        ->select(DB::raw('DATE_FORMAT(dml.created_at, "%d-%m-%Y") as date'),DB::raw('TIME(dml.created_at) as time'), 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName' ,'dml.deviceModel','dml.id')
                            ->where('customerId','=',$this->companyCode)
                            ->orderBy('dml.id', 'asc');
                     }
                }

                    // $getData = new ReportsDataUtilityController($request,$query);

                    // $response = [
                    //               "data"=>$getData->getData()
                    //             ];

                    $getData = new AppDataUtilityController($request,$query);

                    $response = $getData->getData();

                    $status = 200;

                    return response($response,$status);
         }


          public function ExportDeviceModelLog(Request $request)
                    {

                          if( $request->deviceId !=""){
                             if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !="" && $request->lab_id !=""){
                     $query = DB::table('customers as c')
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
                        ->Join('device_model_logs as dml', function($join){
                            $join->on('c.customerId', '=', 'dml.companyName')
                                ->on('d.id', '=', 'dml.deviceId');

                        })
  ->select(DB::raw('DATE_FORMAT(dml.created_at, "%d-%m-%Y") as date'),DB::raw('TIME(dml.created_at) as time'), 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName' ,'dml.deviceModel')                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->where('b.id','=',$request->branch_id)
                            ->where('f.id','=',$request->facility_id)
                            ->where('bl.id','=',$request->building_id)
                            ->where('fl.id','=',$request->floor_id)
                            ->where('lb.id','=',$request->lab_id)
                            ->where('deviceId','=',$request->deviceId)
                            ->orderBy('dml.id', 'asc');
                             }
                    else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !=""){
                             $query = DB::table('customers as c')
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
                        ->Join('device_model_logs as dml', function($join){
                            $join->on('c.customerId', '=', 'dml.companyName')
                                ->on('d.id', '=', 'dml.deviceId');

                        })
  ->select(DB::raw('DATE_FORMAT(dml.created_at, "%d-%m-%Y") as date'),DB::raw('TIME(dml.created_at) as time'), 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName' ,'dml.deviceModel')                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->where('b.id','=',$request->branch_id)
                            ->where('f.id','=',$request->facility_id)
                            ->where('bl.id','=',$request->building_id)
                            ->where('fl.id','=',$request->floor_id)
                            ->where('deviceId','=',$request->deviceId)
                           ->orderBy('dml.id', 'asc');
                    }
                     else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !=""){
                           $query = DB::table('customers as c')
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
                        ->Join('device_model_logs as dml', function($join){
                            $join->on('c.customerId', '=', 'dml.companyName')
                                ->on('d.id', '=', 'dml.deviceId');

                        })
  ->select(DB::raw('DATE_FORMAT(dml.created_at, "%d-%m-%Y") as date'),DB::raw('TIME(dml.created_at) as time'), 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName' ,'dml.deviceModel')                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->where('b.id','=',$request->branch_id)
                            ->where('f.id','=',$request->facility_id)
                            ->where('bl.id','=',$request->building_id)
                            ->where('deviceId','=',$request->deviceId)
                           ->orderBy('dml.id', 'asc');
                     }
                     else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""){
                         $query = DB::table('customers as c')
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
                        ->Join('device_model_logs as dml', function($join){
                            $join->on('c.customerId', '=', 'dml.companyName')
                                ->on('d.id', '=', 'dml.deviceId');

                        })
  ->select(DB::raw('DATE_FORMAT(dml.created_at, "%d-%m-%Y") as date'),DB::raw('TIME(dml.created_at) as time'), 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName' ,'dml.deviceModel')                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->where('b.id','=',$request->branch_id)
                            ->where('f.id','=',$request->facility_id)
                            ->where('deviceId','=',$request->deviceId)
                           ->orderBy('dml.id', 'asc');
                     }
                     else if($request->location_id != "" && $request->branch_id != ""){
                         $query = DB::table('customers as c')
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
                        ->Join('device_model_logs as dml', function($join){
                            $join->on('c.customerId', '=', 'dml.companyName')
                                ->on('d.id', '=', 'dml.deviceId');

                        })
  ->select(DB::raw('DATE_FORMAT(dml.created_at, "%d-%m-%Y") as date'),DB::raw('TIME(dml.created_at) as time'), 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName' ,'dml.deviceModel')                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->where('b.id','=',$request->branch_id)
                            ->where('deviceId','=',$request->deviceId)
                           ->orderBy('dml.id', 'asc');
                     }
                     else if($request->location_id != ""){
                         $query = DB::table('customers as c')
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
                        ->Join('device_model_logs as dml', function($join){
                            $join->on('c.customerId', '=', 'dml.companyName')
                                ->on('d.id', '=', 'dml.deviceId');

                        })
  ->select(DB::raw('DATE_FORMAT(dml.created_at, "%d-%m-%Y") as date'),DB::raw('TIME(dml.created_at) as time'), 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName' ,'dml.deviceModel')                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->where('deviceId','=',$request->deviceId)
                           ->orderBy('dml.id', 'asc');
                     }
                     else{
                          $query = DB::table('customers as c')
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
                        ->Join('device_model_logs as dml', function($join){
                            $join->on('c.customerId', '=', 'dml.companyName')
                                ->on('d.id', '=', 'dml.deviceId');

                        })
  ->select(DB::raw('DATE_FORMAT(dml.created_at, "%d-%m-%Y") as date'),DB::raw('TIME(dml.created_at) as time'), 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName' ,'dml.deviceModel')                            ->where('customerId','=',$this->companyCode)
                            ->where('deviceId','=',$request->deviceId)
                           ->orderBy('dml.id', 'asc');
                     }
                         }else{
                              if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !="" && $request->lab_id !=""){
                         $query = DB::table('customers as c')
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
                        ->Join('device_model_logs as dml', function($join){
                            $join->on('c.customerId', '=', 'dml.companyName')
                                ->on('d.id', '=', 'dml.deviceId');

                        })
  ->select(DB::raw('DATE_FORMAT(dml.created_at, "%d-%m-%Y") as date'),DB::raw('TIME(dml.created_at) as time'), 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName' ,'dml.deviceModel')                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->where('b.id','=',$request->branch_id)
                            ->where('f.id','=',$request->facility_id)
                            ->where('bl.id','=',$request->building_id)
                            ->where('fl.id','=',$request->floor_id)
                            ->where('lb.id','=',$request->lab_id)
                            ->orderBy('dml.id', 'asc');
                             }
                    else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !=""){
                         $query = DB::table('customers as c')
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
                        ->Join('device_model_logs as dml', function($join){
                            $join->on('c.customerId', '=', 'dml.companyName')
                                ->on('d.id', '=', 'dml.deviceId');

                        })
  ->select(DB::raw('DATE_FORMAT(dml.created_at, "%d-%m-%Y") as date'),DB::raw('TIME(dml.created_at) as time'), 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName' ,'dml.deviceModel')                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->where('b.id','=',$request->branch_id)
                            ->where('f.id','=',$request->facility_id)
                            ->where('bl.id','=',$request->building_id)
                            ->where('fl.id','=',$request->floor_id)
                            ->orderBy('dml.id', 'asc');

                    }
                     else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !=""){
                          $query = DB::table('customers as c')
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
                        ->Join('device_model_logs as dml', function($join){
                            $join->on('c.customerId', '=', 'dml.companyName')
                                ->on('d.id', '=', 'dml.deviceId');

                        })
  ->select(DB::raw('DATE_FORMAT(dml.created_at, "%d-%m-%Y") as date'),DB::raw('TIME(dml.created_at) as time'), 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName' ,'dml.deviceModel')                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->where('b.id','=',$request->branch_id)
                            ->where('f.id','=',$request->facility_id)
                            ->where('bl.id','=',$request->building_id)
                            ->orderBy('dml.id', 'asc');
                     }
                     else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""){
                         $query = DB::table('customers as c')
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
                        ->Join('device_model_logs as dml', function($join){
                            $join->on('c.customerId', '=', 'dml.companyName')
                                ->on('d.id', '=', 'dml.deviceId');

                        })
  ->select(DB::raw('DATE_FORMAT(dml.created_at, "%d-%m-%Y") as date'),DB::raw('TIME(dml.created_at) as time'), 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName' ,'dml.deviceModel')                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->where('b.id','=',$request->branch_id)
                            ->where('f.id','=',$request->facility_id)
                            ->orderBy('dml.id', 'asc');
                     }
                     else if($request->location_id != "" && $request->branch_id != ""){
                           $query = DB::table('customers as c')
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
                        ->Join('device_model_logs as dml', function($join){
                            $join->on('c.customerId', '=', 'dml.companyName')
                                ->on('d.id', '=', 'dml.deviceId');

                        })
  ->select(DB::raw('DATE_FORMAT(dml.created_at, "%d-%m-%Y") as date'),DB::raw('TIME(dml.created_at) as time'), 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName' ,'dml.deviceModel')                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->where('b.id','=',$request->branch_id)
                            ->orderBy('dml.id', 'asc');

                     }
                     else if($request->location_id != ""){
                             $query = DB::table('customers as c')
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
                        ->Join('device_model_logs as dml', function($join){
                            $join->on('c.customerId', '=', 'dml.companyName')
                                ->on('d.id', '=', 'dml.deviceId');

                        })
  ->select(DB::raw('DATE_FORMAT(dml.created_at, "%d-%m-%Y") as date'),DB::raw('TIME(dml.created_at) as time'), 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName' ,'dml.deviceModel')                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->orderBy('dml.id', 'asc');
                     }
                     else{
                            $query = DB::table('customers as c')
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
                        ->Join('device_model_logs as dml', function($join){
                            $join->on('c.customerId', '=', 'dml.companyName')
                                ->on('d.id', '=', 'dml.deviceId');

                        })
  ->select(DB::raw('DATE_FORMAT(dml.created_at, "%d-%m-%Y") as date'),DB::raw('TIME(dml.created_at) as time'), 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName' ,'dml.deviceModel')                            ->where('customerId','=',$this->companyCode)
                            ->orderBy('dml.id', 'asc');
                     }
                }



                    return Excel::download(new DeviceModelLogExport($query), 'DeviceModelLogs.xlsx');

                }






            public function EmailDeviceModelLog(Request $request)
                    {
                                            if( $request->deviceId !=""){
                             if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !="" && $request->lab_id !=""){
                     $query = DB::table('customers as c')
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
                        ->Join('device_model_logs as dml', function($join){
                            $join->on('c.customerId', '=', 'dml.companyName')
                                ->on('d.id', '=', 'dml.deviceId');

                        })
  ->select(DB::raw('DATE_FORMAT(dml.created_at, "%d-%m-%Y") as date'),DB::raw('TIME(dml.created_at) as time'), 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName' ,'dml.deviceModel')                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->where('b.id','=',$request->branch_id)
                            ->where('f.id','=',$request->facility_id)
                            ->where('bl.id','=',$request->building_id)
                            ->where('fl.id','=',$request->floor_id)
                            ->where('lb.id','=',$request->lab_id)
                            ->where('deviceId','=',$request->deviceId)
                            ->orderBy('dml.id', 'asc');
                             }
                    else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !=""){
                             $query = DB::table('customers as c')
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
                        ->Join('device_model_logs as dml', function($join){
                            $join->on('c.customerId', '=', 'dml.companyName')
                                ->on('d.id', '=', 'dml.deviceId');

                        })
  ->select(DB::raw('DATE_FORMAT(dml.created_at, "%d-%m-%Y") as date'),DB::raw('TIME(dml.created_at) as time'), 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName' ,'dml.deviceModel')                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->where('b.id','=',$request->branch_id)
                            ->where('f.id','=',$request->facility_id)
                            ->where('bl.id','=',$request->building_id)
                            ->where('fl.id','=',$request->floor_id)
                            ->where('deviceId','=',$request->deviceId)
                           ->orderBy('dml.id', 'asc');
                    }
                     else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !=""){
                           $query = DB::table('customers as c')
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
                        ->Join('device_model_logs as dml', function($join){
                            $join->on('c.customerId', '=', 'dml.companyName')
                                ->on('d.id', '=', 'dml.deviceId');

                        })
  ->select(DB::raw('DATE_FORMAT(dml.created_at, "%d-%m-%Y") as date'),DB::raw('TIME(dml.created_at) as time'), 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName' ,'dml.deviceModel')                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->where('b.id','=',$request->branch_id)
                            ->where('f.id','=',$request->facility_id)
                            ->where('bl.id','=',$request->building_id)
                            ->where('deviceId','=',$request->deviceId)
                           ->orderBy('dml.id', 'asc');
                     }
                     else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""){
                         $query = DB::table('customers as c')
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
                        ->Join('device_model_logs as dml', function($join){
                            $join->on('c.customerId', '=', 'dml.companyName')
                                ->on('d.id', '=', 'dml.deviceId');

                        })
  ->select(DB::raw('DATE_FORMAT(dml.created_at, "%d-%m-%Y") as date'),DB::raw('TIME(dml.created_at) as time'), 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName' ,'dml.deviceModel')                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->where('b.id','=',$request->branch_id)
                            ->where('f.id','=',$request->facility_id)
                            ->where('deviceId','=',$request->deviceId)
                           ->orderBy('dml.id', 'asc');
                     }
                     else if($request->location_id != "" && $request->branch_id != ""){
                         $query = DB::table('customers as c')
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
                        ->Join('device_model_logs as dml', function($join){
                            $join->on('c.customerId', '=', 'dml.companyName')
                                ->on('d.id', '=', 'dml.deviceId');

                        })
  ->select(DB::raw('DATE_FORMAT(dml.created_at, "%d-%m-%Y") as date'),DB::raw('TIME(dml.created_at) as time'), 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName' ,'dml.deviceModel')                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->where('b.id','=',$request->branch_id)
                            ->where('deviceId','=',$request->deviceId)
                           ->orderBy('dml.id', 'asc');
                     }
                     else if($request->location_id != ""){
                         $query = DB::table('customers as c')
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
                        ->Join('device_model_logs as dml', function($join){
                            $join->on('c.customerId', '=', 'dml.companyName')
                                ->on('d.id', '=', 'dml.deviceId');

                        })
  ->select(DB::raw('DATE_FORMAT(dml.created_at, "%d-%m-%Y") as date'),DB::raw('TIME(dml.created_at) as time'), 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName' ,'dml.deviceModel')                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->where('deviceId','=',$request->deviceId)
                           ->orderBy('dml.id', 'asc');
                     }
                     else{
                          $query = DB::table('customers as c')
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
                        ->Join('device_model_logs as dml', function($join){
                            $join->on('c.customerId', '=', 'dml.companyName')
                                ->on('d.id', '=', 'dml.deviceId');

                        })
  ->select(DB::raw('DATE_FORMAT(dml.created_at, "%d-%m-%Y") as date'),DB::raw('TIME(dml.created_at) as time'), 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName' ,'dml.deviceModel')                            ->where('customerId','=',$this->companyCode)
                            ->where('deviceId','=',$request->deviceId)
                           ->orderBy('dml.id', 'asc');
                     }
                         }else{
                              if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !="" && $request->lab_id !=""){
                         $query = DB::table('customers as c')
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
                        ->Join('device_model_logs as dml', function($join){
                            $join->on('c.customerId', '=', 'dml.companyName')
                                ->on('d.id', '=', 'dml.deviceId');

                        })
  ->select(DB::raw('DATE_FORMAT(dml.created_at, "%d-%m-%Y") as date'),DB::raw('TIME(dml.created_at) as time'), 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName' ,'dml.deviceModel')                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->where('b.id','=',$request->branch_id)
                            ->where('f.id','=',$request->facility_id)
                            ->where('bl.id','=',$request->building_id)
                            ->where('fl.id','=',$request->floor_id)
                            ->where('lb.id','=',$request->lab_id)
                            ->orderBy('dml.id', 'asc');
                             }
                    else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !=""){
                         $query = DB::table('customers as c')
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
                        ->Join('device_model_logs as dml', function($join){
                            $join->on('c.customerId', '=', 'dml.companyName')
                                ->on('d.id', '=', 'dml.deviceId');

                        })
  ->select(DB::raw('DATE_FORMAT(dml.created_at, "%d-%m-%Y") as date'),DB::raw('TIME(dml.created_at) as time'), 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName' ,'dml.deviceModel')                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->where('b.id','=',$request->branch_id)
                            ->where('f.id','=',$request->facility_id)
                            ->where('bl.id','=',$request->building_id)
                            ->where('fl.id','=',$request->floor_id)
                            ->orderBy('dml.id', 'asc');

                    }
                     else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !=""){
                          $query = DB::table('customers as c')
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
                        ->Join('device_model_logs as dml', function($join){
                            $join->on('c.customerId', '=', 'dml.companyName')
                                ->on('d.id', '=', 'dml.deviceId');

                        })
  ->select(DB::raw('DATE_FORMAT(dml.created_at, "%d-%m-%Y") as date'),DB::raw('TIME(dml.created_at) as time'), 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName' ,'dml.deviceModel')                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->where('b.id','=',$request->branch_id)
                            ->where('f.id','=',$request->facility_id)
                            ->where('bl.id','=',$request->building_id)
                            ->orderBy('dml.id', 'asc');
                     }
                     else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""){
                         $query = DB::table('customers as c')
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
                        ->Join('device_model_logs as dml', function($join){
                            $join->on('c.customerId', '=', 'dml.companyName')
                                ->on('d.id', '=', 'dml.deviceId');

                        })
  ->select(DB::raw('DATE_FORMAT(dml.created_at, "%d-%m-%Y") as date'),DB::raw('TIME(dml.created_at) as time'), 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName' ,'dml.deviceModel')                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->where('b.id','=',$request->branch_id)
                            ->where('f.id','=',$request->facility_id)
                            ->orderBy('dml.id', 'asc');
                     }
                     else if($request->location_id != "" && $request->branch_id != ""){
                           $query = DB::table('customers as c')
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
                        ->Join('device_model_logs as dml', function($join){
                            $join->on('c.customerId', '=', 'dml.companyName')
                                ->on('d.id', '=', 'dml.deviceId');

                        })
  ->select(DB::raw('DATE_FORMAT(dml.created_at, "%d-%m-%Y") as date'),DB::raw('TIME(dml.created_at) as time'), 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName' ,'dml.deviceModel')                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->where('b.id','=',$request->branch_id)
                            ->orderBy('dml.id', 'asc');

                     }
                     else if($request->location_id != ""){
                             $query = DB::table('customers as c')
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
                        ->Join('device_model_logs as dml', function($join){
                            $join->on('c.customerId', '=', 'dml.companyName')
                                ->on('d.id', '=', 'dml.deviceId');

                        })
  ->select(DB::raw('DATE_FORMAT(dml.created_at, "%d-%m-%Y") as date'),DB::raw('TIME(dml.created_at) as time'), 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName' ,'dml.deviceModel')                            ->where('customerId','=',$this->companyCode)
                            ->where('l.id','=',$request->location_id)
                            ->orderBy('dml.id', 'asc');
                     }
                     else{
                            $query = DB::table('customers as c')
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
                        ->Join('device_model_logs as dml', function($join){
                            $join->on('c.customerId', '=', 'dml.companyName')
                                ->on('d.id', '=', 'dml.deviceId');

                        })
                        ->select(DB::raw('DATE_FORMAT(dml.created_at, "%d-%m-%Y") as date'),DB::raw('TIME(dml.created_at) as time'), 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName' ,'dml.deviceModel')                            ->where('customerId','=',$this->companyCode)
                            ->orderBy('dml.id', 'asc');
                     }
                }

        $attachment =  Excel::raw(new DeviceModelLogExport($query), BaseExcel::XLSX);

        $url = env('APPLICATION_URL');
        $email = $request->header('Userid');
        $data = [
            'meassage' => 'Device Model Log',
            'url' => $url
        ];

        Mail::send('HardwareVersionModelNo',$data, function($messages) use ($email,$attachment){
            $messages->to($email);
            $messages->subject('Device Model Log');
            $messages->attachData($attachment, 'DeviceModelLogs.xlsx',[
                 ]);
        });

           $response = [
                "message"=>"Reports data sent Successfully"
            ];
            $status = 200;

        return response($response, $status);
    }

 }

