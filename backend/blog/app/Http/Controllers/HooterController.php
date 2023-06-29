<?php

namespace App\Http\Controllers;
use App\Exports\CalibrationResultExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Hooter;


class HooterController extends Controller
{
    
    public function store(Request $request)
    {
        $hooter = new Hooter;
        $hooter->buildingId = $request->buildingId;
        $hooter->floorId = $request->floorId;
        $hooter->zoneId = $request->zoneId;
        $hooter->save();
        
    }   
    
    
    
    public function index(Request $request)
    {
        $building = $request->building_id;
        $floor = $request->floor_id;
        $zone = $request->lab_id;
    
        
        
        if($building && $floor && $zone) {
            $query = 'zone';
            
        }else if($building && $floor) {
            $query = $this->fetch($request, 'floor', $floor);
            
        }else if($building) {
           $query = $this->fetch($request, 'building', $building);
            
        }
        
        
        return $query;
    }
   
    
    public function fetch($request, $column, $value)
    {
        $track = 0;
        
        if($column == 'building') {
            $query = DB::table('hooters')->where('buildingId', $value)->get();
            if(count($query) > 0) {
                $track++;
            }
            
        } elseif($column == 'floor') {
            $floor = DB::table('hooters')->where('floorId', $value)->get();
            $building = DB::table('hooters')->where('buildingId', $request->building_id)->where('floorId', null)->get();
             if(count($floor) > 0 || count($building) > 0) {
                $track++;
            }
        }
        
        
        if($track > 0) {
            return 'false';
            
        } else {
            return 'true';
        }
        
    }
}














