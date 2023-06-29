<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AlertCron;
use App\Models\Device;
use App\Models\Sensor;
use App\Http\Controllers\UtilityController;

class AlertController extends Controller
{
    
    protected $companyCode = ""; 
  
    
    function __construct(Request $request) {
        $getData = new UtilityController($request);
        $this->companyCode = $getData->getCompanyCode(); 
    }
    
    public function getAlertDetailedData(Request $request){
        
        $count = 0;
        
        $notifications = array();
        
        $location_id = $request->location_id;
        $branch_id = $request->branch_id;
        $facility_id = $request->facility_id;
        $building_id = $request->building_id;
        $floor_id = $request->floor_id;
        $lab_id = $request->lab_id;
        $device_id = $request->device_id;
        $sensorId = $request->sensorId;
        
        try{
            $query = Sensor::select('*');
            
            $location_id == "" ? "" : $query->where('location_id', '=', $location_id);
            $branch_id == "" ? "" : $query->where('branch_id', '=', $branch_id);
            $facility_id == "" ? "" : $query->where('facility_id', '=', $facility_id);
            $building_id == "" ? "" : $query->where('building_id', '=', $building_id);
            $floor_id == "" ? "" : $query->where('floor_id', '=', $floor_id);
            $lab_id == "" ? "" : $query->where('lab_id','=',$lab_id);
            $device_id == "" ? "" : $query->where('deviceId','=',$device_id);
            $data = $query->get();
            $length = count($data);      
            $sensorCount = 0;
            
            //SELECT * FROM `sensors` where location_id = 4 and branch_id = 3 and facility_id = 4 and building_id = 2 and floor_id = 2 and lab_id = 3 and deviceId = 3 and id = 34
            
            for($x=0;$x<$length;$x++){
                $alertQuery = DB::table('alert_crons')
                                ->join('sensors', 'sensors.id', '=', 'alert_crons.sensorId')
                                ->select(DB::raw('sensors.location_id,sensors.branch_id,sensors.facility_id,sensors.building_id,sensors.floor_id,sensors.lab_id,alert_crons.*'))
                                ->where('alert_crons.sensorTag','=',$data[$x]->sensorTag)
                                ->where('status','=','0')
                                // ->where('alarmType','=','Latch')
                                ->where('alert_crons.companyCode','=',$this->companyCode)
                                ->orderBy('alert_crons.id','desc')
                                ->first();
                if($alertQuery != null){
                    $notifications[] = $alertQuery;    
                }
            }
            $response = [
                "data"=>$notifications
            ];
            $status = 200;
        }catch(Exception $e){
            $response = [
                "error" => $e->getMessage()
            ];
            $status = 404;
        }
        return response($response,$status);
    }
    
    
    public function getAlertData(Request $request){
        
        $count = 0;
        
        $notifications = array();
        
        $dates = array();
        
        $location_id = $request->location_id;
        $branch_id = $request->branch_id;
        $facility_id = $request->facility_id;
        $building_id = $request->building_id;
        $floor_id = $request->floor_id;
        $lab_id = $request->lab_id;
        $device_id = $request->device_id;
        $sensorId = $request->sensorId;
        
        try{
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
                    $join->on('c.customerId', '=', 'd.companyCode')
                        ->on('l.id', '=', 's.location_id')
                        ->on('b.id', '=', 's.branch_id')
                        ->on('f.id','=','s.facility_id')
                        ->on('bl.id','=','s.building_id')
                        ->on('fl.id','=','s.floor_id')
                        ->on('lb.id','=','s.lab_id')
                        ->on('d.id','=','s.deviceid');
                })
                ->select('c.customerId', 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorNameUnit','s.sensorOutput','s.id','s.sensorTag', 's.sensorStatus')
                ->where('c.customerId','=',$this->companyCode);
                $location_id == "" ? "" : $query->where('l.id', '=', $location_id);
                $branch_id == "" ? "" : $query->where('b.id', '=', $branch_id);
                $facility_id == "" ? "" : $query->where('f.id', '=', $facility_id);
                $building_id == "" ? "" : $query->where('bl.id', '=', $building_id);
                $floor_id == "" ? "" : $query->where('fl.id', '=', $floor_id);
                $lab_id == "" ? "" : $query->where('lb.id','=',$lab_id);
                $device_id == "" ? "" : $query->where('d.id','=',$device_id);
               
                $data = $query->get();
                $length = count($data);      
                $sensorCount = 0;
                //SELECT * FROM `sensors` where location_id = 4 and branch_id = 3 and facility_id = 4 and building_id = 2 and floor_id = 2 and lab_id = 3 and deviceId = 3 and id = 34
            
                for($x=0;$x<$length;$x++){
                    $alertQuery = DB::table('alert_crons')
                                        ->join('sensors', 'sensors.id', '=', 'alert_crons.sensorId')
                                        ->select(DB::raw('sensors.location_id,sensors.branch_id,sensors.facility_id,sensors.building_id,sensors.floor_id,sensors.lab_id,alert_crons.*'))
                                    // ->select('*')
                                    ->where('sensorId','=',$data[$x]->id)
                                    ->where('status','=','0')
                                    // ->where('alarmType','=','Latch')
                                    // ->where('alert_crons.companyCode','=',$this->companyCode)
                                    ->orderBy('alert_crons.id','desc')
                                    ->get();
                    $cnt = count($alertQuery); 
                    if($cnt!=0){
                        
                        $alertQuery[0]->stateName = $data[$x]->stateName;
                        $alertQuery[0]->branchName = $data[$x]->branchName;
                        $alertQuery[0]->facilityName = $data[$x]->facilityName;
                        $alertQuery[0]->buildingName = $data[$x]->buildingName;
                        $alertQuery[0]->floorName = $data[$x]->floorName;
                        $alertQuery[0]->labDepName = $data[$x]->labDepName;
                        $alertQuery[0]->deviceName = $data[$x]->deviceName;
                      
                        $dateModified = date("d-m-Y", strtotime($alertQuery[0]->a_date));
                        $alertQuery[0]->dateTime = $dateModified." ".$alertQuery[0]->a_time;
                        // $alertQuery[0]['a_date'] = $alertQuery[0];
                        $notifications[] = $alertQuery[0];
                        $dates[] = $alertQuery[0]->dateTime; //storing only dates in datesArray
                        
                    }
                }
                
                
                $notificationDataCount = count($notifications);
                $dateCount = count($dates);
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
                
                
                //looping unsorted notification array with sorted dates array
                
                for($x=0;$x<$dateCount;$x++){
                    $date = strtotime($dates[$x]);
                    for($j=0;$j<$notificationDataCount;$j++){
                        $date2 = strtotime($notifications[$j]->dateTime);
                        if($date == $date2){
                             $latestData[] = $notifications[$j];    
                        }
                    }
                }
                
                $response = [
                    "data"=>$latestData
                ];
                $status = 200;
        }catch(Exception $e){
            $response = [
                "error" => $e->getMessage()
            ];
            $status = 404;
        }
        return response($response,$status);
    }
    
    public function getAlertDataNew(Request $request){
        
        $count = 0;
        
        $notifications = array();
        
        $dates = array();
        
        $location_id = $request->location_id;
        $branch_id = $request->branch_id;
        $facility_id = $request->facility_id;
        $building_id = $request->building_id;
        $floor_id = $request->floor_id;
        $lab_id = $request->lab_id;
        $device_id = $request->device_id;
        $sensorId = $request->sensorId;
        
        try{
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
                    $join->on('c.customerId', '=', 'd.companyCode')
                        ->on('l.id', '=', 's.location_id')
                        ->on('b.id', '=', 's.branch_id')
                        ->on('f.id','=','s.facility_id')
                        ->on('bl.id','=','s.building_id')
                        ->on('fl.id','=','s.floor_id')
                        ->on('lb.id','=','s.lab_id')
                        ->on('d.id','=','s.deviceid');
                })
                ->select('c.customerId', 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorNameUnit','s.sensorOutput','s.id','s.sensorTag', 's.sensorStatus')
                ->where('c.customerId','=',$this->companyCode);
                $location_id == "" ? "" : $query->where('l.id', '=', $location_id);
                $branch_id == "" ? "" : $query->where('b.id', '=', $branch_id);
                $facility_id == "" ? "" : $query->where('f.id', '=', $facility_id);
                $building_id == "" ? "" : $query->where('bl.id', '=', $building_id);
                $floor_id == "" ? "" : $query->where('fl.id', '=', $floor_id);
                $lab_id == "" ? "" : $query->where('lb.id','=',$lab_id);
                $device_id == "" ? "" : $query->where('d.id','=',$device_id);
               
                $data = $query->get();
                $length = count($data);      
                $sensorCount = 0;
                //SELECT * FROM `sensors` where location_id = 4 and branch_id = 3 and facility_id = 4 and building_id = 2 and floor_id = 2 and lab_id = 3 and deviceId = 3 and id = 34
            
                for($x=0;$x<$length;$x++){
                    $alertQuery = DB::table('alert_crons')
                                        ->join('sensors', 'sensors.id', '=', 'alert_crons.sensorId')
                                        ->select(DB::raw('sensors.location_id,sensors.branch_id,sensors.facility_id,sensors.building_id,sensors.floor_id,sensors.lab_id,alert_crons.*'))
                                    // ->select('*')
                                    ->where('sensorId','=',$data[$x]->id)
                                    ->where('alertCategory','=','1')
                                    ->where('triggeredAlertFlag','=','1')
                                    // ->where('alarmType','=','Latch')
                                    ->where('alert_crons.companyCode','=',$this->companyCode)
                                    ->orderBy('alert_crons.id','desc')
                                    ->get();
                    $cnt = count($alertQuery); 
                    if($cnt!=0){
                        $alertQuery[0]->stateName = $data[$x]->stateName;
                        $alertQuery[0]->branchName = $data[$x]->branchName;
                        $alertQuery[0]->facilityName = $data[$x]->facilityName;
                        $alertQuery[0]->buildingName = $data[$x]->buildingName;
                        $alertQuery[0]->floorName = $data[$x]->floorName;
                        $alertQuery[0]->labDepName = $data[$x]->labDepName;
                        $alertQuery[0]->deviceName = $data[$x]->deviceName;
                      
                        $dateModified = date("d-m-Y", strtotime($alertQuery[0]->a_date));
                        $alertQuery[0]->dateTime = $dateModified." ".$alertQuery[0]->a_time;
                        // $alertQuery[0]['a_date'] = $alertQuery[0];
                        $notifications[] = $alertQuery[0];
                        $dates[] = $alertQuery[0]->dateTime; //storing only dates in datesArray
                    }
                    
                    $alertQuery = DB::table('alert_crons')
                                        ->join('sensors', 'sensors.id', '=', 'alert_crons.sensorId')
                                        ->select(DB::raw('sensors.location_id,sensors.branch_id,sensors.facility_id,sensors.building_id,sensors.floor_id,sensors.lab_id,alert_crons.*'))
                                    ->where('sensorId','=',$data[$x]->id)
                                    ->where('alertCategory','=','2')
                                    ->where('triggeredAlertFlag','=','1')
                                    ->where('alert_crons.companyCode','=',$this->companyCode)
                                    ->orderBy('alert_crons.id','desc')
                                    ->get();
                    $cnt = count($alertQuery); 
                    if($cnt!=0){
                        $alertQuery[0]->stateName = $data[$x]->stateName;
                        $alertQuery[0]->branchName = $data[$x]->branchName;
                        $alertQuery[0]->facilityName = $data[$x]->facilityName;
                        $alertQuery[0]->buildingName = $data[$x]->buildingName;
                        $alertQuery[0]->floorName = $data[$x]->floorName;
                        $alertQuery[0]->labDepName = $data[$x]->labDepName;
                        $alertQuery[0]->deviceName = $data[$x]->deviceName;
                      
                        $dateModified = date("d-m-Y", strtotime($alertQuery[0]->a_date));
                        $alertQuery[0]->dateTime = $dateModified." ".$alertQuery[0]->a_time;
                        $notifications[] = $alertQuery[0];
                        $dates[] = $alertQuery[0]->dateTime; //storing only dates in datesArray
                    }
                    
                    $alertQuery = DB::table('alert_crons')
                                        ->join('sensors', 'sensors.id', '=', 'alert_crons.sensorId')
                                        ->select(DB::raw('sensors.location_id,sensors.branch_id,sensors.facility_id,sensors.building_id,sensors.floor_id,sensors.lab_id,alert_crons.*'))
                                    // ->select('*')
                                    ->where('sensorId','=',$data[$x]->id)
                                    ->where('status','=','0')
                                    ->where('alertCategory','=','3')
                                    ->where('triggeredAlertFlag','=','1')
                                    ->where('alert_crons.companyCode','=',$this->companyCode)
                                    ->orderBy('alert_crons.id','desc')
                                    ->get();
                    $cnt = count($alertQuery); 
                    if($cnt!=0){
                        $alertQuery[0]->stateName = $data[$x]->stateName;
                        $alertQuery[0]->branchName = $data[$x]->branchName;
                        $alertQuery[0]->facilityName = $data[$x]->facilityName;
                        $alertQuery[0]->buildingName = $data[$x]->buildingName;
                        $alertQuery[0]->floorName = $data[$x]->floorName;
                        $alertQuery[0]->labDepName = $data[$x]->labDepName;
                        $alertQuery[0]->deviceName = $data[$x]->deviceName;
                      
                        $dateModified = date("d-m-Y", strtotime($alertQuery[0]->a_date));
                        $alertQuery[0]->dateTime = $dateModified." ".$alertQuery[0]->a_time;
                        $notifications[] = $alertQuery[0];
                        $dates[] = $alertQuery[0]->dateTime; //storing only dates in datesArray
                    } 
                }
                
                
                $notificationDataCount = count($notifications);
                
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
                
                
                $getUniqueDates = array_unique($dates);
                $uniqueDates = array();
                foreach($getUniqueDates as $key => $value){
                    $uniqueDates[] = $value;
                }
                
                //looping unsorted notification array with sorted dates array
                
                $dateCount = count($uniqueDates);
                
                for($x=0;$x<$dateCount;$x++){
                    $date = strtotime($uniqueDates[$x]);
                    for($j=0;$j<$notificationDataCount;$j++){
                        $date2 = strtotime($notifications[$j]->dateTime);
                        if($date == $date2){
                            $latestData[] = $notifications[$j]; 
                        }
                    }
                }
                
                
                
                // 20-03-2023 -> adding disconnected devices to the array
                
               
                $device = DB::table('alert_crons')
                        ->join('devices as d', 'alert_crons.deviceId','=','d.id')
                        ->join('locations as l', 'l.id','=','d.location_id')
                        ->join('branches as bra', 'bra.id','=','d.branch_id')
                        ->join('facilities as f', 'f.id','=','d.facility_id')
                        ->join('buildings as b', 'b.id','=','d.building_id')
                        ->join('floors as fl', 'fl.id','=','d.floor_id')
                        ->join('lab_departments as dep', 'dep.id','=','d.lab_id')
                        // ->select('alert_crons.*','l.stateName', 'bra.branchName','f.facilityName','b.buildingName','fl.floorName','dep.labDepName','d.deviceName')
                        ->select('alert_crons.*','l.id as location_id','l.stateName','bra.id as branch_id','bra.branchName','f.id as facility_id','f.facilityName','b.id as building_id','b.buildingName','fl.id as floor_id','fl.floorName','dep.id as lab_id','dep.labDepName','d.deviceName',
                            DB::raw('CONCAT(DATE_FORMAT(a_date, "%d-%m-%Y"), " ", a_time) as dateTime'))
                        ->where('status','=','0')
                        ->where('alertCategory','=','4')
                        ->where('triggeredAlertFlag','=','1')
                        ->where('alert_crons.companyCode','=',$this->companyCode)
                        ->orderBy('alert_crons.id','desc')
                        ->get();
                     
                $collection = collect($latestData);
                $mergedData = $collection->merge($device);
                
                
                $response = [
                    "data" => $mergedData
                ];
                $status = 200;
                
        }catch(Exception $e){
            $response = [
                "error" => $e->getMessage()
            ];
            $status = 404;
        }
        return response($response,$status);
    }
    
    
    public function getAlertDatas(Request $request)
    {
        $alertQuery = AlertCron::select('*');
        $alertQuery->where('sensorTag','=','pm2.5_gas1');
        $alertQuery->where('status','=',0);
        $data = $alertQuery->first();
        $response = [
                "data"=>$data
            ];
            $status = 200;
            return response($response,$status);
    }
    
    // 14-03-2023
    public function deviceAlert(Request $request)
    {
        $companyCode = $request->header('companyCode');
        
        $device = DB::table('devices as d')
            ->Join('locations as l', 'l.id', '=', 'd.location_id' )
            ->Join('branches as b', 'b.id', '=', 'd.branch_id' )
            ->Join('facilities as f', 'f.id', '=', 'd.facility_id' )
            ->Join('buildings as bl', 'bl.id', '=', 'd.building_id' )
            ->Join('floors as fl', 'fl.id', '=', 'd.floor_id' )
            ->Join('lab_departments as lb', 'lb.id', '=', 'd.lab_id' )
            ->where('d.companyCode',$companyCode)
            ->where('d.disconnectedStatus','1')
            ->select('d.deviceName','lb.labDepName','fl.floorName','bl.buildingName','f.facilityName','b.branchName','l.stateName')
            ->selectRaw("'Device is disconnected' as message")
            ->orderby('d.id', 'desc')
            ->get();
            
        return $device;
        
    }
}

?>