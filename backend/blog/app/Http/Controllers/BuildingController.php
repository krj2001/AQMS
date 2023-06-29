<?php

namespace App\Http\Controllers;

use App\Http\Controllers\UtilityController;
use App\Http\Controllers\UTILITY\DataUtilityController;
use App\Models\Building;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Exception;
use Illuminate\Support\Str;
use App\Http\Controllers\LocationLogController;
use App\Http\Controllers\EventLogController;


class BuildingController extends Controller
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
    
    public function encodeImageFormat($strimg){
    	$splittedData = explode("/",$strimg);
        $format = $splittedData[1];
        $imageFormat = explode(";",$format);
        return $imageFormat[0];
    }
    
    public function index(Request $request)
    {
        $query = Building::query();
        
        if($companyCode = $this->companyCode){
            $query->where('companyCode','=',$companyCode);             
        }

        if($buildingName = $request->buildingName){
            $query->where('buildingName','=',$buildingName);         
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
        
        
        $buildingDataFound = DB::table('buildings')
                ->where('companyCode', '=', $this->companyCode)  
                ->where('location_id', '=', $request->location_id)             
                ->where('branch_id', '=', $request->branch_id)             
                ->where('facility_id', '=', $request->facility_id)      
                ->where('buildingName', '=', $request->buildingName)                  
                ->first(); 

        
        if($buildingDataFound){
            throw new CustomException("Duplicate Entry found");
        }
        

        try{
            $building = new Building;
            $building->companyCode = $this->companyCode;
            $building->location_id = $request->location_id;   
            $building->branch_id = $request->branch_id;            
            $building->facility_id = $request->facility_id;
            $building->buildingName = $request->buildingName;
            $building->coordinates = $request->coordinates;       

            $building->buildingTotalFloors = $request->buildingTotalFloors;
            $building->buildingDescription = $request->buildingDescription;
            
            $image = $request->buildingImg;  // your base64 encoded
            if($image){
                
                
                $format = $this->encodeImageFormat($image);
               
                if($format == "jpg"){
                    $image = str_replace('data:image/jpg;base64,', '', $request->buildingImg);
                    $imageName =  $request->buildingName."_Building.jpg";
                }
                if($format == "png"){
                    $image = str_replace('data:image/png;base64,', '', $request->buildingImg);
                    $imageName =  $request->buildingName."_Building.png";
                }
                if($format == "jpeg"){
                    $image = str_replace('data:image/jpeg;base64,', '', $request->buildingImg);
                    $imageName =  $request->buildingName."_Building.jpeg";
                }
                
                if($format == "jpeg" || $format == "png" || $format == "jpg"){
                    //$image = str_replace('data:image/png;base64,', '', $request->buildingImg);
                    $image = str_replace(' ', '+', $image);
                    // $imageName =  $request->buildingName."_Building.png";
                    //$picture   = date('His').'-'.$filename;                
                    $path = "Customers/".$this->companyCode."/Buildings";     
                    $imagePath = $path."/".$imageName;        
                    Storage::disk('public_uploads')->put($path."/".$imageName, base64_decode($image));    
                    $building->buildingImg = $imagePath;    
                }else{
                    throw new Exception("Please Select the Proper Image Format");
                }
            }            
            
            $building->buildingTag = $request->buildingTag;          
          
            $building->save();
            $response = [
                "message" => "Building name added successfully"
            ];
            $status = 201;         
            
       }catch(QueryException $e){
            $response = [
                "error" => true,
                "message" => $e->getMessage()                
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
    public function update(Request $request, $id)
    {
        try{            
                
            $building = Building::find($id);
            if(!$building){
                throw new CustomException("Building name not found");
            }

            $buildingDataFound = DB::table('buildings')
                ->where('companyCode', '=', $this->companyCode)  
                ->where('location_id', '=', $request->location_id)             
                ->where('branch_id', '=', $request->branch_id)             
                ->where('facility_id', '=', $request->facility_id)             
                ->where('buildingName', '=', $request->buildingName)                       
                ->where('id','<>',$id)                     
                ->first();         
            
            
            if($buildingDataFound){
                throw new CustomException("Duplicate entry data found building name");
            }
            // location log 23-05-2023
            $logController = new LocationLogController();
            $logController->storeLocationLog($request, $building, 'building');

            $building->companyCode = $this->companyCode;
            $building->location_id = $request->location_id;   
            $building->branch_id = $request->branch_id;            
            $building->facility_id = $request->facility_id;
            $building->buildingName = $request->buildingName;
            $building->coordinates = $request->coordinates;       
            $building->buildingTotalFloors = $request->buildingTotalFloors;
            $building->buildingDescription = $request->buildingDescription;
            
            $image = $request->buildingImg;  // your base64 encoded
            if($image){
                $format = $this->encodeImageFormat($image);
                $randomString = Str::random(10);
                
                if($format == "jpg"){
                    $image = str_replace('data:image/jpg;base64,', '', $request->buildingImg);
                    $imageName =  $request->buildingName."_Building.jpg".$randomString;
                }
                if($format == "png"){
                    $image = str_replace('data:image/png;base64,', '', $request->buildingImg);
                    $imageName =  $request->buildingName."_Building.png".$randomString;
                }
                if($format == "jpeg"){
                    $image = str_replace('data:image/jpeg;base64,', '', $request->buildingImg);
                    $imageName =  $request->buildingName."_Building.jpeg".$randomString;
                }
                
                
                if($format == "jpeg" || $format == "png" || $format == "jpg"){
                    //$image = str_replace('data:image/png;base64,', '', $request->buildingImg);
                    $image = str_replace(' ', '+', $image);
                    // $imageName =  $request->buildingName."_Building.png";
                    //$picture   = date('His').'-'.$filename;                
                    $path = "Customers/".$this->companyCode."/Buildings";     
                    // $imagePath = $path."/".$imageName;        
                    Storage::disk('public_uploads')->put($path."/".$imageName, base64_decode($image));    
                    $imagePath = $path."/".$imageName;   
                    $building->buildingImg = $imagePath;         
                }else{
                    throw new Exception("Please Select the Proper Image Format");
                }
            }
            $building->buildingTag = $request->buildingTag;
            $building->update();
            
            $response = [
                "message" => "Building name updated successfully",
            ];
            $status = 200;    
            
            
            // Event logs //31-05-2023
            $logController = new EventLogController();
            $eventDetails = [
                "buildingName" => $request->buildingName,
                "coordinates" => $request->coordinates,
                "buildingDescription" => $request->buildingDescription,
                "buildingTag" => $request->buildingTag,
                "floors" => $request->buildingTotalFloors
            ];
            
            $logController->addLog($request, 'Location Details', $eventDetails);
           
        }catch (QueryException $e) {
            $response = [
                "message" => $e->errorInfo
            ];
            $status = 406; 
            
        }catch(Exception $e){

           $response = [
               "message" => $e->getMessage()
           ];

           $status = 404;
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
    
        $building = Building::find($id);
        if(!$building){
            throw new CustomException("Building name not found");
        } 

        if($building){  
         
            $building->delete();
            $response = [
                "message" => "Building name and related data deleted successfully"
            ];
            $status = 200;             
        }       
        
        return response($response,$status);   
    }
}
