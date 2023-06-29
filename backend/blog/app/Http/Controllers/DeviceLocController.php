<?php

namespace App\Http\Controllers;

use App\Http\Controllers\UtilityController;
use App\Models\DeviceLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Exception;
use Illuminate\Database\QueryException;

class DeviceLocController extends Controller
{
    protected $companyCode = "";    

    function __construct(Request $request) {
        $getData = new UtilityController($request);
        $this->companyCode = $getData->getCompanyCode();        
    }

    public function store(Request $request)
    {
        
        try{      
           
            $device = new DeviceLocation;
            $device->companyCode = $this->companyCode;
            $device->location_id = $request->location_id;   
            $device->branch_id = $request->branch_id;            
            $device->facility_id = $request->facility_id;
            $device->building_id = $request->building_id;
            $device->floor_id = $request->floor_id;
            $device->lab_id = $request->lab_id;   
            $device->category_id = $request->category_id;   
            $device->categoryName = $request->categoryName;   
            $device->device_id = $request->device_id;  
            $device->deviceName = $request->deviceName;
            $device->description = $request->description;   
            $device->assetTag = $request->assetTag;   
            $device->macAddress = $request->macAddress;  
            $device->deviceIcon = $request->deviceIcon; // your base64 encoded     
            $device->floorCords =  $request->floorCords;  
            $device->save();
            $response = [
                "message" => "Device Loc added successfully"
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
    
    
    
    public function index(Request $request)
    {
        $query = DeviceLocation::query();
        
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
    
    
    public function update(Request $request, $id)
    {
        try{

            $deviceLocDataNotFound = DeviceLocation::find($id);
                
            if(!$deviceLocDataNotFound){
                throw new Exception("Device Location data not found");
            }


            // $deviceDataFound = DB::table('devices')
            //     ->where('companyCode', '=', $this->companyCode)  
            //     ->where('location_id', '=', $request->location_id)             
            //     ->where('branch_id', '=', $request->branch_id)             
            //     ->where('facility_id', '=', $request->facility_id)             
            //     ->where('building_id', '=', $request->building_id)     
            //     ->where('category_id', '=', $request->category_id)                     
            //     ->where('deviceName', '=', $request->deviceName) 
            //     ->where('id','<>',$id)                                             
            //     ->first();                                  
                
            // if($deviceDataFound){
            //     throw new Exception("Duplicate entry for device name ");
            // }

            $deviceLoc = DeviceLocation::find($id);
            if($deviceLoc){
                $deviceLoc->companyCode = $this->companyCode;
                $deviceLoc->location_id = $request->location_id;   
                $deviceLoc->branch_id = $request->branch_id;            
                $deviceLoc->facility_id = $request->facility_id;
                $deviceLoc->building_id = $request->building_id;
                $deviceLoc->floor_id = $request->floor_id;
                $deviceLoc->lab_id = $request->lab_id;   
                $deviceLoc->category_id = $request->category_id;   
                $deviceLoc->categoryName = $request->categoryName;   
                $deviceLoc->device_id = $request->device_id;  
                $deviceLoc->deviceName = $request->deviceName;
                $deviceLoc->description = $request->description;   
                $deviceLoc->assetTag = $request->assetTag;   
                $deviceLoc->macAddress = $request->macAddress;  
                $deviceLoc->deviceIcon = $request->deviceIcon; // your base64 encoded     
                $deviceLoc->floorCords =  $request->floorCords;            
                $deviceLoc->save();


                $response = [
                    "message" => "Device Loc updated successfully"
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
    
    
    public function destroy($id)
    {
        try{
            $device = DeviceLocation::find($id);
            if(!$device){
                throw new exception("Device location  not found");
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
