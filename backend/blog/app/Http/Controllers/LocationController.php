<?php

namespace App\Http\Controllers;
use App\Http\Controllers\UTILITY\DataUtilityController;
use Illuminate\Http\Request;
use App\Models\Location;
use Exception;
use App\Exceptions\CustomException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\LocationLogController;
use App\Http\Controllers\EventLogController;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
       
        $query = Location::query();

        $userRole = "";
        $userId = "";
        $companyCode = "";

        if($request->hasHeader('companyCode')) {
            $companyCode = $request->Header('companyCode');
        }

        if($request->hasHeader('userId')){
            $userId = $request->Header('userId');
        }

        if($request->hasHeader('userRole')){
            $userRole = $request->Header('userRole');
        }        

        if($companyCode = $companyCode){
            $query->where('companyCode','=',$companyCode);             
        }

        if($stateName = $request->stateName){
            $query->where('stateName','=',$request->stateName);         
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
        if($request->hasHeader('companyCode')) {
            $companyCode = $request->Header('companyCode');
        }

        if($request->hasHeader('userId')){
            $userId = $request->Header('userId');
        }

        if($request->hasHeader('userRole')){
            $userRole = $request->Header('userRole');
        }        

        //$location = Location::where('stateName', $request->stateName)->first();  
        try{
           $location = DB::table('locations')
                        ->where('companyCode', '=', $companyCode)
                        ->where('stateName', '=', $request->stateName)
                        ->first();
                      
            if($location){
                throw new CustomException("Location name already exist");
            }else{
                $location = new Location;
                $location->companyCode = $companyCode;
                $location->stateName = $request->stateName;           
                // $location->latitude = $request->latitude;   
                // $location->longitude = $request->longitude;  
                $location->coordinates = $request->coordinates;
                $location->save();
                $response = [
                    "message" => "Location name added successfully"
                ];
                $status = 201;     
        }    
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id){
       
        if($request->hasHeader('companyCode')) {
            $companyCode = $request->Header('companyCode');
        }

        if($request->hasHeader('userId')){
            $userId = $request->Header('userId');
        }

        if($request->hasHeader('userRole')){
            $userRole = $request->Header('userRole');
        }
      
        $location = Location::find($id);
        
        $locationDataFound = DB::table('locations')
                            ->where('companyCode', '=', $companyCode)  
                            ->where('stateName', '=', $request->stateName)
                            ->where('id','<>',$id)                
                            ->first();
                            
                            
        if(!$location){
            throw new CustomException("Location name not found");
            
        }
        else if($locationDataFound){
            throw new CustomException("Location name already exist");
            
        }
        else{
            try{
                // $logController = new LocationLogController();
                // $logController->storeLocationLog('pouyh');
                
                $location->companyCode = $companyCode;
                $location->stateName = $request->stateName;           
                $location->coordinates = $request->coordinates;                
                $location->update();
                
                $response = [
                    "message" => "Location details is updated"
                ];
                $status = 200;  
                
            }catch (QueryException $e) {
                $response = [
                    "error" => $e->errorInfo
                ];
                $status = 406; 
            } 
        }              
        
        return response($response,$status);   
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,$id)
    {

        if($request->hasHeader('companyCode')) {
            $companyCode = $request->Header('companyCode');
        }

        if($request->hasHeader('userId')){
            $userId = $request->Header('userId');
        }

        if($request->hasHeader('userRole')){
            $userRole = $request->Header('userRole');
        }

        try{
            $location = Location::find($id);
            if(!$location){
                throw new Exception("Location name not found");
            }
            if($location){                 
                $location->delete();
                $response = [
                    "message" => "Location name and related data deleted successfully"
                ];
                $status = 200;             
            }
        }catch(Exception $e){
            $response = [
                "error" =>  $e->getMessage()
            ];    
            $status = 404;           
        }
        
        return response($response,$status);    
       

    }
}
