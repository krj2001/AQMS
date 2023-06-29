<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\LocationDetailLogs;
use Exception;
use Illuminate\Database\QueryException;


class LocationLogController extends Controller
{
    
    public function storeLocationLog($request, $data, $locationCategory)
    {
        $name = $locationCategory.'Name';
        
        if($data->$name != $request->$name) {
            $log = new LocationDetailLogs;
            
            $log->companyCode = $request->header('Companycode');
            $log->locationId = $request->location_id;
            $log->branchId = $request->branch_id;
            $log->facilityId = $request->facility_id;
            $log->buildingId = $request->building_id;
            $log->floorId = $request->floor_id;
            $log->labId = $request->lab_id;
            $log->locationCategory = $locationCategory;
            $log->previousName = $data->$name;
            $log->updatedName = $request->$name;
            $log->userEmail = $request->header('Userid');
            
            if($locationCategory == 'facility') {
                $log->facilityId = $request->facilityId;
            }
            
            if($locationCategory == 'building') {
                $log->buildingId = $request->buildingId;
            }
            
            if($locationCategory == 'labDep') {
                $log->labId = $request->labid;
            }
            $log->save();
        }
    }
}