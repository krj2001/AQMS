<?php

namespace App\Http\Controllers;
use App\Models\CalibrationTestResult;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as BaseExcel;
use App\Exports\CalibrationResultExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Sensor;
use Illuminate\Support\Facades\Mail;
use DateTime;
use Illuminate\Support\Collection;


class CalibrationReportController extends Controller
{
    
    public function report(Request $request)
    {
        $companyCode = $request->header('Companycode');
        $location = $request->location_id;
        $branch = $request->branch_id;
        $facility = $request->facility_id;
        $building = $request->building_id;
        $floor = $request->floor_id;
        $lab = $request->lab_id;
        $device = $request->device_id;
        $fromDate =  $request->fromDate;
        $toDate = $request->toDate;
        
        $devices = [];
        $calibration = []; 
        $deviceId = [];
        
        $devices = DB::table('devices')->where('companyCode', $companyCode)->select('id')->get();
        
        if($device) {
            $devices = DB::table('devices')->where('companyCode', $companyCode)->where('id', $device)->select('id')->get();
            
        }else if($location && $branch && $facility && $building && $floor && $lab) {
            $devices = DB::table('devices')->where('companyCode', $companyCode)->where('lab_id', $lab)->select('id')->get();
            
        }else if($location && $branch && $facility && $building && $floor) {
            $devices = DB::table('devices')->where('companyCode', $companyCode)->where('floor_id', $floor)->select('id')->get();
             
        }else if($location && $branch && $facility && $building) {
            $devices = DB::table('devices')->where('companyCode', $companyCode)->where('building_id', $building)->select('id')->get();
            
        }else if($location && $branch && $facility) {
            $devices = DB::table('devices')->where('companyCode', $companyCode)->where('facility_id', $facility)->select('id')->get();
            
        }else if($location && $branch) {
            $devices = DB::table('devices')->where('companyCode', $companyCode)->where('branch_id', $branch)->select('id')->get();
            
        }else if($location) {
            $devices = DB::table('devices')->where('companyCode', $companyCode)->where('location_id', $location)->select('id')->get();
            
        }
      
        foreach($devices as $k => $v) {
            $deviceId[] = $v->id;
        }
        
        for($i=0; $i < count($deviceId); $i++) {
            $data = DB::table('calibration_test_results as c')
                ->join('devices as d', 'd.id','=', 'c.deviceId')
                ->where('c.deviceId', $deviceId[$i])
                ->where('c.created_at', '>', $fromDate)
                ->select('c.id','d.deviceName', 'c.sensorTag', 'c.name', 'c.calibrationDate', 'c.calibratedDate', 'c.testResult', 'c.nextDueDate', 'c.userEmail')
                ->get();
                
            foreach($data as $k => $v) {
                $calibration[] = $v;
            }
        }
        
        $response = [
          'data' => $calibration,
          'status' => 200
        ];
        $status = 200;
        
        return response($response, $status);
    }
    
    
    
    public function export(Request $request)
    {
        $companyCode = $request->header('Companycode');
        $location = $request->location_id;
        $branch = $request->branch_id;
        $facility = $request->facility_id;
        $building = $request->building_id;
        $floor = $request->floor_id;
        $lab = $request->lab_id;
        $device = $request->device_id;
        $fromDate =  $request->fromDate;
        $toDate = $request->toDate;
        
        $devices = [];
        $calibration = []; 
        $devices = DB::table('devices')->where('companyCode', $companyCode)->select('id')->get();
        
        if($device != "") {
            $devices = DB::table('devices')->where('companyCode', $companyCode)->where('id', $device)->select('id')->get();
            
        }else if($location != "" && $branch != "" && $facility != "" && $building != "" && $floor != "" && $lab != "") {
            $devices = DB::table('devices')->where('companyCode', $companyCode)->where('lab_id', $lab)->select('id')->get();
            
        }else if($location != "" && $branch != "" && $facility != "" && $building != "" && $floor != "") {
            $devices = DB::table('devices')->where('companyCode', $companyCode)->where('floor_id', $floor)->select('id')->get();
             
        }else if($location != "" && $branch != "" && $facility != "" && $building != "") {
            $devices = DB::table('devices')->where('companyCode', $companyCode)->where('building_id', $building)->select('id')->get();
            
        }else if($location != "" && $branch != "" && $facility != "") {
            $devices = DB::table('devices')->where('companyCode', $companyCode)->where('facility_id', $facility)->select('id')->get();
            
        }else if($location != "" && $branch != "") {
            $devices = DB::table('devices')->where('companyCode', $companyCode)->where('branch_id', $branch)->select('id')->get();
            
        }else if($location != "") {
            $devices = DB::table('devices')->where('companyCode', $companyCode)->where('location_id', $location)->select('id')->get();
            
        }
        
        for($i=0; $i < count($devices); $i++) {
            $data = DB::table('calibration_test_results as c')
                ->join('devices as d', 'd.id','=', 'c.deviceId')
                ->where('c.companyCode', $companyCode)
                ->where('c.deviceId', $devices[$i]->id)
                ->where('c.created_at', '>', $fromDate)
                ->where('c.created_at', '<', $toDate)
                ->select('d.deviceName', 'c.sensorTag', 'c.name', DB::raw("DATE_FORMAT(calibrationDate, '%Y-%m-%d') as calibrationDate"), 'c.calibratedDate', 'c.testResult', 'c.nextDueDate')
                ->get();
                
            foreach($data as $k => $v) {
                $calibration[] = $v;
            }
        }
        
        $collection = new Collection($calibration);
    
        // return $collection;
        return Excel::download(new CalibrationResultExport($collection), 'Calibration.xlsx');
        
         $response = [
            'status' => 200,
            'message' => 'Exported successfully!',
        ];
        $status = 200;
        
        return response($response, $status);
    }

    
    
    public function email(Request $request)
    {   
        $companyCode = $request->header('Companycode');
        $location = $request->location_id;
        $branch = $request->branch_id;
        $facility = $request->facility_id;
        $building = $request->building_id;
        $floor = $request->floor_id;
        $lab = $request->lab_id;
        $device = $request->device_id;
        $fromDate =  $request->fromDate;
        $toDate = $request->toDate;
        
        $devices = [];
        $calibration = [];  
        $devices = DB::table('devices')->where('companyCode', $companyCode)->select('id')->get();
        
        if($device != "") {
            $devices = DB::table('devices')->where('companyCode', $companyCode)->where('id', $device)->select('id')->get();

        }else if($location != "" && $branch != "" && $facility != "" && $building != "" && $floor != "" && $lab != "") {
            $devices = DB::table('devices')->where('companyCode', $companyCode)->where('lab_id', $lab)->select('id')->get();

        }else if($location != "" && $branch != "" && $facility != "" && $building != "" && $floor != "") {
            $devices = DB::table('devices')->where('companyCode', $companyCode)->where('floor_id', $floor)->select('id')->get();

        }else if($location != "" && $branch != "" && $facility != "" && $building != "") {
            $devices = DB::table('devices')->where('companyCode', $companyCode)->where('building_id', $building)->select('id')->get();

        }else if($location != "" && $branch != "" && $facility != "") {
            $devices = DB::table('devices')->where('companyCode', $companyCode)->where('facility_id', $facility)->select('id')->get();

        }else if($location != "" && $branch != "") {
            $devices = DB::table('devices')->where('companyCode', $companyCode)->where('branch_id', $branch)->select('id')->get();

        }else if($location != "") {
            $devices = DB::table('devices')->where('companyCode', $companyCode)->where('location_id', $location)->select('id')->get();

        }
        
        for($i=0; $i < count($devices); $i++) {
            $data = DB::table('calibration_test_results as c')
                ->join('devices as d', 'd.id','=', 'c.deviceId')
                ->where('c.companyCode', $companyCode)
                ->where('c.deviceId', $devices[$i]->id)
                ->where('c.created_at', '>', $fromDate)
                ->where('c.created_at', '<', $toDate)
                ->select('d.deviceName', 'c.sensorTag', 'c.name', DB::raw("DATE_FORMAT(calibrationDate, '%Y-%m-%d') as calibrationDate"), 'c.calibratedDate', 'c.testResult', 'c.nextDueDate')
                ->get();
                
            foreach($data as $k => $v) {
                $calibration[] = $v;
            }
        }
        
        $collection = new Collection($calibration);
        $attachment = Excel::raw(new CalibrationResultExport($collection), BaseExcel::XLSX);
        
        $url = env('APPLICATION_URL');
        // $email = 'vaishakkpoojary@gmail.com';
        $email = $request->header('Userid');
        $data = [
            'meassage' => 'Calibration Result',
            'url' => $url
        ];
        
        Mail::send('CalibrationReport', $data, function($messages) use ($email, $attachment){
            $messages->to($email);
            $messages->subject('Calibration Reports');    
            $messages->attachData($attachment, 'Calibration.xlsx',[
            ]);
        });
        
        $response = [
            'status' => 200,
            'email' => $email,
            'message' => 'Email sent successfully!',
        ];
        $status = 200;
        
        return response($response, $status);
    }
    
    
    public function getUrl()
    {
        $url = env('APPLICATION_URL');
        return $url;
    }
}














