<?php

namespace App\Http\Controllers;
use App\Http\Controllers\UtilityController;

use App\Models\DeviceConfigSetup;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class DeviceConfigSetupController extends Controller
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


    public function getDeviceConfigData($id)
    {
        try{
           $deviceConfigData = DB::table('device_config_setups')
                ->select('*')
                ->where('device_id', '=', $id)->get();
            $response =  [
                'data' => $deviceConfigData,
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

    public function index()
    {
        try{
            $deviceConfigSetup = DeviceConfigSetup::all();
            if(!$deviceConfigSetup){
                throw new Exception("Data not found");
            }
            $response = [
                "data" => $deviceConfigSetup,
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
    public function DeviceConfigAddOrUpdate(Request $request)
    {

        try { 

                $deviceConfigDataFound = DB::table('device_config_setups')
                ->where('companyCode','=',$this->companyCode) 
                ->where('device_id', '=', $request->device_id)                              
                ->first();

                if(!$deviceConfigDataFound)
                  {
                    $deviceConfigSetup = new DeviceConfigSetup;
                    $deviceConfigSetup->companyCode=$this->companyCode;

                    $deviceConfigSetup->device_id=$request->device_id;  
                    $devices = Device::where('id',$request->device_id)->first();
                    
                    $deviceConfigSetup->deviceName = $devices->deviceName;

                    $deviceConfigSetup->accessType = $request->accessType;
                    $deviceConfigSetup->accessPointName = $request->accessPointName;
                    $deviceConfigSetup->ssId = $request->ssId;
                    $deviceConfigSetup->accessPointPassword = $request->accessPointPassword;
                    
                    //secondary
                    $deviceConfigSetup->accessPointNameSecondary = $request->accessPointNameSecondary;
                    $deviceConfigSetup->ssIdSecondary = $request->ssIdSecondary;
                    $deviceConfigSetup->accessPointPasswordSecondary = $request->accessPointPasswordSecondary;
    
                    $deviceConfigSetup->ftpAccountName =$request->ftpAccountName;
                    $deviceConfigSetup->userName = $request->userName;
                    $deviceConfigSetup->ftpPassword = $request->ftpPassword;
                    $deviceConfigSetup->port = $request->port;
                    $deviceConfigSetup->serverUrl = $request->serverUrl;
                    $deviceConfigSetup->folderPath = $request->folderPath;
    
                    $deviceConfigSetup->serviceProvider = $request->serviceProvider;
                    $deviceConfigSetup->apn =$request->apn;
                    $deviceConfigSetup->save();

                    $response = [
                        "message" => "Device config setup  added successfully"
                    ];
                    $status = 200; 
                  }
                  else{

                        $id = $deviceConfigDataFound->id;                        

                        $devices = Device::where('id',$request->device_id)->first();

                        $deviceConfigDataFound = DeviceConfigSetup::find($id);

                        $deviceConfigDataFound->companyCode=$this->companyCode;

                        $deviceConfigDataFound->device_id=$request->device_id; 
                        $deviceConfigDataFound->deviceName = $devices->deviceName;

                        $deviceConfigDataFound->accessType = $request->accessType;
                        $deviceConfigDataFound->accessPointName = $request->accessPointName;
                        $deviceConfigDataFound->ssId = $request->ssId;
                        $deviceConfigDataFound->accessPointPassword = $request->accessPointPassword;
                        
                        
                        //secondary
                        $deviceConfigDataFound->accessPointNameSecondary = $request->accessPointNameSecondary;
                        $deviceConfigDataFound->ssIdSecondary = $request->ssIdSecondary;
                        $deviceConfigDataFound->accessPointPasswordSecondary = $request->accessPointPasswordSecondary;

                        $deviceConfigDataFound->ftpAccountName =$request->ftpAccountName;
                        $deviceConfigDataFound->userName = $request->userName;
                        $deviceConfigDataFound->ftpPassword = $request->ftpPassword;
                        $deviceConfigDataFound->port = $request->port;
                        $deviceConfigDataFound->serverUrl = $request->serverUrl;
                        $deviceConfigDataFound->folderPath = $request->folderPath;

                        $deviceConfigDataFound->serviceProvider = $request->serviceProvider;
                        $deviceConfigDataFound->apn =$request->apn;
                        $deviceConfigDataFound->update();


                        $response = [
                            "message" => "Device config setup  updated successfully"
                        ];
                        $status = 200; 
          

                  }
           
            

        }catch (QueryException $e) {
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
     * @param  \App\Models\DeviceConfigSetup  $deviceConfigSetup
     * @return \Illuminate\Http\Response
     */
    public function show(DeviceConfigSetup $deviceConfigSetup)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DeviceConfigSetup  $deviceConfigSetup
     * @return \Illuminate\Http\Response
     */
    public function edit(DeviceConfigSetup $deviceConfigSetup)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DeviceConfigSetup  $deviceConfigSetup
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {    try {  

        $deviceConfigSetupDataFound = DeviceConfigSetup::find($id);

        if(!$deviceConfigSetupDataFound){
            throw new Exception("Data not found");
        }       

        $deviceConfigSetup = DeviceConfigSetup::find($id);    
        if($deviceConfigSetup){  

        $deviceConfigSetup->companyCode=$this->companyCode;

        $deviceConfigSetup->device_id=$request->device_Id;  
        $devices = Device::where('id',$request->device_Id)->first();
        
        $deviceConfigSetup->deviceName = $devices->deviceName;

        $deviceConfigSetup->accessType = $request->accessType;
        $deviceConfigSetup->accessPointName = $request->accessPointName;
        $deviceConfigSetup->ssId = $request->ssId;
        $deviceConfigSetup->accessPointPassword = $request->accessPointPassword;
        
        //secondary
        $deviceConfigSetup->accessPointNameSecondary = $request->accessPointNameSecondary;
        $deviceConfigSetup->ssIdSecondary = $request->ssIdSecondary;
        $deviceConfigSetup->accessPointPasswordSecondary = $request->accessPointPasswordSecondary;

        $deviceConfigSetup->ftpAccountName =$request->ftpAccountName;
        $deviceConfigSetup->userName = $request->userName;
        $deviceConfigSetup->ftpAccountPassword = $request->ftpAccountPassword;
        $deviceConfigSetup->port = $request->port;
        $deviceConfigSetup->serverUrl = $request->serverUrl;
        $deviceConfigSetup->folderPath = $request->folderPath;

        $deviceConfigSetup->serviceProvider = $request->serviceProvider;
        $deviceConfigSetup->apn =$request->apn;
        $deviceConfigSetup->save();
        $response = [
            "message" => "Config Setup  updated successfully"
        ];
        $status = 200;  
                    
                }            
            }           
            catch (QueryException $e) {
                        $response = [
                            "error" => $e->errorInfo
                        ];
                        $status = 406; 
            }catch(Exception $e){
                $response = [
                    "error" =>  $e->getMessage()
                ];    
                $status = 404;           
            }        
                    return response($response,$status);   

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DeviceConfigSetup  $deviceConfigSetup
     * @return \Illuminate\Http\Response
     */
    public function destroy(DeviceConfigSetup $deviceConfigSetup)
    {
        //
    }
    
    public function deviceDebugModeData(Request $request){
        try{
            
            $id = $request->device_id;
            $data = DB::table('deviceDebug')
                    ->select('*')
                    ->whereRaw('time_stamp >= DATE_SUB(NOW(),INTERVAL 2 MINUTE)')
                    ->where('deviceId', '=', $id)
                    ->take(1)
                    ->get();
             if(count($data) != 0){
                $response = [
                    "data"=>$data,
                    "status"=>200
                ];
                $status = 200;
            }else{
               throw new Exception("Data not found");
            }
        }catch(Exception $e){
            $response = [
                "message" =>  $e->getMessage(),
            ];    
            $status = 406;       
        }
        return response($response, $status);
    }
}
