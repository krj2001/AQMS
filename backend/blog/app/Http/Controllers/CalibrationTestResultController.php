<?php

namespace App\Http\Controllers;
use App\Http\Controllers\UTILITY\DataUtilityController;
use App\Models\CalibrationTestResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\UtilityController;
use App\Models\Sensor;
use App\Models\DeviceModeLogs;
use Illuminate\Support\Facades\Mail;
use App\Models\labDepartment;
use DateTime;


class CalibrationTestResultController extends Controller
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


    public function index(Request $request) 
    {   
        try{ 
           
            if($request->sensorTag == ""){
                throw new Exception("Please Select the sensorTag name");
            }
            
            $nextDueDate = DB::table('calibration_test_results')
                            ->select('nextDueDate')
                            ->where('sensorTag','=',$request->sensorTag)
                            ->where('companyCode','=',$this->companyCode)
                            ->orderBy('id', 'DESC')->first();
            $date = "";
            if($nextDueDate){
                $date = $nextDueDate->nextDueDate;
            }
            
            $sensorUnitName =  DB::table('sensors')
                            ->select('sensorNameUnit')
                            ->where('sensorTag','=',$request->sensorTag)
                            ->where('companyCode','=',$this->companyCode)
                            ->orderBy('id', 'DESC')->first();
                            
            $getSensorUnit = DB::table('sensors')
                        ->select('sensorName')
                        ->where('id','=',$request->id)
                        ->orderBy('id', 'DESC')->first();
            
            $partId =  DB::table('sensor_units')
                            ->select('partId')
                            ->where('id','=', $getSensorUnit->sensorName)
                            ->orderBy('id', 'DESC')->first();
           
            $query = DB::table('calibration_test_results')
                    ->select('*')
                    ->where('sensorTag','=',$request->sensorTag)
                    ->where('companyCode','=',$this->companyCode)
                    ->orderBy('id', 'DESC');   
                     
            
            $getData = new DataUtilityController($request,$query);
            
            $response = [
                "lastDueDate"=>$date,
                "sensorNameUnit"=>$sensorUnitName->sensorNameUnit,
                "partId"=>$partId->partId,
                "data"=>$getData->getData()['data']
            ];
            
            $status = 200;

        }catch(Exception $e){
            $response = [
                "error" =>  $e->getMessage()
            ];    
            $status = 404;       
        }        
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
    


    public function storeOLD(Request $request)
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
                ->where('customerId','=',$this->companyCode)
                ->WHERE('sensorTag','=',$request->sensorTag)
                ->first();
                
                
        $userNotificationEnabledData = DB::table('users')
                ->where('companyCode','=',$this->companyCode)
                ->where('empNotification','=','1')
                ->get();
        
        $current_time = date('Y-m-d H:i:s');         
        $calibrationtestresult = new CalibrationTestResult;
        $calibrationtestresult->companyCode = $this->companyCode;
        $calibrationtestresult->sensorTag = $request->sensorTag;
        $calibrationtestresult->name = $request->name;
        $calibrationtestresult->model = $request->model;
        $calibrationtestresult->testResult = $request->testResult;
        $calibrationtestresult->calibrationDate = $current_time;
        $calibrationtestresult->nextDueDate = $request->nextDueDate;   
        $calibrationtestresult->calibratedDate = $request->calibratedDate;
        $calibrationtestresult->lastDueDate = $request->lastDueDate;
        $device_id = $request->device_id;

        if($request->testResult == "Fail"){
            foreach($userNotificationEnabledData as $user){
                $this->sendCalbrationResultMailToUsers($query, $user->email, $calibrationtestresult->testResult);
            }              

            if($calibrationtestresult->save()){
                if($device_id!=""){
                    $getSensorLabquery = DB::table('sensors')
                        ->where('deviceId','=',$request->device_id)
                        ->where('sensorTag','=',$request->sensorTag)
                        ->get();  
            
                    if(count($getSensorLabquery)!=0){
                        $labDepartment = labDepartment::find($getSensorLabquery[0]->lab_id);
                        if($labDepartment){
                            $labDepartment->labHooterStatus = 1;
                            $labDepartment->save();
                            $response = [
                                "message" => " Calibration test Result added successfully",
                                "result"=>"Fail"
                            ];
                            $status = 201;
                        }else{
                            $response = [
                                "message" => "Calibration test Result added successfully not",
                                "result"=>"Fail"
                            ];
                            $status = 201;   
                        }                        
                    }
                }else{
                    $response = [
                        "message" => "Calibration test Result added successfully"
                    ];
                    $status = 201;  
                }
            }
        }else{
            if($calibrationtestresult->save()){
                if($device_id!=""){
                    $getSensorLabquery = DB::table('sensors')
                        ->where('deviceId','=',$request->device_id)
                        ->where('sensorTag','=',$request->sensorTag)
                        ->get();  
            
                    if(count($getSensorLabquery)!=0){
                        $labDepartment = labDepartment::find($getSensorLabquery[0]->lab_id);
                        if($labDepartment){
                            $labDepartment->labHooterStatus = 1;
                            $labDepartment->save();
                            $response = [
                                "message" => " Calibration test Result added successfully",
                            ];
                            $status = 201;
                        }else{
                            $response = [
                                "message" => "Calibration test Result added successfully"
                            ];
                            $status = 201;   
                        }                        
                    }
                }else{
                    $response = [
                        "message" => "Calibration test Result added successfully"
                    ];
                    $status = 201;  
                }
            }
        }   
        
        return response($response,$status);
    }
    
    
    public function store(Request $request)
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
                ->where('customerId','=',$this->companyCode)
                ->WHERE('sensorTag','=',$request->sensorTag)
                ->first();
                
                
        // $userNotificationEnabledData = DB::table('users')
        //         ->where('companyCode','=',$this->companyCode)
        //         ->where('empNotification','=','1')
        //         ->get();
        
        $current_time = date('Y-m-d H:i:s');         
        $calibrationtestresult = new CalibrationTestResult;
        $calibrationtestresult->companyCode = $this->companyCode;
        $calibrationtestresult->deviceId = $request->device_id;
        $calibrationtestresult->sensorTag = $request->sensorTag;
        $calibrationtestresult->name = $request->name;
        $calibrationtestresult->model = $request->model;
        $calibrationtestresult->testResult = $request->testResult;
        $calibrationtestresult->calibrationDate = $current_time;
        $calibrationtestresult->nextDueDate = $request->nextDueDate;   
        $calibrationtestresult->calibratedDate = $request->calibratedDate;
        $calibrationtestresult->lastDueDate = $request->lastDueDate;
        $calibrationtestresult->userEmail = $request->header('Userid');
        
        $device_id = $request->device_id;
        $calibrationSubject = 'Calibration Result Information';
        $calibrationBody = 'Calibration result details as follows:';
        
        // Fetch calibration details from email_template table 28-02-2023
        $fetch = DB::table('email_templates')->where('companyCode',$this->companyCode)->get();
        
        if($fetch){
            $data = DB::table('email_templates')->where('companyCode',$this->companyCode)->first();
            
            $calibrationSubject = $data->calibrartionSubject." ".$request->sensorTag;
            $calibrationBody =  $data->calibrartionBody;
        }
        
        // $this->updateDeviceMode($request);
        
        if($request->testResult == "Fail"){
            
            $userData = array();
            $companyCode= $this->companyCode;
            $deviceId = $request->device_id;
            $sensorTag = $request->sensorTag;
            
            $userData = $this->getUsers($companyCode,$deviceId,$sensorTag);
            
            foreach($userData as $index => $val){
                // $this->sendCalbrationResultMailToUsers($query, $val, $calibrationtestresult->testResult);
                $this->sendCalbrationResultMailToUsers($query, $val, $request->testResult, $calibrationSubject, $calibrationBody);
            }
            // foreach($userNotificationEnabledData as $user){
            //     $this->sendCalbrationResultMailToUsers($query, $user->email, $calibrationtestresult->testResult);
            // }

            if($calibrationtestresult->save()){
                if($device_id!=""){
                    $getSensorLabquery = DB::table('sensors')
                        ->where('deviceId','=',$request->device_id)
                        ->where('sensorTag','=',$request->sensorTag)
                        ->get();  
            
                    if(count($getSensorLabquery)!=0){
                        $labDepartment = labDepartment::find($getSensorLabquery[0]->lab_id);
                        if($labDepartment){
                            $labDepartment->labHooterStatus = 1;
                            $labDepartment->save();
                            $response = [
                                "message" => " Calibration test Result added successfully",
                                "result"=>"Fail"
                            ];
                            $status = 201;
                        }else{
                            $response = [
                                "message" => "Calibration test Result added successfully not",
                                "result"=>"Fail"
                            ];
                            $status = 201;   
                        }                        
                    }
                }else{
                    $response = [
                        "message" => "Calibration test Result added successfully"
                    ];
                    $status = 201;  
                }
            }
        }else{
            if($calibrationtestresult->save()){
                if($device_id!=""){
                    $getSensorLabquery = DB::table('sensors')
                        ->where('deviceId','=',$request->device_id)
                        ->where('sensorTag','=',$request->sensorTag)
                        ->get();  
            
                    if(count($getSensorLabquery)!=0){
                        $labDepartment = labDepartment::find($getSensorLabquery[0]->lab_id);
                        if($labDepartment){
                            $labDepartment->labHooterStatus = 1;
                            $labDepartment->save();
                            $response = [
                                "message" => " Calibration test Result added successfully",
                            ];
                            $status = 201;
                        }else{
                            $response = [
                                "message" => "Calibration test Result added successfully"
                            ];
                            $status = 201;   
                        }                        
                    }
                }else{
                    $response = [
                        "message" => "Calibration test Result added successfully"
                    ];
                    $status = 201;  
                }
            }
        }   
        
        return response($response,$status);
    }
    
    
    public function updateDeviceMode($request)
    {
        $location = DB::table('sensors')->where('sensorTag', $request->sensorTag)->first();
        $mode = DB::table('devices')->where('id', $request->device_id)->first();
        
        $log = new DeviceModeLogs;
        
        $log->companyCode = $location->companyCode;
        $log->locationId = $location->location_id;
        $log->branchId = $location->branch_id;
        $log->facilityId  = $location->facility_id;
        $log->buildingId  = $location->building_id;
        $log->floorId  = $location->floor_id;
        $log->labId = $location->lab_id;
        $log->deviceId  = $location->deviceId  ;
        $log->sensorId  = $location->id;
        $log->previousMode  = $mode->deviceMode;
        $log->updatedMode  = 'calibration';
        $log->userEmail = $request->header('Userid');
        $log->save();
    }
    


    public function sendCalbrationResultMailToUsers($query, $userEmail, $result, $calibrationSubject, $calibrationBody)
    {   
          $date = new DateTime('Asia/Kolkata');      
          $d = $date->format('Y-m-d H:i:s');
          $url = env('APPLICATION_URL');
          $data = [
                    'calibrationBody'=>$calibrationBody,
                    'userid'=>$userEmail,
                    'subject' => 'Calibration Result Information',
                    'customerName'=>$query->customerId,
                    'stateName'=>$query->stateName,
                    'branchName'=>$query->branchName,
                    'facilityName'=>$query->facilityName,
                    'buildingName'=>$query->buildingName,
                    'floorName'=>$query->floorName,
                    'labDepName'=>$query->labDepName,
                    'deviceName'=>$query->deviceName,
                    'sensorNameUnit'=>$query->sensorNameUnit,
                    'sensorTagName'=>$query->sensorTag,
                    'result'=>$result,
                    'dateTime'=>$d,
                    'url' => $url
                ];
        
        Mail::send('calibrationTestMail',$data, function($messages) use ($userEmail, $calibrationSubject){
            $messages->to($userEmail);
            $messages->subject($calibrationSubject);        
        });
    }
    
    public function getUsers($companyCode,$deviceId,$sensorTag){
        
        //device_id
        $getLocationDetails = DB::table('sensors')
                        ->where('deviceId','=',$deviceId)
                        ->where('sensorTag','=',$sensorTag)
                        ->get();  
        
        $locationListParameters = array();
        $listArray = array();
        $userArray = array();
        
        $locationListParameters["loc_id"] = $getLocationDetails[0]->location_id;
        $locationListParameters["bra_id"] = $getLocationDetails[0]->branch_id;
        $locationListParameters["fac_id"] = $getLocationDetails[0]->facility_id;
        $locationListParameters["bui_id"] = $getLocationDetails[0]->building_id;
        $locationListParameters["flr_id"] = $getLocationDetails[0]->floor_id;
        $locationListParameters["lab_id"] = $getLocationDetails[0]->lab_id;
        
         
        $userNotificationEnabledData = DB::table('users')
                                    ->where('empNotification','=','1')
                                    ->where('changePassword','=','0')
                                    ->where('sec_level_auth','=','0')
                                    ->where('companyCode','=',$companyCode)
                                    ->get();
        
        for($i=0;$i<count($userNotificationEnabledData);$i++){
            $email = $userNotificationEnabledData[$i]->email;
            $location_id = $userNotificationEnabledData[$i]->location_id;
            $branch_id = $userNotificationEnabledData[$i]->branch_id;
            $facility_id = $userNotificationEnabledData[$i]->facility_id;
            $building_id = $userNotificationEnabledData[$i]->building_id;
            $floor_id = $userNotificationEnabledData[$i]->floor_id;
            $lab_id = $userNotificationEnabledData[$i]->lab_id;
            
            
            if($location_id == $locationListParameters['loc_id'] &&  $branch_id == $locationListParameters['bra_id'] && $facility_id == $locationListParameters['fac_id'] &&  $building_id == $locationListParameters['bui_id'] && $floor_id == $locationListParameters['flr_id'] && $lab_id == $locationListParameters['lab_id']){
              //lab   
              $listArray[] = $email;
            }else if($location_id == $locationListParameters['loc_id'] &&  $branch_id == $locationListParameters['bra_id'] && $facility_id == $locationListParameters['fac_id'] &&  $building_id == $locationListParameters['bui_id'] && $floor_id == $locationListParameters['flr_id'] && $lab_id == ""){
              //floor 
              $listArray[] = $email;
            }else if($location_id == $locationListParameters['loc_id'] &&  $branch_id == $locationListParameters['bra_id'] && $facility_id == $locationListParameters['fac_id'] &&  $building_id == $locationListParameters['bui_id'] && $floor_id == "" && $lab_id == ""){
                //building
                $listArray[] = $email;
            }else if($location_id == $locationListParameters['loc_id'] &&  $branch_id == $locationListParameters['bra_id'] && $facility_id == $locationListParameters['fac_id'] &&  $building_id == "" && $floor_id == "" && $lab_id == ""){
                //fac
                $listArray[] = $userArray;
            }else if($location_id == $locationListParameters['loc_id'] &&  $branch_id == $locationListParameters['bra_id'] && $facility_id == "" &&  $building_id == "" && $floor_id == "" && $lab_id == ""){
                //branch
                $listArray[] = $email;
            }else if($location_id == $locationListParameters['loc_id'] &&  $branch_id == "" && $facility_id == "" &&  $building_id == "" && $floor_id == "" && $lab_id == ""){
                //loc
                $listArray[] = $email;
            }else if($location_id == "" &&  $branch_id == "" && $facility_id == "" &&  $building_id == "" && $floor_id == "" && $lab_id == ""){
                //all empty
                $listArray[] = $email;
            }
        }
        
        return $listArray;
    }
    
    public function testUsers(){
        $user = array();
        
        $userData = array();
        $companyCode= "A-TEST";
        $deviceId = 3;
        $sensorTag = "pm2.5_gas1";
        
        $userData = $this->getUsers($companyCode,$deviceId,$sensorTag);
        
        foreach($userData as $index => $val){
            $user[$index][] = $val;
        }
        
        return response($user,200);
    }
}

// https://varmatrix.com/Aqms/api/calibrationTestResult/add
// Method:POST
// request
// {
//     "sensorTag":"pm10",
//     "name":"pm",   
//     "model":"25",   
//     "testResult":"pass",   
//     "nextDueDate":"23/08/2022",      
// }