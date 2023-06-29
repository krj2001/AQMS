<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\UtilityController;
use App\Http\Controllers\UTILITY\DataUtilityController;


use Illuminate\Database\QueryException;

use App\Models\Location;
use App\Models\Branch;
use App\Models\Facilities;
use App\Models\Building;
use App\Models\Floor;
use App\Models\labDepartment;
use App\Models\Device;
use App\Models\Sensor;
use Illuminate\Support\Facades\DB;


class DeviceDataController extends Controller{
    
    protected $companyCode = "";  
    protected $table = "";      

    function __construct(Request $request) {
        $getData = new UtilityController($request);
        $this->companyCode = $getData->getCompanyCode();        
    }

    public function searchDeviceData(Request $request){
  
         if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !="" && $request->lab_id !=""){
                    $query = DB::table('devices')
                    ->select('id','deviceName')
                    ->where('companyCode','=',$this->companyCode)   
                    ->where('location_id','=',$request->location_id)
                    ->where('branch_id','=',$request->branch_id)
                    ->where('facility_id','=',$request->facility_id)
                    ->where('building_id','=',$request->building_id)
                    ->where('floor_id','=',$request->floor_id)
                    ->where('lab_id','=',$request->lab_id)
                    ->get();
                   
            
                }else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !=""){
                   $query = DB::table('devices')
                    ->select('id','deviceName')
                    ->where('companyCode','=',$this->companyCode)   
                    ->where('location_id','=',$request->location_id)
                    ->where('branch_id','=',$request->branch_id)
                    ->where('facility_id','=',$request->facility_id)
                    ->where('building_id','=',$request->building_id)
                    ->where('floor_id','=',$request->floor_id)
                    ->get();
                   

                }else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !=""){
                 $query = DB::table('devices')
                    ->select('id','deviceName')
                    ->where('companyCode','=',$this->companyCode)   
                    ->where('location_id','=',$request->location_id)
                    ->where('branch_id','=',$request->branch_id)
                    ->where('facility_id','=',$request->facility_id)
                    ->where('building_id','=',$request->building_id)
                    ->get();
                   
                }else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""){
                    
                    $query = DB::table('devices')
                    ->select('id','deviceName')
                    ->where('companyCode','=',$this->companyCode)   
                    ->where('location_id','=',$request->location_id)
                    ->where('branch_id','=',$request->branch_id)
                    ->where('facility_id','=',$request->facility_id)
                    ->get();
                  
                }else if($request->location_id != "" && $request->branch_id != ""){
                    
                  $query = DB::table('devices')
                    ->select('id','deviceName')
                    ->where('companyCode','=',$this->companyCode)   
                    ->where('location_id','=',$request->location_id)
                    ->get();
                }else if($request->location_id != ""){
                     $query = DB::table('devices')
                    ->select('id','deviceName')
                    ->where('companyCode','=',$this->companyCode)   
                    ->where('location_id','=',$request->location_id)
                    ->get();
                }
                else {
                   $query = DB::table('devices')
                    ->select('id','deviceName')
                    ->where('companyCode','=',$this->companyCode)
                     ->get();
                }   
                
                
                // $getData = new DataUtilityController($request,$query);
                
                // $response = $getData->getData();
                
                $response = [
                     "data"=>$query
                    ];
                
                
                
                
                $status = 200;
               
                return response($response,$status);
            }
            
    
}

?>