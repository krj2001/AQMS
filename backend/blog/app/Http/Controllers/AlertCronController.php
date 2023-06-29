<?php

namespace App\Http\Controllers;
use App\Http\Controllers\UTILITY\DataUtilityController;
use App\Models\AlertCron;
use App\Models\Sensor;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\UtilityController;

class AlertCronController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     
    protected $companyCode = ""; 
  
    
    function __construct(Request $request) {
        $getData = new UtilityController($request);
        $this->companyCode = $getData->getCompanyCode(); 
    }
    
    
    public function index()
    {
        //
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
     * @param  \App\Models\AlertCron  $alertCron
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $alertData = array();
        $location_id = $request->location_id;
        $branch_id = $request->branch_id;
        $facility_id = $request->facility_id;
        $building_id = $request->building_id;
        $floor_id = $request->floor_id;
        $lab_id = $request->lab_id;
        $deviceId  = $request->device_id;
        $companyCode = $this->companyCode;
        
        $query = Device::query();
        
        if($companyCode != ""){
            $query->where('companyCode','=',$companyCode);             
        }
        if($location_id!=""){
            $query->where('location_id', '=', $location_id);
        }
        if($branch_id!=""){
            $query->where('branch_id', '=', $branch_id);
        }
        if($facility_id!=""){
            $query->where('facility_id', '=', $facility_id);
        }
        if($building_id!=""){
            $query->where('building_id', '=', $building_id);
        }
        if($floor_id!=""){
            $query->where('floor_id', '=', $floor_id);
        }
        if($lab_id!=""){
            $query->where('lab_id','=',$lab_id);
        }
        if($deviceId!=""){
            $query->where('id','=',$deviceId);
        }
        
        $data = $query->get();
        $length = count($data);
        
        for($x=0;$x<$length;$x++){
            $alertQuery = DB::table('alert_crons')
                     ->where('deviceId','=',$data[$x]->id)
                     ->where('status','=','0')
                     ->orderby('id','desc')
                     ->take(500)
                     ->get();
            if(count($alertQuery)!= 0){ 
                $alertLength = count($alertQuery);
                for($j=0;$j<$alertLength;$j++){
                    
                    $alertData['data'][] = $alertQuery[$j];
                }
            }
        }
       
        $response = $alertData;
        $status = 200;
        
        return response($response,$status);  
    }
    
    
    public function showAlertNew(Request $request)
    {
        $alertData = array();
        $notificationData = array();
        $alerts = array();
        $dates = array();
        
        $location_id = $request->location_id;
        $branch_id = $request->branch_id;
        $facility_id = $request->facility_id;
        $building_id = $request->building_id;
        $floor_id = $request->floor_id;
        $lab_id = $request->lab_id;
        $deviceId  = $request->device_id;
        $companyCode = $this->companyCode;
        
        $query = Device::query();
        
        if($companyCode != ""){
            $query->where('companyCode','=',$companyCode);             
        }
        if($location_id!=""){
            $query->where('location_id', '=', $location_id);
        }
        if($branch_id!=""){
            $query->where('branch_id', '=', $branch_id);
        }
        if($facility_id!=""){
            $query->where('facility_id', '=', $facility_id);
        }
        if($building_id!=""){
            $query->where('building_id', '=', $building_id);
        }
        if($floor_id!=""){
            $query->where('floor_id', '=', $floor_id);
        }
        if($lab_id!=""){
            $query->where('lab_id','=',$lab_id);
        }
        if($deviceId!=""){
            $query->where('id','=',$deviceId);
        }
        
        $data = $query->get();
        $length = count($data);
        
        for($x=0;$x<$length;$x++){
            $alertQuery = DB::table('alert_crons')
                     ->where('deviceId','=',$data[$x]->id)
                     ->where('status','=','0')
                     ->orderby('id','desc')
                     ->take(100)
                     ->get();
            if(count($alertQuery)!= 0){ 
                $alertLength = count($alertQuery);
                for($j=0;$j<$alertLength;$j++){
                    $dateModified = date("Y-m-d", strtotime($alertQuery[0]->a_date));
                    $alertQuery[$j]->dateTime = $dateModified." ".$alertQuery[0]->a_time;
                    $alertData['data'][] = $alertQuery[$j];
                    $notificationData[] = $alertQuery[$j];
                    
                    $dates[] = $alertQuery[$j]->dateTime;
                }
            }
        }
        
        
        $notificationDataCount = count($notificationData);
      
       
        $latestData =  array();
        
        //sorting dates in descending order in array
        
        usort($dates, function ($date1, $date2){
          if (strtotime($date1) < strtotime($date2))
             return 1;
          else if (strtotime($date1) > strtotime($date2))
             return -1;
          else
             return 0;
        });
        
        $uniqueDates = array_unique($dates);
        $newDates = array();
        
        foreach($uniqueDates as $key => $value){
            $newDates[] = $value; 
        }
        
        $dateCount = count($newDates);
        
        for($x=0;$x<$dateCount;$x++){
            for($j=0;$j<$notificationDataCount;$j++){
                $date = strtotime($newDates[$x]);
                $date2 = strtotime($notificationData[$j]->dateTime);
                if($date == $date2){
                     $latestData[] = $notificationData[$j];    
                }
            }
        }
        
        $response = [
            "data"=>$latestData
        ];
        $status = 200;
        
        return response($response,$status);  
    }
    
    
    
    public function showAlertNewV2(Request $request)
    {
        $alertData = array();
        $notificationData = array();
        $alerts = array();
        $dates = array();
        
        $location_id = $request->location_id;
        $branch_id = $request->branch_id;
        $facility_id = $request->facility_id;
        $building_id = $request->building_id;
        $floor_id = $request->floor_id;
        $lab_id = $request->lab_id;
        $deviceId  = $request->device_id;
        $companyCode = $this->companyCode;
        
        $query = Device::query();
        
        if($companyCode != ""){
            $query->where('companyCode','=',$companyCode);             
        }
        if($location_id!=""){
            $query->where('location_id', '=', $location_id);
        }
        if($branch_id!=""){
            $query->where('branch_id', '=', $branch_id);
        }
        if($facility_id!=""){
            $query->where('facility_id', '=', $facility_id);
        }
        if($building_id!=""){
            $query->where('building_id', '=', $building_id);
        }
        if($floor_id!=""){
            $query->where('floor_id', '=', $floor_id);
        }
        if($lab_id!=""){
            $query->where('lab_id','=',$lab_id);
        }
        if($deviceId!=""){
            $query->where('id','=',$deviceId);
        }
        
        $data = $query->get();
        $length = count($data);
        
        for($x=0;$x<$length;$x++){
            $alertQuery = DB::table('alert_crons')
                    ->select('alert_crons.*','devices.deviceName')
                    ->join('devices','devices.id','=','alert_crons.deviceId')
                    ->where('deviceId','=',$data[$x]->id)
                    ->where('status','=','0')
                    ->orderby('id','desc')
                    ->take(100)
                    ->get();
                    
            if(count($alertQuery)!= 0){ 
                $alertLength = count($alertQuery);
                for($j=0;$j<$alertLength;$j++){
                    $dateModified = date("d-m-Y", strtotime($alertQuery[$j]->a_date));
                    $alertQuery[$j]->dateTime = $dateModified." ".$alertQuery[$j]->a_time;
                    $alertData['data'][] = $alertQuery[$j];
                    $notificationData[] = $alertQuery[$j];
                    
                    $dates[] = $alertQuery[$j]->dateTime;
                }
            }
        }
        
        
        $notificationDataCount = count($notificationData);
      
       
        $latestData =  array();
        
        //sorting dates in descending order in array
        
        usort($dates, function ($date1, $date2){
          if (strtotime($date1) < strtotime($date2))
             return 1;
          else if (strtotime($date1) > strtotime($date2))
             return -1;
          else
             return 0;
        });
        
        $uniqueDates = array_unique($dates);
        $newDates = array();
        
        foreach($uniqueDates as $key => $value){
            $newDates[] = $value; 
        }
        
        $dateCount = count($newDates);
        
        for($x=0;$x<$dateCount;$x++){
            for($j=0;$j<$notificationDataCount;$j++){
                $date = strtotime($newDates[$x]);
                $date2 = strtotime($notificationData[$j]->dateTime);
                if($date == $date2){
                     $latestData[] = $notificationData[$j];    
                }
            }
        }
        
        $response = [
            "data"=>$latestData
        ];
        $status = 200;
        
        return response($response,$status);  
    }
    
    
    
    
    
    
    
    

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AlertCron  $alertCron
     * @return \Illuminate\Http\Response
     */
    public function edit(AlertCron $alertCron)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AlertCron  $alertCron
     * @return \Illuminate\Http\Response
     */
    public function updateOLD(Request $request)
    {
        $sensorId = $request->sensor_id;
        $reason = $request->clearAlertReason;
        $status = 1;
        $statusMessage = "Cleared";
        
        $sensorType = AlertCron::select('*')
                    ->where('id','=',$sensorId)
                    ->where('status','=','0')
                    ->orderBy('id','desc')
                    ->first();
        
        if($sensorType == null){
            $response = [
                "message" => "Data not Found" 
            ];    
            $status = 401;
        }else{
            $id = $sensorType->id;
            $alarmType = $sensorType->alarmType;
            if($alarmType == "UnLatch"){
                $response = [
                            "message" => "Unlatch alarm cannot be  cleared manually" 
                            
                ];    
                $status = 401; 
            }else{
                
                $getLastAlertQuery = DB::table('sampled_sensor_data_details_MinMaxAvg')
                                     ->where('sensor_id','=',$sensorId)
                                     ->orderBy('id','desc')
                                     ->take(1)
                                     ->get();
                                     
                $count = count($getLastAlertQuery);
                
                if($count>0){
                    $alertType = $getLastAlertQuery[0]->alertType;
                    if($alertType == "NORMAL"){
                        $query = AlertCron::select('*')
                                ->where('id','=',$id)
                                ->where('status','=','0')
                                ->update([
                                     'Reason' => $reason,
                                     'status' => $status,
                                     'statusMessage'=>$statusMessage,
                                     'triggeredAlertFlag'=> 0
                                ]);
                        
                        if($query){
                            $response = [
                                "message" => "Alarms Cleared successfully" 
                            ];    
                            $status = 200; 
                        } 
                        
                    }else{
                        $response = [
                            "message" => "Sensor data are not in normal"
                        ];    
                        $status = 409;     
                    }
                }        
            }
        } 
        
        // if($alarmType == "UnLatch"){
            // $response = [
            //             "message" => "Unlatch alarm cannobe cleared manually" 
            // ];    
            // $status = 401; 
        // }else{
            
        //     $query = AlertCron::select('*')
        //             ->where('sensorId','=',$sensorId)
        //             ->where('status','=','0')
        //             ->update([
        //                  'Reason' => $reason,
        //                  'status' => $status,
        //                  'statusMessage'=>$statusMessage
        //             ]);
        
        //     if($query){
                
        //         //hooter relay status to be enabled once it is acknowledge by the user when clearing the alarm
        //         $sensor = Sensor::select('*')
        //         ->where('id','=',$sensorId)
        //         ->update([
        //             'hooterRelayStatus'=>1
        //         ]);
    
        //         if($sensor){
        //             $response = [
        //                 "message" => "Alarms Cleared successfully" 
        //             ];    
        //             $status = 200; 
        //         }
        //     }
        // }
        
        return response($response,$status);
    }
    
    public function updateOld2(Request $request)
    {
        $sensorId = $request->sensor_id;
        $reason = $request->clearAlertReason;
        $status = 1;
        $statusMessage = "Cleared";
        
        $sensorType = AlertCron::select('*')
                    ->where('id','=',$sensorId)
                    ->where('status','=','0')
                    ->orderBy('id','desc')
                    ->first();
        
        if($sensorType == null){
            $response = [
                "message" => "Data not Found" 
            ];    
            $status = 401;
              
        }else{
            $id = $sensorType->id;
            $alarmType = $sensorType->alarmType;
            $sensorId = $sensorType->sensorId;
            
            if($alarmType == "UnLatch"){
                
                $response = [
                    "message" => "Unlatch alarm cannot be  cleared manually" 
                ];    
                $status = 401; 
                
            }else{
                
                $getLastAlertQuery = DB::table('sampled_sensor_data_details_MinMaxAvg')
                                     ->where('sensor_id','=',$sensorId)
                                     ->orderBy('id','desc')
                                     ->take(1)
                                     ->get();
                                     
                $count = count($getLastAlertQuery);
                
                if($count>0){
                    $alertType = $getLastAlertQuery[0]->alertType;
                    if($alertType == "NORMAL"){
                        
                        $otherDataValues = DB::table('sampled_sensor_data_details_MinMaxAvg')
                                                ->join('sensors', 'sensors.id', '=', 'sampled_sensor_data_details_MinMaxAvg.sensor_id')
                                                   ->select(DB::raw('sensors.stelLimit,sensors.deviceId,sensors.deviceName,sensors.sensorTag,sampled_sensor_data_details_MinMaxAvg.sample_date_time as DATE_TIME,sampled_sensor_data_details_MinMaxAvg.sensor_id,sampled_sensor_data_details_MinMaxAvg.parameterName as parameter,sampled_sensor_data_details_MinMaxAvg.time_stamp AS timekey,MAX(sampled_sensor_data_details_MinMaxAvg.max_val) as par_max,MIN(sampled_sensor_data_details_MinMaxAvg.min_val) as par_min,AVG(sampled_sensor_data_details_MinMaxAvg.avg_val)  as par_avg,sampled_sensor_data_details_MinMaxAvg.last_val as par_last'))
                                                   ->whereRaw('sampled_sensor_data_details_MinMaxAvg.time_stamp >(NOW() - INTERVAL 15 MINUTE)')
                                                   ->where('sampled_sensor_data_details_MinMaxAvg.sensor_id','=',$sensorId)
                                                   ->get();
                                                   
                                                   
                        $stelLimit = $otherDataValues[0]->stelLimit;
                        $avgVal = $otherDataValues[0]->par_avg;
                        
                        if($avgVal > $stelLimit){
                            $response = [
                                    "message" => "Average value is above the stel limit" 
                            ];    
                            $status = 401;
                        }else{
                            
                            
                            $query = AlertCron::select('*')
                                ->where('id','=',$id)
                                ->where('status','=','0')
                                ->update([
                                     'Reason' => $reason,
                                     'status' => $status,
                                     'statusMessage'=>$statusMessage,
                                     'triggeredAlertFlag'=> 0
                                ]);
                        
                            if($query){
                                $response = [
                                    "message" => "Alarms Cleared successfully" 
                                ];    
                                $status = 200; 
                            }else{
                                $response = [
                                    "message" => "Something went wrong" 
                                ];    
                                $status = 401;
                            }
                            
                            $response = [
                                    "message" => $otherDataValues
                            ];    
                            $status = 401;
                            
                        }
                        
                        
                    }else{
                        $response = [
                            "message" => "Sensor data are not in normal"
                        ];    
                        $status = 409;     
                    }
                }else{
                    $response = [
                            "message" => "Something went wrong" 
                    ];    
                    $status = 409;  
                }     
            }
        } 
        
        return response($response,$status);
    }
    
    
    public function update(Request $request)
    {
        $sensorId = $request->sensor_id;
        $reason = $request->clearAlertReason;
        $status = 1;
        $statusMessage = "Cleared";
        
        $sensorType = AlertCron::select('*')
                    ->where('id','=',$sensorId)
                    ->where('status','=','0')
                    ->orderBy('id','desc')
                    ->first();
        
        if(!$sensorType){
            $response = [
                "message" => "Data not Found" 
            ];    
            $status = 401;
              
        }else{
            $id = $sensorType->id;
            $alarmType = $sensorType->alarmType;
            $sensorId = $sensorType->sensorId;
            $alertClearType = $sensorType->alertType;
            
            if($alarmType == "UnLatch"){
                
                $response = [
                    "message" => "Unlatch alarm cannot be  cleared manually" 
                ];    
                $status = 401; 
                
            }else{
                
                $getLastAlertQuery = DB::table('sampled_sensor_data_details_MinMaxAvg')
                                     ->where('sensor_id','=',$sensorId)
                                     ->orderBy('id','desc')
                                     ->take(1)
                                     ->get();
                                     
                $count = count($getLastAlertQuery);
                
                if($count>0){
                    $alertType = $getLastAlertQuery[0]->alertType;
                                         
                    if($alertType == "NORMAL"){
                        
                        $getSensorQuery = DB::table('sensors')
                                         ->where('id','=',$sensorId)
                                         ->get();
                    
                        $isStel = $getSensorQuery[0]->isStel;
                        
                        if($isStel == 0){
                            $query = AlertCron::select('*')
                                    ->where('id','=',$id)
                                    ->where('status','=','0')
                                    ->update([
                                         'Reason' => $reason,
                                         'status' => $status,
                                         'statusMessage'=>$statusMessage,
                                         'triggeredAlertFlag'=> 0,
                                         'alarmClearedUser' => $request->header('Userid')
                                    ]);
                            
                            if($query){
                                $response = [
                                    "message" => "Alarms Cleared successfully" 
                                ];    
                                $status = 200; 
                            }else{
                                $response = [
                                    "message" => "Something went wrong" 
                                ];    
                                $status = 401;
                            }
                        }else{
                            if($alertClearType =="outOfRange"){
                                $query = AlertCron::select('*')
                                        ->where('id','=',$id)
                                        ->where('status','=','0')
                                        ->update([
                                             'Reason' => $reason,
                                             'status' => $status,
                                             'statusMessage'=>$statusMessage,
                                             'triggeredAlertFlag'=> 0
                                        ]);
                                
                                    if($query){
                                        $response = [
                                            "message" => "Alarms Cleared successfully" 
                                        ];    
                                        $status = 200; 
                                    }else{
                                        $response = [
                                            "message" => "Something went wrong" 
                                        ];    
                                        $status = 401;
                                    }
                            }else{
                                $otherDataValues = DB::table('sampled_sensor_data_details_MinMaxAvg')
                                                ->join('sensors', 'sensors.id', '=', 'sampled_sensor_data_details_MinMaxAvg.sensor_id')
                                                   ->select(DB::raw('sensors.stelLimit,sensors.deviceId,sensors.deviceName,sensors.sensorTag,sampled_sensor_data_details_MinMaxAvg.sample_date_time as DATE_TIME,sampled_sensor_data_details_MinMaxAvg.sensor_id,sampled_sensor_data_details_MinMaxAvg.parameterName as parameter,sampled_sensor_data_details_MinMaxAvg.time_stamp AS timekey,MAX(sampled_sensor_data_details_MinMaxAvg.max_val) as par_max,MIN(sampled_sensor_data_details_MinMaxAvg.min_val) as par_min,AVG(sampled_sensor_data_details_MinMaxAvg.avg_val)  as par_avg,sampled_sensor_data_details_MinMaxAvg.last_val as par_last'))
                                                   ->whereRaw('sampled_sensor_data_details_MinMaxAvg.time_stamp >(NOW() - INTERVAL 15 MINUTE)')
                                                   ->where('sampled_sensor_data_details_MinMaxAvg.sensor_id','=',$sensorId)
                                                   ->get();
                                                   
                                $stelLimit = $otherDataValues[0]->stelLimit;
                                $avgVal = $otherDataValues[0]->par_avg;
                                
                                if($avgVal > $stelLimit){
                                    $response = [
                                            "message" => "Average value is above the stel limit" 
                                    ];    
                                    $status = 401;
                                }else{
                                    
                                    $query = AlertCron::select('*')
                                        ->where('id','=',$id)
                                        ->where('status','=','0')
                                        ->update([
                                             'Reason' => $reason,
                                             'status' => $status,
                                             'statusMessage'=>$statusMessage,
                                             'triggeredAlertFlag'=> 0
                                        ]);
                                
                                    if($query){
                                        $response = [
                                            "message" => "Alarms Cleared successfully" 
                                        ];    
                                        $status = 200; 
                                    }else{
                                        $response = [
                                            "message" => "Something went wrong" 
                                        ];    
                                        $status = 401;
                                    }
                                }
                            }
                            
                        }
                        
                    }else{
                        $response = [
                            "message" => "Sensor data are not in normal"
                        ];    
                        $status = 409;     
                    }
                }else{
                    $response = [
                            "message" => "Something went wrong" 
                    ];    
                    $status = 409;  
                }     
            }
        } 
        
        // Event logs //17-06-2023
        if($status == 200) {
            $device = DB::table('devices')->where('id', $sensorType->deviceId)->first();
            
            $logController = new EventLogController();
            $eventDetails = [
                "deviceName" => $device->deviceName,
                "reason" => $reason,
            ];
            
            $logController->addLog($request, 'Alarm Clearance', $eventDetails);
        }
        
        return response($response, $status);
    } 
    
    
    
    

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AlertCron  $alertCron
     * @return \Illuminate\Http\Response
     */
    public function destroy($alarmType, $alertCron)
    {
        //
    }
}
