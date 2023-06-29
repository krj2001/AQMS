<?php

namespace App\Http\Controllers\UTILITY;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AlertCron;
use App\Models\Device;
use App\Models\labDepartment;
use App\Models\Floor;
use App\Models\Building;
use Illuminate\Support\Facades\DB; 
use Carbon\Carbon;

class DataUtilityController extends Controller
{
    protected $total = "";
    protected $page = "";
    protected $perPage = "";
    protected $result = "";  
    protected $sort = "";   
    protected $column = ""; 
    protected $aqiIndex = ""; 
    protected $sensorCount = 0;
    protected $alertCount = 0;
    protected $disconnectedDevices = 0;
    protected $labHooterStatus = 1;
    protected $returnedData = [];
    protected $imageBuildingURL = "";
    protected $imageFloorURL = "";
    protected $imageLabURL = "";
    
    function __construct($request,$query) {
        $device_max_aqi=array();
        if($query) {            
            if($request->lab_id != ''){
                // fetch lab image
                $lab = labDepartment::where('id', $request->lab_id)->first(); 
                if($lab){
                    $this->imageLabURL = $lab->labDepMap;
                }
            } else if($request->floor_id != ""){
                // fetch floor image Floor
                $floor = Floor::where('id', $request->floor_id)->first(); 
                if($floor){
                    $this->imageFloorURL = $floor->floorMap;
                }
            } else if($request->building_id != ''){
                // fetch building image
                $building = Building::where('id', $request->building_id)->first(); 
                if($building){
                    $this->imageBuildingURL = $building->buildingImg;
                }
            }
            
            if($request->lab_id != ""){
                
                $devices = $query->get();
                $length = count($devices);      
                
                for($x=0;$x<$length;$x++){
                    $deviceId = $devices[$x]->id;
                    $companyCode = $devices[$x]->companyCode;
                    $alertCount = 1;
                    $alertQuery = AlertCron::select('*')
                     ->where('deviceId','=',$deviceId)
                     ->where('companyCode','=',$companyCode)
                     ->where('status','=','0')
                     ->take(100)
                     ->get();
                    
                    $alertCount = count($alertQuery);
                    $this->alertCount += $alertCount;
                }
               
                $deviceQuery = Device::select('*')
                     ->where('lab_id','=',$request->lab_id)
                     ->where('companyCode','=',$request->Header('companyCode'))
                     ->where('disconnectedStatus','=','1')
                     ->get();
                     
                $this->disconnectedDevices = $deviceQuery->count();
                
                $labQuery =  labDepartment::select('*')
                             ->where('id','=',$request->lab_id)
                             ->where('companyCode','=',$request->Header('companyCode'))
                             ->first();
                             
               (int)$this->labHooterStatus = $labQuery->labHooterStatus;
            }
            
            $this->perPage = $request->input(key:'perPageData') == "" ? 100 : $request->input(key:'perPageData');
            $this->sort = $request->input(key:'sort') == "" ? "ASC" : $request->input(key:'sort');
            $this->column = $request->input(key:'column') == "" ? "id" : $request->input(key:'sort');
            $query->orderBy($this->column,$this->sort);
            
            $this->page = $request->input(key:'page', default:1);
            $this->total = $query->count();    
            $this->result = $query->offset(value:($this->page - 1) * $this->perPage)->limit($this->perPage)->get();   
            
            //only for devices getting alertcount
            if($request->lab_id != ""){
                $deviceData = array();
                
                $deviceCount = count($this->result);
            
                for($x=0;$x<$deviceCount;$x++){
                      $deviceAlertCount = DB::table('alert_crons')
                                            ->selectRaw('count(*) as alertCount')
                                            ->where('deviceId','=',$this->result[$x]->id)
                                            ->where('status','=','0')
                                            ->get();
                      
                      $this->result[$x]['alertDataCount'] = $deviceAlertCount[0]->alertCount;

                        // $deviceStatusCount = DB::table('sampled_sensor_data_details')
                        //                 ->selectRaw('alertType')
                        //                 ->where('device_id','=',$this->result[$x]->id)
                        //                 ->orderBy('id','DESC')  
                                         
                        // $alertType = "NA";
                        
                        // if($deviceStatusCount){
                        //     $alertType = $deviceStatusCount->alertType;
                        // }           
                        
                        // $this->result[$x]['alertType'] = $alertType;
                  
                    $deviceData[] = $this->result[$x];
                }
                
                $this->result = $deviceData;
            }
            // search aqi index api start
           
            if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !="" && $request->lab_id !=""){
              
                $labData = array();
                $deviceCount = count($this->result);
                $labAQIvalues=array();
                
                /*for($x=0;$x<$deviceCount;$x++){       
                    
                    $deviceData = array();
                    $devicesList = DB::table('devices')
                                        ->selectRaw('id')
                                        ->where('location_id','=',$request->location_id)
                                        ->where('branch_id','=',$request->branch_id)
                                        ->where('facility_id','=',$request->facility_id)
                                        ->where('building_id','=',$request->building_id)
                                        ->where('floor_id','=',$request->floor_id)
                                        ->where('lab_id','=',$request->lab_id)
                                        ->orderBy('id','DESC')
                                        ->get();
                    $deviceCount = count($devicesList);
                        
                    for($j=0;$j<$deviceCount;$j++){
                        $locMaxAqi = DB::table('Aqi_values_per_device')
                                        ->selectRaw('AqiValue,deviceId,labId')
                                            ->where('deviceId','=',$devicesList[$j]->id)
                                            ->where('sampled_date_time', '>' ,Carbon::now()->subHours(1)->toDateTimeString())
                                            ->orderBy('AqiValue','DESC')
                                            // ->whereRaw('AqiValue = (select max(`AqiValue`) from Aqi_values_per_device)')
                                            ->take(1)
                                            ->get();
                        
                            if(count($locMaxAqi) != 0){
                                $deviceData[] = $locMaxAqi[0]->AqiValue;        
                            }else{
                                // $deviceData[] = "NA";        
                            }              
                    }
                    if(count($deviceData) == 0){
                        // $this->result[$x]['aqiIndex'] = "NA";
                            $this->aqiIndex ="NA";
                    }else{
                        // $this->result[$x]['aqiIndex'] = max($deviceData); 
                            $this->aqiIndex =  number_format(max($deviceData),2); 
                    }
                } */
                
                
                // displaying maximum of 1 hours avg value   27-03-2023
               /* 
                $devices = DB::table('Aqi_values_per_device')
                    ->join('devices as d','d.id','=','Aqi_values_per_device.deviceId')
                    ->where('d.location_id','=',$request->location_id)
                    ->where('d.branch_id','=',$request->branch_id)
                    ->where('d.facility_id','=',$request->facility_id)
                    ->where('d.building_id','=',$request->building_id)
                    ->where('d.floor_id','=',$request->floor_id)
                    ->where('d.lab_id','=',$request->lab_id)
                    ->where('sampled_date_time', '>=' ,Carbon::now()->subHours(24)->toDateTimeString())
                    ->where('AqiValue','!=','0')
                    ->select('deviceId')
                    ->distinct()
                    ->get();
                
                $sensorsList = array();
                for($i=0; $i<count($devices); $i++)
                {
                    $data = DB::table('Aqi_values_per_deviceSensor')
                        ->select('sensorId')
                        ->distinct()
                        ->where('sampled_date_time', '>=' ,Carbon::now()->subHours(24)->toDateTimeString())
                        ->where('deviceId',$devices[$i]->deviceId)
                        ->get();
                        
                    if($data){
                        for($j=0; $j<count($data); $j++)
                        {
                            $sensorsList[] = $data[$j]->sensorId;
                        }
                    }
                } 
                
                $maxAverage = array();
                for($k=0; $k<count($sensorsList); $k++)
                {
                    $aqi = DB::table('Aqi_values_per_deviceSensor')
                        ->select(DB::raw('DATE_FORMAT(sampled_date_time, "%Y-%m-%d %H:00:00") as hour'), DB::raw('AVG(AqiValue) as average'))
                        ->where('sampled_date_time', '>=' ,Carbon::now()->subHours(24)->toDateTimeString())
                        ->where('sensorId',$sensorsList[$k])
                        ->where('AqiValue','!=','0')
                        ->groupBy('hour')
                        ->get();
                    
                    $maxAverage[] = $aqi->avg('average');
                }
                */
                
                
                // Modified on 24-04-2023
                
                $sensorsList = array();
                for($i=0; $i<count($devices); $i++)
                {
                    $data = DB::table('Aqi_values_per_deviceSensor')
                        ->select('Aqi_values_per_deviceSensor.sensorId','s.sensorNameUnit')
                        ->distinct()
                        ->join('sensors as s','s.id','=','Aqi_values_per_deviceSensor.sensorId')
                        ->where('Aqi_values_per_deviceSensor.sampled_date_time', '>=' ,Carbon::now()->subMinutes(1440)->toDateTimeString())
                        ->where('Aqi_values_per_deviceSensor.deviceId', $devices[$i]->deviceId)
                        ->get();
                        
                    if($data){
                       foreach($data as $k => $v){
                           $sensorsList[] = $v;
                       }
                    }
                } 
                
                $maxAverage = array();
                for($i=0; $i<count($sensorsList); $i++)
                {
                    $calculate = false;
                    
                    if($sensorsList[$i]->sensorNameUnit == 'CO' || $sensorsList[$i]->sensorNameUnit == 'O3'){
                        
                        $data = DB::table('Aqi_values_per_deviceSensor')
                            ->select(DB::raw("DATE_FORMAT(sampled_date_time, '%Y-%m-%d %H:00:00') AS hour"), DB::raw("AVG(AqiValue) AS average"))
                            ->where('sampled_date_time', '>=', Carbon::now()->subHours(8))
                            ->where('sensorId', $sensorsList[$i]->sensorId)
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
                            ->where('sensorId', $sensorsList[$i]->sensorId)
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
                    $this->aqiIndex =  "$max";
                }
                else
                {
                    $this->aqiIndex =  "0";
                }
            }
            
            else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !=""){
            
                /*$labData = array();
                $labCount = count($this->result);
                $labAQIvalues=array();

                for($x=0; $x<$labCount;$x++){
                    
                    $data = array();
                    $deviceData = array();
                    $aqi = DB::table('Aqi_values_per_deviceSensor')
                        ->select('sensorId')
                        ->where('sampled_date_time', '>=', Carbon::now()->subMinutes(1440)->toDateTimeString())
                        ->where('labId', $this->result[$x]->id)
                        ->distinct()
                        ->get();
                        
                    foreach($aqi as $id){
                        $data[] = $id;
                    }
                
                
                    for($k=0; $k<count($data); $k++)
                    {
                        $aqi = DB::table('Aqi_values_per_deviceSensor')
                            ->select(DB::raw('DATE_FORMAT(sampled_date_time, "%Y-%m-%d %H:00:00") as hour'), DB::raw('AVG(AqiValue) as average'))
                            ->where('sampled_date_time', '>=' ,Carbon::now()->subMinutes(1440)->toDateTimeString())
                            ->where('sensorId',$data[$k]->sensorId)
                            ->groupBy('hour')
                            ->where('AqiValue','!=','0')
                            ->get();
                        
                        $deviceData[] = $aqi->avg('average');
                    }
                        
                    if(!$deviceData || max($deviceData) == 0){
                        $this->result[$x]->aqiIndex = "NA";
                    }else{
                        $this->result[$x]->aqiIndex = number_format(max($deviceData),2);
                    }
                }*/
                
                
                
                /*$labData = array();
                $labCount = count($this->result);
                $labAQIvalues=array();

                for($x=0; $x<$labCount;$x++){
                    
                    $data = array();
                    $deviceData = array();
                    $aqi = DB::table('Aqi_values_per_deviceSensor')
                        ->select('Aqi_values_per_deviceSensor.sensorId','s.sensorNameUnit')
                        ->distinct()
                        ->join('sensors as s','s.id','=','Aqi_values_per_deviceSensor.sensorId')
                        ->where('Aqi_values_per_deviceSensor.sampled_date_time', '>=' ,Carbon::now()->subMinutes(1440)->toDateTimeString())
                        ->where('labId', $this->result[$x]->id)
                        ->get();
                                    
                    foreach($aqi as $id){
                        $data[] = $id;
                    }
                    
                    for($k=0; $k<count($data); $k++)
                    {
                        $aqi = DB::table('Aqi_values_per_deviceSensor')
                            ->select(DB::raw('DATE_FORMAT(sampled_date_time, "%Y-%m-%d %H:00:00") as hour'), DB::raw('AVG(AqiValue) as average'))
                            ->where('sampled_date_time', '>=' ,Carbon::now()->subMinutes(1440)->toDateTimeString())
                            ->where('sensorId',$data[$k]->sensorId)
                            ->groupBy('hour')
                            ->where('AqiValue','!=','0')
                            ->get();
                        
                        $deviceData[] = $aqi->avg('average');
                    }
                        
                    if(!$deviceData || max($deviceData) == 0){
                        $this->result[$x]->aqiIndex = "NA";
                    }else{
                        $this->result[$x]->aqiIndex = number_format(max($deviceData),2);
                    }
                }*/
                    
                    
                    
                    // modified on 24-04-2023
                    
                    $labData = array();
                    $labCount = count($this->result);
                    $labAQIvalues=array();
    
                    for($x=0; $x<$labCount;$x++){
                        
                        $sensors = array();
                        $deviceData = array();
                        $aqi = DB::table('Aqi_values_per_deviceSensor')
                            ->select('Aqi_values_per_deviceSensor.sensorId','s.sensorNameUnit')
                            ->distinct()
                            ->join('sensors as s','s.id','=','Aqi_values_per_deviceSensor.sensorId')
                            ->where('Aqi_values_per_deviceSensor.sampled_date_time', '>=' ,Carbon::now()->subMinutes(1440)->toDateTimeString())
                            ->where('labId', $this->result[$x]->id)
                            ->get();
                                        
                        foreach($aqi as $id){
                            $sensors[] = $id;
                        }
                        
                    for($i=0; $i<count($sensors); $i++)
                    {
                        $calculate = false;
                        
                        if($sensors[$i]->sensorNameUnit == 'CO' ||$sensors[$i]->sensorNameUnit == 'O3'){
                            $data = $this->getInfo($sensors[$i]->sensorId, 8);
                                
                            if(count($data) > 8){
                                $calculate = true;
                            }
            
                        }else{
                            $data = $this->getInfo($sensors[$i]->sensorId, 16);
                                
                            if(count($data) > 16){
                                $calculate = true;
                            }
                        }
                        
                        if($calculate == true){
                            $deviceData[] = $data->avg('average');
                        }
                    }
                      
                    if(!$deviceData || max($deviceData) == 0){
                        $this->result[$x]->aqiIndex = "NA";
                    }else{
                        $this->result[$x]->aqiIndex = number_format(max($deviceData),2);
                    }
                }
            }

            else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !=""){ 
                
           
               /* $labData = array();
                $floorCount = count($this->result);
                $labList = array();
                
                for($x=0; $x<$floorCount; $x++)
                {
                    $data = array();
                    $deviceData = array();
                    $labList = DB::table('lab_departments')
                            ->where('floor_id','=',$this->result[$x]->id)
                            ->get();
                                        
                    
                    for($j=0; $j<count($labList); $j++){
                        
                        $aqi = DB::table('Aqi_values_per_deviceSensor')
                            ->select('sensorId')
                            ->where('sampled_date_time', '>=', Carbon::now()->subMinutes(1440)->toDateTimeString())
                            ->where('labId', $labList[$j]->id)
                            ->distinct()
                            ->get();
                            
                        foreach($aqi as $id){
                            $data[] = $id;
                        }
                
                
                        for($k=0; $k<count($data); $k++)
                        {
                            $aqi = DB::table('Aqi_values_per_deviceSensor')
                                ->select(DB::raw('DATE_FORMAT(sampled_date_time, "%Y-%m-%d %H:00:00") as hour'), DB::raw('AVG(AqiValue) as average'))
                                ->where('sampled_date_time', '>=' ,Carbon::now()->subMinutes(1440)->toDateTimeString())
                                ->where('sensorId',$data[$k]->sensorId)
                                ->groupBy('hour')
                                ->where('AqiValue','!=','0')
                                ->get();
                            
                            $deviceData[] = $aqi->avg('average');
                        }
                    }
                    
                    if(!$deviceData || max($deviceData) == 0){
                            $this->result[$x]->aqiIndex = "NA";
                    }else{
                        $this->result[$x]->aqiIndex = number_format(max($deviceData),2);
                    }
                }*/
                
                // Modified on 25-04-2023
                
                $labData = array();
                $floorCount = count($this->result);
                $labList = array();
                
                for($x=0; $x<$floorCount; $x++)
                {
                    $sensors = array();
                    $deviceData = array();
                    $labList = DB::table('lab_departments')
                        ->where('floor_id','=',$this->result[$x]->id)
                        ->get();
                        
                    for($j=0; $j<count($labList); $j++){
                        
                        $data = array();
                        $deviceData = array();
                        $aqi = DB::table('Aqi_values_per_deviceSensor')
                            ->select('Aqi_values_per_deviceSensor.sensorId','s.sensorNameUnit')
                            ->distinct()
                            ->join('sensors as s','s.id','=','Aqi_values_per_deviceSensor.sensorId')
                            ->where('Aqi_values_per_deviceSensor.sampled_date_time', '>=' ,Carbon::now()->subMinutes(1440)->toDateTimeString())
                            ->where('labId', $labList[$j]->id)
                            ->get();
                                        
                        foreach($aqi as $id){
                            $sensors[] = $id;
                        }
                        
                        for($i=0; $i<count($sensors); $i++)
                        {
                            $calculate = false;
                            
                            if($sensors[$i]->sensorNameUnit == 'CO' ||$sensors[$i]->sensorNameUnit == 'O3'){
                                $data = $this->getInfo($sensors[$i]->sensorId, 8);
                                    
                                if(count($data) > 8){
                                    $calculate = true;
                                }
                
                            }else{
                                $data = $this->getInfo($sensors[$i]->sensorId, 16);
                                    
                                if(count($data) > 16){
                                    $calculate = true;
                                }
                            }
                            
                            if($calculate == true){
                                $deviceData[] = $data->avg('average');
                            }
                        }  
                    }
                    
                    if(!$deviceData || max($deviceData) == 0){
                            $this->result[$x]->aqiIndex = "NA";
                    }else{
                        $this->result[$x]->aqiIndex = number_format(max($deviceData),2);
                    }
                }
            }
            
            else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""){
                
         
                /*$labData = array();
                $buidingCount = count($this->result);
                $labAQIvalues=array();
                
                for($x=0; $x<$buidingCount; $x++)
                {
                    $data = array();
                    $deviceData = array();
                    $labList = DB::table('lab_departments')
                        ->where('location_id','=',$request->location_id)
                        ->where('branch_id','=',$request->branch_id)
                        ->where('facility_id','=',$request->facility_id)
                        ->where('building_id','=',$this->result[$x]->id)
                        ->get();
                                        
                    
                    for($j=0; $j<count($labList); $j++){
                       
                        $aqi = DB::table('Aqi_values_per_deviceSensor')
                            ->select('sensorId')
                            ->where('sampled_date_time', '>=', Carbon::now()->subMinutes(1440)->toDateTimeString())
                            ->where('labId', $labList[$j]->id)
                            ->distinct()
                            ->get();
                            
                        foreach($aqi as $id){
                            $data[] = $id;
                        }
                
                
                        for($k=0; $k<count($data); $k++)
                        {
                            $aqi = DB::table('Aqi_values_per_deviceSensor')
                                ->select(DB::raw('DATE_FORMAT(sampled_date_time, "%Y-%m-%d %H:00:00") as hour'), DB::raw('AVG(AqiValue) as average'))
                                ->where('sampled_date_time', '>=' ,Carbon::now()->subMinutes(1440)->toDateTimeString())
                                ->where('sensorId',$data[$k]->sensorId)
                                ->groupBy('hour')
                                ->where('AqiValue','!=','0')
                                ->get();
                            
                            $deviceData[] = $aqi->avg('average');
                        }
                     
                    }
                    
                    if(!$deviceData || max($deviceData) == 0){
                            $this->result[$x]->aqiIndex = "NA";
                            
                    }else{
                        $this->result[$x]->aqiIndex = number_format(max($deviceData),2);
                    }
                }*/
                
                
                // Modified on 25-04-2023
                
                $labData = array();
                $buidingCount = count($this->result);
                $labAQIvalues=array();
                
                for($x=0; $x<$buidingCount; $x++)
                {
                    $sensors = array();
                    $deviceData = array();
                    $labList = array();
                    $labList = DB::table('lab_departments')
                        ->where('location_id','=',$request->location_id)
                        ->where('branch_id','=',$request->branch_id)
                        ->where('facility_id','=',$request->facility_id)
                        ->where('building_id','=',$this->result[$x]->id)
                        ->get();
                        
                        
                    for($j=0; $j<count($labList); $j++)
                    {
                        $data = array();
                        $deviceData = array();
                        $aqi = DB::table('Aqi_values_per_deviceSensor')
                            ->select('Aqi_values_per_deviceSensor.sensorId','s.sensorNameUnit')
                            ->distinct()
                            ->join('sensors as s','s.id','=','Aqi_values_per_deviceSensor.sensorId')
                            ->where('Aqi_values_per_deviceSensor.sampled_date_time', '>=' ,Carbon::now()->subMinutes(1440)->toDateTimeString())
                            ->where('labId', $labList[$j]->id)
                            ->get();
                                        
                        foreach($aqi as $id){
                            $sensors[] = $id;
                        }
                            
                        for($i=0; $i<count($sensors); $i++)
                        {
                            $calculate = false;
                            
                            if($sensors[$i]->sensorNameUnit == 'CO' ||$sensors[$i]->sensorNameUnit == 'O3'){
                                $data = $this->getInfo($sensors[$i]->sensorId, 8);
                                    
                                if(count($data) > 8){
                                    $calculate = true;
                                }
                
                            }else{
                                $data = $this->getInfo($sensors[$i]->sensorId, 16);
                                    
                                if(count($data) > 16){
                                    $calculate = true;
                                }
                            }
                            
                            if($calculate == true){
                                $deviceData[] = $data->avg('average');
                            }
                        }  
                    }
                    
                    if(!$deviceData || max($deviceData) == 0){
                        $this->result[$x]->aqiIndex = "NA";
                    }else{
                        $this->result[$x]->aqiIndex = number_format(max($deviceData),2);
                    }
                }
            }
            
            else if($request->location_id != "" && $request->branch_id != ""){ 
                
                /*$labData = array();
                $facilityCount = count($this->result);
                $labAQIvalues=array();
                
                for($x=0; $x<$facilityCount; $x++)
                {
                    $deviceData = array();
                    $devicesList = DB::table('devices')
                            ->selectRaw('id')
                            ->where('location_id','=',$request->location_id)
                            ->where('branch_id','=',$request->branch_id)
                            ->where('facility_id','=',$this->result[$x]->id)
                            ->orderBy('id','DESC')
                            ->get();
                                        
                    $deviceCount = count($devicesList);
                    
                    for($j=0;$j<$deviceCount;$j++)
                    {
                        $avgAqi = DB::table('Aqi_values_per_device')
                            ->selectRaw('AVG(AqiValue) AS avgAqi')
                            ->where('deviceId', '=', $devicesList[$j]->id)
                            ->where('sampled_date_time', '>', Carbon::now()->subHours(24)->toDateTimeString())
                            ->first();
                
                        if ($avgAqi->avgAqi != null) { 
                            $deviceData[] = $avgAqi->avgAqi;
                        }             
                    }
                    if(!$deviceData){
                        $this->result[$x]->aqiIndex = "NA";
                    }else{
                        $this->result[$x]->aqiIndex = number_format(max($deviceData),2);   
                    }
                }*/
                
                
                // Modified on 24-04-2023
                
                $labData = array();
                $facilityCount = count($this->result);
                $labList = array();
                $sensors = array();
                
                for($x=0; $x<$facilityCount; $x++)
                {
                    $sensors = array();
                    $deviceData = array();
                    $labList = array();
                    $calculate = 0;
                    
                    $labList = DB::table('lab_departments')
                        ->where('location_id','=',$request->location_id)
                        ->where('branch_id','=',$request->branch_id)
                        ->where('facility_id','=',$this->result[$x]->id)
                        ->get();
                        
                    for($j=0; $j<count($labList); $j++)
                    {
                        $data = array();
                        $deviceData = array();
                        $aqi = DB::table('Aqi_values_per_deviceSensor')
                            ->select('Aqi_values_per_deviceSensor.sensorId','s.sensorNameUnit')
                            ->distinct()
                            ->join('sensors as s','s.id','=','Aqi_values_per_deviceSensor.sensorId')
                            ->where('Aqi_values_per_deviceSensor.sampled_date_time', '>=' ,Carbon::now()->subMinutes(1440)->toDateTimeString())
                            ->where('labId', $labList[$j]->id)
                            ->get();
                                        
                        foreach($aqi as $id){
                            $sensors[] = $id;
                        }
                            
                        $calculate = $this->getSensorDetails($sensors);
                    }
                    if($calculate == true){
                        $avgAqi = DB::table('Aqi_values_per_device')
                            ->selectRaw('AVG(AqiValue) AS avgAqi')
                            ->where('facilityId','=',$this->result[$x]->id)
                            ->where('AqiValue','!=','0')
                            ->where('sampled_date_time', '>', Carbon::now()->subHours(24)->toDateTimeString())
                            ->first();
                            
                        if ($avgAqi->avgAqi != null) { 
                            $deviceData[] = $avgAqi->avgAqi;
                        } 
                    }
                    if(!$deviceData){
                        $this->result[$x]->aqiIndex = "NA";
                    }else{
                        $this->result[$x]->aqiIndex = number_format(max($deviceData),2);   
                    }  
                }
            }
        
            else if($request->location_id != ""){  
                
               /* $labData = array();
                $branchCount = count($this->result);
                $labAQIvalues=array();

                for($x=0; $x<$branchCount; $x++){
                    
                    $deviceData = array();
                    $devicesList = DB::table('devices')
                            ->selectRaw('id')
                            ->where('location_id','=',$request->location_id)
                            ->where('branch_id','=',$this->result[$x]->id)
                            ->orderBy('id','DESC')
                            ->get();
                            
                    $deviceCount = count($devicesList);
                    
                    for($j=0; $j<$deviceCount; $j++){
                        $avgAqi = DB::table('Aqi_values_per_device')
                            ->selectRaw('AVG(AqiValue) AS avgAqi')
                            ->where('deviceId', '=', $devicesList[$j]->id)
                            ->where('sampled_date_time', '>', Carbon::now()->subHours(24)->toDateTimeString())
                            ->first();
                    
                        if ($avgAqi->avgAqi != null) { 
                            $deviceData[] = $avgAqi->avgAqi;
                        }             
                    }
                    if(!$deviceData){
                        $this->result[$x]->aqiIndex = "NA";
                    }
                    else{
                        $this->result[$x]->aqiIndex = number_format(max($deviceData),2);  
                    }
                }*/
                
                $labData = array();
                $branchCount = count($this->result);
                $labList = array();
                $sensors = array();
                
                for($x=0; $x<$branchCount; $x++)
                {
                    $sensors = array();
                    $deviceData = array();
                    $labList = array();
                    $calculate = 0;
                    
                    $labList = DB::table('lab_departments')
                        ->where('location_id','=',$request->location_id)
                        ->where('branch_id','=',$this->result[$x]->id)
                        ->get();
                        
                    for($j=0; $j<count($labList); $j++)
                    {
                        $data = array();
                        $deviceData = array();
                        $aqi = DB::table('Aqi_values_per_deviceSensor')
                            ->select('Aqi_values_per_deviceSensor.sensorId','s.sensorNameUnit')
                            ->distinct()
                            ->join('sensors as s','s.id','=','Aqi_values_per_deviceSensor.sensorId')
                            ->where('Aqi_values_per_deviceSensor.sampled_date_time', '>=' ,Carbon::now()->subMinutes(1440)->toDateTimeString())
                            ->where('labId', $labList[$j]->id)
                            ->get();
                                        
                        foreach($aqi as $id){
                            $sensors[] = $id;
                        }
                        
                        $calculate = $this->getSensorDetails($sensors);
                    }
                    if($calculate == true){
                        $avgAqi = DB::table('Aqi_values_per_device')
                            ->selectRaw('AVG(AqiValue) AS avgAqi')
                            ->where('branchId','=',$this->result[$x]->id)
                            ->where('AqiValue','!=','0')
                            ->where('sampled_date_time', '>', Carbon::now()->subHours(24)->toDateTimeString())
                            ->first();
                            
                        if ($avgAqi->avgAqi != null) { 
                            $deviceData[] = $avgAqi->avgAqi;
                        } 
                    }
                    if(!$deviceData){
                        $this->result[$x]->aqiIndex = "NA";
                    }else{
                        $this->result[$x]->aqiIndex = number_format(max($deviceData),2);   
                    }  
                }
            }
            
            else {
               /* $labData = array();
                $locCount = count($this->result);
                $labAQIvalues=array();
                
                for($x=0;$x<$locCount;$x++){
                    $deviceData = array();
                    $devicesList = DB::table('devices')
                            ->selectRaw('id')
                            ->where('location_id','=',$this->result[$x]->id)
                            ->orderBy('id','DESC')
                            ->get();
                            
                    $deviceCount = count($devicesList);
                        
                    for($j=0;$j<$deviceCount;$j++){
                        $avgAqi = DB::table('Aqi_values_per_device')
                            ->selectRaw('AVG(AqiValue) AS avgAqi')
                            ->where('deviceId', '=', $devicesList[$j]->id)
                            ->where('sampled_date_time', '>', Carbon::now()->subHours(24)->toDateTimeString())
                            ->first();
                    
                        if ($avgAqi->avgAqi != null) { 
                            $deviceData[] = $avgAqi->avgAqi;
                        }             
                    }
                    if(!$deviceData){
                        $this->result[$x]->aqiIndex = "NA";
                    } 
                    else{
                        $this->result[$x]->aqiIndex = number_format(max($deviceData),2); 
                    }
                }*/
                
                $labData = array();
                $locCount = count($this->result);
                $labList = array();
                $sensors = array();
                
                for($x=0; $x<$locCount; $x++)
                {
                    $sensors = array();
                    $deviceData = array();
                    $labList = array();
                    $calculate = 0;
                    
                    $labList = DB::table('lab_departments')
                        ->where('location_id','=',$this->result[$x]->id)
                        ->get();
                        
                    for($j=0; $j<count($labList); $j++)
                    {
                        $data = array();
                        $deviceData = array();
                        $aqi = DB::table('Aqi_values_per_deviceSensor')
                            ->select('Aqi_values_per_deviceSensor.sensorId','s.sensorNameUnit')
                            ->distinct()
                            ->join('sensors as s','s.id','=','Aqi_values_per_deviceSensor.sensorId')
                            ->where('Aqi_values_per_deviceSensor.sampled_date_time', '>=' ,Carbon::now()->subMinutes(1440)->toDateTimeString())
                            ->where('labId', $labList[$j]->id)
                            ->get();
                                        
                        foreach($aqi as $id){
                            $sensors[] = $id;
                        }
                            
                        // for($i=0; $i<count($sensors); $i++)
                        // {
                        //     if($sensors[$i]->sensorNameUnit == 'CO' ||$sensors[$i]->sensorNameUnit == 'O3'){
                        //         $data = $this->getInfo($sensors[$i]->sensorId, 8);
                                    
                        //         if(count($data) > 8){
                        //             $calculate= true;
                        //             break;
                        //         }
                
                        //     }else{
                        //         $data = $this->getInfo($sensors[$i]->sensorId, 16);
                                    
                        //         if(count($data) > 16){
                        //             $calculate = true;
                        //             break;
                        //         }
                        //     }
                        // } 
                        $calculate = $this->getSensorDetails($sensors);
                    }
                    if($calculate == true){
                        $avgAqi = DB::table('Aqi_values_per_device')
                            ->selectRaw('AVG(AqiValue) AS avgAqi')
                            ->where('locationId','=',$this->result[$x]->id)
                            ->where('AqiValue','!=','0')
                            ->where('sampled_date_time', '>', Carbon::now()->subHours(24)->toDateTimeString())
                            ->first();
                            
                        if ($avgAqi->avgAqi != null) { 
                            $deviceData[] = $avgAqi->avgAqi;
                        } 
                    }
                    if(!$deviceData){
                        $this->result[$x]->aqiIndex = "NA";
                    }else{
                        $this->result[$x]->aqiIndex = number_format(max($deviceData),2);   
                    }  
                }
            }
        }
    }    

    function getData(){
       return $returnedData[] = array(
                "data"=>$this->result,
                "sortedType"=>$this->sort,
                "totalData"=>$this->total,
                "perPageData"=>$this->perPage,
                "page"=>$this->page,
                "lastPage"=>ceil(num:$this->total/ $this->perPage),
                "alertCount"=>$this->alertCount,
                "disconnectedDevices"=>$this->disconnectedDevices,
                "labHooterStatus"=>$this->labHooterStatus,
                "aqiIndex"=>$this->aqiIndex,
                "imageBuildingURL"=>$this->imageBuildingURL,
                "imageFloorURL"=>$this->imageFloorURL,
                "imageLabURL"=>$this->imageLabURL,
       );
    }
    
    
    public function getInfo($sensor, $hour)
    {
        $data = DB::table('Aqi_values_per_deviceSensor')
            ->select(DB::raw("DATE_FORMAT(sampled_date_time, '%Y-%m-%d %H:00:00') AS hour"), DB::raw("AVG(AqiValue) AS average"))
            ->where('sampled_date_time', '>=', Carbon::now()->subHours($hour))
            ->where('sensorId', $sensor)
            ->where('AqiValue','!=','0')
            ->groupBy(DB::raw("DATE_FORMAT(sampled_date_time, '%Y-%m-%d %H:00:00')"))
            ->orderBy('hour', 'ASC')
            ->get();
        
        return $data;
    }
    
    
    public function getSensorDetails($sensors)
    {
        $calculate = false;
        
        for($i=0; $i<count($sensors); $i++)
        {
            if($sensors[$i]->sensorNameUnit == 'CO' ||$sensors[$i]->sensorNameUnit == 'O3'){
                $data = $this->getInfo($sensors[$i]->sensorId, 8);
                    
                if(count($data) > 8){
                    $calculate= true;
                    break;
                }

            }else{
                $data = $this->getInfo($sensors[$i]->sensorId, 16);
                    
                if(count($data) > 16){
                    $calculate = true;
                    break;
                }
            }
        } 
        
        return $calculate;
    }
}