<?php

namespace App\Http\Controllers;
use App\Http\Controllers\UTILITY\DataUtilityController;
use App\Http\Controllers\UtilityController;
use App\Models\Facilities;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Exceptions\CustomException;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\LocationLogController;
use App\Http\Controllers\EventLogController;


class FacilitiesController extends Controller
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
       
        $query = Facilities::query();     

        if($companyCode = $this->companyCode){
            $query->where('companyCode','=',$companyCode);             
        }

        if($facilityName = $request->facilityName){
            $query->where('facilityName','=',$facilityName);         
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
        
        $facilityDataFound = DB::table('facilities')
                ->where('companyCode', '=', $this->companyCode)  
                ->where('location_id', '=', $request->location_id)             
                ->where('branch_id', '=', $request->branch_id)             
                ->where('facilityName', '=', $request->facilityName)         
                ->first(); 

        if($facilityDataFound){
            throw new CustomException("Duplicate Entry found");
        }
       
        try{
            $Facilities = new Facilities;
            $Facilities->companyCode = $this->companyCode;
            $Facilities->location_id = $request->location_id;   
            $Facilities->branch_id = $request->branch_id;      
            $Facilities->facilityName = $request->facilityName;          
              $Facilities->coordinates = $request->coordinates;                         
            $Facilities->save();
            $response = [
                "message" => "facilityName added successfully"
            ];
            $status = 201;       
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
     * Display the specified resource.
     *
     * @param  \App\Models\Facilities  $facilities
     * @return \Illuminate\Http\Response
     */
    public function show(Facilities $facilities)
    {
        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Facilities  $facilities
     * @return \Illuminate\Http\Response
     */
    public function edit(Facilities $facilities)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Facilities  $facilities
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id)
    {       
        
       
        try{
            $Facilities = Facilities::find($id);
            if(!$Facilities){
                throw new CustomException("facilityName not found");
            }  
            
            $facilityDataFound = DB::table('facilities')
                ->where('companyCode', '=', $this->companyCode)  
                ->where('location_id', '=', $request->location_id)             
                ->where('branch_id', '=', $request->branch_id)             
                ->where('facilityName', '=', $request->facilityName)
                ->where('id','<>',$id)
                ->first(); 

            if($facilityDataFound){
                throw new CustomException("Duplicate Entry found");
            }
        
            
        
            $Facilities->companyCode = $this->companyCode;
            $Facilities->location_id = $request->location_id;   
            $Facilities->branch_id = $request->branch_id;            
            $Facilities->facilityName = $request->facilityName;          
            $Facilities->coordinates = $request->coordinates;                
            $Facilities->update();
            $response = [
                "message" => "facilityName  updated successfully"
            ];
            $status = 200;    
           
           
            // Event logs //31-05-2023
            $logController = new EventLogController();
            $eventDetails = [
                "facilityName" => $request->facilityName,
                "coordinates" => $request->coordinates
            ];
            
            $logController->addLog($request, 'Location Details', $eventDetails);
            
        }catch (QueryException $e) {
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
     * @param  \App\Models\Facilities  $facilities
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {        
        $Facilities = Facilities::find($id);
        if(!$Facilities){
            throw new CustomException("facilityName not found");
        }

        if($Facilities){                 
            $Facilities->delete();
            $response = [
                "message" => "facilityName deleted successfully"
            ];
            $status = 200;             
        }       
        
        return response($response,$status);   
    }
}
