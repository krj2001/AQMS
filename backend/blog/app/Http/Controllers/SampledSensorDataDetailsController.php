<?php

namespace App\Http\Controllers;

use App\Models\SampledSensorDataDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\UtilityController;
use App\Models\AlertCron;
use App\Models\Device;
use App\Http\Controllers\UTILITY\DataUtilityController;
use DateTime;
use URL;
use Carbon\Carbon;

class SampledSensorDataDetailsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    
    protected $companyCode = ""; 
    protected $alertColor = "";
    
    function __construct(Request $request) {
        $getData = new UtilityController($request);
        $this->companyCode = $getData->getCompanyCode(); 
        $this->alertColor = $getData->getAlertColors();
        
    }
    
    
    public function index()
    {
        $query = SampledSensorDataDetails::select('*');
        $response = $query->get();
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
     * @param  \App\Models\SampledSensorDataDetails  $sampledSensorDataDetails
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $sensorData = array();
        $deviceData = array();
         
        $sensorTagNames = array("O3","NH3","PM10","PM2.5","SO2","NO2"); //get the tag names in array based on deveiceid
        $arrlength = count($sensorTagNames);
        
        for($x = 0; $x<$arrlength; $x++){
              $sensorValues = DB::table('sampled_sensor_data_details')
                ->join('sensors', 'sensors.id', '=', 'sampled_sensor_data_details.sensor_id')
                ->select('sampled_sensor_data_details.*', 'sensors.deviceId','sensors.deviceName','sensors.sensorTag')
                ->where('parameterName','=',$sensorTagNames[$x])
                ->whereBetween('sample_date_time', ['2022-05-14','2022-05-15'])
                ->orderBy('id','desc')
                ->skip(0)->take(10)
                ->get()->toArray();
                $sensorData["id"] =$sensorTagNames[$x];  
                foreach($sensorValues as $sensor){
                  	$sensorData["data"][] = [ 
          	            "y"=>$sensor->last_val,
          	            "x"=>$sensor->sample_date_time
                  	];
                }
                $deviceData[] = $sensorData;
                $sensorData["data"] = [];
        }
        return response($deviceData,200);
    }
    
    public function deviceSensorShow(Request $request){
        $sensorValues = DB::table('sampled_sensor_data_details')
                ->join('sensors', 'sensors.id', '=', 'sampled_sensor_data_details.sensor_id')
                ->select('sampled_sensor_data_details.*', 'sensors.deviceId','sensors.deviceName','sensors.sensorTag')
                ->where('parameterName','=',"PM10")
                ->whereBetween('sample_date_time', ['2022-05-14','2022-05-15'])
                ->orderBy('id','desc')
                ->get()->toArray();
                
        
        $deviceData = [];
        $deviceData['id'] = "PM10";
        $i = 0;
        foreach($sensorValues as $sensor){
            if($i<10){
          	$deviceData["data"][] = [ 
          	            "x"=>$sensor->last_val,
          	            "y"=>$sensor->sample_date_time
          	];
            }
            $i++;
        }
        
        return response($deviceData,200);
    }
    
    /* function to get last sampled sensor data based on device id */
    public function lastSampledData(Request $request){
        
        //detail of sending data to frontend
        // sensorID,
        // Segregation Interval - 30min/1hr/3hrs/6hr/12hr/24hrs
        // Range Interval - 6hr/12hrs/1day/1week/1month (edited) 
      
        $deviceId = $request->deviceId;
        $segregationInterval = $request->segretionInterval; //in mins   $sampling_Interval_min=60;
        $rangeInterval = $request->rangeInterval; //in mins  $backInterval_min=24*60;
        
        $deviceData = array();
        
        $sampling_Interval_min=60;
        $cur_date_time=date("Y-m-d H:i:s");
        $backInterval_min=24*60;
        $date_from=date("Y-m-d H:i:s",strtotime($cur_date_time)-$backInterval_min*60);
       
        $sensorTagIds = DB::table('sensors')
                        ->select('id')
                        ->where('deviceId','=',$deviceId)
                        ->get();
                        
        $length = count($sensorTagIds);
        
        for($x = 0; $x<$length; $x++){
            
                $otherDataValues = DB::table('sampled_sensor_data_details')
                        ->join('sensors', 'sensors.id', '=', 'sampled_sensor_data_details.sensor_id')
                           ->select(DB::raw('sensors.deviceId,sensors.deviceName,sensors.sensorTag,sampled_sensor_data_details.sample_date_time as DATE_TIME,sampled_sensor_data_details.sensor_id,sampled_sensor_data_details.parameterName as parameter,sampled_sensor_data_details.time_stamp AS timekey,MAX(sampled_sensor_data_details.max_val) as par_max,MIN(sampled_sensor_data_details.min_val) as par_min,AVG(sampled_sensor_data_details.avg_val)  as par_avg,sampled_sensor_data_details.last_val as par_last'))
                           ->whereRaw('sampled_sensor_data_details.time_stamp >(NOW() - INTERVAL '.$rangeInterval.' MINUTE)')
                           ->where('sampled_sensor_data_details.sensor_id','=',$sensorTagIds[$x]->id)
                           ->get();
                           
                $minVal = $otherDataValues[0]->par_min;
                $maxVal = $otherDataValues[0]->par_max;
                $avgVal = $otherDataValues[0]->par_min;
                $sensorTagName = $otherDataValues[0]->sensorTag;
                
                if($sensorTagName != ""){
                    $sensorValues = DB::table('sampled_sensor_data_details')
                        ->join('sensors', 'sensors.id', '=', 'sampled_sensor_data_details.sensor_id')
                        ->select(DB::raw('sensors.deviceId,sensors.deviceName,sensors.sensorTag,sampled_sensor_data_details.sample_date_time as DATE_TIME,sampled_sensor_data_details.sensor_id,sampled_sensor_data_details.parameterName as parameter,FLOOR(UNIX_TIMESTAMP(sampled_sensor_data_details.time_stamp)/("'. $segregationInterval.'" * 60)) AS timekey,MAX(sampled_sensor_data_details.max_val) as par_max,MIN(sampled_sensor_data_details.min_val) as par_min,AVG(sampled_sensor_data_details.avg_val)  as par_avg,sampled_sensor_data_details.last_val as par_last'))
                        ->whereRaw('sampled_sensor_data_details.time_stamp >=(NOW() - INTERVAL '.$rangeInterval.' MINUTE)')
                        ->where('sampled_sensor_data_details.sensor_id','=',$sensorTagIds[$x]->id)
                        ->groupBy('timekey')
                        ->get()->toArray();
                                
                    $sensorData["id"] =$sensorTagIds[$x]->id; 
                    // if($sensorTagIds[$x]->id != 43){
                        $sensorData["sensorTag"] = $sensorTagName;
                        $sensorData["min"] = $minVal;
                        $sensorData["max"] = $maxVal;
                        $sensorData["avg"] = $avgVal;
                            foreach($sensorValues as $sensor){
                              	$sensorData["data"][] = [ 
                      	            "y"=>$sensor->par_last,
                      	            "x"=>$sensor->DATE_TIME
                              	];
                            }
                            $deviceData[] = $sensorData;
                            $sensorData["data"] = [];
                            $sensorData["min"] = "";
                            $sensorData["max"] = "";
                            $sensorData["avg"] = "";
                        //}
                    }    
                    
                    
        }
        return response($deviceData,200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SampledSensorDataDetails  $sampledSensorDataDetails
     * @return \Illuminate\Http\Response
     */
    public function edit(SampledSensorDataDetails $sampledSensorDataDetails)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SampledSensorDataDetails  $sampledSensorDataDetails
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SampledSensorDataDetails $sampledSensorDataDetails)
    {
       
    }
    
    /* working function getting single sensor data based on sengeation */
    public function getLastSampledDataOfSensorTagIdBarLineOld(Request $request){
        $color = "";
        if($request->sensorTagId == ""){
            $response = [
                  "data"=>"Sensor Tag Id is required"  
                ];
            $status = 401;
        }
        
        if($request->segretionInterval == ""){
            $response = [
                  "data"=>"Segregation Interval is required"  
                ];
                $status = 401;
        }
        
        if($request->rangeInterval == ""){
            $response = [
                  "data"=>"Range Interval is required"  
                ];
                $status = 401;
            
        }else{
            $sensorTagId = $request->sensorTagId;
            $segregationInterval = $request->segretionInterval; //in mins   $sampling_Interval_min=60;
            $rangeInterval = $request->rangeInterval; //  $backInterval_min=24*60;
            
            $sampling_Interval_min=60;
            $cur_date_time=date("Y-m-d H:i:s");
            $backInterval_min=24*60;
            $date_from=date("Y-m-d H:i:s",strtotime($cur_date_time)-$backInterval_min*60);
           
            //single sensortag data
            $sensorData = array();
            $deviceData = array();
            
            $otherDataValues = DB::table('sampled_sensor_data_details_MinMaxAvg')
                                ->join('sensors', 'sensors.id', '=', 'sampled_sensor_data_details_MinMaxAvg.sensor_id')
                               ->select(DB::raw('sensors.deviceId,sensors.deviceName,sensors.sensorTag,sampled_sensor_data_details_MinMaxAvg.sample_date_time as DATE_TIME,sampled_sensor_data_details_MinMaxAvg.sensor_id,sampled_sensor_data_details_MinMaxAvg.parameterName as parameter,sampled_sensor_data_details_MinMaxAvg.alertType,FLOOR(UNIX_TIMESTAMP(sampled_sensor_data_details_MinMaxAvg.time_stamp)/("'. $segregationInterval.'" * 60)) AS timekey,MAX(sampled_sensor_data_details_MinMaxAvg.max_val) as par_max,MIN(sampled_sensor_data_details_MinMaxAvg.min_val) as par_min,AVG(sampled_sensor_data_details_MinMaxAvg.avg_val)  as par_avg,sampled_sensor_data_details_MinMaxAvg.last_val as par_last'))
                               ->whereRaw('sampled_sensor_data_details_MinMaxAvg.time_stamp >=(NOW() - INTERVAL '.$rangeInterval.' MINUTE)')
                               ->where('sampled_sensor_data_details_MinMaxAvg.sensor_id','=',$sensorTagId)
                               ->get();
                          
            $minVal = $otherDataValues[0]->par_min;
            $maxVal = $otherDataValues[0]->par_max;
            $avgVal = $otherDataValues[0]->par_min;
            $sensorTag = $otherDataValues[0]->sensorTag;
          
            $sensorValues = DB::table('sampled_sensor_data_details_MinMaxAvg')
                            ->join('sensors', 'sensors.id', '=', 'sampled_sensor_data_details_MinMaxAvg.sensor_id')
                            ->select(DB::raw('sensors.deviceId,sensors.criticalAlertType,sensors.deviceName,sensors.sensorTag,sensors.criticalMaxValue,sensors.warningMaxValue,sensors.outofrangeMaxValue,sensors.criticalMinValue,sensors.warningMinValue,sensors.outofrangeMinValue,sampled_sensor_data_details_MinMaxAvg.alertType,sampled_sensor_data_details_MinMaxAvg.sevierity,DATE(sampled_sensor_data_details_MinMaxAvg.sample_date_time) as DATE,TIME_FORMAT(sampled_sensor_data_details_MinMaxAvg.sample_date_time, "%H:%i") as TIME,sampled_sensor_data_details_MinMaxAvg.sensor_id,sampled_sensor_data_details_MinMaxAvg.parameterName as parameter,FLOOR(UNIX_TIMESTAMP(sampled_sensor_data_details_MinMaxAvg.time_stamp)/("'. $segregationInterval.'" * 60)) AS timekey,MAX(sampled_sensor_data_details_MinMaxAvg.max_val) as par_max,MIN(sampled_sensor_data_details_MinMaxAvg.min_val) as par_min,AVG(sampled_sensor_data_details_MinMaxAvg.avg_val)  as par_avg,sampled_sensor_data_details_MinMaxAvg.last_val as par_last'))
                            ->whereRaw('sampled_sensor_data_details_MinMaxAvg.time_stamp >=(NOW() - INTERVAL '.$rangeInterval.' MINUTE)')
                            ->where('sampled_sensor_data_details_MinMaxAvg.sensor_id','=',$sensorTagId)
                            ->groupBy('timekey')
                            ->get()->toArray();                           
           
            $sensorData["sensorTag"] = $sensorTag;

            foreach($sensorValues as $sensor){
                //setting colors based on values
                if($sensor->alertType == "outOfRange"){
                    $color = $this->alertColor['OUTOFRANGE'];
                }
                if($sensor->alertType == "Critical"){
                    $color = $this->alertColor['CRITICAL'];
                }
                if($sensor->alertType == "Warning"){
                    $color = $this->alertColor['WARNING'];
                }
                if($sensor->alertType == "NORMAL"){
                    $color = $this->alertColor['NORMAL'];
                }
                
                //Not to display out of range values
                
                // if($sensor->sevierity !== "LOW" && $sensor->alertType != "Out Of Range"){
                    
                    $sensorData["labels"][] = $sensor->DATE." ".$sensor->TIME;
                    $sensorData["colors"][]= $color;
                    //$sensorData["avgData"][] = $sensor->par_avg;
                    $sensorData["minData"][] = $sensor->par_min;
                    $sensorData["maxData"][] = $sensor->par_max;
                    $sensorData["lastData"][] = $sensor->par_last;
                    $sensorData["warningLevelMin"][] = $sensor->warningMinValue;
                    $sensorData["criticalLevelMin"][] = $sensor->criticalMinValue;
                    $sensorData["outofrangeLevelMin"][] = $sensor->outofrangeMinValue;
                    $sensorData["warningLevelMax"][] = $sensor->warningMaxValue;
                    $sensorData["criticalLevelMax"][] = $sensor->criticalMaxValue;
                    $sensorData["outofrangeLevelMax"][] = $sensor->outofrangeMaxValue;
                    
                   
               
                }
            // }             
                            
            $response = $sensorData;
            $status = 200;
        }
        
        
        return response($response,$status);
    }
    
    
    
    public function getLastSampledDataOfSensorTagIdBarLine(Request $request){
        $color = "";
        if($request->sensorTagId == ""){
            $response = [
                  "data"=>"Sensor Tag Id is required"  
                ];
            $status = 401;
        }
        
        if($request->segretionInterval == ""){
            $response = [
                  "data"=>"Segregation Interval is required"  
                ];
                $status = 401;
        }
        
        if($request->rangeInterval == ""){
            $response = [
                  "data"=>"Range Interval is required"  
                ];
                $status = 401;
            
        }else{
            $sensorTagId = $request->sensorTagId;
            $segregationInterval = intval($request->segretionInterval); //in mins   $sampling_Interval_min=60;
            $rangeInterval = intval($request->rangeInterval); //  $backInterval_min=24*60;
            
            $sampling_Interval_min=60;
            $cur_date_time=date("Y-m-d H:i:s");
            $backInterval_min=24*60;
            $date_from=date("Y-m-d H:i:s",strtotime($cur_date_time)-$backInterval_min*60);
           
            //single sensortag data
            $sensorData = array();
            $deviceData = array();
            
            $otherDataValues = DB::table('sampled_sensor_data_details_MinMaxAvg')
                                ->join('sensors', 'sensors.id', '=', 'sampled_sensor_data_details_MinMaxAvg.sensor_id')
                               ->select(DB::raw('sensors.deviceId,sensors.deviceName,sensors.sensorTag,sampled_sensor_data_details_MinMaxAvg.sample_date_time as DATE_TIME,sampled_sensor_data_details_MinMaxAvg.sensor_id,sampled_sensor_data_details_MinMaxAvg.parameterName as parameter,sampled_sensor_data_details_MinMaxAvg.alertType,FLOOR(UNIX_TIMESTAMP(sampled_sensor_data_details_MinMaxAvg.current_date_time)/("'. $segregationInterval.'" * 60)) AS timekey,MAX(sampled_sensor_data_details_MinMaxAvg.max_val) as par_max,MIN(sampled_sensor_data_details_MinMaxAvg.min_val) as par_min,AVG(sampled_sensor_data_details_MinMaxAvg.avg_val)  as par_avg,sampled_sensor_data_details_MinMaxAvg.last_val as par_last'))
                               ->whereRaw('sampled_sensor_data_details_MinMaxAvg.time_stamp >=(NOW() - INTERVAL '.$rangeInterval.' MINUTE)')
                               ->where('sampled_sensor_data_details_MinMaxAvg.sensor_id','=',$sensorTagId)
                               ->get();
                          
            $minVal = $otherDataValues[0]->par_min;
            $maxVal = $otherDataValues[0]->par_max;
            $avgVal = $otherDataValues[0]->par_min;
            $sensorTag = $otherDataValues[0]->sensorTag;
          
            $sensorValues = DB::table('sampled_sensor_data_details_MinMaxAvg')
                            ->join('sensors', 'sensors.id', '=', 'sampled_sensor_data_details_MinMaxAvg.sensor_id')
                            ->select(DB::raw('sensors.deviceId,sensors.criticalAlertType,sensors.warningAlertType,sensors.outofrangeAlertType,sensors.deviceName,sensors.sensorTag,sensors.criticalMaxValue,sensors.warningMaxValue,sensors.outofrangeMaxValue,sensors.criticalMinValue,sensors.warningMinValue,sensors.outofrangeMinValue,sampled_sensor_data_details_MinMaxAvg.alertType,sampled_sensor_data_details_MinMaxAvg.sevierity,DATE(sampled_sensor_data_details_MinMaxAvg.current_date_time) as DATE,TIME_FORMAT(sampled_sensor_data_details_MinMaxAvg.current_date_time, "%H:%i") as TIME,sampled_sensor_data_details_MinMaxAvg.sensor_id,sampled_sensor_data_details_MinMaxAvg.parameterName as parameter,FLOOR(UNIX_TIMESTAMP(sampled_sensor_data_details_MinMaxAvg.time_stamp)/("'. $segregationInterval.'" * 60)) AS timekey,MAX(sampled_sensor_data_details_MinMaxAvg.max_val) as par_max,MIN(sampled_sensor_data_details_MinMaxAvg.min_val) as par_min,AVG(sampled_sensor_data_details_MinMaxAvg.avg_val)  as par_avg,sampled_sensor_data_details_MinMaxAvg.last_val as par_last'))
                            ->whereRaw('sampled_sensor_data_details_MinMaxAvg.time_stamp >=(NOW() - INTERVAL '.$rangeInterval.' MINUTE)')
                            ->where('sampled_sensor_data_details_MinMaxAvg.sensor_id','=',$sensorTagId)
                            ->groupBy('timekey')
                            ->get()->toArray();                           
           
            $sensorData["sensorTag"] = $sensorTag;

            foreach($sensorValues as $sensor){
                //setting colors based on values
                if($sensor->alertType == "outOfRange"){
                    $color = $this->alertColor['OUTOFRANGE'];
                }
                if($sensor->alertType == "Critical"){
                    $color = $this->alertColor['CRITICAL'];
                }
                if($sensor->alertType == "Warning"){
                    $color = $this->alertColor['WARNING'];
                }
                if($sensor->alertType == "NORMAL"){
                    $color = $this->alertColor['NORMAL'];
                }
                
                //Not to display out of range values
                
                // if($sensor->sevierity !== "LOW" && $sensor->alertType != "Out Of Range"){
                    
                    $sensorData["labels"][] = $sensor->DATE." ".$sensor->TIME;
                    $sensorData["colors"][]= $color;
                    //$sensorData["avgData"][] = $sensor->par_avg;
                    $sensorData["minData"][] = $sensor->par_min;
                    $sensorData["maxData"][] = $sensor->par_max;
                    $sensorData["lastData"][] = $sensor->par_last;
                    
                    //critical
                    if($sensor->criticalAlertType == "High")
                    {
                        $sensorData["criticalLevelMax"][] = $sensor->criticalMaxValue;
                    }
                    
                    if($sensor->criticalAlertType == "Low")
                    {
                        $sensorData["criticalLevelMin"][] = $sensor->criticalMinValue;
                    }
                    
                    if($sensor->criticalAlertType == "Both")
                    {
                        $sensorData["criticalLevelMin"][] = $sensor->criticalMinValue;
                        $sensorData["criticalLevelMax"][] = $sensor->criticalMaxValue;
                    }
                    
                    if($sensor->warningAlertType == "High")
                    {
                        $sensorData["warningLevelMax"][] = $sensor->warningMaxValue;
                    }
                    
                    if($sensor->warningAlertType == "Low")
                    {
                        $sensorData["warningLevelMin"][] = $sensor->warningMinValue;
                    }
                    
                    if($sensor->warningAlertType == "Both")
                    {
                        $sensorData["warningLevelMin"][] = $sensor->warningMinValue;
                        $sensorData["warningLevelMax"][] = $sensor->warningMaxValue;
                    }
                    
                    
                    if($sensor->outofrangeAlertType == "High")
                    {
                        $sensorData["outofrangeLevelMax"][] = $sensor->outofrangeMaxValue;
                    }
                    
                    if($sensor->outofrangeAlertType == "Low")
                    {
                        $sensorData["outofrangeLevelMin"][] = $sensor->outofrangeMinValue;
                    }
                    
                    if($sensor->outofrangeAlertType == "Both")
                    {
                        $sensorData["outofrangeLevelMin"][] = $sensor->outofrangeMinValue;
                        $sensorData["outofrangeLevelMax"][] = $sensor->outofrangeMaxValue;
                    }
                    
                    // $sensorData["criticalAlertType"][] = $sensor->criticalAlertType;
                    // $sensorData["warningAlertType"][] = $sensor->warningAlertType;
                    // $sensorData["outofrangeAlertType"][] = $sensor->outofrangeAlertType;
                }
            // }             
                            
            $response = $sensorData;
            $status = 200;
        }
        
        return response($response,$status);
    }
    
    public function getLastSampledDataOfSensorTagId(Request $request){
        
        if($request->sensorTagId == ""){
            $response = [
                  "data"=>"Sensor Tag Id is required"  
                ];
            $status = 401;
        }
        
        if($request->segretionInterval == ""){
            $response = [
                  "data"=>"Segregation Interval is required"  
                ];
                $status = 401;
        }
        
        if($request->rangeInterval == ""){
            $response = [
                  "data"=>"Range Interval is required"  
                ];
                $status = 401;
            
        }else{
            $sensorTagId = $request->sensorTagId;
            $segregationInterval = $request->segretionInterval; //in mins   $sampling_Interval_min=60;
            $rangeInterval = $request->rangeInterval; //  $backInterval_min=24*60;
            
            
            $sampling_Interval_min=60;
            $cur_date_time=date("Y-m-d H:i:s");
            $backInterval_min=24*60;
            $date_from=date("Y-m-d H:i:s",strtotime($cur_date_time)-$backInterval_min*60);
           
            //single sensortag data
            
            $sensorData = array();
            $deviceData = array();
            
            $otherDataValues = DB::table('sampled_sensor_data_details')
                                ->join('sensors', 'sensors.id', '=', 'sampled_sensor_data_details_MinMaxAvg.sensor_id')
                               ->select(DB::raw('sensors.deviceId,sensors.deviceName,sensors.sensorTag,sampled_sensor_data_details_MinMaxAvg.sample_date_time as DATE_TIME,sampled_sensor_data_details_MinMaxAvg.sensor_id,sampled_sensor_data_details_MinMaxAvg.parameterName as parameter,sampled_sensor_data_details_MinMaxAvg.time_stamp AS timekey,MAX(sampled_sensor_data_details.max_val) as par_max,MIN(sampled_sensor_data_details_MinMaxAvg.min_val) as par_min,AVG(sampled_sensor_data_details_MinMaxAvg.avg_val)  as par_avg,sampled_sensor_data_details_MinMaxAvg.last_val as par_last'))
                               ->whereRaw('sampled_sensor_data_details_MinMaxAvg.time_stamp >= (NOW() - INTERVAL '.$rangeInterval.' MINUTE)')
                               ->where('sampled_sensor_data_details_MinMaxAvg.sensor_id','=',$sensorTagId)
                               ->get();
                               
            $minVal = $otherDataValues[0]->par_min;
            $maxVal = $otherDataValues[0]->par_max;
            $avgVal = $otherDataValues[0]->par_min;
            $sensorTag = $otherDataValues[0]->sensorTag;
             
          
            $sensorValues = DB::table('sampled_sensor_data_details')
                            ->join('sensors', 'sensors.id', '=', 'sampled_sensor_data_details_MinMaxAvg.sensor_id')
                            ->select(DB::raw('sensors.deviceId,sensors.deviceName,sensors.sensorTag,sampled_sensor_data_details_MinMaxAvg.sample_date_time as DATE_TIME,sampled_sensor_data_details_MinMaxAvg.sensor_id,sampled_sensor_data_details_MinMaxAvg.parameterName as parameter,FLOOR(UNIX_TIMESTAMP(sampled_sensor_data_details_MinMaxAvg.time_stamp)/("'. $segregationInterval.'" * 60)) AS timekey,MAX(sampled_sensor_data_details_MinMaxAvg.max_val) as par_max,MIN(sampled_sensor_data_details_MinMaxAvg.min_val) as par_min,AVG(sampled_sensor_data_details_MinMaxAvg.avg_val)  as par_avg,sampled_sensor_data_details_MinMaxAvg.last_val as par_last'))
                            ->whereRaw('sampled_sensor_data_details_MinMaxAvg.time_stamp >= (NOW() - INTERVAL '.$rangeInterval.' MINUTE)')
                            ->where('sampled_sensor_data_details_MinMaxAvg.sensor_id','=',$sensorTagId)
                            ->groupBy('timekey')
                            ->get()->toArray();
                            
            $sensorData["id"] = $sensorTag;
            $sensorData["min"] = $minVal;
            $sensorData["max"] = $maxVal;
            $sensorData["avg"] = $avgVal;
            foreach($sensorValues as $sensor){
              	$sensorData["data"][] = [ 
      	            "y"=>$sensor->par_last,
      	            "x"=>$sensor->DATE_TIME
              	];
            }               
                            
            $response = $sensorData;
            $status = 200;
        }
        
        return response($response,$status);
    }
    
    public function liveDataDeviceIdOld(Request $request){
         
        $deviceId = $request->device_id;
        
        $deviceDisconnectedStatus = DB::table('devices')
                            ->where('id','=',$deviceId)
                            ->get();
                            
        $disconnectedStatus = $deviceDisconnectedStatus[0]->disconnectedStatus;
                    
                    
        $deviceAqiIndex = DB::table('Aqi_values_per_device')
                    ->where('deviceId','=',$deviceId)
                    ->orderBy('id','desc')
                    ->first();
                    
        if($deviceAqiIndex)
        {
            $aqiIndex =  $deviceAqiIndex->AqiValue;
        }
        else
        {
            $aqiIndex =  "NA";
        }
        
        $alertQuery = AlertCron::select('*')
                     ->where('deviceId','=',$deviceId)
                     ->where('companyCode','=',$this->companyCode)
                     ->where('status','=','0')
                     ->take(50)
                     ->get();
                 
        $alertCount = $alertQuery->count();    
         
        $sensorTagsOfDeviceId = DB::table('customers as c')
                ->join('locations as l', 'c.customerId', '=', 'l.companyCode')
                ->Join('branches as b', function($join){
                    $join->on('l.id', '=', 'b.location_id')
                         ->on('c.customerId', '=', 'b.companyCode');
                })
                ->Join('facilities as f', function($join){
                    $join->on('c.customerId', '=', 'f.companyCode')
                        ->on('l.id', '=', 'f.location_id')
                        ->on('b.id', '=', 'f.branch_id');
                })
                ->Join('buildings as bl', function($join){
                    $join->on('c.customerId', '=', 'bl.companyCode')
                        ->on('l.id', '=', 'bl.location_id')
                        ->on('b.id', '=', 'bl.branch_id')
                        ->on('f.id','=','bl.facility_id');
                })
                ->Join('floors as fl', function($join){
                    $join->on('c.customerId', '=', 'fl.companyCode')
                        ->on('l.id', '=', 'fl.location_id')
                        ->on('b.id', '=', 'fl.branch_id')
                        ->on('f.id','=','fl.facility_id')
                        ->on('bl.id','=','fl.building_id');
                })
                ->Join('lab_departments as lb', function($join){
                    $join->on('c.customerId', '=', 'lb.companyCode')
                        ->on('l.id', '=', 'lb.location_id')
                        ->on('b.id', '=', 'lb.branch_id')
                        ->on('f.id','=','lb.facility_id')
                        ->on('bl.id','=','lb.building_id')
                        ->on('fl.id','=','lb.floor_id');
                })
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
                ->select('c.customerId', 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorNameUnit','s.sensorOutput','s.id','s.sensorTag', 's.sensorStatus','s.units','s.maxRatedReadingScale','s.minRatedReadingScale')
                ->WHERE('customerId','=',$this->companyCode)
                ->WHERE('deviceId','=',$deviceId)
                ->get();
                
        $length = count($sensorTagsOfDeviceId);      
        $sensorCount = 0;
        
        $deviceData = array();
        $output = array();
        $sensorData = array();
        
        $alertColor = "";
        $alertLightColor = "";
        //SELECT AVG(avg_val) FROM `sampled_sensor_data_details_MinMaxAvg` where device_id = 120 and sensor_id = 208 and time_stamp >= DATE_SUB(NOW(),INTERVAL 1500 MINUTE) order by id desc
        for($x=0;$x<$length;$x++){
            $otherDataValues = DB::table('sampled_sensor_data_details_MinMaxAvg')
                    ->join('sensors', 'sensors.id', '=', 'sampled_sensor_data_details_MinMaxAvg.sensor_id')
                    ->select(DB::raw('sensors.deviceId,sensors.deviceName,sensors.sensorTag,sensors.units,sensors.maxRatedReadingScale,sensors.minRatedReadingScale,sampled_sensor_data_details_MinMaxAvg.alertType,sampled_sensor_data_details_MinMaxAvg.sample_date_time as DATE_TIME,sampled_sensor_data_details_MinMaxAvg.sensor_id,sampled_sensor_data_details_MinMaxAvg.parameterName as parameter,sampled_sensor_data_details_MinMaxAvg.sample_date_time AS timekey,  MAX(sampled_sensor_data_details_MinMaxAvg.avg_val) as par_max,   MIN(sampled_sensor_data_details_MinMaxAvg.avg_val) as par_min,  AVG(sampled_sensor_data_details_MinMaxAvg.avg_val) as par_avg,sampled_sensor_data_details_MinMaxAvg.last_val as par_last'))
                    ->where('sampled_sensor_data_details_MinMaxAvg.sensor_id','=',$sensorTagsOfDeviceId[$x]->id)
                    ->whereRaw('time_stamp >= DATE_SUB(NOW(),INTERVAL 15 MINUTE)')
                    ->orderBy('sampled_sensor_data_details_MinMaxAvg.id','desc')
                    ->first();
                        
            if($otherDataValues != ""){
                
                $fetchDataValues = DB::table('sampled_sensor_data_details_MinMaxAvg')
                        ->join('sensors', 'sensors.id', '=', 'sampled_sensor_data_details_MinMaxAvg.sensor_id')
                        ->select(DB::raw('sampled_sensor_data_details_MinMaxAvg.last_val,sampled_sensor_data_details_MinMaxAvg.alertType'))
                        ->where('sampled_sensor_data_details_MinMaxAvg.sensor_id','=',$sensorTagsOfDeviceId[$x]->id)
                        ->whereRaw('time_stamp >= DATE_SUB(NOW(),INTERVAL 1 MINUTE)')
                        ->orderBy('sampled_sensor_data_details_MinMaxAvg.id','desc')
                        ->first();
                        
                if($fetchDataValues!=""){
                    $l = $fetchDataValues->last_val;
                    if($fetchDataValues->alertType === "Critical"){
                        $alertColor = $this->alertColor['CRITICAL'];
                        $alertLightColor = $this->alertColor['CRITICALLIGHTCOLOR'];
                    }
                    if($fetchDataValues->alertType === "Warning"){
                        $alertColor = $this->alertColor['WARNING'];
                        $alertLightColor = $this->alertColor['WARNINGLIGHTCOLOR'];
                    }
                    if($fetchDataValues->alertType === "outOfRange"){
                        $alertColor = $this->alertColor['OUTOFRANGE'];
                        $alertLightColor = $this->alertColor['OUTOFRANGELIGHTCOLOR'];
                    }
                    if($fetchDataValues->alertType === "NORMAL"){
                        $alertColor = $this->alertColor['NORMAL'];
                        $alertLightColor = $this->alertColor['NORMALLIGHTCOLOR'];
                    }
                }else{
                    $l = 0;
                }
                    
                $min = floatval($otherDataValues->par_min);
                $max = floatval($otherDataValues->par_max);
                $avg = floatval($otherDataValues->par_avg);
                $last = floatval($l);
                $minVal = number_format($min,2);
                $maxVal = number_format($max,2);
                $avgVal = number_format($avg,2);
                $lastVal = number_format($last,2);
                $sensorTagName = $otherDataValues->sensorTag; 
                
                if($sensorTagName != ""){
                    if($sensorTagsOfDeviceId[$x]->sensorOutput == "Analog"){
                        $sensorData['customerId']  = $sensorTagsOfDeviceId[$x]->customerId;
                        $sensorData['stateName']   = $sensorTagsOfDeviceId[$x]->stateName;
                        $sensorData['branchName']  = $sensorTagsOfDeviceId[$x]->branchName;
                        $sensorData['facilityName'] = $sensorTagsOfDeviceId[$x]->facilityName;
                        $sensorData['buildingName'] = $sensorTagsOfDeviceId[$x]->buildingName;
                        $sensorData['floorName'] = $sensorTagsOfDeviceId[$x]->floorName;
                        $sensorData['labDepName'] = $sensorTagsOfDeviceId[$x]->labDepName;
                        $sensorData['deviceName'] = $sensorTagsOfDeviceId[$x]->deviceName;
                        $sensorData['sensorNameUnit'] = $sensorTagsOfDeviceId[$x]->sensorNameUnit;
                        $sensorData['sensorOutput'] = $sensorTagsOfDeviceId[$x]->sensorOutput;
                        $sensorData['units'] = $sensorTagsOfDeviceId[$x]->units;
                        $sensorData['minRatedReadingScale'] = $sensorTagsOfDeviceId[$x]->minRatedReadingScale;
                        $sensorData['maxRatedReadingScale'] = $sensorTagsOfDeviceId[$x]->maxRatedReadingScale;
                        $sensorData["sensorTag"] = $sensorTagName;
                        $sensorData["sensorTagId"] = $sensorTagsOfDeviceId[$x]->id;
                        $sensorData["sensorStatus"] = $sensorTagsOfDeviceId[$x]->sensorStatus;
                        $sensorData["min"] = $minVal;
                        $sensorData["max"] = $maxVal;
                        $sensorData["avg"] = $avgVal;
                        $sensorData["last"] = $lastVal;
                        $sensorData["alertColor"] = $alertColor;
                        $sensorData["alertLightColor"] = $alertLightColor;
                        $deviceData['Analog']['data'][] = $sensorData;
                        $sensorCount++;
                    }
                    
                    if($sensorTagsOfDeviceId[$x]->sensorOutput == "Digital"){
                        $sensorData['customerId']  = $sensorTagsOfDeviceId[$x]->customerId;
                        $sensorData['stateName']   = $sensorTagsOfDeviceId[$x]->stateName;
                        $sensorData['branchName']  = $sensorTagsOfDeviceId[$x]->branchName;
                        $sensorData['facilityName'] = $sensorTagsOfDeviceId[$x]->facilityName;
                        $sensorData['buildingName'] = $sensorTagsOfDeviceId[$x]->buildingName;
                        $sensorData['floorName'] = $sensorTagsOfDeviceId[$x]->floorName;
                        $sensorData['labDepName'] = $sensorTagsOfDeviceId[$x]->labDepName;
                        $sensorData['deviceName'] = $sensorTagsOfDeviceId[$x]->deviceName;
                        $sensorData['sensorNameUnit'] = $sensorTagsOfDeviceId[$x]->sensorNameUnit;
                        $sensorData['sensorOutput'] = $sensorTagsOfDeviceId[$x]->sensorOutput;
                        $sensorData['units'] = $sensorTagsOfDeviceId[$x]->units;
                        $sensorData['minRatedReadingScale'] = $sensorTagsOfDeviceId[$x]->minRatedReadingScale;
                        $sensorData['maxRatedReadingScale'] = $sensorTagsOfDeviceId[$x]->maxRatedReadingScale;
                        $sensorData["sensorTag"] = $sensorTagName;
                        $sensorData["sensorTagId"] = $sensorTagsOfDeviceId[$x]->id;
                        $sensorData["sensorStatus"] = $sensorTagsOfDeviceId[$x]->sensorStatus;
                        $sensorData["min"] = $minVal;
                        $sensorData["max"] = $maxVal;
                        $sensorData["avg"] = $avgVal;
                        $sensorData["last"] = $lastVal;
                        $sensorData["alertColor"] = $alertColor;
                        $sensorData["alertLightColor"] = $alertLightColor;
                        $deviceData['Digital']['data'][] = $sensorData;
                        $sensorCount++;
                    }
                    
                    if($sensorTagsOfDeviceId[$x]->sensorOutput == "Modbus"){
                        $sensorData['customerId']  = $sensorTagsOfDeviceId[$x]->customerId;
                        $sensorData['stateName']   = $sensorTagsOfDeviceId[$x]->stateName;
                        $sensorData['branchName']  = $sensorTagsOfDeviceId[$x]->branchName;
                        $sensorData['facilityName'] = $sensorTagsOfDeviceId[$x]->facilityName;
                        $sensorData['buildingName'] = $sensorTagsOfDeviceId[$x]->buildingName;
                        $sensorData['floorName'] = $sensorTagsOfDeviceId[$x]->floorName;
                        $sensorData['labDepName'] = $sensorTagsOfDeviceId[$x]->labDepName;
                        $sensorData['deviceName'] = $sensorTagsOfDeviceId[$x]->deviceName;
                        $sensorData['sensorNameUnit'] = $sensorTagsOfDeviceId[$x]->sensorNameUnit;
                        $sensorData['sensorOutput'] = $sensorTagsOfDeviceId[$x]->sensorOutput;
                        $sensorData['units'] = $sensorTagsOfDeviceId[$x]->units;
                        $sensorData['minRatedReadingScale'] = $sensorTagsOfDeviceId[$x]->minRatedReadingScale;
                        $sensorData['maxRatedReadingScale'] = $sensorTagsOfDeviceId[$x]->maxRatedReadingScale;
                        $sensorData["sensorTag"] = $sensorTagName;
                        $sensorData["sensorTagId"] = $sensorTagsOfDeviceId[$x]->id;
                        $sensorData["sensorStatus"] = $sensorTagsOfDeviceId[$x]->sensorStatus;
                        $sensorData["min"] = $minVal;
                        $sensorData["max"] = $maxVal;
                        $sensorData["avg"] = $avgVal;
                        $sensorData["last"] = $lastVal;
                        $sensorData["alertColor"] = $alertColor;
                        $sensorData["alertLightColor"] = $alertLightColor;
                        $deviceData['Modbus']['data'][] = $sensorData;
                        $sensorCount++;
                    }
                }
            }
        }
           
        $deviceData['sensorCount'] = $sensorCount;
        $deviceData['alertCount'] = $alertCount;
        $deviceData['aqiIndex'] = $aqiIndex;
        $deviceData['disconnectedStatus'] = $disconnectedStatus;
        $response = $deviceData;
                
        return response($response, 200);
                
    }
    
    public function liveDataDeviceIdold2(Request $request){
         
        $deviceId = $request->device_id;
        
        // $deviceAqiIndex = DB::table('Aqi_values_per_device')
        //             ->where('deviceId','=',$deviceId)
        //             ->orderBy('id','desc')
        //             ->first();
                    
        // if($deviceAqiIndex)
        // {
        //     $aqiIndex =  $deviceAqiIndex->AqiValue;
        // }
        // else
        // {
        //     $aqiIndex =  "NA";
        // }
        
        $alertQuery = AlertCron::select('*')
                     ->where('deviceId','=',$deviceId)
                     ->where('companyCode','=',$this->companyCode)
                     ->where('status','=','0')
                     ->take(50)
                     ->get();
                 
        $alertCount = $alertQuery->count();    
        
        // $getData = new DataUtilityController($request,$query);
        // $alertCount = $getData->getData()['totalData'];
         
        $sensorTagsOfDeviceId = DB::table('customers as c')
                ->join('locations as l', 'c.customerId', '=', 'l.companyCode')
                ->Join('branches as b', function($join){
                    $join->on('l.id', '=', 'b.location_id')
                         ->on('c.customerId', '=', 'b.companyCode');
                })
                ->Join('facilities as f', function($join){
                    $join->on('c.customerId', '=', 'f.companyCode')
                        ->on('l.id', '=', 'f.location_id')
                        ->on('b.id', '=', 'f.branch_id');
                })
                ->Join('buildings as bl', function($join){
                    $join->on('c.customerId', '=', 'bl.companyCode')
                        ->on('l.id', '=', 'bl.location_id')
                        ->on('b.id', '=', 'bl.branch_id')
                        ->on('f.id','=','bl.facility_id');
                })
                ->Join('floors as fl', function($join){
                    $join->on('c.customerId', '=', 'fl.companyCode')
                        ->on('l.id', '=', 'fl.location_id')
                        ->on('b.id', '=', 'fl.branch_id')
                        ->on('f.id','=','fl.facility_id')
                        ->on('bl.id','=','fl.building_id');
                })
                ->Join('lab_departments as lb', function($join){
                    $join->on('c.customerId', '=', 'lb.companyCode')
                        ->on('l.id', '=', 'lb.location_id')
                        ->on('b.id', '=', 'lb.branch_id')
                        ->on('f.id','=','lb.facility_id')
                        ->on('bl.id','=','lb.building_id')
                        ->on('fl.id','=','lb.floor_id');
                })
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
                ->select('c.customerId', 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorNameUnit','s.sensorOutput','s.id','s.sensorTag', 's.sensorStatus','s.units','s.maxRatedReadingScale','s.minRatedReadingScale')
                ->WHERE('customerId','=',$this->companyCode)
                ->WHERE('deviceId','=',$deviceId)
                ->get();
                
        $length = count($sensorTagsOfDeviceId);      
        $sensorCount = 0;
        
        $deviceData = array();
        $output = array();
        $sensorData = array();
        $alertColor = "";
        $alertLightColor = "";
        
        for($x=0;$x<$length;$x++){
            $otherDataValues = DB::table('sampled_sensor_data_details_MinMaxAvg')
                    ->join('sensors', 'sensors.id', '=', 'sampled_sensor_data_details_MinMaxAvg.sensor_id')
                    ->select(DB::raw('sensors.deviceId,sensors.deviceName,sensors.sensorTag,sensors.units,sensors.maxRatedReadingScale,sensors.minRatedReadingScale,sampled_sensor_data_details_MinMaxAvg.alertType,sampled_sensor_data_details_MinMaxAvg.sample_date_time as DATE_TIME,sampled_sensor_data_details_MinMaxAvg.sensor_id,sampled_sensor_data_details_MinMaxAvg.parameterName as parameter,sampled_sensor_data_details_MinMaxAvg.sample_date_time AS timekey,sampled_sensor_data_details_MinMaxAvg.max_val as par_max,sampled_sensor_data_details_MinMaxAvg.min_val as par_min,sampled_sensor_data_details_MinMaxAvg.avg_val as par_avg,sampled_sensor_data_details_MinMaxAvg.last_val as par_last'))
                    ->where('sampled_sensor_data_details_MinMaxAvg.sensor_id','=',$sensorTagsOfDeviceId[$x]->id)
                    ->orderBy('sampled_sensor_data_details_MinMaxAvg.id','desc')
                    ->first();
                          
            if($otherDataValues != ""){
                $minVal = round(floatval($otherDataValues->par_min),2);
                $maxVal = round(floatval($otherDataValues->par_max),2);
                $avgVal = round(floatval($otherDataValues->par_avg),2);
                $lastVal = round(floatval($otherDataValues->par_last),2);
                $sensorTagName = $otherDataValues->sensorTag; 
                if($otherDataValues->alertType === "Critical"){
                    $alertColor = $this->alertColor['CRITICAL'];
                    $alertLightColor = $this->alertColor['CRITICALLIGHTCOLOR'];
                }
                if($otherDataValues->alertType === "Warning"){
                    $alertColor = $this->alertColor['WARNING'];
                     $alertLightColor = $this->alertColor['WARNINGLIGHTCOLOR'];
                }
                if($otherDataValues->alertType === "outOfRange"){
                    $alertColor = $this->alertColor['OUTOFRANGE'];
                     $alertLightColor = $this->alertColor['OUTOFRANGELIGHTCOLOR'];
                }
                if($otherDataValues->alertType === "NORMAL"){
                    $alertColor = $this->alertColor['NORMAL'];
                    $alertLightColor = $this->alertColor['NORMALLIGHTCOLOR'];
                }
                
                if($sensorTagName != ""){
                    if($sensorTagsOfDeviceId[$x]->sensorOutput == "Analog"){
                        $sensorData['customerId']  = $sensorTagsOfDeviceId[$x]->customerId;
                        $sensorData['stateName']   = $sensorTagsOfDeviceId[$x]->stateName;
                        $sensorData['branchName']  = $sensorTagsOfDeviceId[$x]->branchName;
                        $sensorData['facilityName'] = $sensorTagsOfDeviceId[$x]->facilityName;
                        $sensorData['buildingName'] = $sensorTagsOfDeviceId[$x]->buildingName;
                        $sensorData['floorName'] = $sensorTagsOfDeviceId[$x]->floorName;
                        $sensorData['labDepName'] = $sensorTagsOfDeviceId[$x]->labDepName;
                        $sensorData['deviceName'] = $sensorTagsOfDeviceId[$x]->deviceName;
                        $sensorData['sensorNameUnit'] = $sensorTagsOfDeviceId[$x]->sensorNameUnit;
                        $sensorData['sensorOutput'] = $sensorTagsOfDeviceId[$x]->sensorOutput;
                        $sensorData['units'] = $sensorTagsOfDeviceId[$x]->units;
                        $sensorData['minRatedReadingScale'] = $sensorTagsOfDeviceId[$x]->minRatedReadingScale;
                        $sensorData['maxRatedReadingScale'] = $sensorTagsOfDeviceId[$x]->maxRatedReadingScale;
                        $sensorData["sensorTag"] = $sensorTagName;
                        $sensorData["sensorTagId"] = $sensorTagsOfDeviceId[$x]->id;
                        $sensorData["sensorStatus"] = $sensorTagsOfDeviceId[$x]->sensorStatus;
                        $sensorData["min"] = $minVal;
                        $sensorData["max"] = $maxVal;
                        $sensorData["avg"] = $avgVal;
                        $sensorData["last"] = $lastVal;
                        $sensorData["alertColor"] = $alertColor;
                        $sensorData["alertLightColor"] = $alertLightColor;
                        $deviceData['Analog']['data'][] = $sensorData;
                        $sensorCount++;
                    }
                    
                    if($sensorTagsOfDeviceId[$x]->sensorOutput == "Digital"){
                        $sensorData['customerId']  = $sensorTagsOfDeviceId[$x]->customerId;
                        $sensorData['stateName']   = $sensorTagsOfDeviceId[$x]->stateName;
                        $sensorData['branchName']  = $sensorTagsOfDeviceId[$x]->branchName;
                        $sensorData['facilityName'] = $sensorTagsOfDeviceId[$x]->facilityName;
                        $sensorData['buildingName'] = $sensorTagsOfDeviceId[$x]->buildingName;
                        $sensorData['floorName'] = $sensorTagsOfDeviceId[$x]->floorName;
                        $sensorData['labDepName'] = $sensorTagsOfDeviceId[$x]->labDepName;
                        $sensorData['deviceName'] = $sensorTagsOfDeviceId[$x]->deviceName;
                        $sensorData['sensorNameUnit'] = $sensorTagsOfDeviceId[$x]->sensorNameUnit;
                        $sensorData['sensorOutput'] = $sensorTagsOfDeviceId[$x]->sensorOutput;
                        $sensorData['units'] = $sensorTagsOfDeviceId[$x]->units;
                        $sensorData['minRatedReadingScale'] = $sensorTagsOfDeviceId[$x]->minRatedReadingScale;
                        $sensorData['maxRatedReadingScale'] = $sensorTagsOfDeviceId[$x]->maxRatedReadingScale;
                        $sensorData["sensorTag"] = $sensorTagName;
                        $sensorData["sensorTagId"] = $sensorTagsOfDeviceId[$x]->id;
                        $sensorData["sensorStatus"] = $sensorTagsOfDeviceId[$x]->sensorStatus;
                        $sensorData["min"] = $minVal;
                        $sensorData["max"] = $maxVal;
                        $sensorData["avg"] = $avgVal;
                        $sensorData["last"] = $lastVal;
                        $sensorData["alertColor"] = $alertColor;
                        $sensorData["alertLightColor"] = $alertLightColor;
                        $deviceData['Digital']['data'][] = $sensorData;
                        $sensorCount++;
                    }
                    
                    if($sensorTagsOfDeviceId[$x]->sensorOutput == "Modbus"){
                        $sensorData['customerId']  = $sensorTagsOfDeviceId[$x]->customerId;
                        $sensorData['stateName']   = $sensorTagsOfDeviceId[$x]->stateName;
                        $sensorData['branchName']  = $sensorTagsOfDeviceId[$x]->branchName;
                        $sensorData['facilityName'] = $sensorTagsOfDeviceId[$x]->facilityName;
                        $sensorData['buildingName'] = $sensorTagsOfDeviceId[$x]->buildingName;
                        $sensorData['floorName'] = $sensorTagsOfDeviceId[$x]->floorName;
                        $sensorData['labDepName'] = $sensorTagsOfDeviceId[$x]->labDepName;
                        $sensorData['deviceName'] = $sensorTagsOfDeviceId[$x]->deviceName;
                        $sensorData['sensorNameUnit'] = $sensorTagsOfDeviceId[$x]->sensorNameUnit;
                        $sensorData['sensorOutput'] = $sensorTagsOfDeviceId[$x]->sensorOutput;
                        $sensorData['units'] = $sensorTagsOfDeviceId[$x]->units;
                        $sensorData['minRatedReadingScale'] = $sensorTagsOfDeviceId[$x]->minRatedReadingScale;
                        $sensorData['maxRatedReadingScale'] = $sensorTagsOfDeviceId[$x]->maxRatedReadingScale;
                        $sensorData["sensorTag"] = $sensorTagName;
                        $sensorData["sensorTagId"] = $sensorTagsOfDeviceId[$x]->id;
                        $sensorData["sensorStatus"] = $sensorTagsOfDeviceId[$x]->sensorStatus;
                        $sensorData["min"] = $minVal;
                        $sensorData["max"] = $maxVal;
                        $sensorData["avg"] = $avgVal;
                        $sensorData["last"] = $lastVal;
                        $sensorData["alertColor"] = $alertColor;
                        $sensorData["alertLightColor"] = $alertLightColor;
                        $deviceData['Modbus']['data'][] = $sensorData;
                        $sensorCount++;
                    }
                }
            }
        }
           
        $deviceData['sensorCount'] = $sensorCount;
        $deviceData['alertCount'] = $alertCount;
        $deviceData['aqiIndex'] = $aqiIndex;
        $response = $deviceData;
                
        return response($deviceData, 200);
                
    }
    
    public function liveDataDeviceId(Request $request){
         
        $deviceId = $request->device_id;
        
        $deviceDisconnectedStatus = DB::table('devices')
                            ->where('id','=',$deviceId)
                            ->get();
                            
        $disconnectedStatus = $deviceDisconnectedStatus[0]->disconnectedStatus;
                    
        // $deviceAqiIndex = DB::table('Aqi_values_per_device')
        //             ->where('deviceId','=',$deviceId)
        //             ->orderBy('id','desc')
        //             ->first();
        
        $sensors = DB::table('Aqi_values_per_deviceSensor')
            ->select('sensorId')
            ->distinct()
            ->where('sampled_date_time', '>=' ,Carbon::now()->subMinutes(1440)->toDateTimeString())
            ->where('deviceId','164')
            ->get();
        
        
        for($i=0; $i<count($sensors); $i++)
        {
            $data = DB::table('Aqi_values_per_deviceSensor')
            ->select(DB::raw('DATE_FORMAT(sampled_date_time, "%Y-%m-%d %H:00:00") as hour'), DB::raw('AVG(AqiValue) as average'))
            ->where('sampled_date_time', '>=' ,Carbon::now()->subMinutes(1440)->toDateTimeString())
            ->where('deviceId','164')
            ->where('sensorId',$sensors[$i]->sensorId)
            ->groupBy('hour')
            ->get();
            
            $maxAverage[] = $data->max('average');
        }  
        
        $max = round(max($maxAverage),1);
           
        if($max)
        {
            $aqiIndex =  $max;
        }
        else
        {
            $aqiIndex =  "NA";
        }
        
        // if($deviceAqiIndex)
        // {
        //     $aqiIndex =  $deviceAqiIndex->AqiValue;
        // }
        // else
        // {
        //     $aqiIndex =  "NA";
        // }
        
        $alertQuery = AlertCron::select('*')
                     ->where('deviceId','=',$deviceId)
                     ->where('companyCode','=',$this->companyCode)
                     ->where('status','=','0')
                     ->take(50)
                     ->get();
                 
        $alertCount = $alertQuery->count();    
         
        $sensorTagsOfDeviceId = DB::table('customers as c')
                ->join('locations as l', 'c.customerId', '=', 'l.companyCode')
                ->Join('branches as b', function($join){
                    $join->on('l.id', '=', 'b.location_id')
                         ->on('c.customerId', '=', 'b.companyCode');
                })
                ->Join('facilities as f', function($join){
                    $join->on('c.customerId', '=', 'f.companyCode')
                        ->on('l.id', '=', 'f.location_id')
                        ->on('b.id', '=', 'f.branch_id');
                })
                ->Join('buildings as bl', function($join){
                    $join->on('c.customerId', '=', 'bl.companyCode')
                        ->on('l.id', '=', 'bl.location_id')
                        ->on('b.id', '=', 'bl.branch_id')
                        ->on('f.id','=','bl.facility_id');
                })
                ->Join('floors as fl', function($join){
                    $join->on('c.customerId', '=', 'fl.companyCode')
                        ->on('l.id', '=', 'fl.location_id')
                        ->on('b.id', '=', 'fl.branch_id')
                        ->on('f.id','=','fl.facility_id')
                        ->on('bl.id','=','fl.building_id');
                })
                ->Join('lab_departments as lb', function($join){
                    $join->on('c.customerId', '=', 'lb.companyCode')
                        ->on('l.id', '=', 'lb.location_id')
                        ->on('b.id', '=', 'lb.branch_id')
                        ->on('f.id','=','lb.facility_id')
                        ->on('bl.id','=','lb.building_id')
                        ->on('fl.id','=','lb.floor_id');
                })
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
                ->select('c.customerId', 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorNameUnit','s.sensorOutput','s.id','s.sensorTag', 's.sensorStatus','s.units','s.maxRatedReadingScale','s.minRatedReadingScale')
                ->WHERE('customerId','=',$this->companyCode)
                ->WHERE('deviceId','=',$deviceId)
                ->get();
                
        $length = count($sensorTagsOfDeviceId);      
        $sensorCount = 0;
        
        $deviceData = array();
        $output = array();
        $sensorData = array();
        
        $alertColor = "";
        $alertLightColor = "";
        //SELECT AVG(avg_val) FROM `sampled_sensor_data_details_MinMaxAvg` where device_id = 120 and sensor_id = 208 and time_stamp >= DATE_SUB(NOW(),INTERVAL 1500 MINUTE) order by id desc
        for($x=0;$x<$length;$x++){
            $otherDataValues = DB::table('sampled_sensor_data_details_MinMaxAvg')
                    ->join('sensors', 'sensors.id', '=', 'sampled_sensor_data_details_MinMaxAvg.sensor_id')
                    ->select(DB::raw('sensors.deviceId,sensors.deviceName,sensors.sensorTag,sensors.units,sensors.maxRatedReadingScale,sensors.minRatedReadingScale,sampled_sensor_data_details_MinMaxAvg.alertType,sampled_sensor_data_details_MinMaxAvg.sample_date_time as DATE_TIME,sampled_sensor_data_details_MinMaxAvg.sensor_id,sampled_sensor_data_details_MinMaxAvg.parameterName as parameter,sampled_sensor_data_details_MinMaxAvg.sample_date_time AS timekey,  MAX(sampled_sensor_data_details_MinMaxAvg.avg_val) as par_max,   MIN(sampled_sensor_data_details_MinMaxAvg.avg_val) as par_min,  AVG(sampled_sensor_data_details_MinMaxAvg.avg_val) as par_avg,sampled_sensor_data_details_MinMaxAvg.last_val as par_last'))
                    ->where('sampled_sensor_data_details_MinMaxAvg.sensor_id','=',$sensorTagsOfDeviceId[$x]->id)
                    ->whereRaw('time_stamp >= DATE_SUB(NOW(),INTERVAL 15 MINUTE)')
                    ->orderBy('sampled_sensor_data_details_MinMaxAvg.id','desc')
                    ->first();
                        
            if($otherDataValues != ""){
                $fetchDataValues = DB::table('sampled_sensor_data_details_MinMaxAvg')
                        ->join('sensors', 'sensors.id', '=', 'sampled_sensor_data_details_MinMaxAvg.sensor_id')
                        ->select(DB::raw('sampled_sensor_data_details_MinMaxAvg.last_val,sampled_sensor_data_details_MinMaxAvg.alertType'))
                        ->where('sampled_sensor_data_details_MinMaxAvg.sensor_id','=',$sensorTagsOfDeviceId[$x]->id)
                        ->whereRaw('time_stamp >= DATE_SUB(NOW(),INTERVAL 1 MINUTE)')
                        ->orderBy('sampled_sensor_data_details_MinMaxAvg.id','desc')
                        ->first();
                        
                if($fetchDataValues!=""){
                    $l = $fetchDataValues->last_val;
                    
                    if($fetchDataValues->alertType === "Critical"){
                        $alertColor = $this->alertColor['CRITICAL'];
                        $alertLightColor = $this->alertColor['CRITICALLIGHTCOLOR'];
                    }
                    if($fetchDataValues->alertType === "Warning"){
                        $alertColor = $this->alertColor['WARNING'];
                        $alertLightColor = $this->alertColor['WARNINGLIGHTCOLOR'];
                    }
                    if($fetchDataValues->alertType === "outOfRange"){
                        $alertColor = $this->alertColor['OUTOFRANGE'];
                        $alertLightColor = $this->alertColor['OUTOFRANGELIGHTCOLOR'];
                    }
                    if($fetchDataValues->alertType === "NORMAL"){
                        $alertColor = $this->alertColor['NORMAL'];
                        $alertLightColor = $this->alertColor['NORMALLIGHTCOLOR'];
                    }
                }else{
                    $l = 0;
                }
                    
                $min = floatval($otherDataValues->par_min);
                $max = floatval($otherDataValues->par_max);
                $avg = floatval($otherDataValues->par_avg);
                $last = floatval($l);
                $minVal = number_format($min,2);
                $maxVal = number_format($max,2);
                $avgVal = number_format($avg,2);
                $lastVal = number_format($last,2);
                $sensorTagName = $otherDataValues->sensorTag; 
                
                if($sensorTagName != ""){
                    if($sensorTagsOfDeviceId[$x]->sensorOutput == "Analog"){
                        $sensorData['customerId']  = $sensorTagsOfDeviceId[$x]->customerId;
                        $sensorData['stateName']   = $sensorTagsOfDeviceId[$x]->stateName;
                        $sensorData['branchName']  = $sensorTagsOfDeviceId[$x]->branchName;
                        $sensorData['facilityName'] = $sensorTagsOfDeviceId[$x]->facilityName;
                        $sensorData['buildingName'] = $sensorTagsOfDeviceId[$x]->buildingName;
                        $sensorData['floorName'] = $sensorTagsOfDeviceId[$x]->floorName;
                        $sensorData['labDepName'] = $sensorTagsOfDeviceId[$x]->labDepName;
                        $sensorData['deviceName'] = $sensorTagsOfDeviceId[$x]->deviceName;
                        $sensorData['sensorNameUnit'] = $sensorTagsOfDeviceId[$x]->sensorNameUnit;
                        $sensorData['sensorOutput'] = $sensorTagsOfDeviceId[$x]->sensorOutput;
                        $sensorData['units'] = $sensorTagsOfDeviceId[$x]->units;
                        $sensorData['minRatedReadingScale'] = $sensorTagsOfDeviceId[$x]->minRatedReadingScale;
                        $sensorData['maxRatedReadingScale'] = $sensorTagsOfDeviceId[$x]->maxRatedReadingScale;
                        $sensorData["sensorTag"] = $sensorTagName;
                        $sensorData["sensorTagId"] = $sensorTagsOfDeviceId[$x]->id;
                        $sensorData["sensorStatus"] = $sensorTagsOfDeviceId[$x]->sensorStatus;
                        $sensorData["min"] = $minVal;
                        $sensorData["max"] = $maxVal;
                        $sensorData["avg"] = $avgVal;
                        $sensorData["last"] = $lastVal;
                        $sensorData["alertColor"] = $alertColor;
                        $sensorData["alertLightColor"] = $alertLightColor;
                        $deviceData['Analog']['data'][] = $sensorData;
                        $sensorCount++;
                    }
                    
                    if($sensorTagsOfDeviceId[$x]->sensorOutput == "Digital"){
                        $sensorData['customerId']  = $sensorTagsOfDeviceId[$x]->customerId;
                        $sensorData['stateName']   = $sensorTagsOfDeviceId[$x]->stateName;
                        $sensorData['branchName']  = $sensorTagsOfDeviceId[$x]->branchName;
                        $sensorData['facilityName'] = $sensorTagsOfDeviceId[$x]->facilityName;
                        $sensorData['buildingName'] = $sensorTagsOfDeviceId[$x]->buildingName;
                        $sensorData['floorName'] = $sensorTagsOfDeviceId[$x]->floorName;
                        $sensorData['labDepName'] = $sensorTagsOfDeviceId[$x]->labDepName;
                        $sensorData['deviceName'] = $sensorTagsOfDeviceId[$x]->deviceName;
                        $sensorData['sensorNameUnit'] = $sensorTagsOfDeviceId[$x]->sensorNameUnit;
                        $sensorData['sensorOutput'] = $sensorTagsOfDeviceId[$x]->sensorOutput;
                        $sensorData['units'] = $sensorTagsOfDeviceId[$x]->units;
                        $sensorData['minRatedReadingScale'] = $sensorTagsOfDeviceId[$x]->minRatedReadingScale;
                        $sensorData['maxRatedReadingScale'] = $sensorTagsOfDeviceId[$x]->maxRatedReadingScale;
                        $sensorData["sensorTag"] = $sensorTagName;
                        $sensorData["sensorTagId"] = $sensorTagsOfDeviceId[$x]->id;
                        $sensorData["sensorStatus"] = $sensorTagsOfDeviceId[$x]->sensorStatus;
                        $sensorData["min"] = $minVal;
                        $sensorData["max"] = $maxVal;
                        $sensorData["avg"] = $avgVal;
                        $sensorData["last"] = $lastVal;
                        $sensorData["alertColor"] = $alertColor;
                        $sensorData["alertLightColor"] = $alertLightColor;
                        $deviceData['Digital']['data'][] = $sensorData;
                        $sensorCount++;
                    }
                    
                    if($sensorTagsOfDeviceId[$x]->sensorOutput == "Modbus"){
                        $sensorData['customerId']  = $sensorTagsOfDeviceId[$x]->customerId;
                        $sensorData['stateName']   = $sensorTagsOfDeviceId[$x]->stateName;
                        $sensorData['branchName']  = $sensorTagsOfDeviceId[$x]->branchName;
                        $sensorData['facilityName'] = $sensorTagsOfDeviceId[$x]->facilityName;
                        $sensorData['buildingName'] = $sensorTagsOfDeviceId[$x]->buildingName;
                        $sensorData['floorName'] = $sensorTagsOfDeviceId[$x]->floorName;
                        $sensorData['labDepName'] = $sensorTagsOfDeviceId[$x]->labDepName;
                        $sensorData['deviceName'] = $sensorTagsOfDeviceId[$x]->deviceName;
                        $sensorData['sensorNameUnit'] = $sensorTagsOfDeviceId[$x]->sensorNameUnit;
                        $sensorData['sensorOutput'] = $sensorTagsOfDeviceId[$x]->sensorOutput;
                        $sensorData['units'] = $sensorTagsOfDeviceId[$x]->units;
                        $sensorData['minRatedReadingScale'] = $sensorTagsOfDeviceId[$x]->minRatedReadingScale;
                        $sensorData['maxRatedReadingScale'] = $sensorTagsOfDeviceId[$x]->maxRatedReadingScale;
                        $sensorData["sensorTag"] = $sensorTagName;
                        $sensorData["sensorTagId"] = $sensorTagsOfDeviceId[$x]->id;
                        $sensorData["sensorStatus"] = $sensorTagsOfDeviceId[$x]->sensorStatus;
                        $sensorData["min"] = $minVal;
                        $sensorData["max"] = $maxVal;
                        $sensorData["avg"] = $avgVal;
                        $sensorData["last"] = $lastVal;
                        $sensorData["alertColor"] = $alertColor;
                        $sensorData["alertLightColor"] = $alertLightColor;
                        $deviceData['Modbus']['data'][] = $sensorData;
                        $sensorCount++;
                    }
                }
            }
        }
           
        $deviceData['sensorCount'] = $sensorCount;
        $deviceData['alertCount'] = $alertCount;
        $deviceData['aqiIndex'] = $aqiIndex;
        $deviceData['disconnectedStatus'] = $disconnectedStatus;
        $response = $deviceData;
        
        return response($response, 200);
                
    }
    
    
    public function liveDataDeviceIdNew(Request $request){
         
        $deviceId = $request->device_id;
        
        $deviceDisconnectedStatus = DB::table('devices')
                            ->where('id','=',$deviceId)
                            ->get();
                            
        $disconnectedStatus = $deviceDisconnectedStatus[0]->disconnectedStatus;
                    
        $deviceAqiIndex = DB::table('Aqi_values_per_device')
                    ->where('deviceId','=',$deviceId)
                    ->orderBy('id','desc')
                    ->first();
                    
        if($deviceAqiIndex)
        {
            $aqiIndex =  $deviceAqiIndex->AqiValue;
        }
        else
        {
            $aqiIndex =  "NA";
        }
        
        $alertQuery = AlertCron::select('*')
                     ->where('deviceId','=',$deviceId)
                     ->where('companyCode','=',$this->companyCode)
                     ->where('status','=','0')
                     ->take(50)
                     ->get();
                 
        $alertCount = $alertQuery->count();    
         
        $sensorTagsOfDeviceId = DB::table('customers as c')
                ->join('locations as l', 'c.customerId', '=', 'l.companyCode')
                ->Join('branches as b', function($join){
                    $join->on('l.id', '=', 'b.location_id')
                         ->on('c.customerId', '=', 'b.companyCode');
                })
                ->Join('facilities as f', function($join){
                    $join->on('c.customerId', '=', 'f.companyCode')
                        ->on('l.id', '=', 'f.location_id')
                        ->on('b.id', '=', 'f.branch_id');
                })
                ->Join('buildings as bl', function($join){
                    $join->on('c.customerId', '=', 'bl.companyCode')
                        ->on('l.id', '=', 'bl.location_id')
                        ->on('b.id', '=', 'bl.branch_id')
                        ->on('f.id','=','bl.facility_id');
                })
                ->Join('floors as fl', function($join){
                    $join->on('c.customerId', '=', 'fl.companyCode')
                        ->on('l.id', '=', 'fl.location_id')
                        ->on('b.id', '=', 'fl.branch_id')
                        ->on('f.id','=','fl.facility_id')
                        ->on('bl.id','=','fl.building_id');
                })
                ->Join('lab_departments as lb', function($join){
                    $join->on('c.customerId', '=', 'lb.companyCode')
                        ->on('l.id', '=', 'lb.location_id')
                        ->on('b.id', '=', 'lb.branch_id')
                        ->on('f.id','=','lb.facility_id')
                        ->on('bl.id','=','lb.building_id')
                        ->on('fl.id','=','lb.floor_id');
                })
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
                ->select('c.customerId', 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorNameUnit','s.sensorOutput','s.id','s.sensorTag', 's.sensorStatus','s.units','s.maxRatedReadingScale','s.minRatedReadingScale')
                ->WHERE('customerId','=',$this->companyCode)
                ->WHERE('deviceId','=',$deviceId)
                ->get();
                
        $length = count($sensorTagsOfDeviceId);      
        $sensorCount = 0;
        
        $deviceData = array();
        $output = array();
        $sensorData = array();
        
        $alertColor = "";
        $alertLightColor = "";
        //SELECT AVG(avg_val) FROM `sampled_sensor_data_details_MinMaxAvg` where device_id = 120 and sensor_id = 208 and time_stamp >= DATE_SUB(NOW(),INTERVAL 1500 MINUTE) order by id desc
        for($x=0;$x<$length;$x++){
            $otherDataValues = DB::table('sampled_sensor_data_details_MinMaxAvg')
                    ->join('sensors', 'sensors.id', '=', 'sampled_sensor_data_details_MinMaxAvg.sensor_id')
                    ->select(DB::raw('sensors.deviceId,sensors.deviceName,sensors.sensorTag,sensors.units,sensors.maxRatedReadingScale,sensors.minRatedReadingScale,sampled_sensor_data_details_MinMaxAvg.alertType,sampled_sensor_data_details_MinMaxAvg.sample_date_time as DATE_TIME,sampled_sensor_data_details_MinMaxAvg.sensor_id,sampled_sensor_data_details_MinMaxAvg.parameterName as parameter,sampled_sensor_data_details_MinMaxAvg.sample_date_time AS timekey,  MAX(sampled_sensor_data_details_MinMaxAvg.avg_val) as par_max,   MIN(sampled_sensor_data_details_MinMaxAvg.avg_val) as par_min,  AVG(sampled_sensor_data_details_MinMaxAvg.avg_val) as par_avg,sampled_sensor_data_details_MinMaxAvg.last_val as par_last'))
                    ->where('sampled_sensor_data_details_MinMaxAvg.sensor_id','=',$sensorTagsOfDeviceId[$x]->id)
                    ->whereRaw('time_stamp >= DATE_SUB(NOW(),INTERVAL 15 MINUTE)')
                    ->orderBy('sampled_sensor_data_details_MinMaxAvg.id','desc')
                    ->first();
            
            $getSensorAlertList = DB::table('alert_crons')
                        ->select('alertType')
                        ->where('sensorId','=',$sensorTagsOfDeviceId[$x]->id)
                        ->where('triggeredAlertFlag','=','1')
                        //->whereRaw('alertType IN ("Critical","Warning")')
                        //->where('alertType','<>','Stel')
                        ->get();
                
            $alertList = array();
            
            foreach($getSensorAlertList as $key => $value){
                $alertList[] = $value->alertType;    
            }
            
            if(in_array("Critical",$alertList) || in_array("Stel",$alertList)){
                //$alertColor = "Critical";
                $alertColor = $this->alertColor['CRITICAL'];
                $alertLightColor = $this->alertColor['CRITICALLIGHTCOLOR'];
            }else if(in_array("TWA",$alertList) || in_array("Warning",$alertList)){
                //$alertColor = "Warning";
                $alertColor = $this->alertColor['WARNING'];
                $alertLightColor = $this->alertColor['WARNINGLIGHTCOLOR'];
            }else if(in_array("outOfRange",$alertList)){
                //$alertColor = "Outof range";
                $alertColor = $this->alertColor['OUTOFRANGE'];
                $alertLightColor = $this->alertColor['OUTOFRANGELIGHTCOLOR'];
            }
            
            if($otherDataValues != ""){
                $fetchDataValues = DB::table('sampled_sensor_data_details_MinMaxAvg')
                        ->join('sensors', 'sensors.id', '=', 'sampled_sensor_data_details_MinMaxAvg.sensor_id')
                        ->select(DB::raw('sampled_sensor_data_details_MinMaxAvg.last_val,sampled_sensor_data_details_MinMaxAvg.alertType'))
                        ->where('sampled_sensor_data_details_MinMaxAvg.sensor_id','=',$sensorTagsOfDeviceId[$x]->id)
                        ->whereRaw('time_stamp >= DATE_SUB(NOW(),INTERVAL 1 MINUTE)')
                        ->orderBy('sampled_sensor_data_details_MinMaxAvg.id','desc')
                        ->first();
                        
                if($fetchDataValues!=""){
                    $l = $fetchDataValues->last_val;
                    
                    /*
                    if($fetchDataValues->alertType === "Critical"){
                        $alertColor = $this->alertColor['CRITICAL'];
                        $alertLightColor = $this->alertColor['CRITICALLIGHTCOLOR'];
                    }
                    if($fetchDataValues->alertType === "Warning"){
                        $alertColor = $this->alertColor['WARNING'];
                        $alertLightColor = $this->alertColor['WARNINGLIGHTCOLOR'];
                    }
                    if($fetchDataValues->alertType === "outOfRange"){
                        $alertColor = $this->alertColor['OUTOFRANGE'];
                        $alertLightColor = $this->alertColor['OUTOFRANGELIGHTCOLOR'];
                    }
                    if($fetchDataValues->alertType === "NORMAL"){
                        $alertColor = $this->alertColor['NORMAL'];
                        $alertLightColor = $this->alertColor['NORMALLIGHTCOLOR'];
                    }*/
                }else{
                    $l = 0;
                }
                    
                $min = floatval($otherDataValues->par_min);
                $max = floatval($otherDataValues->par_max);
                $avg = floatval($otherDataValues->par_avg);
                $last = floatval($l);
                $minVal = number_format($min,2);
                $maxVal = number_format($max,2);
                $avgVal = number_format($avg,2);
                $lastVal = number_format($last,2);
                $sensorTagName = $otherDataValues->sensorTag; 
                
                if($sensorTagName != ""){
                    if($sensorTagsOfDeviceId[$x]->sensorOutput == "Analog"){
                        $sensorData['customerId']  = $sensorTagsOfDeviceId[$x]->customerId;
                        $sensorData['stateName']   = $sensorTagsOfDeviceId[$x]->stateName;
                        $sensorData['branchName']  = $sensorTagsOfDeviceId[$x]->branchName;
                        $sensorData['facilityName'] = $sensorTagsOfDeviceId[$x]->facilityName;
                        $sensorData['buildingName'] = $sensorTagsOfDeviceId[$x]->buildingName;
                        $sensorData['floorName'] = $sensorTagsOfDeviceId[$x]->floorName;
                        $sensorData['labDepName'] = $sensorTagsOfDeviceId[$x]->labDepName;
                        $sensorData['deviceName'] = $sensorTagsOfDeviceId[$x]->deviceName;
                        $sensorData['sensorNameUnit'] = $sensorTagsOfDeviceId[$x]->sensorNameUnit;
                        $sensorData['sensorOutput'] = $sensorTagsOfDeviceId[$x]->sensorOutput;
                        $sensorData['units'] = $sensorTagsOfDeviceId[$x]->units;
                        $sensorData['minRatedReadingScale'] = $sensorTagsOfDeviceId[$x]->minRatedReadingScale;
                        $sensorData['maxRatedReadingScale'] = $sensorTagsOfDeviceId[$x]->maxRatedReadingScale;
                        $sensorData["sensorTag"] = $sensorTagName;
                        $sensorData["sensorTagId"] = $sensorTagsOfDeviceId[$x]->id;
                        $sensorData["sensorStatus"] = $sensorTagsOfDeviceId[$x]->sensorStatus;
                        $sensorData["min"] = $minVal;
                        $sensorData["max"] = $maxVal;
                        $sensorData["avg"] = $avgVal;
                        $sensorData["last"] = $lastVal;
                        $sensorData["alertColor"] = $alertColor;
                        $sensorData["alertLightColor"] = $alertLightColor;
                        $sensorData["alertList"] = $alertList;
                        $deviceData['Analog']['data'][] = $sensorData;
                        $sensorCount++;
                    }
                    
                    if($sensorTagsOfDeviceId[$x]->sensorOutput == "Digital"){
                        $sensorData['customerId']  = $sensorTagsOfDeviceId[$x]->customerId;
                        $sensorData['stateName']   = $sensorTagsOfDeviceId[$x]->stateName;
                        $sensorData['branchName']  = $sensorTagsOfDeviceId[$x]->branchName;
                        $sensorData['facilityName'] = $sensorTagsOfDeviceId[$x]->facilityName;
                        $sensorData['buildingName'] = $sensorTagsOfDeviceId[$x]->buildingName;
                        $sensorData['floorName'] = $sensorTagsOfDeviceId[$x]->floorName;
                        $sensorData['labDepName'] = $sensorTagsOfDeviceId[$x]->labDepName;
                        $sensorData['deviceName'] = $sensorTagsOfDeviceId[$x]->deviceName;
                        $sensorData['sensorNameUnit'] = $sensorTagsOfDeviceId[$x]->sensorNameUnit;
                        $sensorData['sensorOutput'] = $sensorTagsOfDeviceId[$x]->sensorOutput;
                        $sensorData['units'] = $sensorTagsOfDeviceId[$x]->units;
                        $sensorData['minRatedReadingScale'] = $sensorTagsOfDeviceId[$x]->minRatedReadingScale;
                        $sensorData['maxRatedReadingScale'] = $sensorTagsOfDeviceId[$x]->maxRatedReadingScale;
                        $sensorData["sensorTag"] = $sensorTagName;
                        $sensorData["sensorTagId"] = $sensorTagsOfDeviceId[$x]->id;
                        $sensorData["sensorStatus"] = $sensorTagsOfDeviceId[$x]->sensorStatus;
                        $sensorData["min"] = $minVal;
                        $sensorData["max"] = $maxVal;
                        $sensorData["avg"] = $avgVal;
                        $sensorData["last"] = $lastVal;
                        $sensorData["alertColor"] = $alertColor;
                        $sensorData["alertLightColor"] = $alertLightColor;
                        $sensorData["alertList"] = $alertList;
                        $deviceData['Digital']['data'][] = $sensorData;
                        $sensorCount++;
                    }
                    
                    if($sensorTagsOfDeviceId[$x]->sensorOutput == "Modbus"){
                        $sensorData['customerId']  = $sensorTagsOfDeviceId[$x]->customerId;
                        $sensorData['stateName']   = $sensorTagsOfDeviceId[$x]->stateName;
                        $sensorData['branchName']  = $sensorTagsOfDeviceId[$x]->branchName;
                        $sensorData['facilityName'] = $sensorTagsOfDeviceId[$x]->facilityName;
                        $sensorData['buildingName'] = $sensorTagsOfDeviceId[$x]->buildingName;
                        $sensorData['floorName'] = $sensorTagsOfDeviceId[$x]->floorName;
                        $sensorData['labDepName'] = $sensorTagsOfDeviceId[$x]->labDepName;
                        $sensorData['deviceName'] = $sensorTagsOfDeviceId[$x]->deviceName;
                        $sensorData['sensorNameUnit'] = $sensorTagsOfDeviceId[$x]->sensorNameUnit;
                        $sensorData['sensorOutput'] = $sensorTagsOfDeviceId[$x]->sensorOutput;
                        $sensorData['units'] = $sensorTagsOfDeviceId[$x]->units;
                        $sensorData['minRatedReadingScale'] = $sensorTagsOfDeviceId[$x]->minRatedReadingScale;
                        $sensorData['maxRatedReadingScale'] = $sensorTagsOfDeviceId[$x]->maxRatedReadingScale;
                        $sensorData["sensorTag"] = $sensorTagName;
                        $sensorData["sensorTagId"] = $sensorTagsOfDeviceId[$x]->id;
                        $sensorData["sensorStatus"] = $sensorTagsOfDeviceId[$x]->sensorStatus;
                        $sensorData["min"] = $minVal;
                        $sensorData["max"] = $maxVal;
                        $sensorData["avg"] = $avgVal;
                        $sensorData["last"] = $lastVal;
                        $sensorData["alertColor"] = $alertColor;
                        $sensorData["alertLightColor"] = $alertLightColor;
                        $sensorData["alertList"] = $alertList;
                        $deviceData['Modbus']['data'][] = $sensorData;
                        $sensorCount++;
                    }
                }
            }
        }
        
           
        $deviceData['sensorCount'] = $sensorCount;
        $deviceData['alertCount'] = $alertCount;
        $deviceData['aqiIndex'] = $aqiIndex;
        $deviceData['disconnectedStatus'] = $disconnectedStatus;
        $response = $deviceData;
                
        return response($response, 200);
                
    }
    
    
    public function liveDataDeviceIdTest(Request $request)
    {
        $deviceId = $request->device_id;
        
        $deviceDisconnectedStatus = DB::table('devices')
                            ->where('id','=',$deviceId)
                            ->get();
                            
        $disconnectedStatus = $deviceDisconnectedStatus[0]->disconnectedStatus;
                    
      /*  $deviceAqiIndex = DB::table('Aqi_values_per_device')
                    ->where('deviceId','=',$deviceId)
                    ->where('sampled_date_time', '>' ,Carbon::now()->subHours(1)->toDateTimeString())
                    //->orderBy('id','desc')
                    ->orderBy('AqiValue','DESC')
                    ->first();
                    
        if($deviceAqiIndex)
        {
            $aqiIndex =  $deviceAqiIndex->AqiValue;
        }
        else
        {
            $aqiIndex =  "NA";
        }*/
        
        
        /*modified by vaishak 1602-1632 on 27-03-2023
        
        $sensors = DB::table('Aqi_values_per_deviceSensor')
            ->select('sensorId')
            ->distinct()
            ->where('sampled_date_time', '>=' ,Carbon::now()->subMinutes(1440)->toDateTimeString())
            ->where('deviceId',$deviceId)
            ->get();
        
        $maxAverage = array();
        for($i=0; $i<count($sensors); $i++)
        {
            $data = DB::table('Aqi_values_per_deviceSensor')
            ->select(DB::raw('DATE_FORMAT(sampled_date_time, "%Y-%m-%d %H:00:00") as hour'), DB::raw('AVG(AqiValue) as average'))
            ->where('sampled_date_time', '>=' ,Carbon::now()->subMinutes(1440)->toDateTimeString())
            ->where('deviceId',$deviceId)
            ->where('sensorId',$sensors[$i]->sensorId)
            ->where('AqiValue','!=','0')
            ->groupBy('hour')
            ->get();
            
            $maxAverage[] = $data->avg('average');
        }  */
        
        
        
        // modified on 24-04-2023   latest updated code
        
        $sensors = DB::table('Aqi_values_per_deviceSensor')
            ->select('Aqi_values_per_deviceSensor.sensorId','s.sensorNameUnit')
            ->distinct()
            ->join('sensors as s','s.id','=','Aqi_values_per_deviceSensor.sensorId')
            ->where('Aqi_values_per_deviceSensor.sampled_date_time', '>=' ,Carbon::now()->subMinutes(1440)->toDateTimeString())
            ->where('Aqi_values_per_deviceSensor.deviceId',$deviceId)
            ->get();
        
        $maxAverage = array();
        for($i=0; $i<count($sensors); $i++)
        {
            $calculate = false;
            
            if($sensors[$i]->sensorNameUnit == 'CO' ||$sensors[$i]->sensorNameUnit == 'O3'){
                
                $data = DB::table('Aqi_values_per_deviceSensor')
                    ->select(DB::raw("DATE_FORMAT(sampled_date_time, '%Y-%m-%d %H:00:00') AS hour"), DB::raw("AVG(AqiValue) AS average"))
                    ->where('sampled_date_time', '>=', Carbon::now()->subHours(8))
                    ->where('Aqi_values_per_deviceSensor.deviceId', $deviceId)
                    ->where('sensorId', $sensors[$i]->sensorId)
                    ->where('AqiValue','!=','0')
                    ->groupBy(DB::raw("DATE_FORMAT(sampled_date_time, '%Y-%m-%d %H:00:00')"))
                    ->orderBy('hour', 'ASC')
                    ->get();
                    
                if(count($data) > 8){
                    $calculate = true;
                }

            }else{
                 $data = DB::table('Aqi_values_per_deviceSensor')
                    ->select(DB::raw("DATE_FORMAT(sampled_date_time, '%Y-%m-%d %H:00:00') AS hour"), DB::raw("AVG(AqiValue) AS average"))
                    ->where('sampled_date_time', '>=', Carbon::now()->subHours(16))
                    ->where('Aqi_values_per_deviceSensor.deviceId', $deviceId)
                    ->where('sensorId', $sensors[$i]->sensorId)
                    ->where('AqiValue','!=','0')
                    ->groupBy(DB::raw("DATE_FORMAT(sampled_date_time, '%Y-%m-%d %H:00:00')"))
                    ->orderBy('hour', 'ASC')
                    ->get();
                    
                if(count($data) > 16){
                    $calculate = true;
                }
            }
            
            if($calculate == true){
                $maxAverage[] = $data->avg('average');
            }
        }
        
        if($maxAverage)
        {
            $max = round(max($maxAverage), 1);
            $aqiIndex =  "$max";
        }
        else
        {
            $aqiIndex =  "0";
        }
        
        $alertQuery = AlertCron::select('*')
                     ->where('deviceId','=',$deviceId)
                     ->where('companyCode','=',$this->companyCode)
                     ->where('status','=','0')
                     ->take(50)
                     ->get();
                 
        $alertCount = $alertQuery->count();    
         
        $sensorTagsOfDeviceId = DB::table('customers as c')
                ->join('locations as l', 'c.customerId', '=', 'l.companyCode')
                ->Join('branches as b', function($join){
                    $join->on('l.id', '=', 'b.location_id')
                         ->on('c.customerId', '=', 'b.companyCode');
                })
                ->Join('facilities as f', function($join){
                    $join->on('c.customerId', '=', 'f.companyCode')
                        ->on('l.id', '=', 'f.location_id')
                        ->on('b.id', '=', 'f.branch_id');
                })
                ->Join('buildings as bl', function($join){
                    $join->on('c.customerId', '=', 'bl.companyCode')
                        ->on('l.id', '=', 'bl.location_id')
                        ->on('b.id', '=', 'bl.branch_id')
                        ->on('f.id','=','bl.facility_id');
                })
                ->Join('floors as fl', function($join){
                    $join->on('c.customerId', '=', 'fl.companyCode')
                        ->on('l.id', '=', 'fl.location_id')
                        ->on('b.id', '=', 'fl.branch_id')
                        ->on('f.id','=','fl.facility_id')
                        ->on('bl.id','=','fl.building_id');
                })
                ->Join('lab_departments as lb', function($join){
                    $join->on('c.customerId', '=', 'lb.companyCode')
                        ->on('l.id', '=', 'lb.location_id')
                        ->on('b.id', '=', 'lb.branch_id')
                        ->on('f.id','=','lb.facility_id')
                        ->on('bl.id','=','lb.building_id')
                        ->on('fl.id','=','lb.floor_id');
                })
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
                ->select('c.customerId', 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorNameUnit','s.sensorOutput','s.id','s.sensorTag', 's.sensorStatus','s.units','s.maxRatedReadingScale','s.minRatedReadingScale')
                ->WHERE('customerId','=',$this->companyCode)
                ->WHERE('deviceId','=',$deviceId)
                ->get();
                
        $length = count($sensorTagsOfDeviceId);      
        $sensorCount = 0;
        
        $deviceData = array();
        $output = array();
        $sensorData = array();
        
        $alertColor = "";
        $alertLightColor = "";
        //SELECT AVG(avg_val) FROM `sampled_sensor_data_details_MinMaxAvg` where device_id = 120 and sensor_id = 208 and time_stamp >= DATE_SUB(NOW(),INTERVAL 1500 MINUTE) order by id desc
        for($x=0;$x<$length;$x++){
            $otherDataValues = DB::table('sampled_sensor_data_details_MinMaxAvg')
                    ->join('sensors', 'sensors.id', '=', 'sampled_sensor_data_details_MinMaxAvg.sensor_id')
                    ->select(DB::raw('sensors.deviceId,sensors.deviceName,sensors.sensorTag,sensors.units,sensors.maxRatedReadingScale,sensors.isStel,sensors.stelLimit,sensors.minRatedReadingScale,sampled_sensor_data_details_MinMaxAvg.alertType,sampled_sensor_data_details_MinMaxAvg.sample_date_time as DATE_TIME,sampled_sensor_data_details_MinMaxAvg.sensor_id,sampled_sensor_data_details_MinMaxAvg.parameterName as parameter,sampled_sensor_data_details_MinMaxAvg.sample_date_time AS timekey,  MAX(sampled_sensor_data_details_MinMaxAvg.avg_val) as par_max,   MIN(sampled_sensor_data_details_MinMaxAvg.avg_val) as par_min,  AVG(sampled_sensor_data_details_MinMaxAvg.avg_val) as par_avg,sampled_sensor_data_details_MinMaxAvg.last_val as par_last'))
                    ->where('sampled_sensor_data_details_MinMaxAvg.sensor_id','=',$sensorTagsOfDeviceId[$x]->id)
                    ->whereRaw('time_stamp >= DATE_SUB(NOW(),INTERVAL 15 MINUTE)')
                    ->orderBy('sampled_sensor_data_details_MinMaxAvg.id','desc')
                    ->first();
            
            $stelLimit = $otherDataValues->stelLimit;
            $isStel = $otherDataValues->isStel;
            $alertType = "";
            
            if($otherDataValues != ""){
                $fetchDataValues = DB::table('sampled_sensor_data_details_MinMaxAvg')
                        ->join('sensors', 'sensors.id', '=', 'sampled_sensor_data_details_MinMaxAvg.sensor_id')
                        ->select(DB::raw('sampled_sensor_data_details_MinMaxAvg.last_val,sampled_sensor_data_details_MinMaxAvg.alertType'))
                        ->where('sampled_sensor_data_details_MinMaxAvg.sensor_id','=',$sensorTagsOfDeviceId[$x]->id)
                        ->whereRaw('time_stamp >= DATE_SUB(NOW(),INTERVAL 1 MINUTE)')
                        ->orderBy('sampled_sensor_data_details_MinMaxAvg.id','desc')
                        ->first();
                        
                if($fetchDataValues!=""){
                    $l = $fetchDataValues->last_val;
                    $alertType = $fetchDataValues->alertType;
                    if($isStel == 1){
                        if($alertType == "NORMAL"){
                            if(floatval($otherDataValues->par_avg)>floatval($stelLimit)){
                                $alertColor = $this->alertColor['CRITICAL'];
                                $alertLightColor = $this->alertColor['CRITICALLIGHTCOLOR'];
                            }else{
                                $alertColor = $this->alertColor['NORMAL'];
                                $alertLightColor = $this->alertColor['NORMALLIGHTCOLOR'];
                            }
                        }
                        
                        if($alertType == "Critical"){
                            $alertColor = $this->alertColor['CRITICAL'];
                            $alertLightColor = $this->alertColor['CRITICALLIGHTCOLOR'];
                        }
                        
                        if($alertType == "Warning"){
                            if(floatval($otherDataValues->par_avg)>floatval($stelLimit)){
                                $alertColor = $this->alertColor['CRITICAL'];
                                $alertLightColor = $this->alertColor['CRITICALLIGHTCOLOR'];
                            }else{
                                $alertColor = $this->alertColor['WARNING'];
                                $alertLightColor = $this->alertColor['WARNINGLIGHTCOLOR'];
                            }
                        }
                        
                        if($alertType == "outOfRange"){
                            $alertColor = $this->alertColor['OUTOFRANGE'];
                            $alertLightColor = $this->alertColor['OUTOFRANGELIGHTCOLOR'];
                        }
                        
                    }else{
                        if($alertType == "NORMAL"){
                            $alertColor = $this->alertColor['NORMAL'];
                            $alertLightColor = $this->alertColor['NORMALLIGHTCOLOR'];
                        }
                        
                        if($alertType == "Critical"){
                            $alertColor = $this->alertColor['CRITICAL'];
                            $alertLightColor = $this->alertColor['CRITICALLIGHTCOLOR'];
                        }
                        
                        if($alertType == "Warning"){
                            $alertColor = $this->alertColor['WARNING'];
                            $alertLightColor = $this->alertColor['WARNINGLIGHTCOLOR'];
                        }
                        
                        if($alertType == "outOfRange"){
                            $alertColor = $this->alertColor['OUTOFRANGE'];
                            $alertLightColor = $this->alertColor['OUTOFRANGELIGHTCOLOR'];
                        }
                    }
                    
                    
                    
                    
                    
                    /*
                    if($fetchDataValues->alertType === "Critical"){
                        $alertColor = $this->alertColor['CRITICAL'];
                        $alertLightColor = $this->alertColor['CRITICALLIGHTCOLOR'];
                    }
                    if($fetchDataValues->alertType === "Warning"){
                        $alertColor = $this->alertColor['WARNING'];
                        $alertLightColor = $this->alertColor['WARNINGLIGHTCOLOR'];
                    }
                    if($fetchDataValues->alertType === "outOfRange"){
                        $alertColor = $this->alertColor['OUTOFRANGE'];
                        $alertLightColor = $this->alertColor['OUTOFRANGELIGHTCOLOR'];
                    }
                    if($fetchDataValues->alertType === "NORMAL"){
                        $alertColor = $this->alertColor['NORMAL'];
                        $alertLightColor = $this->alertColor['NORMALLIGHTCOLOR'];
                    }*/
                    
                    
                }else{
                    $l = 0;
                }
                    
                $min = floatval($otherDataValues->par_min);
                $max = floatval($otherDataValues->par_max);
                $avg = floatval($otherDataValues->par_avg);
                $last = floatval($l);
                $minVal = number_format($min,2);
                $maxVal = number_format($max,2);
                $avgVal = number_format($avg,2);
                $lastVal = number_format($last,2);
                $sensorTagName = $otherDataValues->sensorTag; 
                
                
                if($sensorTagName != ""){
                    if($sensorTagsOfDeviceId[$x]->sensorOutput == "Analog"){
                        $sensorData['customerId']  = $sensorTagsOfDeviceId[$x]->customerId;
                        $sensorData['stateName']   = $sensorTagsOfDeviceId[$x]->stateName;
                        $sensorData['branchName']  = $sensorTagsOfDeviceId[$x]->branchName;
                        $sensorData['facilityName'] = $sensorTagsOfDeviceId[$x]->facilityName;
                        $sensorData['buildingName'] = $sensorTagsOfDeviceId[$x]->buildingName;
                        $sensorData['floorName'] = $sensorTagsOfDeviceId[$x]->floorName;
                        $sensorData['labDepName'] = $sensorTagsOfDeviceId[$x]->labDepName;
                        $sensorData['deviceName'] = $sensorTagsOfDeviceId[$x]->deviceName;
                        $sensorData['sensorNameUnit'] = $sensorTagsOfDeviceId[$x]->sensorNameUnit;
                        $sensorData['sensorOutput'] = $sensorTagsOfDeviceId[$x]->sensorOutput;
                        $sensorData['units'] = $sensorTagsOfDeviceId[$x]->units;
                        $sensorData['minRatedReadingScale'] = $sensorTagsOfDeviceId[$x]->minRatedReadingScale;
                        $sensorData['maxRatedReadingScale'] = $sensorTagsOfDeviceId[$x]->maxRatedReadingScale;
                        $sensorData["sensorTag"] = $sensorTagName;
                        $sensorData["sensorTagId"] = $sensorTagsOfDeviceId[$x]->id;
                        $sensorData["sensorStatus"] = $sensorTagsOfDeviceId[$x]->sensorStatus;
                        $sensorData["min"] = $minVal;
                        $sensorData["max"] = $maxVal;
                        $sensorData["avg"] = $avgVal;
                        $sensorData["last"] = $lastVal;
                        $sensorData["alertColor"] = $alertColor;
                        $sensorData["alertLightColor"] = $alertLightColor;
                        $sensorData["isStel"] = $isStel;
                        $sensorData["stelLimit"] = $stelLimit;
                        $sensorData["alertType"] = $alertType;
                        $deviceData['Analog']['data'][] = $sensorData;
                        $sensorCount++;
                    }
                    
                    if($sensorTagsOfDeviceId[$x]->sensorOutput == "Digital"){
                        $sensorData['customerId']  = $sensorTagsOfDeviceId[$x]->customerId;
                        $sensorData['stateName']   = $sensorTagsOfDeviceId[$x]->stateName;
                        $sensorData['branchName']  = $sensorTagsOfDeviceId[$x]->branchName;
                        $sensorData['facilityName'] = $sensorTagsOfDeviceId[$x]->facilityName;
                        $sensorData['buildingName'] = $sensorTagsOfDeviceId[$x]->buildingName;
                        $sensorData['floorName'] = $sensorTagsOfDeviceId[$x]->floorName;
                        $sensorData['labDepName'] = $sensorTagsOfDeviceId[$x]->labDepName;
                        $sensorData['deviceName'] = $sensorTagsOfDeviceId[$x]->deviceName;
                        $sensorData['sensorNameUnit'] = $sensorTagsOfDeviceId[$x]->sensorNameUnit;
                        $sensorData['sensorOutput'] = $sensorTagsOfDeviceId[$x]->sensorOutput;
                        $sensorData['units'] = $sensorTagsOfDeviceId[$x]->units;
                        $sensorData['minRatedReadingScale'] = $sensorTagsOfDeviceId[$x]->minRatedReadingScale;
                        $sensorData['maxRatedReadingScale'] = $sensorTagsOfDeviceId[$x]->maxRatedReadingScale;
                        $sensorData["sensorTag"] = $sensorTagName;
                        $sensorData["sensorTagId"] = $sensorTagsOfDeviceId[$x]->id;
                        $sensorData["sensorStatus"] = $sensorTagsOfDeviceId[$x]->sensorStatus;
                        $sensorData["min"] = $minVal;
                        $sensorData["max"] = $maxVal;
                        $sensorData["avg"] = $avgVal;
                        $sensorData["last"] = $lastVal;
                        $sensorData["alertColor"] = $alertColor;
                        $sensorData["alertLightColor"] = $alertLightColor;
                        $sensorData["isStel"] = $isStel;
                        $sensorData["stelLimit"] = $stelLimit;
                        $sensorData["alertType"] = $alertType;
                        $deviceData['Digital']['data'][] = $sensorData;
                        $sensorCount++;
                    }
                    
                    if($sensorTagsOfDeviceId[$x]->sensorOutput == "Modbus"){
                        $sensorData['customerId']  = $sensorTagsOfDeviceId[$x]->customerId;
                        $sensorData['stateName']   = $sensorTagsOfDeviceId[$x]->stateName;
                        $sensorData['branchName']  = $sensorTagsOfDeviceId[$x]->branchName;
                        $sensorData['facilityName'] = $sensorTagsOfDeviceId[$x]->facilityName;
                        $sensorData['buildingName'] = $sensorTagsOfDeviceId[$x]->buildingName;
                        $sensorData['floorName'] = $sensorTagsOfDeviceId[$x]->floorName;
                        $sensorData['labDepName'] = $sensorTagsOfDeviceId[$x]->labDepName;
                        $sensorData['deviceName'] = $sensorTagsOfDeviceId[$x]->deviceName;
                        $sensorData['sensorNameUnit'] = $sensorTagsOfDeviceId[$x]->sensorNameUnit;
                        $sensorData['sensorOutput'] = $sensorTagsOfDeviceId[$x]->sensorOutput;
                        $sensorData['units'] = $sensorTagsOfDeviceId[$x]->units;
                        $sensorData['minRatedReadingScale'] = $sensorTagsOfDeviceId[$x]->minRatedReadingScale;
                        $sensorData['maxRatedReadingScale'] = $sensorTagsOfDeviceId[$x]->maxRatedReadingScale;
                        $sensorData["sensorTag"] = $sensorTagName;
                        $sensorData["sensorTagId"] = $sensorTagsOfDeviceId[$x]->id;
                        $sensorData["sensorStatus"] = $sensorTagsOfDeviceId[$x]->sensorStatus;
                        $sensorData["min"] = $minVal;
                        $sensorData["max"] = $maxVal;
                        $sensorData["avg"] = $avgVal;
                        $sensorData["last"] = $lastVal;
                        $sensorData["alertColor"] = $alertColor;
                        $sensorData["alertLightColor"] = $alertLightColor;
                        $sensorData["isStel"] = $isStel;
                        $sensorData["stelLimit"] = $stelLimit;
                        $sensorData["alertType"] = $alertType;
                        $deviceData['Modbus']['data'][] = $sensorData;
                        $sensorCount++;
                    }
                }
            }
        }
        
           
        $deviceData['sensorCount'] = $sensorCount;
        $deviceData['alertCount'] = $alertCount;
        $deviceData['aqiIndex'] = $aqiIndex;
        $deviceData['disconnectedStatus'] = $disconnectedStatus;
        $response = $deviceData;
                
        return response($response, 200);
                
    }
    
    


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SampledSensorDataDetails  $sampledSensorDataDetails
     * @return \Illuminate\Http\Response
     */
    public function destroy(SampledSensorDataDetails $sampledSensorDataDetails)
    {
        //
    }
    
    
    public function getDeviceAqiGraphOld(Request $request){
        $fromDate = $request->fromDate;
        $toDate =  $request->toDate;
        $deviceId = $request->device_id;
        
        if($fromDate!= "" && $toDate!=""){
            //get only device max aqi
            
            $getDeviceAqi = DB::table('Aqi_values_per_device')
                            ->selectRaw('MAX(AqiValue) as AQIVAL, DATE_FORMAT(sampled_date_time,"%d-%m-%Y") as DATE')
                            ->where('deviceId','=',$deviceId)
                            //->whereBetween('DATE(sampled_date_time)', [$fromDate,  $toDate])
                            // ->groupBy('DATE')
                            ->get();
                            
            if(count($getDeviceAqi)){
                $deviceAQI = array();
            
                $aqms_class_value_clr=array("GOOD"=>"#10CD1A",
                                "SATISFACTORY"=>"#BCF389",
                                "MODERATELY POLLUTED"=>"#F5F31B",
                                "POOR"=>"#F8C91E",
                                "VERY POOR"=>"#E60B0F",
                                "SEVERE"=>"#890C0C");
                                
                $aqms_class_range=array("GOOD"=>array(0,50),
                            "SATISFACTORY"=>array(51,100),
                            "MODERATELY POLLUTED"=>array(101,200),
                            "POOR"=>array(201,300),
                            "VERY POOR"=>array(301,400),
                            "SEVERE"=>array(401,9999));
                
                foreach($getDeviceAqi as $data){
                    $deviceAQI["labels"][] = $data->DATE;
                    $deviceAQI["values"][] = number_format($data->AQIVAL,2);
                    
                    if($data->AQIVAL >= $aqms_class_range["SEVERE"][0] && $data->AQIVAL <= $aqms_class_range["SEVERE"][1]){
                        $deviceAQI["status"][] = "SEVERE";
                        $deviceAQI["color"][] = $aqms_class_value_clr["SEVERE"];
                    }else if($data->AQIVAL >= $aqms_class_range["VERY POOR"][0] && $data->AQIVAL <= $aqms_class_range["VERY POOR"][1]){
                        $deviceAQI["status"][] = "VERY POOR";
                        $deviceAQI["color"][] = $aqms_class_value_clr["VERY POOR"];
                    }else if($data->AQIVAL >= $aqms_class_range["POOR"][0] && $data->AQIVAL <= $aqms_class_range["POOR"][1]){
                        $deviceAQI["status"][] = "POOR";
                        $deviceAQI["color"][] = $aqms_class_value_clr["POOR"];
                    }else if($data->AQIVAL >= $aqms_class_range["MODERATELY POLLUTED"][0] && $data->AQIVAL <= $aqms_class_range["MODERATELY POLLUTED"][1]){
                        $deviceAQI["status"][] = "MODERATELY POLLUTED";
                        $deviceAQI["color"][] = $aqms_class_value_clr["MODERATELY POLLUTED"];
                    }else if($data->AQIVAL >= $aqms_class_range["SATISFACTORY"][0] && $data->AQIVAL <= $aqms_class_range["SATISFACTORY"][1]){
                        $deviceAQI["status"][] = "SATISFACTORY";
                        $deviceAQI["color"][] = $aqms_class_value_clr["SATISFACTORY"];
                    }else if($data->AQIVAL >= $aqms_class_range["GOOD"][0] && $data->AQIVAL <= $aqms_class_range["GOOD"][1]){
                        $deviceAQI["status"][] = "GOOD";
                        $deviceAQI["color"][] = $aqms_class_value_clr["GOOD"];
                    }
                }
                
                $getDeviceName = DB::table('devices')
                                    ->select('*')
                                    ->where('id','=',$deviceId)
                                    ->get();
                $deviceAQI["sensorName"] = $getDeviceName[0]->deviceName;
                $deviceData["sensorList"][] = $deviceAQI;
               
                $deviceData["labels"] =   $deviceData["sensorList"][0]["labels"];
                $response = $deviceData;
            }else{
                $deviceAQI["values"] = [];
                $deviceAQI["status"] = [];
                $deviceAQI["color"] = [];
                $getDeviceName = DB::table('devices')
                                    ->select('*')
                                    ->where('id','=',$deviceId)
                                    ->get();
                $deviceAQI["sensorName"] = $getDeviceName[0]->deviceName;
              
                $deviceData["sensorList"][] = $deviceAQI;
                $deviceData["labels"] = [];
                
               
                $response = $deviceData;
               
            }
            
            $status = 200;
            return response($response,$status);
            
        }else{
            
            $date = new DateTime('Asia/Kolkata');      
            $current_time = $date->format('Y-m-d H:i:s');
        
            //$sensorTagId = 176; //$request->sensorTagId;
            $segregationInterval = 60; //$request->segretionInterval; //in mins   $sampling_Interval_min=60;
            $rangeInterval = 1440; //$request->rangeInterval; //  $backInterval_min=24*60;
            
            // $sensorTagId = 245;
            // $sampling_Interval_min=60;
            // $cur_date_time=date("Y-m-d H:i:s");
            // $backInterval_min=24*60;
            // single sensortag data
            
            $sensorData = array();
            $deviceData = array();
            
            $device = DB::table('Aqi_values_per_deviceSensor')
                        ->selectRaw('DISTINCT(sensorId) as sensorId')
                        ->where('deviceId','=',$deviceId)
                        //->where('sensorId','<>',244)
                        ->get();
                        
            if(count($device)>0){
                $sensorList = array();
            
                foreach($device as $sensorId){
                    $sensorTagId = $sensorId->sensorId;
                    
                    $getSensorDetails = DB::table("sensors")     
                                    ->select('*')
                                    ->where('id','=',$sensorTagId)
                                    ->get();
                    $sensor = [
                        "sensorTag"=>$getSensorDetails[0]->sensorTag,
                        "sensorNameUnit"=>$getSensorDetails[0]->sensorNameUnit
                    ];
                    
                    $sensorData['sensorName'] = $sensor['sensorTag'];
                    $sensorData['sensorUnit'] = $sensor['sensorNameUnit'];
                    
                    $sensorChartValues = array();
                    
                    //currently aqi chart is been taken from AQI_CHART_PARAMETER_SCALINGS, but later using same query it can be taken from sensor unit or sensor table
                    
                    $getStatusAndColor = DB::table("AQI_CHART_PARAMETER_SCALINGS")
                                            ->select('*')
                                            ->where('AQI_PARAMETER','=',$sensor["sensorNameUnit"])
                                            ->get();
                    
                    foreach($getStatusAndColor as $value){
                        if($value->CLASSIFICATION_LABEL == "GOOD"){
                            $sensorChartValues["GOOD"]["MIN"] = $value->MIN_VAL;
                            $sensorChartValues["GOOD"]["MAX"] = $value->MAX_VAL;
                        }
                        if($value->CLASSIFICATION_LABEL == "SATISFACTORY"){
                            $sensorChartValues["SATISFACTORY"]["MIN"] = $value->MIN_VAL;
                            $sensorChartValues["SATISFACTORY"]["MAX"] = $value->MAX_VAL;
                        }
                        if($value->CLASSIFICATION_LABEL == "MODERATELY POLLUTED"){
                            $sensorChartValues["MODERATELY POLLUTED"]["MIN"] = $value->MIN_VAL;
                            $sensorChartValues["MODERATELY POLLUTED"]["MAX"] = $value->MAX_VAL;
                        }
                        if($value->CLASSIFICATION_LABEL == "POOR"){
                            $sensorChartValues["POOR"]["MIN"] = $value->MIN_VAL;
                            $sensorChartValues["POOR"]["MAX"] = $value->MAX_VAL;
                        }
                        if($value->CLASSIFICATION_LABEL == "VERY POOR"){
                            $sensorChartValues["VERY POOR"]["MIN"] = $value->MIN_VAL;
                            $sensorChartValues["VERY POOR"]["MAX"] = $value->MAX_VAL;
                        }
                        if($value->CLASSIFICATION_LABEL == "SEVERE"){
                            $sensorChartValues["SEVERE"]["MIN"] = $value->MIN_VAL;
                            $sensorChartValues["SEVERE"]["MAX"] = $value->MAX_VAL;
                        }
                    }
                    
                    $aqms_class_value_clr=array("GOOD"=>"#10CD1A",
                                "SATISFACTORY"=>"#BCF389",
                                "MODERATELY POLLUTED"=>"#F5F31B",
                                "POOR"=>"#F8C91E",
                                "VERY POOR"=>"#E60B0F",
                                "SEVERE"=>"#890C0C");
                    
                                    
                    $sensorAqiDataValues = DB::table("Aqi_values_per_deviceSensor")
                                        ->select(DB::raw('sensorId, avg(AqiValue) as AQIVAL,DATE(sampled_date_time) as DATE,TIME_FORMAT(sampled_date_time, "%H:%i") as TIME,FLOOR(UNIX_TIMESTAMP(sampled_date_time)/("'. $segregationInterval.'" * 60)) AS timekey'))
                                        ->where('sensorId','=',$sensorTagId)
                                        ->whereRaw('sampled_date_time > ("'.$current_time.'" - INTERVAL '.$rangeInterval.' MINUTE)')
                                        ->groupBy('timekey')
                                        ->get()->toArray();  
                                        
                    $cnt = count($sensorAqiDataValues);
                    
                                        
                    foreach($sensorAqiDataValues as $sensorAqi){
                      
                        // $deviceData["labels"][] = $sensorAqi->TIME;
                        $sensorData["labels"][] = $sensorAqi->TIME;
                        $sensorData["values"][] = round(floatVal($sensorAqi->AQIVAL),2);
                        
                        
                        
                        if($sensorTagId!=244){
                            if($sensorAqi->AQIVAL >= $sensorChartValues["SEVERE"]["MIN"] && $sensorAqi->AQIVAL <= $sensorChartValues["SEVERE"]["MAX"]){
                                $sensorData["status"][] = "SEVERE";
                                $sensorData["color"][] = $aqms_class_value_clr["SEVERE"];
                            }else if($sensorAqi->AQIVAL >= $sensorChartValues["VERY POOR"]["MIN"] && $sensorAqi->AQIVAL <= $sensorChartValues["VERY POOR"]["MAX"]){
                                $sensorData["status"][] = "VERY POOR";
                                $sensorData["color"][] = $aqms_class_value_clr["VERY POOR"];
                            }else if($sensorAqi->AQIVAL >= $sensorChartValues["POOR"]["MIN"] && $sensorAqi->AQIVAL <= $sensorChartValues["POOR"]["MAX"]){
                                $sensorData["status"][] = "POOR";
                                $sensorData["color"][] = $aqms_class_value_clr["POOR"];
                            }else if($sensorAqi->AQIVAL >= $sensorChartValues["MODERATELY POLLUTED"]["MIN"] && $sensorAqi->AQIVAL <= $sensorChartValues["MODERATELY POLLUTED"]["MAX"]){
                                $sensorData["status"][] = "MODERATELY POLLUTED";
                                $sensorData["color"][] = $aqms_class_value_clr["MODERATELY POLLUTED"];
                            }else if($sensorAqi->AQIVAL >= $sensorChartValues["SATISFACTORY"]["MIN"] && $sensorAqi->AQIVAL <= $sensorChartValues["SATISFACTORY"]["MAX"]){
                                $sensorData["status"][] = "SATISFACTORY";
                                $sensorData["color"][] = $aqms_class_value_clr["SATISFACTORY"];
                            }else if($sensorAqi->AQIVAL >= $sensorChartValues["GOOD"]["MIN"] && $sensorAqi->AQIVAL <= $sensorChartValues["GOOD"]["MAX"]){
                                $sensorData["status"][] = "GOOD";
                                $sensorData["color"][] = $aqms_class_value_clr["GOOD"];
                            }
                        }
                        
                    }
                    
                    $deviceData["sensorList"][] =$sensorData;
                    $sensorData["labels"] = [];
                    $sensorData["values"] = [];
                    $sensorData["status"] = [];
                    $sensorData["color"] = [];
                    
                }
                $deviceData["labels"] = $deviceData["sensorList"][0]["labels"];
                $response = $deviceData;
            }else{
                $sensorData["labels"] = [];
                $sensorData["values"] = [];
                $sensorData["status"] = [];
                $sensorData["color"] = [];
                $deviceData["sensorList"][] =$sensorData;
                $deviceData["labels"] = [];
                $response = $deviceData;
            }
            
            $status = 200;
            return response($response,$status);
          
        }
        
    }
    
    
    public function getDeviceAqiGraph(Request $request)
    {
        $fromDate = $request->fromDate;
        $toDate =  $request->toDate;
        $deviceId = $request->device_id;
        $startDate = date('Y-m-d', strtotime("-1 day", strtotime($fromDate)));
        $endDate = date('Y-m-d', strtotime("+1 day", strtotime($toDate)));
        if($fromDate!= "" && $toDate!=""){
            //get only device max aqi
            
            $getDeviceAqi = DB::table('Aqi_values_per_device')
                            ->selectRaw('MAX(AqiValue) as AQIVAL, DATE_FORMAT(sampled_date_time,"%d-%m-%Y") as DATE,  DATE(sampled_date_time)')
                            ->where('deviceId','=',$deviceId)
                            ->whereBetween('sampled_date_time', [$startDate,  $endDate])
                            // ->whereDate('sampled_date_time','>=',$startDate)
                            // ->whereDate('sampled_date_time','<=',$toDate)
                            ->groupBy('DATE(sampled_date_time)')
                            ->get();
                            
            if(count($getDeviceAqi)){
                $deviceAQI = array();
            
                $aqms_class_value_clr=array("GOOD"=>"#10CD1A",
                                "SATISFACTORY"=>"#BCF389",
                                "MODERATELY POLLUTED"=>"#F5F31B",
                                "POOR"=>"#F8C91E",
                                "VERY POOR"=>"#E60B0F",
                                "SEVERE"=>"#890C0C");
                                
                $aqms_class_range=array("GOOD"=>array(0,50),
                            "SATISFACTORY"=>array(51,100),
                            "MODERATELY POLLUTED"=>array(101,200),
                            "POOR"=>array(201,300),
                            "VERY POOR"=>array(301,400),
                            "SEVERE"=>array(401,9999));
                
                foreach($getDeviceAqi as $data){
                    $deviceAQI["labels"][] = $data->DATE;
                    // $formatedValue = number_format($data->AQIVAL,2);
                    // $deviceAQI["values"][] = str_replace(',', '', $formatedValue);
                    $deviceAQI["values"][] = round($data->AQIVAL,0);
                    
                    // if($data->AQIVAL >= $aqms_class_range["SEVERE"][0] && $data->AQIVAL <= $aqms_class_range["SEVERE"][1]){
                    if($data->AQIVAL >= $aqms_class_range["SEVERE"][0]){
                        $deviceAQI["status"][] = "SEVERE";
                        $deviceAQI["color"][] = $aqms_class_value_clr["SEVERE"];
                    }else if($data->AQIVAL >= $aqms_class_range["VERY POOR"][0] && $data->AQIVAL <= $aqms_class_range["VERY POOR"][1]){
                        $deviceAQI["status"][] = "VERY POOR";
                        $deviceAQI["color"][] = $aqms_class_value_clr["VERY POOR"];
                    }else if($data->AQIVAL >= $aqms_class_range["POOR"][0] && $data->AQIVAL <= $aqms_class_range["POOR"][1]){
                        $deviceAQI["status"][] = "POOR";
                        $deviceAQI["color"][] = $aqms_class_value_clr["POOR"];
                    }else if($data->AQIVAL >= $aqms_class_range["MODERATELY POLLUTED"][0] && $data->AQIVAL <= $aqms_class_range["MODERATELY POLLUTED"][1]){
                        $deviceAQI["status"][] = "MODERATELY POLLUTED";
                        $deviceAQI["color"][] = $aqms_class_value_clr["MODERATELY POLLUTED"];
                    }else if($data->AQIVAL >= $aqms_class_range["SATISFACTORY"][0] && $data->AQIVAL <= $aqms_class_range["SATISFACTORY"][1]){
                        $deviceAQI["status"][] = "SATISFACTORY";
                        $deviceAQI["color"][] = $aqms_class_value_clr["SATISFACTORY"];
                    }else if($data->AQIVAL >= $aqms_class_range["GOOD"][0] && $data->AQIVAL <= $aqms_class_range["GOOD"][1]){
                        $deviceAQI["status"][] = "GOOD";
                        $deviceAQI["color"][] = $aqms_class_value_clr["GOOD"];
                    }
                }
                
                $getDeviceName = DB::table('devices')
                                    ->select('*')
                                    ->where('id','=',$deviceId)
                                    ->get();
                $deviceAQI["sensorName"] = $getDeviceName[0]->deviceName;
                $deviceData["sensorList"][] = $deviceAQI;
               
                $deviceData["labels"] =   $deviceData["sensorList"][0]["labels"];
                $response = $deviceData;
            }else{
                $deviceAQI["values"] = [];
                $deviceAQI["status"] = [];
                $deviceAQI["color"] = [];
                $getDeviceName = DB::table('devices')
                                    ->select('*')
                                    ->where('id','=',$deviceId)
                                    ->get();
                $deviceAQI["sensorName"] = $getDeviceName[0]->deviceName;
              
                $deviceData["sensorList"][] = $deviceAQI;
                $deviceData["labels"] = [];
                
               
                $response = $deviceData;
               
            }
            
            $status = 200;
            return response($response,$status);
            
        }else{
            
            $date = new DateTime('Asia/Kolkata');      
            $current_time = $date->format('Y-m-d H:i:s');
        
            //$sensorTagId = 176; //$request->sensorTagId;
            $segregationInterval = 60; //$request->segretionInterval; //in mins   $sampling_Interval_min=60;
            $rangeInterval = 1440; //$request->rangeInterval; //  $backInterval_min=24*60;
            
            // $sensorTagId = 245;
            // $sampling_Interval_min=60;
            // $cur_date_time=date("Y-m-d H:i:s");
            // $backInterval_min=24*60;
            // single sensortag data
            
            $sensorData = array();
            $deviceData = array();
            
            $device = DB::table('sensors')
                        ->selectRaw('DISTINCT(id) as sensorId')
                        ->where('isAqi','=',1)
                        ->where('deviceId','=',$deviceId)
                        //->where('sensorId','<>',244)
                        ->get();
                        
            if(count($device)>0){
                $sensorList = array();
            
                foreach($device as $sensorId){
                    $sensorTagId = $sensorId->sensorId;
                    
                    $getSensorDetails = DB::table("sensors")     
                                    ->select('*')
                                    ->where('id','=',$sensorTagId)
                                    ->orderBy('id','desc')
                                    ->get();
                    
                    if(count($getSensorDetails)>0){
                        $sensor = [
                            "sensorTag"=>$getSensorDetails[0]->sensorTag,
                            "sensorNameUnit"=>$getSensorDetails[0]->sensorNameUnit
                        ];
                        
                        $sensorData['sensorName'] = $sensor['sensorTag'];
                        $sensorData['sensorUnit'] = $sensor['sensorNameUnit'];
                        
                        $sensorChartValues = array();
                        
                       
                        
                        $aqms_class_value_clr=array("GOOD"=>"#10CD1A",
                                    "SATISFACTORY"=>"#BCF389",
                                    "MODERATELY POLLUTED"=>"#F5F31B",
                                    "POOR"=>"#F8C91E",
                                    "VERY POOR"=>"#E60B0F",
                                    "SEVERE"=>"#890C0C");
                        
                                        
                        $sensorAqiDataValues = DB::table("Aqi_values_per_deviceSensor")
                                            ->select(DB::raw('sensorId, avg(AqiValue) as AQIVAL,DATE(sampled_date_time) as DATE,TIME_FORMAT(sampled_date_time, "%H:%i") as TIME,FLOOR(UNIX_TIMESTAMP(sampled_date_time)/("'. $segregationInterval.'" * 60)) AS timekey'))
                                            ->where('sensorId','=',$sensorTagId)
                                            ->whereRaw('sampled_date_time > ("'.$current_time.'" - INTERVAL '.$rangeInterval.' MINUTE)')
                                            ->groupBy('timekey')
                                            ->get()->toArray();  
                                            
                        $cnt = count($sensorAqiDataValues);
                        
                        $aqms_class_range=array("GOOD"=>array(0,50),
                                "SATISFACTORY"=>array(51,100),
                                "MODERATELY POLLUTED"=>array(101,200),
                                "POOR"=>array(201,300),
                                "VERY POOR"=>array(301,400),
                                "SEVERE"=>array(401,9999));  
                                
                                
                        for($j=0;$j<$cnt;$j++){
                            // $val = "";
                            $sensorData["labels"][] = $sensorAqiDataValues[$j]->TIME;
                            $sensorData["values"][] = round(floatVal($sensorAqiDataValues[$j]->AQIVAL),2);
                            $val = round(floatVal($sensorAqiDataValues[$j]->AQIVAL));
                            
                            // if($val >= $aqms_class_range["SEVERE"][0] && $val <= $aqms_class_range["SEVERE"][1]){
                            if($val >= $aqms_class_range["SEVERE"][0]){
                                $sensorData["status"][] = "SEVERE";
                                $sensorData["color"][] = $aqms_class_value_clr["SEVERE"];
                            }
                            if($val >= $aqms_class_range["VERY POOR"][0] && $val <= $aqms_class_range["VERY POOR"][1]){
                                $sensorData["status"][] = "VERY POOR";
                                $sensorData["color"][] = $aqms_class_value_clr["VERY POOR"];
                            }
                            if($val >= $aqms_class_range["POOR"][0] && $val <= $aqms_class_range["POOR"][1]){
                                $sensorData["status"][] = "POOR";
                                $sensorData["color"][] = $aqms_class_value_clr["POOR"];
                            }
                            if($val >= $aqms_class_range["MODERATELY POLLUTED"][0] && $val <= $aqms_class_range["MODERATELY POLLUTED"][1]){
                                $sensorData["status"][] = "MODERATELY POLLUTED";
                                $sensorData["color"][] = $aqms_class_value_clr["MODERATELY POLLUTED"];
                            }
                            if($val >= $aqms_class_range["SATISFACTORY"][0] && $val <= $aqms_class_range["SATISFACTORY"][1]){
                                $sensorData["status"][] = "SATISFACTORY";
                                $sensorData["color"][] = $aqms_class_value_clr["SATISFACTORY"];
                            }
                            if($val >= $aqms_class_range["GOOD"][0] && $val <= $aqms_class_range["GOOD"][1]){
                                $sensorData["status"][] = "GOOD";
                                $sensorData["color"][] = $aqms_class_value_clr["GOOD"];
                            }
                        }
                        
                        /*
                                
                        foreach($sensorAqiDataValues as $sensorAqi){
                          
                            // $deviceData["labels"][] = $sensorAqi->TIME;
                            $sensorData["labels"][] = $sensorAqi->TIME;
                            $sensorData["values"][] = round(floatVal($sensorAqi->AQIVAL),2);
                            $val = round(floatVal($sensorAqi->AQIVAL),2);
                            
                            
                            if($val >= $aqms_class_range["SEVERE"][0] && $val <= $aqms_class_range["SEVERE"][1]){
                                $sensorData["status"][] = "SEVERE";
                                $sensorData["color"][] = $aqms_class_value_clr["SEVERE"];
                            }else if($val >= $aqms_class_range["VERY POOR"][0] && $val <= $aqms_class_range["VERY POOR"][1]){
                                $sensorData["status"][] = "VERY POOR";
                                $sensorData["color"][] = $aqms_class_value_clr["VERY POOR"];
                            }else if($val >= $aqms_class_range["POOR"][0] && $val <= $aqms_class_range["POOR"][1]){
                                $sensorData["status"][] = "POOR";
                                $sensorData["color"][] = $aqms_class_value_clr["POOR"];
                            }else if($val >= $aqms_class_range["MODERATELY POLLUTED"][0] && $val <= $aqms_class_range["MODERATELY POLLUTED"][1]){
                                $sensorData["status"][] = "MODERATELY POLLUTED";
                                $sensorData["color"][] = $aqms_class_value_clr["MODERATELY POLLUTED"];
                            }else if($val >= $aqms_class_range["SATISFACTORY"][0] && $val <= $aqms_class_range["SATISFACTORY"][1]){
                                $sensorData["status"][] = "SATISFACTORY";
                                $sensorData["color"][] = $aqms_class_value_clr["SATISFACTORY"];
                            }else if($val >= $aqms_class_range["GOOD"][0] && $val <= $aqms_class_range["GOOD"][1]){
                                $sensorData["status"][] = "GOOD";
                                $sensorData["color"][] = $aqms_class_value_clr["GOOD"];
                            }
                                
                        
                              
                            if($sensorTagId!=244){
                                if($sensorAqi->AQIVAL >= $sensorChartValues["SEVERE"]["MIN"] && $sensorAqi->AQIVAL <= $sensorChartValues["SEVERE"]["MAX"]){
                                    $sensorData["status"][] = "SEVERE";
                                    $sensorData["color"][] = $aqms_class_value_clr["SEVERE"];
                                }else if($sensorAqi->AQIVAL >= $sensorChartValues["VERY POOR"]["MIN"] && $sensorAqi->AQIVAL <= $sensorChartValues["VERY POOR"]["MAX"]){
                                    $sensorData["status"][] = "VERY POOR";
                                    $sensorData["color"][] = $aqms_class_value_clr["VERY POOR"];
                                }else if($sensorAqi->AQIVAL >= $sensorChartValues["POOR"]["MIN"] && $sensorAqi->AQIVAL <= $sensorChartValues["POOR"]["MAX"]){
                                    $sensorData["status"][] = "POOR";
                                    $sensorData["color"][] = $aqms_class_value_clr["POOR"];
                                }else if($sensorAqi->AQIVAL >= $sensorChartValues["MODERATELY POLLUTED"]["MIN"] && $sensorAqi->AQIVAL <= $sensorChartValues["MODERATELY POLLUTED"]["MAX"]){
                                    $sensorData["status"][] = "MODERATELY POLLUTED";
                                    $sensorData["color"][] = $aqms_class_value_clr["MODERATELY POLLUTED"];
                                }else if($sensorAqi->AQIVAL >= $sensorChartValues["SATISFACTORY"]["MIN"] && $sensorAqi->AQIVAL <= $sensorChartValues["SATISFACTORY"]["MAX"]){
                                    $sensorData["status"][] = "SATISFACTORY";
                                    $sensorData["color"][] = $aqms_class_value_clr["SATISFACTORY"];
                                }else if($sensorAqi->AQIVAL >= $sensorChartValues["GOOD"]["MIN"] && $sensorAqi->AQIVAL <= $sensorChartValues["GOOD"]["MAX"]){
                                    $sensorData["status"][] = "GOOD";
                                    $sensorData["color"][] = $aqms_class_value_clr["GOOD"];
                                }
                            }
                            
                            
                            
                            
                        }*/
                    }
                    
                    $deviceData["sensorList"][] =$sensorData;
                    // $sensorData["labels"] = [];
                    // $sensorData["values"] = [];
                    // $sensorData["status"] = [];
                    // $sensorData["color"] = [];
                    $sensorData = [];
                }
                $deviceData["labels"] = $deviceData["sensorList"][0]["labels"];
                $response = $deviceData;
            }else{
                $sensorData["labels"] = [];
                $sensorData["values"] = [];
                $sensorData["status"] = [];
                $sensorData["color"] = [];
                $deviceData["sensorList"][] =$sensorData;
                $deviceData["labels"] = [];
                $response = $deviceData;
            }
            
            $status = 200;
            return response($response,$status);
          
        }
        
    }
    
    
    public function test1()
    {
       $devices = DB::table('Aqi_values_per_device')
            ->join('devices as d','d.id','=','Aqi_values_per_device.deviceId')
            ->where('d.floor_id','=','72')
            ->select('deviceId')
            ->distinct()
            ->where('sampled_date_time', '>=' ,Carbon::now()->subMinutes(1440)->toDateTimeString())
            ->where('AqiValue','!=','0')
            ->get();
        
        
        for($i=0; $i<count($devices); $i++)
        {
            $data = DB::table('Aqi_values_per_deviceSensor')
                ->select('sensorId')
                ->distinct()
                ->where('sampled_date_time', '>=' ,Carbon::now()->subMinutes(1440)->toDateTimeString())
                ->where('deviceId',$devices[$i]->deviceId)
                ->get();
                
            if($data){
                
                for($j=0; $j<count($data); $j++)
                {
                    $sensorsList[] = $data[$j]->sensorId;
                }
            }
        } 
        
        for($k=0; $k<count($sensorsList); $k++)
        {
            $aqi = DB::table('Aqi_values_per_deviceSensor')
                ->select(DB::raw('DATE_FORMAT(sampled_date_time, "%Y-%m-%d %H:00:00") as hour'), DB::raw('AVG(AqiValue) as average'))
                ->where('sampled_date_time', '>=' ,Carbon::now()->subMinutes(1440)->toDateTimeString())
                ->where('deviceId','164')
                ->where('sensorId',$sensorsList[$k])
                ->groupBy('hour')
                ->get();
            
            $maxAverage[] = $aqi->max('average');
        }
        
        $latestAqi = max($maxAverage);
        
        if($latestAqi){
            $aqiIndex = round($latestAqi, 2);
            
        }else{
            $aqiIndex = "NA";
        }

       
        return $devices;
    }
    
    
    public function test(Request $request)
    {
        $devices = ['165'];
        for($i=0; $i<count($devices); $i++)
        {
            $data = DB::table('Aqi_values_per_deviceSensor')
                ->select('Aqi_values_per_deviceSensor.sensorId','s.sensorNameUnit')
                ->distinct()
                ->join('sensors as s','s.id','=','Aqi_values_per_deviceSensor.sensorId')
                ->where('Aqi_values_per_deviceSensor.sampled_date_time', '>=' ,Carbon::now()->subMinutes(1440)->toDateTimeString())
                ->where('Aqi_values_per_deviceSensor.deviceId', $devices[$i])
                ->get();
                
            if($data){
               foreach($data as $k => $v){
                   $sensorsList[] = $v;
               }
            }
        } 
            
        return $sensorsList;
    }
}
