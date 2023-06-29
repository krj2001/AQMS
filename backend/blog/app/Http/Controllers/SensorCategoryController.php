<?php

namespace App\Http\Controllers;
use App\Http\Controllers\UtilityController;
use App\Http\Controllers\UTILITY\DataUtilityController;
use App\Models\SensorCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Exception;
use Illuminate\Database\QueryException;

class SensorCategoryController extends Controller
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
        $query = SensorCategory::query(); 
        $query->where('companyCode','=',$this->companyCode);
         
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
            
            $sensorCategoryDataFound = DB::table('sensor_categories')        
                            ->where('sensorName', '=', $request->sensorName)   
                            ->where('companyCode', '=', $this->companyCode)              
                            ->first();  
            
            if($sensorCategoryDataFound){
                throw new Exception("Duplicate entry for sensor category name");
            }                 
                             
            $sensorCategory = new SensorCategory;
            $sensorCategory->companyCode = $this->companyCode;
            $sensorCategory->sensorName = $request->sensorName;
            $sensorCategory->sensorDescriptions = $request->sensorDescriptions;
            $sensorCategory->measureUnitList = $request->measureUnitList;

         
            $sensorCategory->save();
            $response = [
                "message" => "Sensor category added successfully"
            ];
            $status = 200;                

        }catch(Exception $e){
            $response = [
                "message" => $e->getMessage()
            ];
            $status = 409;     
        }catch (QueryException $e) {
            $response = [
                "message" => $e->messageInfo
            ];
            $status = 406; 
        }         
       
       return response($response,$status);   
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SensorCategory  $sensorCategory
     * @return \Illuminate\Http\Response
     */
    public function show(SensorCategory $sensorCategory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SensorCategory  $sensorCategory
     * @return \Illuminate\Http\Response
     */
    public function edit(SensorCategory $sensorCategory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SensorCategory  $sensorCategory
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try{         
            $sensorCategoryDataFound = SensorCategory::find($id);    
            if(!$sensorCategoryDataFound){
                throw new Exception("Data not found");
            }

            $sensorCategoryDataFound = DB::table('sensor_categories')            
                ->where('sensorName', '=', $request->sensorName)     
                ->where('companyCode', '=', $this->companyCode)                           
                ->where('id','<>',$id)              
                ->first();            

            if($sensorCategoryDataFound){
                throw new Exception("Duplicate entry For Sensor categories");
            }
            
            $sensorCategory = SensorCategory::find($id);    
            if($sensorCategory){                
                $sensorCategory->companyCode = $this->companyCode;
                $sensorCategory->sensorName = $request->sensorName;
                $sensorCategory->sensorDescriptions = $request->sensorDescriptions;     
                $sensorCategory->measureUnitList = $request->measureUnitList;
                
              
                
                $sensorCategory->save();
                $response = [
                    "message" => "Sensor category updated successfully"
                ];
                $status = 200;     
            }   
        }catch (QueryException $e) {
            $response = [
                "message" => $e->messageInfo
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SensorCategory  $sensorCategory
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try{         
            $sensorCategoryDataFound = SensorCategory::find($id);    
            if(!$sensorCategoryDataFound){
                throw new Exception("Data not found");
            }
            $sensorCategory = SensorCategory::find($id);    
            if($sensorCategory){         
                $sensorCategory->delete();
                $response = [
                    "message" => "Sensor category and related data deleted successfully"
                ];
                $status = 200;     
            }            
        }catch (QueryException $e) {
            $response = [
                "message" => $e->messageInfo
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
    
    public function sensorCategoryUnitsDisplay($id){
        try{         
            $sensorCategoryDataFound = SensorCategory::find($id);    
            if(!$sensorCategoryDataFound){
                throw new Exception("Data not found");
            }
            
            $sensorCategory = SensorCategory::query()
                                ->where('id','=',$id)
                                ->get();
                            
            if($sensorCategory){         
                $response = [
                    "data" => $sensorCategory
                ];
                $status = 200;     
            }            
        }catch (QueryException $e) {
            $response = [
                "message" => $e->messageInfo
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
}
