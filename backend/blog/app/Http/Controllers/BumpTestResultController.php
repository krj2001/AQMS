<?php

namespace App\Http\Controllers;
use App\Http\Controllers\UTILITY\DataUtilityController;
use Illuminate\Support\Facades\DB;
use App\Models\BumpTestResult;
use App\Models\DeviceModeLogs;
use Illuminate\Http\Request;
use App\Http\Controllers\UtilityController;
use App\Models\Sensor;
use App\Models\labDepartment;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;


$path = base_path('app/Http/Controllers/EmailCredential.php');
require_once($path);

class BumpTestResultController extends Controller
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
            if($request->sensorTagName == ""){
                throw new Exception("Please Select the sensorTag name");
            }
            
             $nextDueDate = DB::table('bump_test_results')
                            ->select('nextDueDate')
                            ->where('sensorTagName','=',$request->sensorTagName)
                            ->where('companyCode','=',$this->companyCode)
                            ->orderBy('id', 'DESC')->first();
            $date = "";
            if($nextDueDate){
                $date = $nextDueDate->nextDueDate;
            }
           
            $query = DB::table('bump_test_results')
            ->select('*')
            ->where('sensorTagName','=',$request->sensorTagName)
            ->where('companyCode','=',$this->companyCode)
            ->orderBy('id', 'DESC');
            $getData = new DataUtilityController($request,$query);
            
            $last = DB::table('sensors')->where('sensorTag', $request->sensorTagName)->get();
            $zeroCheckValue = $last[0]->zeroCheckValue;
            $spanCheckValue = $last[0]->spanCheckValue;
            
            $response = [
                "nextDueDate"=>$date,
                "data"=>$getData->getData()['data'],
                "sortedType"=>$getData->getData()['sortedType'],
                "totalData"=>$getData->getData()['totalData'],
                "perPageData"=>$getData->getData()['perPageData'],
                "page"=>$getData->getData()['page'],
                "lastPage"=>$getData->getData()['lastPage'],
                "alertCount"=>$getData->getData()['alertCount'],
                "disconnectedDevices"=>$getData->getData()['disconnectedDevices'],
                "labHooterStatus"=>$getData->getData()['labHooterStatus'],
                "aqiIndex"=>$getData->getData()['aqiIndex'],
                "zeroCheckValue" => $zeroCheckValue,
                "spanCheckValue" => $spanCheckValue,
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
    
    public function storeOld(Request $request)
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
                    $join->on('c.customerId', '=', 's.companyCode')
                        ->on('l.id', '=', 's.location_id')
                        ->on('b.id', '=', 's.branch_id')
                        ->on('f.id','=','s.facility_id')
                        ->on('bl.id','=','s.building_id')
                        ->on('fl.id','=','s.floor_id')
                        ->on('lb.id','=','s.lab_id')
                        ->on('d.id','=','s.deviceid');
                })
                ->select('c.customerId', 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorNameUnit','s.sensorTag')
                ->WHERE('customerId','=',$this->companyCode)
                ->WHERE('sensorTag','=',$request->sensorTagName)
                ->first();
                
                
                $userNotificationEnabledData = DB::table('users')
                        ->where('companyCode','=',$this->companyCode)
                        ->where('empNotification','=','1')
                        ->get();

                
        $current_time = date('Y-m-d H:i:s');         
        $bumptestresult = new BumpTestResult;
        $bumptestresult->companyCode = $this->companyCode;
        $bumptestresult->device_id = $request->device_id;
        $bumptestresult->sensorTagName = $request->sensorTagName;
        $bumptestresult->lastDueDate = $request->lastDueDate;
        $bumptestresult->typeCheck = $request->typeCheck;
        $bumptestresult->percentageConcentrationGas = $request->percentageConcentrationGas;
        $bumptestresult->durationPeriod = $request->durationPeriod;
        $bumptestresult->displayedValue = $request->displayedValue;
        $bumptestresult->percentageDeviation = $request->percentageDeviation;
        $bumptestresult->calibrationDate = $current_time;
       
        $bumptestresult->nextDueDate = $request->nextDueDate;
        
        if($request->lastDueDate == ""){
            $bumptestresult->lastDueDate = "NA";
        }
        
        if($request->percentageConcentrationGas == ""){
            $bumptestresult->percentageConcentrationGas = "0";
        }
        
        if($request->percentageDeviation == ""){
            $bumptestresult->percentageDeviation = "NA";
        }
        
        if($request->displayedValue == ""){
            $bumptestresult->displayedValue = "NA";
        }
        
        if($request->percentageDeviation == ""){
        
           $bumptestresult->result = "Fail";  
        }
        else if($request->percentageDeviation >= 0 && $request->percentageDeviation <= 10){
            $bumptestresult->result = "Pass";

            foreach($userNotificationEnabledData as $user){
                $this->sendBumpTestDueDateMailToUsers($query, $user->email, $bumptestresult->result, $request->typeCheck, $mailSubject, $mailBody);
            }
            
        }
        else{
            
            $bumptestresult->result = "Fail";
            
            foreach($userNotificationEnabledData as $user){
                $this->sendBumpTestDueDateMailToUsers($query, $user->email, $bumptestresult->result, $request->typeCheck, $mailSubject, $mailBody);
            }
        }

        if($bumptestresult->save()){

            $getSensorLabquery = DB::table('sensors')
                    ->where('deviceId','=',$request->device_id)
                    ->where('sensorTag','=',$request->sensorTagName)
                    ->get();  
            
            if(count($getSensorLabquery)!=0){
                $labDepartment = labDepartment::find($getSensorLabquery[0]->lab_id);
                if($labDepartment){
                    $labDepartment->labHooterStatus = 1;
                    $labDepartment->save();
                    $response = [
                        "message" => "Bump test Result added successfully",
                    ];
                    $status = 201;
                }else{

                }
            }
            
             
        }else{
            $response = [
                "message" => "Something went wrong"    
                
            ];
            $status = 201; 
        }

         
        
        return response($response,$status);
    }
    
    
    /* 12/29/2022*/
    
    public function storeOLD2(Request $request)
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
                    $join->on('c.customerId', '=', 's.companyCode')
                        ->on('l.id', '=', 's.location_id')
                        ->on('b.id', '=', 's.branch_id')
                        ->on('f.id','=','s.facility_id')
                        ->on('bl.id','=','s.building_id')
                        ->on('fl.id','=','s.floor_id')
                        ->on('lb.id','=','s.lab_id')
                        ->on('d.id','=','s.deviceid');
                })
                ->select('c.customerId', 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorNameUnit','s.sensorTag')
                ->WHERE('customerId','=',$this->companyCode)
                ->WHERE('sensorTag','=',$request->sensorTagName)
                ->first();
                
                
                $userNotificationEnabledData = DB::table('users')
                        ->where('companyCode','=',$this->companyCode)
                        ->where('empNotification','=','1')
                        ->get();

                
        $current_time = date('Y-m-d H:i:s');         
        $bumptestresult = new BumpTestResult;
        $bumptestresult->companyCode = $this->companyCode;
        $bumptestresult->device_id = $request->device_id;
        $bumptestresult->sensorTagName = $request->sensorTagName;
        $bumptestresult->lastDueDate = $request->lastDueDate;
        $bumptestresult->typeCheck = $request->typeCheck;
        $bumptestresult->percentageConcentrationGas = $request->percentageConcentrationGas;
        $bumptestresult->durationPeriod = $request->durationPeriod;
        $bumptestresult->displayedValue = $request->displayedValue;
        $bumptestresult->percentageDeviation = $request->percentageDeviation;
        $bumptestresult->calibrationDate = $current_time;
       
        $bumptestresult->nextDueDate = $request->nextDueDate;
        
        if($request->lastDueDate == ""){
            $bumptestresult->lastDueDate = "NA";
        }
        
        if($request->percentageConcentrationGas == ""){
            $bumptestresult->percentageConcentrationGas = "0";
        }
        
        if($request->percentageDeviation == ""){
            $bumptestresult->percentageDeviation = "NA";
        }
        
        if($request->displayedValue == ""){
            $bumptestresult->displayedValue = "NA";
        }
        
        if($request->result == "Pass"){
            $bumptestresult->result = $request->result;
            
            foreach($userNotificationEnabledData as $user){
                $this->sendBumpTestDueDateMailToUsers($query, $user->email, $bumptestresult->result, $request->typeCheck, $mailSubject, $mailBody);
            }
            
        }
        else{
            $bumptestresult->result = $request->result;
           
            foreach($userNotificationEnabledData as $user){
                $this->sendBumpTestDueDateMailToUsers($query, $user->email, $bumptestresult->result, $request->typeCheck, $mailSubject, $mailBody);
            }
            
        }

        if($bumptestresult->save()){

            $getSensorLabquery = DB::table('sensors')
                    ->where('deviceId','=',$request->device_id)
                    ->where('sensorTag','=',$request->sensorTagName)
                    ->get();  
                    
            $id = $getSensorLabquery[0]->id;
            
            
            
            if($request->result == "Pass"){
                $sensor = Sensor::find($id); 
                $sensor->sensorStatus= 1;
                $sensor->save();
            }else{
                $sensor = Sensor::find($id); 
                $sensor->sensorStatus= 0;
                $sensor->save();                   
            }
            
            
            
            if(count($getSensorLabquery)!=0){
                $labDepartment = labDepartment::find($getSensorLabquery[0]->lab_id);
                if($labDepartment){
                    $labDepartment->labHooterStatus = 1;
                    $labDepartment->save();
                    $response = [
                        "message" => "Bump test Result added successfully",
                    ];
                    $status = 201;
                }else{

                }
            }
             
        }else{
            $response = [
                "message" => "Something went wrong"    
                
            ];
            $status = 201; 
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
                    $join->on('c.customerId', '=', 's.companyCode')
                        ->on('l.id', '=', 's.location_id')
                        ->on('b.id', '=', 's.branch_id')
                        ->on('f.id','=','s.facility_id')
                        ->on('bl.id','=','s.building_id')
                        ->on('fl.id','=','s.floor_id')
                        ->on('lb.id','=','s.lab_id')
                        ->on('d.id','=','s.deviceid');
                })
                ->select('c.customerId','c.customerName', 'l.stateName', 'b.branchName','f.facilityName','bl.buildingName','fl.floorName','lb.labDepName','d.deviceName','s.sensorNameUnit','s.sensorTag')
                ->WHERE('customerId','=',$this->companyCode)
                ->WHERE('sensorTag','=',$request->sensorTagName)
                ->first();
                
                
                $userNotificationEnabledData = DB::table('users')
                        ->where('companyCode','=',$this->companyCode)
                        ->where('empNotification','=','1')
                        ->get();

                
        $current_time = date('Y-m-d H:i:s');         
        $bumptestresult = new BumpTestResult;
        $bumptestresult->companyCode = $this->companyCode;
        $bumptestresult->device_id = $request->device_id;
        $bumptestresult->sensorTagName = $request->sensorTagName;
        $bumptestresult->lastDueDate = $request->lastDueDate;
        $bumptestresult->typeCheck = $request->typeCheck;
        $bumptestresult->percentageConcentrationGas = $request->percentageConcentrationGas;
        $bumptestresult->durationPeriod = $request->durationPeriod;
        $bumptestresult->displayedValue = $request->displayedValue;
        $bumptestresult->percentageDeviation = $request->percentageDeviation;
        $bumptestresult->calibrationDate = $current_time;
       
        $bumptestresult->nextDueDate = $request->nextDueDate;
        $bumptestresult->userEmail = $request->header('Userid');
        
        if($request->lastDueDate == ""){
            $bumptestresult->lastDueDate = "NA";
        }
        
        if($request->percentageConcentrationGas == ""){
            $bumptestresult->percentageConcentrationGas = "0";
        }
        
        if($request->percentageDeviation == ""){
            $bumptestresult->percentageDeviation = "NA";
        }
        
        if($request->displayedValue == ""){
            $bumptestresult->displayedValue = "NA";
        }
        
        $userData = array();
        $companyCode= $this->companyCode;
        $deviceId = $request->device_id;
        $sensorTag = $request->sensorTagName;
        
        $userData = $this->getUsers($companyCode,$deviceId,$sensorTag);
        
        //mailDetails 28-02-20223
        $mailSubject = "Bump Test Result of";
        $mailBody = "Please find the Bump Test results with location details below";
        
        $mailDetails = DB::table('email_templates')->where('companyCode',$companyCode)->get();
        if(count($mailDetails) > 0){
            $mailDetails = DB::table('email_templates')->where('companyCode',$companyCode)->first();
            $mailSubject = $mailDetails->bumpTestSubject;
            $mailBody = $mailDetails->bumpTestBody;
        }
        
        if($request->result == "Pass"){
            $bumptestresult->result = $request->result;
            //get location sensortag location mapped verified users 1/7/2022
            foreach($userData as $index => $val){
                $this->sendBumpTestDueDateMailToUsers($query, $val, $bumptestresult->result, $bumptestresult->typeCheck, $mailSubject, $mailBody);
            }
            
            /*
            foreach($userNotificationEnabledData as $user){
                $this->sendBumpTestDueDateMailToUsers($query, $user->email, $bumptestresult->result, $bumptestresult->typeCheck);
            }*/
        }
        else{
            $bumptestresult->result = $request->result;
            foreach($userData as $index => $val){
                $this->sendBumpTestDueDateMailToUsers($query, $val, $bumptestresult->result, $bumptestresult->typeCheck, $mailSubject, $mailBody);
            }
            
            /*
            foreach($userNotificationEnabledData as $user){
                $this->sendBumpTestDueDateMailToUsers($query, $user->email, $bumptestresult->result, $bumptestresult->typeCheck);
            }*/
        }

        // $this->updateDeviceMode($request);
        
        if($bumptestresult->save()){

            $getSensorLabquery = DB::table('sensors')
                    ->where('deviceId','=',$request->device_id)
                    ->where('sensorTag','=',$request->sensorTagName)
                    ->get();  
            $id = $getSensorLabquery[0]->id;
            
            if($request->result == "Pass"){
                $sensor = Sensor::find($id); 
                $sensor->sensorStatus= 1;
                $sensor->save();
            }else{
                $sensor = Sensor::find($id); 
                $sensor->sensorStatus= 0;
                $sensor->save();                   
            }
            
            if(count($getSensorLabquery)!=0){
                $labDepartment = labDepartment::find($getSensorLabquery[0]->lab_id);
                if($labDepartment){
                    $labDepartment->labHooterStatus = 1;
                    $labDepartment->save();
                    $response = [
                        "message" => "Bump test Result added successfully",
                    ];
                    $status = 201;
                }else{

                }
            }
             
        }else{
            $response = [
                "message" => "Something went wrong"    
                
            ];
            $status = 201; 
        }
        
        return response($response,$status);
    }

 
    public function updateDeviceMode($request)
    {
        $location = DB::table('sensors')->where('sensorTag', $request->sensorTagName)->first();
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
        $log->updatedMode  = 'bumpTest';
        $log->userEmail = $request->header('Userid');
        $log->save();
    }


    
    public function sendBumpTestDueDateMailToUsers($query, $userEmail, $result, $typeCheck, $mailSubject, $mailBody)
    {   
          $data = [
                    'userid'=>$userEmail,
                    'subject' => 'BumpTest Result Information-2',
                    'body' => $mailBody,
                    'customerName'=>$query->customerName,
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
                    'typeCheck'=>$typeCheck,
                    'url' => env('APPLICATION_URL')
                ];
        
        Mail::send('bumpTestMail',$data, function($messages) use ($userEmail, $mailSubject){
            $messages->to($userEmail);
            $messages->subject($mailSubject);
        });
        
    }
    
    public function sql(){
        
        $sensorTagName = 'pm2.5_gas1';       
        
        //sample join query
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
                ->WHERE('customerId','=','A-TEST')
                ->WHERE('sensorTag','=',$sensorTagName)
                ->first();
                
                
                //sample mail with multiple content in data
                // $email = "abhishek@rdltech.in";
                // $data = [
                //     'userid'=>$email,
                //     'subject' => 'Application employee Credentials',
                //     'body' =>"123456",
                //     "content"=>"hello"
                // ];
        
                // Mail::send('credentialmail',$data, function($messages) use ($email){
                //     $messages->to($email);
                //     $messages->subject('Application login credentials');        
                // });
                
                
                
                $userNotificationEnabledData = DB::table('users')
                        ->where('empNotification','=','1')
                        ->get();
                        
                $userNames = array();
                foreach($userNotificationEnabledData as $user){
                    $this->sendBumpTestDueDateMailToUsers($query, $user->email);
                   
                }
                
                $response = [
                    "data"=>$userNames
                ];
                
        return response($response,200);
        
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
    
    public function sendEmail()
    {
        //   $a = getCredential('A-TEST');
        //   return $a[0];
        
        // $email = 'vaishakkpoojary@gmail.com';
        // $password = 'fxkvnmmnvkhahniu'; 
        
        // // Config::set('mail.mailers.smtp.username', $email);
        // // Config::set('mail.mailers.smtp.password', $password);
        
        // Config::set('mail.mailers.smtp.host', 'smtp.gmail.com');
        // Config::set('mail.mailers.smtp.port', 465);
        // Config::set('mail.mailers.smtp.username', $email);
        // Config::set('mail.mailers.smtp.password', $password);
        // Config::set('mail.mailers.smtp.encryption', 'tls');
        // Config::set('mail.from.address', $email);
        // Config::set('mail.from.name', env('APP_NAME'));

        Mail::send([], [], function ($message) {
            $message->to('vaishakkpoojary@gmail.com');
            $message->subject('Test email');
            // $message->html('<h1>This is a test email body</h1>');
        });

        echo "email sent";
    }
}


//Request input
// {
//     "device_id":3,
//     "sensorTagName":"HydroSen-02",
//     "lastDueDate":"22-08-2022",
//     "typeCheck":"span",
//     "percentageConcentrationGas":"23",
//     "durationPeriod":"5",
//     "displayedValue":"35",
//     "percentageDeviation":"2",
//     "calibrationDate":"22-08-2022",
//     "nextDueDate":"26-08-2022"  
// }