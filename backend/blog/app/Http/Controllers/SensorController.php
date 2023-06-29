<?php

namespace App\Http\Controllers;
use App\Http\Controllers\UtilityController;
use App\Http\Controllers\UTILITY\DataUtilityController;
use App\Models\Sensor;
use App\Models\SensorLimitChangeLog;
use App\Models\SensorConfigLogs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Exception;
use Illuminate\Database\QueryException;
use App\Models\Categories;
use App\Models\Device;
use App\Models\SensorCategory;
use App\Models\SensorUnit;
use App\Http\Controllers\UserLog;
use Auth;

class SensorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    protected $userRole = "";   
    protected $companyCode = "";   
      protected $table = "";

    function __construct(Request $request) {
        $getData = new UtilityController($request);
        $this->companyCode = $getData->getCompanyCode();   
        $this->userRole  = $getData->getUserRole();
    }
    
    public function getSensorTagData(Request $request){
        $query = Sensor::select(
                    'companyCode',
                    'location_id',
                    'branch_id',   
                    'facility_id', 
                    'building_id',
                    'floor_id',
                    'lab_id',
                    
                    'categoryId',//1
                    'deviceCategory',//AQMI
                    
                    'sensorCategoryId',//1
                    'sensorCategoryName',//Temperature
                    
                    'deviceId',//34
                    'deviceName',//AQMI-007
            
                    'sensorName',//2
                    'sensorNameUnit',//h20
                    
                    'sensorOutput',
                    'sensorType',
                    'sensorTag'
                    
                );
                
            $perPage = 20;
            $page = $request->input(key:'page', default:1);
            $total = $query->count();
            $result = $query->offset(value:($page - 1) * $perPage)->limit($perPage)->get();
            $response = [
                    "data" => $result
            ];
            $status = 200;     
            return response($response, $status);
        
    }


    public function index(Request $request)
    {
        try{
            if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !="" && $request->lab_id !="" && $request->deviceId !=""){
                    $query = Sensor::select(
                            // 'companyCode',
                            // 'location_id',
                            // 'branch_id',   
                            // 'facility_id', 
                            // 'building_id',
                            // 'floor_id',
                            // 'lab_id',
                                                
                          
                            
                            'categoryId',//1
                            'deviceCategory',//AQMI
                            
                            'sensorCategoryId',//1
                            'sensorCategoryName',//Temperature
                            
                            'deviceId',//34
                            'deviceName',//AQMI-007
                    
                            'sensorName',//2
                            'sensorNameUnit',//h20
                            
                            'sensorOutput',
                            'sensorType',
                            'sensorTag',
                            
                            // 'registerId',
                            // 'registerType',
                            // 'slaveId',
                            
                            // 'subnetMask',
                            // 'units',
                            // 'ipAddress',
                            // 'length',
                    
                            // 'maxRatedReading',
                            // 'maxRatedReadingChecked',
                            // 'maxRatedReadingScale',
                            // 'minRatedReading',
                            // 'minRatedReadingChecked',
                            // 'minRatedReadingScale',
                            
                            // 'pollingIntervalType',
                            
                            // 'criticalMinValue',
                            // 'criticalMaxValue',
                            // 'criticalAlertType',
                            // 'criticalLowAlert',
                            // 'criticalHighAlert',
                            
                            
                            // 'warningMinValue',
                            // 'warningMaxValue',
                            // 'warningAlertType',
                            // 'warningLowAlert',
                            // 'warningHighAlert',
                            
                            
                            // 'outofrangeMinValue',
                            // 'outofrangeMaxValue',
                            // 'outofrangeAlertType',
                            // 'outofrangeLowAlert',
                            // 'outofrangeHighAlert',
        
                        
                        
                        
                        
                        
                        
                        );
                    $query->where('companyCode','=',$this->companyCode);
                    $query->where('location_id','=',$request->location_id);
                    $query->where('branch_id','=',$request->branch_id);
                    $query->where('facility_id','=',$request->facility_id);
                    $query->where('building_id','=',$request->building_id);
                    $query->where('floor_id','=',$request->floor_id);
                    $query->where('lab_id','=',$request->lab_id);
                    $query->where('deviceId','=',$request->deviceId);
                    $query->where('sensorOutput','=','Digital');
                    
                    $perPage = 20;
                    $page = $request->input(key:'page', default:1);
                    $total = $query->count();
                    $result = $query->offset(value:($page - 1) * $perPage)->limit($perPage)->get();
                    
                                      
                    $query1 = Sensor::select(
                             // 'companyCode',
                            // 'location_id',
                            // 'branch_id',   
                            // 'facility_id', 
                            // 'building_id',
                            // 'floor_id',
                            // 'lab_id',
                                                
                          
                            
                            'categoryId',//1
                            'deviceCategory',//AQMI
                            
                            'sensorCategoryId',//1
                            'sensorCategoryName',//Temperature
                            
                            'deviceId',//34
                            'deviceName',//AQMI-007
                    
                            'sensorName',//2
                            'sensorNameUnit',//h20
                            
                            'sensorOutput',
                            'sensorType',
                            'sensorTag',
                            
                            // 'registerId',
                            // 'registerType',
                            // 'slaveId',
                            
                            // 'subnetMask',
                            // 'units',
                            // 'ipAddress',
                            // 'length',
                    
                            // 'maxRatedReading',
                            // 'maxRatedReadingChecked',
                            // 'maxRatedReadingScale',
                            // 'minRatedReading',
                            // 'minRatedReadingChecked',
                            // 'minRatedReadingScale',
                            
                            // 'pollingIntervalType',
                            
                            // 'criticalMinValue',
                            // 'criticalMaxValue',
                            // 'criticalAlertType',
                            // 'criticalLowAlert',
                            // 'criticalHighAlert',
                            
                            
                            // 'warningMinValue',
                            // 'warningMaxValue',
                            // 'warningAlertType',
                            // 'warningLowAlert',
                            // 'warningHighAlert',
                            
                            
                            // 'outofrangeMinValue',
                            // 'outofrangeMaxValue',
                            // 'outofrangeAlertType',
                            // 'outofrangeLowAlert',
                            // 'outofrangeHighAlert',
                        
                        
                        
                        
                        );
                    $query1->where('companyCode','=',$this->companyCode);
                    $query1->where('location_id','=',$request->location_id);
                    $query1->where('branch_id','=',$request->branch_id);
                    $query1->where('facility_id','=',$request->facility_id);
                    $query1->where('building_id','=',$request->building_id);
                    $query1->where('floor_id','=',$request->floor_id);
                    $query1->where('lab_id','=',$request->lab_id);
                    $query1->where('deviceId','=',$request->deviceId);
                    $query1->where('sensorOutput','=','Analog');
                    $perPage = 20;
                    $page = $request->input(key:'page', default:1);
                    $total1 = $query1->count();
                    $result1 = $query1->offset(value:($page - 1) * $perPage)->limit($perPage)->get();
                    
                    
                    $query2 = Sensor::select(
                             // 'companyCode',
                            // 'location_id',
                            // 'branch_id',   
                            // 'facility_id', 
                            // 'building_id',
                            // 'floor_id',
                            // 'lab_id',
                                                
                          
                            
                            'categoryId',//1
                            'deviceCategory',//AQMI
                            
                            'sensorCategoryId',//1
                            'sensorCategoryName',//Temperature
                            
                            'deviceId',//34
                            'deviceName',//AQMI-007
                    
                            'sensorName',//2
                            'sensorNameUnit',//h20
                            
                            'sensorOutput',
                            'sensorType',
                            'sensorTag',
                            
                            // 'registerId',
                            // 'registerType',
                            // 'slaveId',
                            
                            // 'subnetMask',
                            // 'units',
                            // 'ipAddress',
                            // 'length',
                    
                            // 'maxRatedReading',
                            // 'maxRatedReadingChecked',
                            // 'maxRatedReadingScale',
                            // 'minRatedReading',
                            // 'minRatedReadingChecked',
                            // 'minRatedReadingScale',
                            
                            // 'pollingIntervalType',
                            
                            // 'criticalMinValue',
                            // 'criticalMaxValue',
                            // 'criticalAlertType',
                            // 'criticalLowAlert',
                            // 'criticalHighAlert',
                            
                            
                            // 'warningMinValue',
                            // 'warningMaxValue',
                            // 'warningAlertType',
                            // 'warningLowAlert',
                            // 'warningHighAlert',
                            
                            
                            // 'outofrangeMinValue',
                            // 'outofrangeMaxValue',
                            // 'outofrangeAlertType',
                            // 'outofrangeLowAlert',
                            // 'outofrangeHighAlert',
                        );
                    $query2->where('companyCode','=',$this->companyCode);
                    $query2->where('location_id','=',$request->location_id);
                    $query2->where('branch_id','=',$request->branch_id);
                    $query2->where('facility_id','=',$request->facility_id);
                    $query2->where('building_id','=',$request->building_id);
                    $query2->where('floor_id','=',$request->floor_id);
                    $query2->where('lab_id','=',$request->lab_id);
                    $query2->where('deviceId','=',$request->deviceId);
                    $query2->where('sensorOutput','=','Modbus');
                    $perPage = 20;
                    $page = $request->input(key:'page', default:1);
                    $total2 = $query2->count();
                    $result2 = $query2->offset(value:($page - 1) * $perPage)->limit($perPage)->get();
                    
                    
                    $response =  [
                        "Digital" => [
                            'data' => $result,
                            'totalData'=>$total,
                            'page'=>$page,
                            'lastPage'=>ceil(num:$total/ $perPage)
                            ],
                        "Analog" => [
                            'data' => $result1,
                            'totalData'=>$total,
                            'page'=>$page,
                            'lastPage'=>ceil(num:$total/ $perPage)
                            ],
                        "Modbas" => [
                            'data' => $result2,
                            'totalData'=>$total,
                            'page'=>$page,
                            'lastPage'=>ceil(num:$total/ $perPage)
                            ],
                            
                        
                    ];
                    $status = 200;
                    return response($response,$status);
            }
        }catch(Exception $e){
            $response = [
                "message" =>  $e->getMessage()
            ];
            $status = 404;
        }catch (QueryException $e) {
                $response = [
                    "message" => $e->errorInfo
                ];
                $status = 406; 
            } 
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try{
            
            $getSensorCount = DB::table('sensors')->where([['deviceId',$request->deviceId],['sensorOutput','=',$request->sensorOutput]])->orderBy('id','ASC')->get();
            
            $sensorCount = count($getSensorCount);
            
            // if($sensorCount > 4){
            //     throw new Exception("Only 4 {$request->sensorOutput} sensor can be  deployed for device ");
            
            if($request->sensorOutput == 'Analog' && $sensorCount>=4){
                throw new Exception("Only 4 {$request->sensorOutput} sensor can be  deployed for device ");
    
            }else if($request->sensorOutput == 'Digital' && $sensorCount>=2){
                throw new Exception("Only 2 {$request->sensorOutput} sensor can be  deployed for device ");
        
            }else if($request->sensorOutput == 'Modbus' && $sensorCount>=6){
                throw new Exception("Only 6 {$request->sensorOutput} sensor can be  deployed for device ");      
                
            }else{
           
                $sensorDataFound = DB::table('sensors')
                  ->where('companyCode','=',$this->companyCode)    
                    ->where('location_id','=',$request->location_id)
                    ->where('branch_id','=',$request->branch_id)
                    ->where('facility_id','=',$request->facility_id) 
                    ->where('building_id','=',$request->building_id) 
                    ->where('floor_id','=',$request->floor_id)
                    ->where('lab_id','=',$request->lab_id)
                    ->where('deviceId','=',$request->deviceId)
                    ->where('sensorTag','=',$request->sensorTag)
                    ->first();                      
               
                if($sensorDataFound){
                    throw new Exception("{$request->sensorTag} sensor is already deployed for device ");
                }
            
                $sensor = new Sensor;
                $sensor->companyCode=$this->companyCode;
                $sensor->location_id=$request->location_id;
                $sensor->branch_id=$request->branch_id;
                $sensor->facility_id=$request->facility_id;
                $sensor->building_id=$request->building_id;
                $sensor->floor_id=$request->floor_id;
                $sensor->lab_id=$request->lab_id;
                
                $sensor->categoryId=$request->categoryId;            
                $categories = Categories::where('id',$request->categoryId)->first();        
                $sensor->deviceCategory= $categories->categoryName;
    
                $sensor->deviceId=$request->deviceId;  
                $devices = Device::where('id',$request->deviceId)->first();
                $sensor->deviceName = $devices->deviceName;
                
                $sensor->sensorCategoryId=$request->sensorCategoryId;            
                $sensorCategories = SensorCategory::where('id',$request->sensorCategoryId)->first();        
                $sensor->sensorCategoryName= $sensorCategories->sensorName;
               
                
                $sensor->sensorName=$request->sensorName;
                $sensorUnit = SensorUnit::where('id',$request->sensorName)->first();        
                $sensor->sensorNameUnit= $sensorUnit->sensorName;
                
                $sensor->conversionType=$request->conversionType;
                
                $sensor->sensorOutput= $request->sensorOutput;
                $sensor->sensorType  = $request->sensorType;
                $sensor->sensorTag   = $request->sensorTag;
                $sensor->registerId  = $request->registerId;
                $sensor->registerType= $request->registerType;
                $sensor->slaveId     = $request->slaveId;
                $sensor->subnetMask  = $request->subnetMask;
                $sensor->units       = $request->units;
                $sensor->ipAddress   = $request->ipAddress;
                $sensor->length      = $request->length;
                $sensor->maxRatedReading        = $request->maxRatedReading;
                $sensor->maxRatedReadingChecked = $request->maxRatedReadingChecked;
                $sensor->maxRatedReadingScale   = $request->maxRatedReadingScale;
                $sensor->minRatedReading        = $request->minRatedReading;
                $sensor->minRatedReadingChecked = $request->minRatedReadingChecked;
                $sensor->minRatedReadingScale   = $request->minRatedReadingScale;
                
                $sensor->pollingIntervalType = $request->pollingIntervalType;
                
                $sensor->criticalMinValue = $request->criticalMinValue;
                $sensor->criticalMaxValue = $request->criticalMaxValue;
                $sensor->criticalAlertType = $request->criticalAlertType;
                $sensor->criticalLowAlert = $request->criticalLowAlert;
                $sensor->criticalHighAlert = $request->criticalHighAlert;
                
                $sensor->warningMinValue = $request->warningMinValue;
                $sensor->warningMaxValue = $request->warningMaxValue;
                $sensor->warningAlertType = $request->warningAlertType;
                $sensor->warningLowAlert = $request->warningLowAlert;
                $sensor->warningHighAlert = $request->warningHighAlert;
                
                $sensor->outofrangeMinValue = $request->outofrangeMinValue;
                $sensor->outofrangeMaxValue = $request->outofrangeMaxValue;
                $sensor->outofrangeAlertType = $request->outofrangeAlertType;
                $sensor->outofrangeLowAlert = $request->outofrangeLowAlert;
                $sensor->outofrangeHighAlert = $request->outofrangeHighAlert;
                
                $sensor->digitalAlertType = $request->digitalAlertType;
                $sensor->digitalLowAlert = $request->digitalLowAlert;
                $sensor->digitalHighAlert = $request->digitalHighAlert;
                
                $sensor->isStel = $request->isStel;                
                $sensor->stelDuration = $request->stelDuration;
                $sensor->stelStartTime = $request->stelStartTime;
                $sensor->stelType = $request->stelType;
                $sensor->stelLimit = $request->stelLimit;
                $sensor->stelAlert = $request->stelAlert;
                
                $sensor->twaDuration = $request->twaDuration;
                $sensor->twaStartTime = $request->twaStartTime;
                $sensor->twaType = $request->twaType;
                $sensor->twaLimit = $request->twaLimit;
                $sensor->twaAlert = $request->twaAlert;
    
                $sensor->alarm = $request->alarm;
                $sensor->unLatchDuration = $request->unLatchDuration;  
                
                $sensor->isAQI = $request->isAQI;         
                $sensor->parmGoodMinScale = $request->parmGoodMinScale;
                $sensor->parmGoodMaxScale = $request->parmGoodMaxScale;
                $sensor->parmSatisfactoryMinScale = $request->parmSatisfactoryMinScale;
                $sensor->parmSatisfactoryMaxScale = $request->parmSatisfactoryMaxScale;
                $sensor->parmModerateMinScale = $request->parmModerateMinScale;
                $sensor->parmModerateMaxScale = $request->parmModerateMaxScale;
                $sensor->parmPoorMinScale = $request->parmPoorMinScale;
                $sensor->parmPoorMaxScale = $request->parmPoorMaxScale;
                $sensor->parmVeryPoorMinScale = $request->parmVeryPoorMinScale;
                $sensor->parmVeryPoorMaxScale = $request->parmVeryPoorMaxScale;
                $sensor->parmSevereMinScale = $request->parmSevereMinScale;
                $sensor->parmSevereMaxScale = $request->parmSevereMaxScale;
                 
                $sensor->criticalRefMinValue = $request->criticalMinValue;
                $sensor->criticalRefMaxValue = $request->criticalMaxValue;
                $sensor->warningRefMinValue = $request->warningMinValue;
                $sensor->warningRefMaxValue = $request->warningMaxValue;
                $sensor->outofrangeRefMinValue = $request->outofrangeMinValue;
                $sensor->outofrangeRefMaxValue = $request->outofrangeMaxValue;
                
                $sensor->relayOutput = $request->relayOutput;
                $sensor->sensorFault = $request->sensorFault;
                
                $sensor->audioDecibelLevel = 68;
                
                // $sensor->typeCheck = $request->typeCheck;               // 9-5-2023
                $sensor->zeroCheckValue = $request->zeroCheckValue;
                $sensor->spanCheckValue = $request->spanCheckValue;

            
                $sensor->refSensorTag = $request->sensorTag;            //refference values 02-06-2023
                $sensor->refAlarm = $request->alarm;
                $sensor->refPollingIntervalType = $request->pollingIntervalType;
                
                $sensor->criticalRefAlertType = $request->criticalAlertType;
                $sensor->criticalRefLowMessage = $request->criticalLowAlert;
                $sensor->criticalRefHighMessage = $request->criticalHighAlert;
                
                $sensor->warningRefAlertType = $request->warningAlertType;
                $sensor->warningRefLowMessage = $request->warningLowAlert;
                $sensor->warningRefHighMessage = $request->warningHighAlert;
                
                $sensor->outofrangeRefAlertType = $request->outofrangeAlertType;
                $sensor->outofrangeRefLowMessage = $request->outofrangeLowAlert;
                $sensor->outofrangeRefHighMessage = $request->outofrangeHighAlert;
                
                $sensor->save();
                $response = [
                    "message" => "Data Added successfully"
                ];
                $status = 200;                
            }
        } catch (QueryException $e) {
            $response = [
                "message" => $e->errorInfo
            ];
            $status = 404; 
        }  catch (Exception $e){
            $response = [
                "message"=> $e->getMessage()
            ];
            $status = 404;
        }    
       
       return response($response,$status);   
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Sensor  $sensor
     * @return \Illuminate\Http\Response
     */
    public function show(Sensor $sensor)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Sensor  $sensor
     * @return \Illuminate\Http\Response
     */
    public function edit(Sensor $sensor)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Sensor  $sensor
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        
        $criticalMinValueChange="";
        $criticalMaxValueChange="";
        $warningMinValueChange="";
        $warningMaxValueChange="";
        $outofrangeMinValueChange="";
        $outofrangeMaxValueChange="";
            
            
        try{         
            $sensorDataFound = Sensor::find($id);    
            if(!$sensorDataFound){
                throw new Exception("Data not founds");
            }  
            
            $sensorDataFound = DB::table('sensors')
              ->where('companyCode','=',$this->companyCode)    
                ->where('location_id','=',$request->location_id)
                ->where('branch_id','=',$request->branch_id)
                ->where('facility_id','=',$request->facility_id) 
                ->where('building_id','=',$request->building_id) 
                ->where('floor_id','=',$request->floor_id)
                ->where('lab_id','=',$request->lab_id)
                ->where('deviceId','=',$request->deviceId)
                ->where('sensorTag','=',$request->sensorTag)
                ->where('id','<>',$id)
                ->first();                      
           
            if($sensorDataFound){
                throw new Exception("{$request->sensorTag} sensor is already deployed for device ");
            }  
            
            $sensor = Sensor::find($id);   
                            
            $userRole = $this->userRole;
            if($userRole === 'systemSpecialist')
            {
                $sensor = Sensor::find($id);   
                $sensor->criticalRefMinValue = $request->criticalMinValue;
                $sensor->criticalRefMaxValue = $request->criticalMaxValue;
                $sensor->warningRefMinValue = $request->warningMinValue;
                $sensor->warningRefMaxValue = $request->warningMaxValue;
                $sensor->outofrangeRefMinValue = $request->outofrangeMinValue;
                $sensor->outofrangeRefMaxValue = $request->outofrangeMaxValue;
            }
            
            
            if($sensor){  

                $sensor->companyCode=$this->companyCode;
                
                //critical min
                if($sensor->criticalMinValue!=$request->criticalMinValue){
                    $criticalMinValueChange = "OLD - ".$sensor->criticalMinValue." AND NEW - ".$request->criticalMinValue;
                }
               
                //critical max
                if($sensor->criticalMaxValue!=$request->criticalMaxValue){
                    $criticalMaxValueChange = "OLD - ".$sensor->criticalMaxValue." AND NEW - ".$request->criticalMaxValue;
                }
              
                //warning min
                if($sensor->warningMinValue!=$request->warningMinValue){
                    $warningMinValueChange = "OLD - ".$sensor->warningMinValue." AND NEW - ".$request->warningMinValue;
                }
                
                //warning max
                if($sensor->warningMaxValue!=$request->warningMaxValue){
                    $warningMaxValueChange = "OLD - ".$sensor->warningMaxValue." AND NEW - ".$request->warningMaxValue;
                }
                
                //outofrange min
                if($sensor->outofrangeMinValue!=$request->outofrangeMinValue){
                    $outofrangeMinValueChange = "OLD - ".$sensor->outofrangeMinValue." AND NEW - ".$request->outofrangeMinValue;
                }
               
                
                //outofrange max
                if($sensor->outofrangeMaxValue!=$request->outofrangeMaxValue){
                    $outofrangeMaxValueChange = "OLD - ".$sensor->outofrangeMaxValue." AND NEW - ".$request->outofrangeMaxValue;
                }
                
                
                //commented for time being testing limit changes
                
                $sensor->location_id=$request->location_id;
                $sensor->branch_id=$request->branch_id;
                $sensor->facility_id=$request->facility_id;
                $sensor->building_id=$request->building_id;
                $sensor->floor_id=$request->floor_id;
                $sensor->lab_id=$request->lab_id;
                
                $sensor->categoryId=$request->categoryId;            
                $categories = Categories::where('id',$request->categoryId)->first();        
                $sensor->deviceCategory= $categories->categoryName;
    
                $sensor->deviceId=$request->deviceId;  
                $devices = Device::where('id',$request->deviceId)->first();
                $sensor->deviceName = $devices->deviceName;
                
                $sensor->sensorCategoryId=$request->sensorCategoryId;            
                $sensorCategories = SensorCategory::where('id',$request->sensorCategoryId)->first();        
                $sensor->sensorCategoryName= $sensorCategories->sensorName;
               
                
                $sensor->sensorName=$request->sensorName;
                $sensorUnit = SensorUnit::where('id',$request->sensorName)->first();        
                $sensor->sensorNameUnit= $sensorUnit->sensorName;
                
                $sensor->conversionType=$request->conversionType;
                
                $sensor->sensorOutput= $request->sensorOutput;
                $sensor->sensorType  = $request->sensorType;
                $sensor->sensorTag   = $request->sensorTag;
                $sensor->registerId  = $request->registerId;
                $sensor->registerType= $request->registerType;
                $sensor->slaveId     = $request->slaveId;
                $sensor->subnetMask  = $request->subnetMask;
                $sensor->units       = $request->units;
                $sensor->ipAddress   = $request->ipAddress;
                $sensor->length      = $request->length;
                $sensor->maxRatedReading        = $request->maxRatedReading;
                $sensor->maxRatedReadingChecked = $request->maxRatedReadingChecked;
                $sensor->maxRatedReadingScale   = $request->maxRatedReadingScale;
                $sensor->minRatedReading        = $request->minRatedReading;
                $sensor->minRatedReadingChecked = $request->minRatedReadingChecked;
                $sensor->minRatedReadingScale   = $request->minRatedReadingScale;
                
                $sensor->pollingIntervalType = $request->pollingIntervalType;
                
                $sensor->criticalMinValue = $request->criticalMinValue;
                $sensor->criticalMaxValue = $request->criticalMaxValue;
                
                
                $sensor->criticalAlertType = $request->criticalAlertType;
                $sensor->criticalLowAlert = $request->criticalLowAlert;
                $sensor->criticalHighAlert = $request->criticalHighAlert;
                
                $sensor->warningMinValue = $request->warningMinValue;
                $sensor->warningMaxValue = $request->warningMaxValue;
                
                
                $sensor->warningAlertType = $request->warningAlertType;
                $sensor->warningLowAlert = $request->warningLowAlert;
                $sensor->warningHighAlert = $request->warningHighAlert;
                
                $sensor->outofrangeMinValue = $request->outofrangeMinValue;
                $sensor->outofrangeMaxValue = $request->outofrangeMaxValue;
                
                
                $sensor->outofrangeAlertType = $request->outofrangeAlertType;
                $sensor->outofrangeLowAlert = $request->outofrangeLowAlert;
                $sensor->outofrangeHighAlert = $request->outofrangeHighAlert;
                
                $sensor->digitalAlertType = $request->digitalAlertType;
                $sensor->digitalLowAlert = $request->digitalLowAlert;
                $sensor->digitalHighAlert = $request->digitalHighAlert;
                
                $sensor->isStel = $request->isStel;                
                $sensor->stelDuration = $request->stelDuration;
                $sensor->stelStartTime = $request->stelStartTime;
                $sensor->stelType = $request->stelType;
                $sensor->stelLimit = $request->stelLimit;
                $sensor->stelAlert = $request->stelAlert;
                
                $sensor->twaDuration = $request->twaDuration;
                $sensor->twaStartTime = $request->twaStartTime;
                $sensor->twaType = $request->twaType;
                $sensor->twaLimit = $request->twaLimit;
                $sensor->twaAlert = $request->twaAlert;
    
                $sensor->alarm = $request->alarm;
                $sensor->unLatchDuration = $request->unLatchDuration;  
                
                $sensorUnit->isAQI = $request->isAQI;         
                $sensorUnit->parmGoodMinScale = $request->parmGoodMinScale;
                $sensorUnit->parmGoodMaxScale = $request->parmGoodMaxScale;
                $sensorUnit->parmSatisfactoryMinScale = $request->parmSatisfactoryMinScale;
                $sensorUnit->parmSatisfactoryMaxScale = $request->parmSatisfactoryMaxScale;
                $sensorUnit->parmModerateMinScale = $request->parmModerateMinScale;
                $sensorUnit->parmModerateMaxScale = $request->parmModerateMaxScale;
                $sensorUnit->parmPoorMinScale = $request->parmPoorMinScale;
                $sensorUnit->parmPoorMaxScale = $request->parmPoorMaxScale;
                $sensorUnit->parmVeryPoorMinScale = $request->parmVeryPoorMinScale;
                $sensorUnit->parmVeryPoorMaxScale = $request->parmVeryPoorMaxScale;
                $sensorUnit->parmSevereMinScale = $request->parmSevereMinScale;
                $sensorUnit->parmSevereMaxScale = $request->parmSevereMaxScale;
                
                $sensor->relayOutput = $request->relayOutput;
                $sensor->sensorFault = $request->sensorFault;  
               
                // Implement on 20-05-2023 tp track the sensor config changes
                
                // $userEmail = $request->header('Userid');
                // $this->sensorConfig($sensor, $userEmail);
                
                $sensor->save();
                
                if($criticalMinValueChange || $criticalMaxValueChange || $warningMinValueChange || $warningMaxValueChange || $outofrangeMinValueChange || $outofrangeMaxValueChange){
                    
                    $SensorLimitChangeLog = new SensorLimitChangeLog;
                    // $userRole = $this->userRole;
                    //     if($userRole == 'systemSpecialist')
                    //     {
                            
                    //     $SensorLimitChangeLog->criticalRefMinValue = $request->criticalMinValue;
                    //     $SensorLimitChangeLog->criticalRefMaxValue = $request->criticalMaxValue;
                    //     $SensorLimitChangeLog->warningRefMinValue = $request->warningMinValue;
                    //     $SensorLimitChangeLog->warningRefMaxValue = $request->warningMaxValue;
                    //     $SensorLimitChangeLog->outofrangeRefMinValue = $request->outofrangeMinValue;
                    //     $SensorLimitChangeLog->outofrangeRefMaxValue = $request->outofrangeMaxValue;
        
                    //     }
                    
                    
                    $SensorLimitChangeLog->companyCode = $this->companyCode;
                    $SensorLimitChangeLog->device_id = $request->deviceId;
                    $SensorLimitChangeLog->sensor_id = $id;
                    $SensorLimitChangeLog->criticalMinValue = $criticalMinValueChange;
                    $SensorLimitChangeLog->criticalMaxValue = $criticalMaxValueChange;
                    $SensorLimitChangeLog->warningMinValue = $warningMinValueChange;
                    $SensorLimitChangeLog->warningMaxValue = $warningMaxValueChange;
                    $SensorLimitChangeLog->outofrangeMinValue = $outofrangeMinValueChange;
                    $SensorLimitChangeLog->outofrangeMaxValue = $outofrangeMaxValueChange;
                    $SensorLimitChangeLog->email = Auth::user()->email;
                    $SensorLimitChangeLog->save();
                }
                
                // Event logs //31-05-2023
                $device = DB::table('devices')
                    ->where('id', $request->deviceId)
                    ->get();
                
                $affectedColumns = [];
                foreach ($sensor->getChanges() as $attribute => $value) {
                    if ($attribute !== 'updated_at') {
                        $affectedColumns[$attribute] = $value;
                    }
                }
                
                $affectedColumns['Sensor name'] = $sensor->sensorTag;  
                $affectedColumns['Device name'] = $device[0]->deviceName;
                
                $logController = new EventLogController();
                $logController->addLog($request, 'Sensor Config', $affectedColumns);
                
                $response = [
                    "message" => "Sensor updated successfully",
                    "data" => $affectedColumns
                ];
                $status = 200;   
            }   
            
        }catch (QueryException $e) {
            $response = [
                "message" => $e->errorInfo
            ];
            $status = 406; 
            
        }catch(Exception $e){
            $response = [
                "message" =>  $e->getMessage()
            ];    
            $status = 404;           
        }   
        
        return response($response,$status);    
    }


    public function sensorConfig($sensor, $userEmail)
    {
        $sc = new SensorConfigLogs;
       
        $sc->companyCode = $sensor->companyCode;
        $sc->locationId = $sensor->location_id;
        $sc->branchId = $sensor->branch_id;
        $sc->facilityId = $sensor->facility_id;
        $sc->buildingId = $sensor->building_id;
        $sc->floorId = $sensor->floor_id;
        $sc->labId = $sensor->lab_id;
        $sc->deviceId = $sensor->deviceId;
        $sc->sensorId = $sensor->id;
        $sc->sensorTag = $sensor->sensorTag;
        $sc->criticalMinValue = $sensor->criticalMinValue;
        $sc->criticalMaxValue = $sensor->criticalMaxValue;
        $sc->warningMinValue = $sensor->warningMinValue;
        $sc->warningMaxValue = $sensor->warningMaxValue;
        $sc->outofrangeMinValue = $sensor->outofrangeMinValue;
        $sc->outofrangeMaxValue = $sensor->outofrangeMaxValue;
        $sc->userEmail = $userEmail;
       
        $sc->save();
    }
   
   
    public function destroy($id)
    {
        try{
            $sensor = Sensor::find($id);
            if(!$sensor){
                throw new exception("Sensor name not found");
            }          

            if($sensor){                 
                $sensor->delete();
                $response = [
                    "message" => "Sensor name and related data deleted successfully"
                ];
                $status = 200;             
            }   
        }catch(Exception $e){
            $response = [
                "message"=>$e->getMessage()
            ];
            $status = 409;
        }
                    
        return response($response,$status); 
    }
    
    public function deviceDeployedSensors($id)
    {
        $query = Sensor::select('sensorTag','id');
                $query->where('deviceId','=',$id);  
        
        // $query = DB::table('sensor_units')
        //                 ->join('sensors', 'sensors.sensorName', '=', 'sensor_units.id')
        //               ->select(DB::raw('sensors.sensorTag'))
        //               ->where('sensor_units.bumpTestRequired','=','ON')
        //               ->where('sensors.deviceId','=',$id);


        $response = $query->get();
        $status = 200;
        
        return response($response,$status);
    }
    
    public function bumptestDeviceDeployedSensors($id)
    {
        $query = DB::table('sensor_units')
                        ->join('sensors','sensors.sensorName', '=', 'sensor_units.id')
                        // ->join('sensors', 'sensors.companyCode','=','sensor_units.companyCode')
                        ->select(DB::raw('sensorTag'))
                        ->where('sensor_units.bumpTestRequired','=','ON')
                        ->where('sensors.deviceId','=',$id)
                        ->where('sensors.companyCode','=',$this->companyCode);
        
        //SELECT sensor_units.sensorCategoryName, sensor_units.sensorName, sensor_units.bumpTestRequired, sensors.companyCode, sensors.deviceId, sensors.sensorTag FROM sensor_units INNER JOIN sensors ON sensor_units.id = sensors.sensorName  where sensor_units.bumpTestRequired = 'ON' AND sensors.companyCode ='A-TEST' and sensors.deviceId = 3
        $response = $query->get();
        $status = 200;
        
        return response($response,$status);
    }
    
    
    
    public function sensorPropertiesUpdate(Request $request,$id)
    {
        $sensor = Sensor::find($id);    
        if($sensor){  
            if($request->sensorStatus!=""){
                $sensor->sensorStatus=$request->sensorStatus;                
            }
            if($request->notificationStatus!=""){
                $sensor->notificationStatus=$request->notificationStatus;
            }  
            
            if($request->hooterRelayStatus!=""){
                $sensor->hooterRelayStatus = $request->hooterRelayStatus;
            }
            
            if($request->audioDecibelLevel!=""){
                $sensor->audioDecibelLevel = $request->audioDecibelLevel;
            }
            
            $sensor->update();
            
            // Event logs //31-05-2023
            
            $device = DB::table('sensors as s')
                ->where('s.id', $id)
                ->join('devices as d', 'd.id', '=', 's.deviceId')
                ->select('d.deviceName','s.*')
                ->get();
                
                
            $affectedColumns = [];
            foreach ($sensor->getChanges() as $attribute => $value) {
                if ($attribute !== 'updated_at') {
                    if($value == 0) {
                        $affectedColumns[$attribute] = 'disabled';
                        
                    }else if($value == 1) {
                        $affectedColumns[$attribute] = 'enabled';
                    }
                }
            }
                
            $affectedColumns['Sensor name'] = $sensor->sensorTag;  
            $affectedColumns['Device name'] = $device[0]->deviceName;
            
            
            $logController = new EventLogController();
            $logController->addLog($request, 'Sensor Config', $affectedColumns);
            
            
            $response = [
                "message" => "Settings Updated Successfully",
                "data "=> $affectedColumns
            ];
            $status = 200; 
        }
        
        return response($response,$status);
    }
    
    
    public function getSensorUnit(Request $request)
    {
        $sensorData = [];
        $sensorUnit = DB::table('sensor_units')->where('id', $request->id)->get();
        $sensorData['sensorName'] = $sensorUnit[0]->sensorName;
        
        if(!$sensorUnit){
            $data['data'] = 'not found';
            $status = 400;
            
        }else{
            $data['data'] = $sensorData;
            $status = 200;
        }
        
        return response($data, $status);
    }
    
    
    public function getSensorRefValues($id)
    {
        $sensor = [];
        $sensor = DB::table('sensors')
            ->where('id', $id)
            ->select('id','refSensorTag','refAlarm','refPollingIntervalType','criticalRefAlertType','criticalRefLowMessage','criticalRefHighMessage','warningRefAlertType','warningRefLowMessage','warningRefHighMessage','outofrangeRefAlertType','outofrangeRefLowMessage','outofrangeRefHighMessage')
            ->get();
        
        $data['data'] = $sensor;
        $status = 200;
        
        return response($data, $status);
    }
    
    
    public function navigateAlarm(Request $request)
    {
        if($request->type == 'device'){
            $dbInfo = ['devices', 'deviceName', $request->deviceName];
            
        }else if($request->type == 'sensor'){
            $dbInfo = ['sensors', 'sensorTag', $request->sensorName];
        }
        
        $info = DB::table($dbInfo[0].' as db')->where($dbInfo[1], $dbInfo[2])
            ->join('locations as l', 'l.id', '=', 'db.location_id')
            ->join('branches as br', 'br.id', '=', 'db.branch_id')
            ->join('facilities as fac', 'fac.id', '=', 'db.facility_id')
            ->join('buildings as b', 'b.id', '=', 'db.building_id')
            ->join('floors as f', 'f.id', '=', 'db.floor_id')
            ->join('lab_departments as lb', 'lb.id', '=', 'db.lab_id')
            ->select('db.*', 'l.id as location_id', 'l.stateName', 'br.id as branch_id', 'br.branchName', 'fac.id as facility_id', 'fac.facilityName',
            'b.id as building_id', 'b.buildingName', 'f.id as floor_id', 'f.floorName', 'lb.id as lab_id', 'lb.labDepName',
            DB::raw('l.stateName as stateLabel'),  DB::raw('br.branchName as branchLabel'),  DB::raw('fac.facilityName as facilityLabel'),  DB::raw('b.buildingName as buildingLabel'),  DB::raw('f.floorName as floorLabel'),  DB::raw('lb.labDepName as labLabel'))
            ->get();
        
        return $info;
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}
