<?php

namespace App\Http\Controllers;
use App\Http\Controllers\UtilityController;
use App\Models\Device;
use App\Models\DeviceConfigSetup;
use App\Models\Categories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Exception;
use DateTime;
use Illuminate\Database\QueryException;

class DeviceConfigController extends Controller
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
        $query = Device::query();
        
        if($companyCode = $this->companyCode){
            $query->where('companyCode','=',$companyCode);             
        }       

        $perPage = 10;
        $page = $request->input(key:'page', default:1);
        $total = $query->count();

        $result = $query->offset(value:($page - 1) * $perPage)->limit($perPage)->get();                
        $response =  [
            'data' => $result,
            'totalData'=>$total,            
            'page'=>$page,
            'lastPage'=>ceil(num:$total/ $perPage)
        ];
        $status = 200;
        return response($response,$status);   
    }

    public function configDevice(Request $request){
        
        $date = new DateTime('Asia/Kolkata');      
        $current_time = $date->format('Y-m-d H:i:s');
        
        $ID = $request->ID;
        $CONFIG = $request->CONFIG;
        $channel = $request->CH;
        $status = $request->STATUS;
        $macAddressReq = $request->MAC;
        $mode = $request->MODE;
        $tagid = $request->TAG;
        $currentMode = "";
        
        if($macAddressReq != ""){
            
            $getDeviceFound = Device::query()
                    // ->where('id','=',$ID)
                    ->where('macAddress','=',$macAddressReq)
                    ->get();
                    
            $getDeviceFoundCount = $getDeviceFound->count();
            
            if($getDeviceFoundCount == 0){
                return $macAddressReq." DEVICE NOT FOUND";   
            }else{
                $deviceId = $getDeviceFound[0]->id;
                $macAddress = $getDeviceFound[0]->macAddress;
                $deviceMode = $getDeviceFound[0]->deviceMode;
                
                // 1 FIRMWARE UPGRADATION
                // 2 ENABLE
                // 3 DISABLE
                // 4 BUMP TEST
                
                if($deviceMode == "firmwareUpgradation"){
                    $currentMode = 4;
                }
                
                if($deviceMode == "debug"){
                    $currentMode = 5;
                }
                
                if($deviceMode == "enabled"){
                    $currentMode = 2;
                }
                
                if($deviceMode == "disabled"){
                    $currentMode = 3;
                }
                
                if($deviceMode == "config"){
                    $currentMode = 1;
                }
                
                if($deviceMode == "bumpTest"){
                    $currentMode = 6;
                }
                
                
                
                // enabled
                // disabled
                // bumpTest
                // calibration
                // firmwareUpgradation
                // config
                // debug
                
                if($macAddressReq == $macAddress && $status == '0' &&  $mode == 1){
                    
                    //update disconnectstatus to 0 because device is found and connected
                    $device = Device::find($deviceId);
                    if($device){
                        $device->deviceMode = "config";
                        $device->disconnectedStatus = 0;
                        $device->configurationStatus = 1;
                        $device->configurationProcessStatus = 1;
                        $device->save();
                    }
                    
                    //echo "24:D7:EB:87:19:1C,RDL456"; //macAddress and device ID
                    return $macAddress.",".$deviceId;    
                }else{
                    return "Kanval";
                }
            }
            
        }else if($ID != ""){
            
            $getDeviceData = Device::query()
                    ->where('id','=',$ID)
                    ->get();
                    
            $getDeviceDataCount = $getDeviceData->count();
            
            if($getDeviceDataCount == 0){
                return "DATA NOT FOUND";
            }else{
                
                $deviceTag = $getDeviceData[0]->deviceTag;
                $deviceId = $getDeviceData[0]->id;
                $binFile = $getDeviceData[0]->firmwarePushUrl; 
                $nonPollingPriority = $getDeviceData[0]->nonPollingPriority;
                $pollingPriority = $getDeviceData[0]->pollingPriority;
                $analogMemoryAddressIndex =  $getDeviceData[0]->lastAnalogMemoryAddressIndex;
                $digitalMemoryAddressIndex = $getDeviceData[0]->lastDigitalMemoryAddressIndex;
                $modbusMmeoryAddressIndex = $getDeviceData[0]->lastModbusMemoryAddressIndex;
                $deviceMode = $getDeviceData[0]->deviceMode;
                
                // 1 FIRMWARE UPGRADATION
                // 2 ENABLE
                // 3 DISABLE
                // 4 BUMP TEST
                
                if($deviceMode == "firmwareUpgradation"){
                    $currentMode = 4;
                }
                
                if($deviceMode == "enabled"){
                    $currentMode = 2;
                }
                
                if($deviceMode == "debug"){
                    $currentMode = 5;
                }
                
                if($deviceMode == "disabled"){
                    $currentMode = 3;
                }
                
                if($deviceMode == "config"){
                    $currentMode = 1;
                }
                
                if($deviceMode == "bumpTest"){
                    $currentMode = 6;
                }
                
                // enabled  2 
                // disabled 3
                // bumpTest
                // calibration
                // firmwareUpgradation 4 
                // config 1
                // debug
                
                
                $getConfigSetup = DeviceConfigSetup::query()
                                ->where('device_id','=',$deviceId)
                                ->get();
                
                $getConfigSetupCount = $getConfigSetup->count();
                
                if($getConfigSetupCount == 0){
                    return "SSID NOT FOUND";
                }else{
                    
                    $ssid = $getConfigSetup[0]->ssId;
                    $password = $getConfigSetup[0]->accessPointPassword;
                    
                    /***************************** Check  for configurations ************************/
                    
                    //RESTART_DATABASE
                    if($ID == $deviceId && $CONFIG == 'RESTART' && $channel == '1' && $status == '0'){
                        
                        $device = Device::find($deviceId);
                        
                        if($device){
                            $device->deviceMode = "config";
                            $device->lastAnalogMemoryAddressIndex = 0;
                            $device->lastDigitalMemoryAddressIndex = 0;
                            $device->lastModbusMemoryAddressIndex = 0;
                            $device->configurationProcessStatus = 1;
                            $device->save();
                            $updateSensorTagDeviceReadingStatus = DB::table('sensors')
                                                                    ->where('deviceId','=',$deviceId)
                                                                    ->update([
                                                                        'deviceReadStatus' => 0,
                                                                        'memoryAddressValue'=>""
                                                                    ]);
                                                                    
                        }
                        
                        return $deviceId."AT-RESTART";
                        
                    }
                    else if($ID == $deviceId && $CONFIG == 'RESTART' && $channel == '1' && $status == '1'){
                        echo  $deviceId."-OK-WR";
                    }
                    
                    else if($ID == $deviceId && $CONFIG == 'CONFIG' && $channel == '1' && $status == '1'){
                        echo  $deviceId."-OK-WR";
                    }
                    
                    else if($ID == $deviceId && $CONFIG == 'ENABLE' && $channel == '1' && $status == '1' && $currentMode == $mode){
                        $device = Device::find($deviceId);
                        if($device){
                            $device->disconnectedStatus = 0;
                            $device->deviceMode = "enabled";
                            $device->modeChangedDateTime = $current_time;
                            $device->configurationProcessStatus = 2;
                            $device->save();
                        }
                        echo  $deviceId."-OK-WR";
                    }
                    else if($ID == $deviceId && $CONFIG == 'OTA' && $channel == '1' && $status == '0' && $currentMode == $mode){
                        if($binFile == ""){  
                             //echo $deviceId."-AT-WRITE=200=http://wisething.in/AQMi_indoor_firmware.bin";  
                             echo $deviceId."-AT-WRITE=200=".$binFile;   
                        }else{
                            echo $deviceId."-AT-WRITE=200=".$binFile;    
                        }
                        
                    }
                    else if($ID == $deviceId && $CONFIG == 'OTA' && $channel == '1' && $status == '1' && $currentMode == $mode){
                        echo  $deviceId."-OK-WR";
                    }
                    
                    /**************************** ADDITIONAL MODE SETTING *************************/
                    
                    else if($ID == $deviceId && $CONFIG == 'MODE' && $channel == '1' && $status == '0'&&  $mode == '1'){
                        // echo "RDL456-AT-WRITE=2000=2";  
                        //  RDL456-AT-CONFRDL
                        $device = Device::find($deviceId);
                        if($device){
                            $device->disconnectedStatus = 0;
                            $device->save();
                        }
                        return $deviceId."-AT-WRITE=2000=".$currentMode;
                    }
                    else if($ID == $deviceId && $CONFIG == 'MODE' && $channel == '1' && $status == '1'&&  $mode == '1'){
                        echo  $deviceId."-OK-WR";
                    }
                    
                    
                    /*****************************CONFIGURING  SSID**********************************/
                    else if($ID == $deviceId && $CONFIG == 'SSID' && $channel == '1' && $status == '0' && $currentMode == $mode){
                        
                        //Format echo "RDL456-AT-WRITE=10=linksys";
                        return $deviceId."-AT-WRITE=10=".$ssid;
                        
                    }else if($ID == $deviceId && $CONFIG == 'SSID' && $channel == '1' && $status == '1'  && $currentMode == $mode){
                        //Format echo "RDL456-OK-WR";
                        return $deviceId."-OK-WR";
                    }
                    
                    
                    /****************************** CONFIGURING  PASSWORD******************************/
                    elseif($ID == $deviceId && $CONFIG == 'PASS' && $channel == '1' && $status == '0' && $currentMode == $mode){
                        
                        //Format echo "RDL456-AT-WRITE=30=2020@RDL";
                        return $deviceId."-AT-WRITE=30=".$password;
                        
                    }
                    else if($ID == $deviceId && $CONFIG == 'PASS' && $channel == '1' && $status == '1' && $currentMode == $mode){
                        
                        //Format echo "RDL456-OK-WR";
                        return $deviceId."-OK-WR";
                        
                    }
                    
                    /*************************CONFIGURING DEVICE ID****************************************/
                    elseif($ID == $deviceId && $CONFIG == 'ID' && $channel == '1' && $status == '0' && $currentMode == $mode){
                        
                        //echo "RDL456-AT-WRITE=50=RDL456";
                        return $deviceId."-AT-WRITE=50=".$deviceId;
                       
                        
                    }
                    else if($ID == $deviceId && $CONFIG == 'ID' && $channel == '1' && $status == '1' && $currentMode == $mode){
                        
                        //echo "RDL456-OK-WR";
                        return $deviceId."-OK-WR";
                        
                    }
                    
                    /**************************CONFIGURING UPLODING URL***************************************/

                    elseif($ID == $deviceId && $CONFIG == 'URL' && $channel == '1' && $status == '0' && $currentMode == $mode){
                        
                        //echo "RDL456-AT-WRITE=80=http://industrypi.com/rdl_rnd_test/uploaddata.php";
                        return $deviceId."-AT-WRITE=80=http://wisething.in/aideaLabs/AQMS_DATA_EXTRACTION_CRON/AqmiPushData.php";
                    }
                    else if($ID == $deviceId && $CONFIG == 'URL' && $channel == '1' && $status == '1' && $currentMode == $mode){
                        return $deviceId."-OK-WR";
                    }
                    
                    /************************ CONFIGURING PIROITY  ***************************************/ 
                    
                    elseif($ID == $deviceId && $CONFIG == 'PRTY' && $channel == '1' && $status == '0' && $currentMode == $mode){
                        //echo "RDL456-AT-WRITE=300=10";
                        return $deviceId."-AT-WRITE=300=".$pollingPriority;
                    }
                    else if($ID == $deviceId && $CONFIG == 'PRTY' && $channel == '1' && $status == '1' && $currentMode == $mode){
                        return $deviceId."-OK-WR";
                    }
                    
                    /*****************************NO  PIROITY*******************************************/
                    elseif($ID == $deviceId && $CONFIG == 'NONPRTY' && $channel == '1' && $status == '0' && $currentMode == $mode){
                        
                        //echo "RDL456-AT-WRITE=310=60";
                        return $deviceId."-AT-WRITE=310=".$nonPollingPriority;
                        
                    }
                    else if($ID == $deviceId && $CONFIG == 'NONPRTY' && $channel == '1' && $status == '1' && $currentMode == $mode){
                        return $deviceId."-OK-WR";
                    }
                    
                   /**************************************ANALOG *******************************************/
                   
                    else if($CONFIG == "ADC"){
                        
                        if($analogMemoryAddressIndex == 4){
                            
                            // return "All address 500, 530, 560, 590 for device ".$deviceId." Allocated";
                            return $deviceId."-OK-WR";
                            
                        }else{
                            
                            $analogMemoryAddressArray = array(500,530,560,590);
                            
                            $previousAddress = $analogMemoryAddressIndex; //get from device table of previous address  step 2
                            
                            $memoryAddress = $analogMemoryAddressArray[$previousAddress];
                            
                            $readingAddress = $memoryAddress; //assgining memory address to reading address ex:500
                            
                            $readingChannel = array_search($readingAddress, $analogMemoryAddressArray);
                            
                            $mainChannelIndexCount  = number_format($readingChannel) + 1;
                            
                            $readingStatus = number_format($mainChannelIndexCount) - 1;
                            
                            //[STEP 1] get only one analog sensor which is not read in device
                            //[STEP 2] get device analog last updated memory address -> [STEP 2]  called in the $previous address
                            //[STEP 3] write the sensorTag to device  and update the status to 1 in db and update memoryAddress of the device where sensorTag is write (1 indicates sensor tag writtien in device and 0 indicates sensorTag not written in device) step 3
                            //[STEP 4] while writting device update the memory address of the device table , Analog column to last updated memory address index step 4
                            //[STEP 5] if memory address is equal to 3 update to initial state step 5
                            
                            //step1 execution
                            
                            if($ID == $deviceId && $CONFIG == 'ADC' && $channel == $mainChannelIndexCount && $status == '0' && $currentMode == $mode){
                               
                                $getAnalogSensor = DB::table('sensors')->where([['deviceId',$deviceId],['sensorOutput','=','Analog'],['deviceReadStatus','0']])->orderBy('id','ASC')->take(1)->get();
                                
                                if(count($getAnalogSensor) == 0){
                                    
                                    // return "NA,2,1"; 2 is disable
                                    
                                    $updateDeviceMemoryAddress = Device::select('*')
                                        ->where('id','=',$deviceId)
                                        ->update([
                                             'lastAnalogMemoryAddressIndex' => $previousAddress+1,
                                        ]);
                                    
                                    if($updateDeviceMemoryAddress){
                                        return $deviceId."-AT-WRITE=".$readingAddress."=NA,2,1";
                                    }
                                    
                                }else{
                                
                                    $sensorTag = $getAnalogSensor[0]->sensorTag;
                                    $sensorTagId = $getAnalogSensor[0]->id;
                                    
                                    $readingStatus = $getAnalogSensor[0]->deviceReadStatus;
                                    
                                    $pollingIntervalType = $getAnalogSensor[0]->pollingIntervalType;
                                    
                                    if($pollingIntervalType == "Priority"){
                                        $readingPriority = 1; //[1-> Priority,  2-> NonPriority]
                                    }else{
                                        $readingPriority = 2; //[1-> Priority,  2-> NonPriority]
                                    }
                                    
                                    $readingId = $sensorTag;
                                   
                                    // if sensortag not there and status = 0
                                    
                                    //echo "RDL456-AT-WRITE=500=abcd,1,1";
                                    
                                    //updating sensorTag deviceReadStatus to 1 step 2
                                    
                                    if($status == '0'){
                                        
                                        $updateDeviceMemoryAddress = Device::select('*')
                                        ->where('id','=',$deviceId)
                                        ->update([
                                             'lastAnalogMemoryAddressIndex' => $previousAddress+1,
                                        ]);
                                    
                                        if($updateDeviceMemoryAddress){
                                            $updateSensorTagDeviceReadingStatus = DB::table('sensors')
                                                                                    ->where('id','=',$sensorTagId)
                                                                                    ->update([
                                                                                        'deviceReadStatus' => 1,
                                                                                        'memoryAddressValue'=>$memoryAddress
                                                                                    ]);
                                            if($updateSensorTagDeviceReadingStatus){
                                                return $deviceId."-AT-WRITE=".$readingAddress."=".$readingId.",1,".$readingPriority;
                                            }
                                        } 
                                    }else{
                                        
                                    }
                                }
                            }else if($ID == $deviceId && $CONFIG == 'ADC' && $channel == $readingStatus && $status == '1' && $currentMode == $mode){
                                //echo $deviceId."-OK-WR";
                                return $deviceId."-OK-WR";
                            }
                            
                            else{
                                //echo "NA";
                                return "NA,2,1";
                            }
                        }
                    }
                    
                    /**************************************DIGITAL *******************************************/
                    
                    else if($CONFIG == "DIGTAL"){
                        
                        if($digitalMemoryAddressIndex == 2){
                            //return "All address 650, 680, 710, 740 for device ".$deviceId." Allocated";
                             return $deviceId."-OK-WR";
                        }else{
                            //$digitalMemoryAddressArray = array(650,680,710,740);
                            
                            $digitalMemoryAddressArray = array(650,680);
                            
                            $previousAddress = $digitalMemoryAddressIndex; //get from device table of previous address 
                            
                            $memoryAddress = $digitalMemoryAddressArray[$previousAddress];
                            
                            $readingAddress = $memoryAddress; //assgining memory address to reading address ex:500
                            
                            $readingChannel = array_search($readingAddress, $digitalMemoryAddressArray);
                            
                            $mainChannelIndexCount  =  number_format($readingChannel) + 1;
                            
                            $readingStatus = number_format($mainChannelIndexCount) - 1;
                            
                            //[STEP 1] get only one analog sensor which is not read in device
                            //[STEP 2] get device Digital last updated memory address -> [STEP 2]  called in the $previous address
                            //[STEP 3] write the sensorTag to device  and update the status to 1 in db and update memoryAddress of the device where sensorTag is write (1 indicates sensor tag writtien in device and 0 indicates sensorTag not written in device) step 3
                            //[STEP 4] while writting device update the memory address of the device table , Digital column to last updated memory address index step 4
                            //[STEP 5] if memory address is equal to 3 update to initial state step 5
                            
                            //step1 execution
                            
                            if($ID == $deviceId && $CONFIG == 'DIGTAL' && $channel == $mainChannelIndexCount && $status == '0'&& $currentMode == $mode){
                               
                                $getAnalogSensor = DB::table('sensors')->where([['deviceId',$deviceId],['sensorOutput','=','Digital'],['deviceReadStatus','0']])->orderBy('id','ASC')->take(1)->get();
                                
                                if(count($getAnalogSensor) == 0){
                                    // return "NA,2,1";
                                    $updateDeviceMemoryAddress = Device::select('*')
                                        ->where('id','=',$deviceId)
                                        ->update([
                                             'lastDigitalMemoryAddressIndex' => $previousAddress+1,
                                        ]);
                                        
                                    if($updateDeviceMemoryAddress){
                                        return $deviceId."-AT-WRITE=".$readingAddress."=NA,2,1";
                                    }
                                    
                                }else{
                               
                                    $sensorTag = $getAnalogSensor[0]->sensorTag;
                                    $sensorTagId = $getAnalogSensor[0]->id;
                                    
                                    $readingId = $sensorTag;
                                   
                                    //echo "RDL456-AT-WRITE=500=abcd,1,1";
                                    
                                    //updating sensorTag deviceReadStatus to 1 step 2
                                    
                                    if($status == '0'){
                                        
                                        $updateDeviceMemoryAddress = Device::select('*')
                                        ->where('id','=',$deviceId)
                                        ->update([
                                             'lastDigitalMemoryAddressIndex' => $previousAddress+1,
                                        ]);
                                    
                                        if($updateDeviceMemoryAddress){
                                            $updateSensorTagDeviceReadingStatus = DB::table('sensors')
                                                                                    ->where('id','=',$sensorTagId)
                                                                                    ->update([
                                                                                        'deviceReadStatus' => 1,
                                                                                        'memoryAddressValue'=>$memoryAddress
                                                                                    ]);
                                            if($updateSensorTagDeviceReadingStatus){
                                                return $deviceId."-AT-WRITE=".$readingAddress."=".$readingId.",1,1";
                                            }
                                        }   
                                    }
                                }
                                
                            }else if($ID == $deviceId && $CONFIG == 'DIGTAL' && $channel == $readingStatus && $status == '1'&& $currentMode == $mode){
                                return $deviceId."-OK-WR";
                            }else{
                                return "NA,2,1";
                            }
                        }
                    }
                    
                    /**************************************MODBUS *******************************************/
                    
                    else if($CONFIG == "METER"){
                        if($ID == $deviceId && $CONFIG == 'METER' && $channel == '1' && $status == '0'&& $currentMode == $mode){
                            $getModbusSensor = DB::table('sensors')
                                                ->where([['deviceId',$deviceId],['sensorOutput','=','Modbus']])
                                                ->orderBy('id','ASC')
                                                ->get();
                            $cnt = count($getModbusSensor);
                            
                            return $deviceId."-AT-METER=".$cnt;
                        }
                        else if($ID == $deviceId && $CONFIG == 'METER' && $channel == '1' && $status == '1'&& $currentMode == $mode){
                            echo $deviceId."-OK-WR";
                        }else{
                            return "NA,2,1";
                        }
                    }
                    
                    /**************************************MODBUS *******************************************/
                    
                    else if($CONFIG == 'MODBUS'){
                        
                        if($modbusMmeoryAddressIndex == 6){
                            //return "All address 1010, 1060, 1110, 1160 for device ".$deviceId." Allocated";
                            
                            // $device = Device::find($deviceId);
                            // if($device){
                            //     $device->configurationProcessStatus = 2;
                            //     $device->save();
                            // }
                            
                            echo $deviceId."-OK-WR";
                        }else{
                            $modbusMemoryAddressArray = array(1010,1060,1110,1160,1210,1260);
                            
                            $previousAddress = $modbusMmeoryAddressIndex; //get from device table of previous address 
                            
                            $memoryAddress = $modbusMemoryAddressArray[$previousAddress];
                            
                            $readingAddress = $memoryAddress; //assgining memory address to reading address ex:500
                            
                            $readingChannel = array_search($readingAddress, $modbusMemoryAddressArray);
                            
                            $mainChannelIndexCount  = $readingChannel+1;
                            
                            $readingStatus = number_format($mainChannelIndexCount) - 1;
                            
                            //[STEP 1] get only one analog sensor which is not read in device
                            //[STEP 2] get device Digital last updated memory address -> [STEP 2]  called in the $previous address
                            //[STEP 3] write the sensorTag to device  and update the status to 1 in db and update memoryAddress of the device where sensorTag is write (1 indicates sensor tag writtien in device and 0 indicates sensorTag not written in device) step 3
                            //[STEP 4] while writting device update the memory address of the device table , Digital column to last updated memory address index step 4
                            //[STEP 5] if memory address is equal to 3 update to initial state step 5
                            
                            //step1 execution
                            
                            if($ID == $deviceId && $CONFIG == 'MODBUS' && $channel == $mainChannelIndexCount && $status == '0'&& $currentMode == $mode){
                               
                                $getAnalogSensor = DB::table('sensors')->where([['deviceId',$deviceId],['sensorOutput','=','Modbus'],['deviceReadStatus','0']])->orderBy('id','ASC')->take(1)->get();
                                
                                if(count($getAnalogSensor) == 0){
                                    //return "NA,2,1";
                                    $updateDeviceMemoryAddress = Device::select('*')
                                        ->where('id','=',$deviceId)
                                        ->update([
                                             'lastModbusMemoryAddressIndex' => $previousAddress+1,
                                    ]);
                                    
                                    if($updateDeviceMemoryAddress){
                                        return $deviceId."-AT-WRITE=".$readingAddress."=NA,2,1";
                                    }
                                    
                                }else{
                                
                                    $sensorTag = $getAnalogSensor[0]->sensorTag;
                                    $sensorTagId = $getAnalogSensor[0]->id;
                                    
                                    $slaveID = $getAnalogSensor[0]->slaveId; //slaveid
                                    $functionalCode = $getAnalogSensor[0]->registerType; //fc->registerType
                                    $length = $getAnalogSensor[0]->length; //length->l 
                                    $registerId = $getAnalogSensor[0]->registerId; //RegisterId->RI (register address   db)
                                    $pollingIntervalType = $getAnalogSensor[0]->pollingIntervalType;
                                    
                                    $readingId = $sensorTag; //tagname
                                    $readingSlaveID = $slaveID; // [val in number (1 to 255)]
                                    
                                    if($length == "16 Bit"){ //   [64 BIT->1     &&   32BIT->2]
                                        $readingLength = 1;
                                    }else{
                                        $readingLength = 2;
                                    }
                                    
                                    $readingFunctionalCode = $functionalCode;  //[1-> Input coil,  2-> Discreate coil, 3-> HoldingRegister, 4-> Input Register]val 3
                                    
                                    if($pollingIntervalType == "Priority"){
                                        $readingPriority = 1; //[1-> Priority,  2-> NonPriority]
                                    }else{
                                        $readingPriority = 2; //[1-> Priority,  2-> NonPriority]
                                    }
                                    
                                    
                                    $readingRegisterId = $registerId;//[val in number (1 to n)] 
                                   
                                     //echo "RDL456-AT-WRITE=1010=uuiuy,1,3,25,2,20";   //SLAVE ID ,FC ,ADDRESS,LENG,PRTY
                                    
                                    //updating sensorTag deviceReadStatus to 1 step 2
                                    
                                    if($status == '0'){
                                        
                                        $updateDeviceMemoryAddress = Device::select('*')
                                        ->where('id','=',$deviceId)
                                        ->update([
                                             'lastModbusMemoryAddressIndex' => $previousAddress+1,
                                        ]);
                                    
                                        if($updateDeviceMemoryAddress){
                                            $updateSensorTagDeviceReadingStatus = DB::table('sensors')
                                                                                    ->where('id','=',$sensorTagId)
                                                                                    ->update([
                                                                                        'deviceReadStatus' => 1,
                                                                                        'memoryAddressValue'=>$memoryAddress
                                                                                    ]);
                                            if($updateSensorTagDeviceReadingStatus){
                                                 //echo "RDL456-AT-WRITE=1010=uuiuy,1,3,25,2,20";   //SLAVE ID ,FC ,ADDRESS,LENG,PRTY
                                                return $deviceId."-AT-WRITE=".$readingAddress."=".$readingId.",".$readingSlaveID.",".$readingFunctionalCode.",".$readingRegisterId.",".$readingLength.",".$readingPriority;
                                            }
                                        }   
                                    }
                                }
                                
                            }else if($ID == $deviceId && $CONFIG == 'MODBUS' && $channel == $readingStatus && $status == '1'&& $currentMode == $mode){
                                echo $deviceId."-OK-WR";
                            }else{
                                return "NA,2,1";
                            }
                        }
                    }
                    
                    else
                    {
                        echo "kanwal"; 
                    }
                }
            }
        }else{
            return "Please Send MacAddress AND DeviceId Or Both";
        }
    }
    
    
    
    
    public function configurationStatus(Request $request){
        $id = $request->device_id;
        try{
            $device = Device::find($id);
            if(!$device){
                throw new exception("Device name not found");
            }          

            if($device){                 
                $configurationStatus = $device->configurationStatus;
                if($configurationStatus == 1){
                    $response = [
                        "message" => "Device Configuration started",
                        "isInitialProgress" => 1
                        
                    ];
                    $status = 200;    
                }
                else{
                    $response = [
                        "message" => "Device Configuration not started",
                        "isInitialProgress" => 0
                    ];
                    $status = 200; 
                }
                             
            }   
        }catch(Exception $e){
            $response = [
                "message"=>$e->getMessage()
            ];
            $status = 409;
        }
        return response($response,$status); 
    } 
    
    
   
    public function  configurationProcessStatus(Request $request){
        $id = $request->device_id;
        try{
            $device = Device::find($id);
            if(!$device){
                throw new exception("Device name not found");
            }          

            if($device){                 
                $configurationProcessStatus = $device->configurationProcessStatus;
                $mode = $device->deviceMode;
                
                if($configurationProcessStatus == 1) {
                    $response = [
                        "message"=>"Device Configuration In Process",
                        "configComplete" => 0
                    ];
                    $status = 409;
                    
                } else if($configurationProcessStatus == 2) {
                    $response = [
                        "message" => "Device Configuration Success",
                        "configComplete" => 1
                    ];
                    $status = 200; 
                    
                    
                    $logController = new EventLogController();                  // Event logs implemented on 09-06-2023
                    
                    if($mode == 'config') {
                        $eventDetails = [
                            "deviceName" => $device->deviceName,
                            "result" => 'configuration success',
                        ];
                        
                        $logController->addLog($request, 'Configuration', $eventDetails);
                        
                    }else if($mode == 'firmwareUpgradation') {
                        $logController->insertFirmwareLog($request, $id, 'Success');
                    }
                    
                }else{
                    $response = [
                        "message"=>"device is not started or failure",
                        "configComplete" => -1
                    ];
                    $status = 409;
                }
            }   
        }catch(Exception $e){
            $response = [
                "message"=>$e->getMessage()
            ];
            $status = 409;
        }
        return response($response,$status); 
    }
    
    
    
    
    
    public function connectDevice(Request $request){
        $ID = $request->ID;
        $CONFIG = $request->CONFIG;
        $channel = $request->CH;
        $status = $request->STATUS;
        
        
        
        /*****************************SSID**********************************/

        if($ID == 'RDL456' && $CONFIG == 'SSID' && $channel == '1' && $status == '0'){
            echo "RDL456-AT-WRITE=10=linksys";
        }
        else if($ID == 'RDL456' && $CONFIG == 'SSID' && $channel == '1' && $status == '1'){
            echo "RDL456-OK-WR";
        }
        
        
        
        
        /******************************PASSWORD******************************/
        elseif($ID == 'RDL456' && $CONFIG == 'PASS' && $channel == '1' && $status == '0'){
            echo "RDL456-AT-WRITE=30=2020@RDL";
        }
        else if($CONFIG == 'PASS' && $channel == '1' && $status == '1'){
            echo "RDL456-OK-WR";
        }
        /*************************ID****************************************/
        elseif($ID == 'RDL456' && $CONFIG == 'ID' && $channel == '1' && $status == '0'){
            echo "RDL456-AT-WRITE=50=RDL456";
        }
        else if($ID == 'RDL456' && $CONFIG == 'ID' && $channel == '1' && $status == '1'){
            echo "RDL456-OK-WR";
        }
        /**************************UPLODING URL***************************************/

        elseif($ID == 'RDL456' && $CONFIG == 'URL' && $channel == '1' && $status == '0'){
            echo "RDL456-AT-WRITE=80=http://industrypi.com/rdl_rnd_test/uploaddata.php";
        }
        else if($ID == 'RDL456' && $CONFIG == 'URL' && $channel == '1' && $status == '1'){
            echo "RDL456-OK-WR";
        }
        /*************************OTA****************************************/

        elseif($ID == 'RDL456' && $CONFIG == 'OTA' && $channel == '1' && $status == '0'){
            echo "RDL456-AT-WRITE=200=http://industrypi.com/rdl_rnd_test/uploaddata.php";
        }
        else if($ID == 'RDL456' && $CONFIG == 'OTA' && $channel == '1' && $status == '1'){
            echo "RDL456-OK-WR";
        }
        /************************  PIROITY  ***************************************/

        elseif($ID == 'RDL456' && $CONFIG == 'PRTY' && $channel == '1' && $status == '0'){
            echo "RDL456-AT-WRITE=300=10";
        }
        else if($CONFIG == 'PRTY' && $channel == '1' && $status == '1'){
            echo "RDL456-OK-WR";
        }
        /*****************************NO  PIROITY*******************************************/
        elseif($ID == 'RDL456' && $CONFIG == 'NONPRTY' && $channel == '1' && $status == '0'){
            echo "RDL456-AT-WRITE=310=60";
        }
        else if($CONFIG == 'NONPRTY' && $channel == '1' && $status == '1'){
            echo "RDL456-OK-WR";
        }
        /*****************************ADC*****************************************/  
        elseif($ID == 'RDL456' && $CONFIG == 'ADC' && $channel == '1' && $status == '0'){
            echo "RDL456-AT-WRITE=500=1,1,1,1,1";  //ch1,ch2,ch3,ch4,PRY
        }
        else if($ID == 'RDL456' && $CONFIG == 'ADC' && $channel == '1' && $status == '1'){
            echo "RDL456-OK-WR";
        }
        /*
        elseif($ID == 'RDL456' && $CONFIG == 'ADC' && $channel == '2' && $status == '0'){
            echo "RDL456-AT-WRITE=520=1,1";
        }
        else if($ID == 'RDL456' && $CONFIG == 'ADC' && $channel == '2' && $status == '1'){
            echo "RDL456-OK-WR";
        }

        elseif($ID == 'RDL456' && $CONFIG == 'ADC' && $channel == '3' && $status == '0'){
            echo "RDL456-AT-WRITE=540=1,1";
        }
        else if($ID == 'RDL456' && $CONFIG == 'ADC' && $channel == '3' && $status == '1'){
            echo "RDL456-OK-WR";
        }

        elseif($ID == 'RDL456' && $CONFIG == 'ADC' && $channel == '4' && $status == '0'){
            echo "RDL456-AT-WRITE=560=1,1";
        }
        else if($ID == 'RDL456' && $CONFIG == 'ADC' && $channel == '4' && $status == '1'){
            echo "RDL456-OK-WR";
        }*/

        /*****************************DIGTAL*****************************************/  
        elseif($ID == 'RDL456' && $CONFIG == 'DIGTAL' && $channel == '1' && $status == '0'){
            echo "RDL456-AT-WRITE=600=1,1,1,1,2";   //ch1,ch2,ch3,ch4,PRY
        }
        else if($ID == 'RDL456' && $CONFIG == 'DIGTAL' && $channel == '1' && $status == '1'){
            echo "RDL456-OK-WR";
        }
        /*
        elseif($ID == 'RDL456' && $CONFIG == 'DIGTAL' && $channel == '2' && $status == '0'){
            echo "RDL456-AT-WRITE=620=1,1";
        }
        else if($ID == 'RDL456' && $CONFIG == 'DIGTAL' && $channel == '2' && $status == '1'){
            echo "RDL456-OK-WR";
        }

        elseif($ID == 'RDL456' && $CONFIG == 'DIGTAL' && $channel == '3' && $status == '0'){
            echo "RDL456-AT-WRITE=640=1,1";
        }
        else if($ID == 'RDL456' && $CONFIG == 'DIGTAL' && $channel == '3' && $status == '1'){
            echo "RDL456-OK-WR";
        }

        elseif($ID == 'RDL456' && $CONFIG == 'DIGTAL' && $channel == '4' && $status == '0'){
            echo "RDL456-AT-WRITE=660=1,1";
        }
        else if($ID == 'RDL456' && $CONFIG == 'DIGTAL' && $channel == '4' && $status == '1'){
            echo "RDL456-OK-WR";
        }*/
        /**************************************MODBUS *******************************************/
        else if($ID == 'RDL456' && $CONFIG == 'METER' && $channel == '1' && $status == '0'){
            echo "RDL456-AT-METER=4";
        }
        else if($ID == 'RDL456' && $CONFIG == 'METER' && $channel == '1' && $status == '1'){
            echo "RDL456-OK-WR";
        }

        else if($ID == 'RDL456' && $CONFIG == 'MODBUS' && $channel == '1' && $status == '0'){
            echo "RDL456-AT-WRITE=1010=1,3,25,2,20";   //SLAVE ID ,FC ,ADDRESS,LENG,PRTY
        }
        else if($ID == 'RDL456' && $CONFIG == 'MODBUS' && $channel == '1' && $status == '1'){
            echo "RDL456-OK-WR";
        }

        else if($ID == 'RDL456' && $CONFIG == 'MODBUS' && $channel == '2' && $status == '0'){
            echo "RDL456-AT-WRITE=1060=1,3,45,2,10";   //SLAVE ID ,FC ,ADDRESS,LENG,PRTY
        }
        else if($ID == 'RDL456' && $CONFIG == 'MODBUS' && $channel == '2' && $status == '1'){
            echo "RDL456-OK-WR";
        }


        else if($ID == 'RDL456' && $CONFIG == 'MODBUS' && $channel == '3' && $status == '0'){
            echo "RDL456-AT-WRITE=1110=1,3,10,2,15";   //SLAVE ID ,FC ,ADDRESS,LENG,PRTY
        }
        else if($ID == 'RDL456' && $CONFIG == 'MODBUS' && $channel == '3' && $status == '1'){
            echo "RDL456-OK-WR";
        }


        else if($ID == 'RDL456' && $CONFIG == 'MODBUS' && $channel == '4' && $status == '0'){
            echo "RDL456-AT-WRITE=1160=1,3,55,2,30";   //SLAVE ID ,FC ,ADDRESS,LENG,PRTY
        }
        else if($ID == 'RDL456' && $CONFIG == 'MODBUS' && $channel == '4' && $status == '1'){
            echo "RDL456-OK-WR";
        }



        else
        {
            echo "kanwal"; 
        }
        
    }
    
    
    
    
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Device  $device
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try{
            $device = Device::find($id);
            if(!$device){
                throw new exception("Device name not found");
            }          

            if($device){                 
                $device->delete();
                $response = [
                    "message" => "Device name and related data deleted successfully"
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
}
