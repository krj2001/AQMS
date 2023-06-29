<?php

namespace App\Http\Controllers;
use App\Http\Controllers\UtilityController;
use App\Http\Controllers\UTILITY\DataUtilityController;
use App\Models\Device;
use App\Models\SensorLimitChangeLog;
use App\Models\FirmwareVersionChangeLog;
use App\Models\Categories;
use App\Models\DeviceModelLog;
use App\Models\deviceConfigLogs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Exception;
use Illuminate\Database\QueryException;
use DateTime;

class DeviceController extends Controller
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

        $getData = new DataUtilityController($request,$query);
        $response = $getData->getData();
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
        
        try{
            $deviceDataFound = DB::table('devices')
                ->where('companyCode', '=', $this->companyCode)  
                // ->where('location_id', '=', $request->location_id)             
                // ->where('branch_id', '=', $request->branch_id)             
                // ->where('facility_id', '=', $request->facility_id)             
                // ->where('building_id', '=', $request->building_id) 
                // ->where('category_id', '=', $request->category_id)     
               // ->where('deviceName', '=', $request->deviceName)     
                ->where('deviceTag', '=', $request->deviceTag)                                   
                ->first();                      
                
            if($deviceDataFound){
                throw new Exception("Duplicate entry for device name ");
            }

            $device = new Device;
            
            $categories = Categories::where('id',$request->category_id)->first();
            $deviceCategory = $categories->categoryName;
                
            $device->companyCode = $this->companyCode;
            $device->location_id = $request->location_id;   
            $device->branch_id = $request->branch_id;            
            $device->facility_id = $request->facility_id;
            $device->building_id = $request->building_id;
            
            $device->floor_id=$request->floor_id;
            $device->floorCords=$request->floorCords;
            $device->lab_id=$request->lab_id;
            
            $device->deviceName = $request->deviceName;
            $device->deviceCategory = $deviceCategory;    
            $device->category_id = $request->category_id;   
            $device->firmwareVersion = $request->firmwareVersion;   
            $device->macAddress = $request->macAddress; 
            $device->hardwareModelVersion = $request->hardwareModelVersion;
            
            $fimwareBinFile = $request->firmwareBinFile;

            $image = $request->deviceImage;  // your base64 encoded

            //Image file creation
            if($image){
                $image = str_replace('data:image/png;base64,', '', $request->deviceImage);
                $image = str_replace(' ', '+', $image);
                $imageName =  $request->deviceName.".png";
                //$picture   = date('His').'-'.$filename;                
                $path = "Customers/".$this->companyCode."/Buildings/devices";     
                $imagePath = $path."/".$imageName;        
                Storage::disk('public_uploads')->put($path."/".$imageName, base64_decode($image));    
                $device->deviceImage = $imagePath;              
            }        
            
        
            $accessPath = "http://wisething.in/aideaLabs/blog/public/";
            
            //datapush file creation
            $dataPushFileName =  $request->deviceName."_DataPush.json";
            $dataPushdata = json_encode(['Element 1','Element 2','Element 3','Element 4','Element 5']);
            $dataPushUrlpath = "Customers/".$this->companyCode."/Buildings/devices/ConfigSettingFile/dataPush";     
            Storage::disk('public_uploads')->put($dataPushUrlpath."/".$dataPushFileName, $dataPushdata); 

            
            //firmwarepush file creation
            
            if($fimwareBinFile){
                $firmwarePushdata = str_replace('data:application/octet-stream;base64,', '', $request->firmwareBinFile);
                $firmwarePushdata = str_replace(' ', '+', $firmwarePushdata);
                $firmwarePushFileName =  $request->deviceName."_firmware.bin";     
                //$firmwarePushUrlpath = "ConfigSettingFile/dataPush".$firmwarePushFileName; 
                $firmwarePushUrlpath = $firmwarePushFileName;
                Storage::disk('public_uploads')->put($firmwarePushUrlpath."/".$firmwarePushFileName, base64_decode($firmwarePushdata)); 
            }
            
            
            $device->deviceTag =  $request->deviceTag;  
            $device->nonPollingPriority =  $request->nonPollingPriority;  
            $device->pollingPriority =  $request->pollingPriority;  
            
            $device->dataPushUrl = $accessPath.$dataPushUrlpath."/".$dataPushFileName;
            $device->firmwarePushUrl = $accessPath.$firmwarePushUrlpath."/".$firmwarePushFileName;
            
            $device->binFileName = $request->binFileName;
            $device->xAxisTimeInterval = $request->xAxisTimeInterval;
            
            // $device->save();
            
            if($device->save()){
                
                $FirmwareVersionChangeLog = new FirmwareVersionChangeLog;
                $FirmwareVersionChangeLog->companyCode = $this->companyCode;
                $FirmwareVersionChangeLog->device_id = $device->id;
                $FirmwareVersionChangeLog->deviceName = $request->deviceName;
                $FirmwareVersionChangeLog->firmwareVersion =$request->firmwareVersion;
                $FirmwareVersionChangeLog->save();
            }
            
            if($device->save()){
                $model = new DeviceModelLog;
                $model->companyName = $request->header('companyCode');
                $model->deviceId = $device->id;
                $model->deviceName = $device->deviceName;
                $model->deviceModel = $request->hardwareModelVersion;
                                        
                $model->save();
            }
                                            
            $response = [
                "message" => "Device name added successfully"
            ];
            $status = 201;  
            
        }catch(Exception $e){
            $response = [
                "message"=>$e->getMessage()
            ];            
            $status = 406;
            
        }catch(QueryException $e){
            $response = [
                "error" => $e->errorInfo
            ];
            $status = 406; 
       }
       return response($response,$status);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Device  $device
     * @return \Illuminate\Http\Response
     */
    public function show(Device $device)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Device  $device
     * @return \Illuminate\Http\Response
     */
    public function edit(Device $device)
    {
        
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Device  $device
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try{

            $deviceDataNotFound = Device::find($id);
                
            if(!$deviceDataNotFound){
                throw new Exception("Device data not found");
            }


            $deviceDataFound = DB::table('devices')
                ->where('companyCode', '=', $this->companyCode)  
                ->where('location_id', '=', $request->location_id)             
                ->where('branch_id', '=', $request->branch_id)             
                ->where('facility_id', '=', $request->facility_id)             
                ->where('building_id', '=', $request->building_id)   
                ->where('category_id', '=', $request->category_id)     
                ->where('deviceName', '=', $request->deviceName) 
                ->where('id','<>',$id)                                             
                ->first();                                  
                
            if($deviceDataFound){
                throw new Exception("Duplicate entry for device name ");
            }

            $device = Device::find($id);
            if($device){
                
                $categories = Categories::where('id',$request->category_id)->first();
                $deviceCategory = $categories->categoryName;
            
            
                $device->companyCode = $this->companyCode;
                $device->location_id = $request->location_id;   
                $device->branch_id = $request->branch_id;            
                $device->facility_id = $request->facility_id;
                $device->building_id = $request->building_id;
                
                $device->floor_id=$request->floor_id;
                $device->floorCords=$request->floorCords;
                $device->lab_id=$request->lab_id;
            
            
                $device->deviceName = $request->deviceName;
                $device->category_id = $request->category_id;   
                $device->deviceCategory = $deviceCategory;    
                $device->firmwareVersion = $request->firmwareVersion;   
                $device->macAddress = $request->macAddress;
                $device->hardwareModelVersion = $request->hardwareModelVersion;  
                
                $fimwareBinFile = $request->firmwareBinFile;

                $image = $request->deviceImage;  // your base64 encoded
    
                //Image file creation
                if($image){
                    $image = str_replace('data:image/png;base64,', '', $request->deviceImage);
                    $image = str_replace(' ', '+', $image);
                    $imageName =  $request->deviceName.".png";
                    //$picture   = date('His').'-'.$filename;                
                    $path = "Customers/".$this->companyCode."/Buildings/devices";     
                    $imagePath = $path."/".$imageName;        
                    Storage::disk('public_uploads')->put($path."/".$imageName, base64_decode($image));    
                    $device->deviceImage = $imagePath;              
                }        
                
            
                $accessPath = "http://wisething.in/aideaLabs/blog/public/";
                
                //datapush file creation
                $dataPushFileName =  $request->deviceName."_DataPush.json";
                $dataPushdata = json_encode(['Element 1','Element 2','Element 3','Element 4','Element 5']);
                $dataPushUrlpath = "Customers/".$this->companyCode."/Buildings/devices/ConfigSettingFile";     
                Storage::disk('public_uploads')->put($dataPushUrlpath."/".$dataPushFileName, $dataPushdata); 
    
                
                //firmwarepush file creation
                
                if($fimwareBinFile){
                    $firmwarePushdata = str_replace('data:application/octet-stream;base64,', '', $request->firmwareBinFile);
                    $firmwarePushdata = str_replace(' ', '+', $firmwarePushdata);
                    $firmwarePushFileName =  $request->deviceName."_firmware.bin";           
                    //$firmwarePushUrlpath = "Customers/".$this->companyCode."/Buildings/devices/ConfigSettingFile/dataPush".$firmwarePushFileName; 
                    $firmwarePushUrlpath = $firmwarePushFileName; 
                    Storage::disk('public_uploads')->put($accessPath.$firmwarePushUrlpath."/".$firmwarePushFileName, base64_decode($firmwarePushdata)); 
                }
                
                
                $device->deviceTag =  $request->deviceTag;  
                $device->nonPollingPriority =  $request->nonPollingPriority;  
                $device->pollingPriority =  $request->pollingPriority;  
                
                $device->dataPushUrl = $accessPath.$dataPushUrlpath."/".$dataPushFileName;
                $device->firmwarePushUrl = $accessPath.$firmwarePushUrlpath."/".$firmwarePushFileName;
                $device->binFileName = $request->binFileName;
                $device->xAxisTimeInterval = $request->xAxisTimeInterval;

                
                $device->save();
                $response = [
                    "message" => "Device name updated successfully"
                ];
                $status = 201;   
            }    

        }catch(Exception $e){
            $response = [
                "message"=>$e->getMessage()
            ];            
            $status = 406;
        }catch(QueryException $e){
            $response = [
                "error" => $e->errorInfo
            ];
            $status = 406; 
      }
      return response($response,$status);
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


    public function updateDeviceMode(Request $request,  $id)
    {
        $date = new DateTime('Asia/Kolkata');      
        $current_time = $date->format('Y-m-d H:i:s');
        $user = $request->header('Userid');
        
        try{
            $device = Device::find($id);
            if(!$device){
                throw new exception("Device name not found");
            }          

            if($device){        
                $disconnectedStatus = $device->disconnectedStatus;
                $deviceMode = $request->deviceMode;

                
                if($deviceMode == "config"){
                    if($disconnectedStatus == 1){
                        $response = [
                            "message" => "Device is not connected",
                            "deviceMode"=> $request->deviceMode,
                            "deviceId"=>$id
                        ];
                        $status = 500;
                    }else{
                        $device->deviceMode = $request->deviceMode;   
                        $device->modeChangedUser = $user;
                        $device->save();
                        
                        $response = [
                            "message" => "Device mode updated successfully",
                            "deviceMode"=> $request->deviceMode,
                            "deviceId"=>$id
                        ];
                        $status = 200;
                    }
                    
                }else if($deviceMode == "firmwareUpgradation"){
                    if($disconnectedStatus == 1){
                        $response = [
                            "message" => "Device is not connected",
                            "deviceMode"=> $request->deviceMode,
                            "deviceId"=>$id
                        ];
                        $status = 500;
                    }else{
                        $device->deviceMode = $request->deviceMode;
                        $device->modeChangedUser = $user;
                        $device->save();
                        
                        $response = [
                            "message" => "Device mode updated successfully",
                            "deviceMode"=> $request->deviceMode,
                            "deviceId"=>$id
                        ];
                        $status = 200;
                    }
                    
                }else if($deviceMode == "bumpTest"){
                    if($disconnectedStatus == 1){
                        $response = [
                            "message" => "Device is not connected",
                            "deviceMode"=> $request->deviceMode,
                            "deviceId"=>$id
                        ];
                        $status = 500;
                    }else{
                        $device->deviceMode = $request->deviceMode;
                        $device->modeChangedUser = $user;
                        $device->save();
                        
                        $response = [
                            "message" => "Device mode updated successfully",
                            "deviceMode"=> $request->deviceMode,
                            "deviceId"=>$id
                        ];
                        $status = 200;
                    }
                    
                }else if($deviceMode == "debug"){
                    if($disconnectedStatus == 1){
                        $response = [
                            "message" => "Device is not connected",
                            "deviceMode"=> $request->deviceMode,
                            "deviceId"=>$id
                        ];
                        $status = 500;
                    }else{
                        $device->deviceMode = $request->deviceMode;
                        $device->modeChangedUser = $user;
                        $device->save();
                        
                        $response = [
                            "message" => "Device mode updated successfully",
                            "deviceMode"=> $request->deviceMode,
                            "deviceId"=>$id
                        ];
                        $status = 200;
                    }
                    
                }else{
                    $device->deviceMode = $request->deviceMode;
                    $device->modeChangedDateTime = $current_time;
                    $device->modeChangedUser = $user;
                    $device->save();
                    
                    $response = [
                        "message" => "Device mode updated successfully",
                        "deviceMode"=> $request->deviceMode,
                        "deviceId"=>$id
                    ];
                    $status = 200;       
                    
                    // Event logs //01-06-2023
                    $logController = new EventLogController();
                    $eventDetails = [
                        "deviceName" => $device->deviceName,
                        "mode" => $request->deviceMode,
                    ];
                    
                    $logController->addLog($request, 'Enable / Disable Mode', $eventDetails);
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
    
    
    // vaisjak 22-05-2023
    public function updateDeviceModeLog($request, $latestMode)
    {
        $location = DB::table('devices')->where('id', $request->device_id)->first();
        
        $log = new DeviceModeLogs;
        
        $log->companyCode = $location->companyCode;
        $log->locationId = $location->location_id;
        $log->branchId = $location->branch_id;
        $log->facilityId  = $location->facility_id;
        $log->buildingId  = $location->building_id;
        $log->floorId  = $location->floor_id;
        $log->labId = $location->lab_id;
        $log->deviceId  = $location->deviceId  ;
        // $log->sensorId  = $location->id;
        $log->previousMode  = $location->deviceMode;
        $log->updatedMode  = $latestMode;
        $log->userEmail = $request->header('Userid');
        $log->save();
    }
    
    
    public function binUpdate(Request $request){
        $fimwareBinFile = $request->firmwareBinFile;
        if($fimwareBinFile){
            $firmwarePushdata = str_replace('data:application/octet-stream;base64,', '', $request->firmwareBinFile);
            $firmwarePushdata = str_replace(' ', '+', $firmwarePushdata);
            $firmwarePushFileName =  $request->deviceName."_firmware.bin";           
            $firmwarePushUrlpath = "Binfile"; 
            Storage::disk('public_uploads')->put($firmwarePushUrlpath."/".$firmwarePushFileName, base64_decode($firmwarePushdata));
            return response("updated", 200);
        }
    }
    
    
    public function update1(Request $request, $id)
    {
        try{
            $deviceDataNotFound = Device::find($id);
                
            if(!$deviceDataNotFound){
                throw new Exception("Device data not found");
            }

            $deviceDataFound = DB::table('devices')
                ->where('companyCode', '=', $this->companyCode)  
                ->where('location_id', '=', $request->location_id)             
                ->where('branch_id', '=', $request->branch_id)             
                ->where('facility_id', '=', $request->facility_id)             
                ->where('building_id', '=', $request->building_id)   
                ->where('category_id', '=', $request->category_id)     
                ->where('deviceName', '=', $request->deviceName) 
                ->where('id','<>',$id)                                             
                ->first();                                  
                
            if($deviceDataFound){
                throw new Exception("Duplicate entry for device name ");
            }

            $device = Device::find($id);
            if($device){
                
                 $firmwareVersionDataFound = DB::table('firmware_version_reports')
                ->where('firmwareVersion', '=', $request->firmwareVersion)  
                ->first();   
                
                $categories = Categories::where('id',$request->category_id)->first();
                $deviceCategory = $categories->categoryName;
            
            
                $device->companyCode = $this->companyCode;
                $device->location_id = $request->location_id;   
                $device->branch_id = $request->branch_id;            
                $device->facility_id = $request->facility_id;
                $device->building_id = $request->building_id;
                
                $device->floor_id=$request->floor_id;
                $device->floorCords=$request->floorCords;
                $device->lab_id=$request->lab_id;
            
            
                $device->deviceName = $request->deviceName;
                $device->category_id = $request->category_id;   
                $device->deviceCategory = $deviceCategory;    
                $device->firmwareVersion = $request->firmwareVersion;   
                $device->macAddress = $request->macAddress;  
                $device->hardwareModelVersion = $request->hardwareModelVersion;  
                
                $fimwareBinFile = $request->firmwareBinFile;

                $image = $request->deviceImage;  // your base64 encoded
    
                //Image file creation
                if($image){
                    $image = str_replace('data:image/png;base64,', '', $request->deviceImage);
                    $image = str_replace(' ', '+', $image);
                    $imageName =  $request->deviceName.".png";
                    //$picture   = date('His').'-'.$filename;                
                    $path = "Customers/".$this->companyCode."/Buildings/devices";     
                    $imagePath = $path."/".$imageName;        
                    Storage::disk('public_uploads')->put($path."/".$imageName, base64_decode($image));    
                    $device->deviceImage = $imagePath;              
                }        
                
            
                $accessPath = "http://wisething.in/aideaLabs/blog/public/";
                
                //datapush file creation
                $dataPushFileName =  $request->deviceName."_DataPush.json";
                $dataPushdata = json_encode(['Element 1','Element 2','Element 3','Element 4','Element 5']);
                $dataPushUrlpath = "Customers/".$this->companyCode."/Buildings/devices/ConfigSettingFile";     
                Storage::disk('public_uploads')->put($dataPushUrlpath."/".$dataPushFileName, $dataPushdata); 
    
                
                //firmwarepush file creation
                
                if($fimwareBinFile){
         
                    $firmwarePushdata = str_replace('data:application/octet-stream;base64,', '', $request->firmwareBinFile);
                    $firmwarePushdata = str_replace(' ', '+', $firmwarePushdata);
                    $firmwarePushFileName =  $request->deviceName."_firmware.bin";           
                    $firmwarePushUrlpath = "ConfigSettingFile/"; 
                    Storage::disk('public_uploads')->put($firmwarePushUrlpath."/".$firmwarePushFileName, base64_decode($firmwarePushdata)); 
                    $device->firmwarePushUrl = $accessPath.$firmwarePushUrlpath.$firmwarePushFileName;
                }
                
                $device->deviceTag =  $request->deviceTag;  
                $device->nonPollingPriority =  $request->nonPollingPriority;  
                $device->pollingPriority =  $request->pollingPriority;
                // $device->dataPushUrl = $accessPath.$dataPushUrlpath."/".$dataPushFileName;
                // $device->firmwarePushUrl = $accessPath.$firmwarePushUrlpath.$firmwarePushFileName;
                 $device->binFileName = $request->binFileName;
                $device->xAxisTimeInterval = $request->xAxisTimeInterval;
                
                $this->deviceConfig($device, $request->header('Userid'));
                
                if($device->save()){
                    // if(!$firmwareVersionDataFound)      // commented because firmware logs are done in event log controller
                    // {
                    //     $FirmwareVersionChangeLog = new FirmwareVersionChangeLog;
                    //     $FirmwareVersionChangeLog->companyCode = $this->companyCode;
                    //     $FirmwareVersionChangeLog->device_id = $id;
                    //     $FirmwareVersionChangeLog->deviceName = $request->deviceName;
                    //     $FirmwareVersionChangeLog->firmwareVersion =$request->firmwareVersion;
                    //     $FirmwareVersionChangeLog->save();
                    // }
                }
                
                // Modified by vaishak 15-02-2022
                $deviceModelLog = DB::table('device_model_logs')->where('deviceId',$request->id)->get();
                
                if(count($deviceModelLog)<=0){
                    $model = new DeviceModelLog;
                    $model->companyName = $request->header('companyCode');
                    $model->deviceId = $request->id;
                    $model->deviceName = $request->deviceName;
                    $model->deviceModel = $request->hardwareModelVersion;
                    
                    $model->save();
                    
                }else{
                    $deviceModelLog1 = DB::table('device_model_logs')->where('deviceId',$request->id)->latest()->first();
                    
                    if($deviceModelLog1->deviceModel != $request->hardwareModelVersion){
                        
                        $model = new DeviceModelLog;
                        $model->companyName = $request->header('companyCode');
                        $model->deviceId = $request->id;
                        $model->deviceName = $request->deviceName;
                        $model->deviceModel = $request->hardwareModelVersion;
                        
                        $model->save();
                    }
                }
                
                // Event logs 31-05-2023
                $affectedColumns = [];
                foreach ($device->getChanges() as $attribute => $value) {
                    if ($attribute !== 'updated_at') {
                        $affectedColumns[$attribute] = $value;
                    }
                }
                $affectedColumns["Device Name"] = $request->deviceName;
                
                $logController = new EventLogController();
                $logController->addLog($request, 'Device Config', $affectedColumns);
                
                $response = [
                    "message" => "Successfully updated",
                    "data" => $affectedColumns
                ];
                $status = 201; 
                
            }    
            
        }catch(Exception $e){
            $response = [
                "message"=>$e->getMessage()
            ];            
            $status = 406;
            
        }catch(QueryException $e){
            $response = [
                "error" => $e->errorInfo
            ];
            $status = 406; 
        }
        
        return response($response,$status);
    }
    
    public function deviceConfig($device, $userEmail)
    {
        $deviceConfig = new deviceConfigLogs;
        
        $deviceConfig->companyCode = $device->companyCode;
        $deviceConfig->locationId = $device->location_id;   
        $deviceConfig->branchId = $device->branch_id;            
        $deviceConfig->facilityId = $device->facility_id;
        $deviceConfig->buildingId = $device->building_id;
        $deviceConfig->floorId = $device->floor_id;
        $deviceConfig->labId = $device->lab_id;
        $deviceConfig->deviceName = $device->deviceName;
        $deviceConfig->deviceCategory = $device->deviceCategory;
        $deviceConfig->firmwareVersion = $device->firmwareVersion;   
        $deviceConfig->macAddress = $device->macAddress;  
        $deviceConfig->hardwareModelVersion = $device->hardwareModelVersion;  
        $deviceConfig->pollingPriority = $device->pollingPriority;   
        $deviceConfig->nonPollingPriority = $device->nonPollingPriority;   
        $deviceConfig->userEmail = $userEmail;
        
        $deviceConfig->save();
    }
}











// https://varmatrix.com/Aqms/api/calibrationTestResult/add
// {
//     "sensorTag":"pm10",
//     "name":"pm",   
//     "model":"25",   
//     "testResult":"pass",   
//     "calibrationDate":"22/08/2022",
//     "nextDueDate":"23/08/2022"      
// }