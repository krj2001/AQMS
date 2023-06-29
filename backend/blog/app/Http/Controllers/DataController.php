<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\UtilityController;
use App\Http\Controllers\UTILITY\DataUtilityController;
use Carbon\Carbon;


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


class DataController extends Controller{
    
    protected $companyCode = "";  
    protected $table = "";      

    function __construct(Request $request) {
        $getData = new UtilityController($request);
        $this->companyCode = $getData->getCompanyCode();        
    }

    public function search(Request $request){
       
        $sensorCatergoryBased = "";
      
        if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !="" && $request->lab_id !="" && $request->device_id !=""){
            // $query = Sensor::query();
            // $query->where('companyCode','=',$this->companyCode);    
            // $query->where('location_id','=',$request->location_id); 
            // $query->where('branch_id','=',$request->branch_id); 
            // $query->where('facility_id','=',$request->facility_id); 
            // $query->where('building_id','=',$request->building_id); 
            // $query->where('floor_id','=',$request->floor_id); 
            // $query->where('lab_id','=',$request->lab_id); 
            // $query->where('device_id','=',$request->device_id); 
            // $this->table  = "Sensors";
            
            
            $query = Sensor::select('*');
                    $query->where('companyCode','=',$this->companyCode);
                    $query->where('location_id','=',$request->location_id);
                    $query->where('branch_id','=',$request->branch_id);
                    $query->where('facility_id','=',$request->facility_id);
                    $query->where('building_id','=',$request->building_id);
                    $query->where('floor_id','=',$request->floor_id);
                    $query->where('lab_id','=',$request->lab_id);
                    $query->where('deviceId','=',$request->device_id);
                    $query->where('sensorOutput','=','Digital');
                    
                    $perPage = 20;
                    $page = $request->input(key:'page', default:1);
                    $total = $query->count();
                    $result = $query->offset(value:($page - 1) * $perPage)->limit($perPage)->get();
                                      
                    $query1 = Sensor::select('*');
                    $query1->where('companyCode','=',$this->companyCode);
                    $query1->where('location_id','=',$request->location_id);
                    $query1->where('branch_id','=',$request->branch_id);
                    $query1->where('facility_id','=',$request->facility_id);
                    $query1->where('building_id','=',$request->building_id);
                    $query1->where('floor_id','=',$request->floor_id);
                    $query1->where('lab_id','=',$request->lab_id);
                    $query1->where('deviceId','=',$request->device_id);
                    $query1->where('sensorOutput','=','Analog');
                    
                    $perPage = 20;
                    $page = $request->input(key:'page', default:1);
                    $total1 = $query1->count();
                    $result1 = $query1->offset(value:($page - 1) * $perPage)->limit($perPage)->get();
                    
                    $query2 = Sensor::select('*');
                    $query2->where('companyCode','=',$this->companyCode);
                    $query2->where('location_id','=',$request->location_id);
                    $query2->where('branch_id','=',$request->branch_id);
                    $query2->where('facility_id','=',$request->facility_id);
                    $query2->where('building_id','=',$request->building_id);
                    $query2->where('floor_id','=',$request->floor_id);
                    $query2->where('lab_id','=',$request->lab_id);
                    $query2->where('deviceId','=',$request->device_id);
                    $query2->where('sensorOutput','=','Modbus');
                    
                    $perPage = 20;
                    $page = $request->input(key:'page', default:1);
                    $total2 = $query2->count();
                    $result2 = $query2->offset(value:($page - 1) * $perPage)->limit($perPage)->get();
            
                    $sensorCatergoryBased = "sensor";
                    
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
            
                    return response($response,200);
            
            
    
        }else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !="" && $request->lab_id !="" && $request->sensorCategoryId){
            $query = Device::query();
            $query->where('companyCode','=',$this->companyCode);    
            $query->where('location_id','=',$request->location_id); 
            $query->where('branch_id','=',$request->branch_id); 
            $query->where('facility_id','=',$request->facility_id); 
            $query->where('building_id','=',$request->building_id); 
            $query->where('floor_id','=',$request->floor_id); 
            $query->where('lab_id','=',$request->lab_id); 
            $query->where('category_id','=',$request->sensorCategoryId); 
            $this->table  = "categoryDevices";   
    
        }else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !="" && $request->lab_id !=""){
            $query = Device::query();
            $query->where('companyCode','=',$this->companyCode);    
            $query->where('location_id','=',$request->location_id); 
            $query->where('branch_id','=',$request->branch_id); 
            $query->where('facility_id','=',$request->facility_id); 
            $query->where('building_id','=',$request->building_id); 
            $query->where('floor_id','=',$request->floor_id); 
            $query->where('lab_id','=',$request->lab_id);
            $this->table  = "Devices";   
            // $response = $query->get();
            // $status=200;
            // return response($response,$status);
    
        }else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !=""){
            $query = labDepartment::query();
            $query->where('companyCode','=',$this->companyCode);    
            $query->where('location_id','=',$request->location_id); 
            $query->where('branch_id','=',$request->branch_id); 
            $query->where('facility_id','=',$request->facility_id); 
            $query->where('building_id','=',$request->building_id); 
            $query->where('floor_id','=',$request->floor_id); 
            $this->table  = "labDepartment";   
    
        }else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !=""){
            $query = Floor::query();
            $query->where('companyCode','=',$this->companyCode);    
            $query->where('location_id','=',$request->location_id); 
            $query->where('branch_id','=',$request->branch_id); 
            $query->where('facility_id','=',$request->facility_id); 
            $query->where('building_id','=',$request->building_id); 
            $this->table  = "floors";   
    
        }else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""){
            $query = Building::query();
            $query->where('companyCode','=',$this->companyCode);    
            $query->where('location_id','=',$request->location_id); 
            $query->where('branch_id','=',$request->branch_id); 
            $query->where('facility_id','=',$request->facility_id); 
            $this->table  = "Buildings";   
    
        }else if($request->location_id != "" && $request->branch_id != ""){
            $query = Facilities::query();
            $query->where('companyCode','=',$this->companyCode);    
            $query->where('location_id','=',$request->location_id); 
            $query->where('branch_id','=',$request->branch_id); 
            $this->table  = "Facility";   
    
        }else if($request->location_id != ""){
            $query = Branch::query();
            $query->where('companyCode','=',$this->companyCode);    
            $query->where('location_id','=',$request->location_id); 
            $this->table  = "Branches";
           
        }
        else{
            $query = Location::query(); 
            $query->where('companyCode','=',$this->companyCode); 
            $this->table  = "Locations";         
        }   
        
        
        $getData = new DataUtilityController($request,$query);
        
        $response = $getData->getData();
        
        
        
        
        $status = 200;
       
        return response($response,$status);
    }
    
    
    public function searchDevice(Request $request){
       
        $sensorCatergoryBased = "";
      
        if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !="" && $request->lab_id !="" && $request->device_id !=""){
            // $query = Sensor::query();
            // $query->where('companyCode','=',$this->companyCode);    
            // $query->where('location_id','=',$request->location_id); 
            // $query->where('branch_id','=',$request->branch_id); 
            // $query->where('facility_id','=',$request->facility_id); 
            // $query->where('building_id','=',$request->building_id); 
            // $query->where('floor_id','=',$request->floor_id); 
            // $query->where('lab_id','=',$request->lab_id); 
            // $query->where('device_id','=',$request->device_id); 
            // $this->table  = "Sensors";
            
            
            $query = Sensor::select('*');
                    $query->where('companyCode','=',$this->companyCode);
                    $query->where('location_id','=',$request->location_id);
                    $query->where('branch_id','=',$request->branch_id);
                    $query->where('facility_id','=',$request->facility_id);
                    $query->where('building_id','=',$request->building_id);
                    $query->where('floor_id','=',$request->floor_id);
                    $query->where('lab_id','=',$request->lab_id);
                    $query->where('deviceId','=',$request->device_id);
                    $query->where('sensorOutput','=','Digital');
                    
                    $perPage = 20;
                    $page = $request->input(key:'page', default:1);
                    $total = $query->count();
                    $result = $query->offset(value:($page - 1) * $perPage)->limit($perPage)->get();
                                      
                    $query1 = Sensor::select('*');
                    $query1->where('companyCode','=',$this->companyCode);
                    $query1->where('location_id','=',$request->location_id);
                    $query1->where('branch_id','=',$request->branch_id);
                    $query1->where('facility_id','=',$request->facility_id);
                    $query1->where('building_id','=',$request->building_id);
                    $query1->where('floor_id','=',$request->floor_id);
                    $query1->where('lab_id','=',$request->lab_id);
                    $query1->where('deviceId','=',$request->device_id);
                    $query1->where('sensorOutput','=','Analog');
                    
                    $perPage = 20;
                    $page = $request->input(key:'page', default:1);
                    $total1 = $query1->count();
                    $result1 = $query1->offset(value:($page - 1) * $perPage)->limit($perPage)->get();
                    
                    $query2 = Sensor::select('*');
                    $query2->where('companyCode','=',$this->companyCode);
                    $query2->where('location_id','=',$request->location_id);
                    $query2->where('branch_id','=',$request->branch_id);
                    $query2->where('facility_id','=',$request->facility_id);
                    $query2->where('building_id','=',$request->building_id);
                    $query2->where('floor_id','=',$request->floor_id);
                    $query2->where('lab_id','=',$request->lab_id);
                    $query2->where('deviceId','=',$request->device_id);
                    $query2->where('sensorOutput','=','Modbus');
                    
                    $perPage = 20;
                    $page = $request->input(key:'page', default:1);
                    $total2 = $query2->count();
                    $result2 = $query2->offset(value:($page - 1) * $perPage)->limit($perPage)->get();
            
                    $sensorCatergoryBased = "sensor";
                    
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
            
                    return response($response,200);
            
            
    
        }else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !="" && $request->lab_id !="" && $request->sensorCategoryId){
            $query = Device::query();
            $query->where('companyCode','=',$this->companyCode);    
            $query->where('location_id','=',$request->location_id); 
            $query->where('branch_id','=',$request->branch_id); 
            $query->where('facility_id','=',$request->facility_id); 
            $query->where('building_id','=',$request->building_id); 
            $query->where('floor_id','=',$request->floor_id); 
            $query->where('lab_id','=',$request->lab_id); 
            $query->where('category_id','=',$request->sensorCategoryId); 
            $this->table  = "categoryDevices";   
    
        }else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !="" && $request->lab_id !=""){
            $query = Device::query();
            $query->where('companyCode','=',$this->companyCode);    
            $query->where('location_id','=',$request->location_id); 
            $query->where('branch_id','=',$request->branch_id); 
            $query->where('facility_id','=',$request->facility_id); 
            $query->where('building_id','=',$request->building_id); 
            $query->where('floor_id','=',$request->floor_id); 
            $query->where('lab_id','=',$request->lab_id);
            $this->table  = "Devices"; 
            
            $devices = $query->get();
            
            
            $deviceCount = count($devices);
            
            $deviceData = array();
            
            for($x=0;$x<$deviceCount;$x++){
                  $deviceAlertCount = DB::table('alert_crons')
                                        ->selectRaw('count(*) as alertCount')
                                        ->where('deviceId','=',$devices[$x]->id)
                                        ->where('status','=','0')
                                        ->get();
                  
                  $devices[$x]['alertDataCount'] = $deviceAlertCount[0]->alertCount;
              
                $deviceData[] = $devices[$x];
            }
            
            $response = $deviceData;
    
        }else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !="" && $request->floor_id !=""){
            $query = labDepartment::query();
            $query->where('companyCode','=',$this->companyCode);    
            $query->where('location_id','=',$request->location_id); 
            $query->where('branch_id','=',$request->branch_id); 
            $query->where('facility_id','=',$request->facility_id); 
            $query->where('building_id','=',$request->building_id); 
            $query->where('floor_id','=',$request->floor_id); 
            $this->table  = "labDepartment";   
    
        }else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""  && $request->building_id !=""){
            $query = Floor::query();
            $query->where('companyCode','=',$this->companyCode);    
            $query->where('location_id','=',$request->location_id); 
            $query->where('branch_id','=',$request->branch_id); 
            $query->where('facility_id','=',$request->facility_id); 
            $query->where('building_id','=',$request->building_id); 
            $this->table  = "floors";   
    
        }else if($request->location_id != "" && $request->branch_id != "" && $request->facility_id != ""){
            $query = Building::query();
            $query->where('companyCode','=',$this->companyCode);    
            $query->where('location_id','=',$request->location_id); 
            $query->where('branch_id','=',$request->branch_id); 
            $query->where('facility_id','=',$request->facility_id); 
            $this->table  = "Buildings";   
    
        }else if($request->location_id != "" && $request->branch_id != ""){
            $query = Facilities::query();
            $query->where('companyCode','=',$this->companyCode);    
            $query->where('location_id','=',$request->location_id); 
            $query->where('branch_id','=',$request->branch_id); 
            $this->table  = "Facility";   
    
        }else if($request->location_id != ""){
            $query = Branch::query();
            $query->where('companyCode','=',$this->companyCode);    
            $query->where('location_id','=',$request->location_id); 
            $this->table  = "Branches";
           
        }
        else{
            $query = Location::query(); 
            $query->where('companyCode','=',$this->companyCode); 
            $this->table  = "Locations";         
        }   
        
        
        // $getData = new DataUtilityController($request,$query);
        
        // $response = $getData->getData();
        
        
        
        
        $status = 200;
       
        return response($response,$status);
    }
    
    public function testApi()
    {
        $data = ['61','55'];
        $calculate = 0;
        $sensors = array();
        
        for($x=0; $x<count($data); $x++)
        {
            $sensors = DB::table('Aqi_values_per_deviceSensor')
                ->select('sensorId')
                ->distinct()
                ->where('facilityId', '=', $data[$x])
                ->where('AqiValue','!=',0)
                ->where('sampled_date_time', '>', Carbon::now()->subHours(24)->toDateTimeString())
                ->get();
                    
            for($i=0; $i<count($sensors); $i++)
            {
                $data = DB::table('Aqi_values_per_deviceSensor')
                    ->select(DB::raw("DATE_FORMAT(sampled_date_time, '%Y-%m-%d %H:00:00') AS hour"), DB::raw("AVG(AqiValue) AS average"))
                    ->where('sampled_date_time', '>=', Carbon::now()->subHours(16))
                    ->where('sensorId', $sensors[$i]->sensorId)
                    ->where('AqiValue','!=','0')
                    ->groupBy(DB::raw("DATE_FORMAT(sampled_date_time, '%Y-%m-%d %H:00:00')"))
                    ->orderBy('hour', 'ASC')
                    ->get();
                
                if(count($data)>3){
                    $calculate ++;
                    break;
                }
            }
            
            if($calculate > 3){
                $a[$data[$x]] = $calculate;
            }
        }
                
        return $a;
    }
    
    
    
    
    
    
    
    
}

?>