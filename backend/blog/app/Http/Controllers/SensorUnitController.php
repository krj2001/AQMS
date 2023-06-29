<?php

namespace App\Http\Controllers;
use App\Http\Controllers\UTILITY\DataUtilityController;
use App\Models\SensorUnit;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use App\Models\SensorCategory;
use App\Http\Controllers\UtilityController;


class SensorUnitController extends Controller
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

    public function index(Request $request,$id)
    {
        try{ 
            $query = DB::table('sensor_units')
            ->select('*')
            ->where('sensorCategoryId','=',$id)->get();
            $response =  [
                'data' => $query,
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
    
    
    public function getData(Request $request)
    {
        try{ 
            $query = SensorUnit::query();
            
            if($companyCode = $this->companyCode){
                $query->where('companyCode','=',$companyCode);             
            }
            
            $getData = new DataUtilityController($request,$query);
            $response = $getData->getData();
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
    public function store(Request $request)
    {
        try{
               
            $sensorUnitDataFound = DB::table('sensor_units')    
                ->where('companyCode', '=', $this->companyCode)                  
                ->where('sensorCategoryId', '=', $request->sensorCategoryId)
                ->where('sensorName', '=', $request->sensorName)
                ->first();  

            if($sensorUnitDataFound){
                throw new Exception("Duplicate entry for sensor category name");
            } 
            
            
                $sensorUnit = new SensorUnit;
                $sensorUnit->companyCode = $this->companyCode;
                $sensorUnit->sensorCategoryId = $request->sensorCategoryId;

                $sensorCategoryDataFound = DB::table('sensor_categories')        
                            ->where('id', '=', $request->sensorCategoryId)                              
                            ->first();                 

                $sensorUnit->sensorCategoryName = $sensorCategoryDataFound->sensorName;

                $sensorUnit->sensorName = $request->sensorName;
                $sensorUnit->manufacturer = $request->manufacturer;
                $sensorUnit->partId = $request->partId;
                $sensorUnit->sensorOutput = $request->sensorOutput;
                $sensorUnit->sensorType = $request->sensorType;
                $sensorUnit->units = $request->units;
                $sensorUnit->minRatedReading = $request->minRatedReading;
                $sensorUnit->minRatedReadingChecked = $request->minRatedReadingChecked;
                $sensorUnit->minRatedReadingScale = $request->minRatedReadingScale;
                $sensorUnit->maxRatedReading = $request->maxRatedReading;
                $sensorUnit->maxRatedReadingChecked = $request->maxRatedReadingChecked;
                $sensorUnit->maxRatedReadingScale = $request->maxRatedReadingScale;
                $sensorUnit->slaveId = $request->slaveId;
                $sensorUnit->registerId = $request->registerId;
                $sensorUnit->length = $request->length;
                $sensorUnit->registerType = $request->registerType;
                $sensorUnit->conversionType = $request->conversionType;
                $sensorUnit->ipAddress = $request->ipAddress;
                $sensorUnit->subnetMask = $request->subnetMask; 
                
                
                $sensorUnit->criticalMinValue = $request->criticalMinValue;
                $sensorUnit->criticalMaxValue = $request->criticalMaxValue;
    
                $sensorUnit->warningMinValue = $request->warningMinValue;
                $sensorUnit->warningMaxValue = $request->warningMaxValue;
    
                $sensorUnit->outofrangeMinValue = $request->outofrangeMinValue;
                $sensorUnit->outofrangeMaxValue = $request->outofrangeMaxValue;
                
                $sensorUnit->isStel = $request->isStel;                
                $sensorUnit->stelDuration = $request->stelDuration;
                $sensorUnit->stelStartTime = $request->stelStartTime;
                $sensorUnit->stelType = $request->stelType;
                $sensorUnit->stelLimit = $request->stelLimit;
                $sensorUnit->stelAlert = $request->stelAlert;
                
                $sensorUnit->twaDuration = $request->twaDuration;
                $sensorUnit->twaStartTime = $request->twaStartTime;
                $sensorUnit->twaType = $request->twaType;
                $sensorUnit->twaLimit = $request->twaLimit;
                $sensorUnit->twaAlert = $request->twaAlert;
    
                $sensorUnit->alarm = $request->alarm;
                $sensorUnit->unLatchDuration = $request->unLatchDuration;  
                
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
                
                $sensorUnit->relayOutput = $request->relayOutput;
                
                $sensorUnit->bumpTestRequired = $request->bumpTestRequired;
                $sensorUnit->zeroCheckValue = $request->zeroCheckValue;
                $sensorUnit->spanCheckValue = $request->spanCheckValue;
                
                $sensorUnit->save();

                $response = [
                    "message" => "Sensor Unit added successfully",
                    
                ];
                $status = 200; 

        
        }catch (QueryException $e) {
            $response = [
                "message" => $e->errorInfo
            ];
            $status = 406; 
        }catch (Exception $e) {
            $response = [
                "message" => $e->getMessage()
            ];
            $status = 406; 
        }         
       
       return response($response,$status);  
        
       
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SensorUnit  $sensorUnit
     * @return \Illuminate\Http\Response
     */
    public function show(SensorUnit $sensorUnit)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SensorUnit  $sensorUnit
     * @return \Illuminate\Http\Response
     */
    public function edit(SensorUnit $sensorUnit)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SensorUnit  $sensorUnit
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try { 

        $sensorUnitDataFound = SensorUnit::find($id);
        if(!$sensorUnitDataFound){
            throw new Exception("Data not found");
        }
        $sensorUnitDataFound = DB::table('sensor_units')
                ->where('companyCode', '=', $this->companyCode)                  
                ->where('sensorCategoryId', '=', $request->sensorCategoryId)
                ->where('sensorName', '=', $request->sensorName)
                ->where('id','<>',$id)              
                ->first();

        if($sensorUnitDataFound) {
            throw new Exception("Duplicate entry For sensor unit");
        }

        $sensorUnit = SensorUnit::find($id);    
        if($sensorUnit){
            $sensorUnit->companyCode = $this->companyCode;
            $sensorUnit->sensorCategoryId = $request->sensorCategoryId;

            $sensorCategoryDataFound = DB::table('sensor_categories')        
                            ->where('id', '=', $request->sensorCategoryId)                              
                            ->first(); 

            $sensorUnit->sensorCategoryName = $sensorCategoryDataFound->sensorName;

            $sensorUnit->sensorName = $request->sensorName;
            $sensorUnit->manufacturer = $request->manufacturer;
            $sensorUnit->partId = $request->partId;
            $sensorUnit->sensorOutput = $request->sensorOutput;
            $sensorUnit->sensorType = $request->sensorType;
            $sensorUnit->units = $request->units;
            $sensorUnit->minRatedReading = $request->minRatedReading;
            $sensorUnit->minRatedReadingChecked = $request->minRatedReadingChecked;
            $sensorUnit->minRatedReadingScale = $request->minRatedReadingScale;
            $sensorUnit->maxRatedReading = $request->maxRatedReading;
            $sensorUnit->maxRatedReadingChecked = $request->maxRatedReadingChecked;
            $sensorUnit->maxRatedReadingScale = $request->maxRatedReadingScale;
            $sensorUnit->slaveId = $request->slaveId;
            $sensorUnit->registerId = $request->registerId;
            $sensorUnit->length = $request->length;
            $sensorUnit->registerType = $request->registerType;
            $sensorUnit->conversionType = $request->conversionType;
            $sensorUnit->ipAddress = $request->ipAddress;
            $sensorUnit->subnetMask = $request->subnetMask; 
            
            
            $sensorUnit->criticalMinValue = $request->criticalMinValue;
            $sensorUnit->criticalMaxValue = $request->criticalMaxValue;
            

            $sensorUnit->warningMinValue = $request->warningMinValue;
            $sensorUnit->warningMaxValue = $request->warningMaxValue;
           

            $sensorUnit->outofrangeMinValue = $request->outofrangeMinValue;
            $sensorUnit->outofrangeMaxValue = $request->outofrangeMaxValue;
            
            
            $sensorUnit->isStel = $request->isStel;                
            $sensorUnit->stelDuration = $request->stelDuration;
            $sensorUnit->stelStartTime = $request->stelStartTime;
            $sensorUnit->stelType = $request->stelType;
            $sensorUnit->stelLimit = $request->stelLimit;
            $sensorUnit->stelAlert = $request->stelAlert;
            
            $sensorUnit->twaDuration = $request->twaDuration;
            $sensorUnit->twaStartTime = $request->twaStartTime;
            $sensorUnit->twaType = $request->twaType;
            $sensorUnit->twaLimit = $request->twaLimit;
            $sensorUnit->twaAlert = $request->twaAlert;

            $sensorUnit->alarm = $request->alarm;
            $sensorUnit->unLatchDuration = $request->unLatchDuration;  
            
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
            
            $sensorUnit->relayOutput = $request->relayOutput;
            
            $sensorUnit->bumpTestRequired = $request->bumpTestRequired;
            $sensorUnit->zeroCheckValue = $request->zeroCheckValue;
            $sensorUnit->spanCheckValue = $request->spanCheckValue;
                
            $sensorUnit->save();

            $response = [
                "message" => "Sensor Unit updated successfully",
                
            ];
            $status = 200; 
        }

        }catch (QueryException $e) {
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
     * @param  \App\Models\SensorUnit  $sensorUnit
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      try {
        $sensorUnitDataFound = SensorUnit::find($id);
        if(!$sensorUnitDataFound){
            throw new Exception("Data not found");
        }

        $sensorUnit = SensorUnit::find($id);
        if($sensorUnit){
            $sensorUnit->delete();
            $response = [
                "message" => "Sensor Unit and related data deleted successfully"
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
    
    
    public function StelTwd(Request $request,$id)
    {
        try {
            $sensorUnitDataFound = SensorUnit::find($id);
            if(!$sensorUnitDataFound){
                throw new Exception("Data not found");
            }

            $sensorUnit = SensorUnit::find($id);
            if($sensorUnit){

                $sensorUnit->isStel = $request->isStel;                
                $sensorUnit->stelDuration = $request->stelDuration;
                $sensorUnit->stelType = $request->stelType;
                $sensorUnit->stelLimit = $request->stelLimit;
                $sensorUnit->stelAlert = $request->stelAlert;
                
                $sensorUnit->twaDuration = $request->twaDuration;
                $sensorUnit->twaType = $request->twaType;
                $sensorUnit->twaLimit = $request->twaLimit;
                $sensorUnit->twaAlert = $request->twaAlert;

                $sensorUnit->alarm = $request->alarm;
                $sensorUnit->unLatchDuration = $request->unLatchDuration;             
        
                $sensorUnit->save();
                
                $response = [
                    "message" => "data updated successfully"
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
}
