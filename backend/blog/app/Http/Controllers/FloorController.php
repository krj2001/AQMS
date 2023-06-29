<?php

namespace App\Http\Controllers;

use App\Http\Controllers\UtilityController;
use App\Http\Controllers\UTILITY\DataUtilityController;
use App\Models\Floor;
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


class FloorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    protected $companyCode = "";    
    
    public function encodeImageFormat($strimg){
    	$splittedData = explode("/",$strimg);
        $format = $splittedData[1];
        $imageFormat = explode(";",$format);
        return $imageFormat[0];
    }
    
    function __construct(Request $request) {
        $getData = new UtilityController($request);
        $this->companyCode = $getData->getCompanyCode();
        
    }


    public function index(Request $request)
    {
        $query = Floor::query();                

        if($companyCode = $this->companyCode){
            $query->where('companyCode','=',$companyCode);             
        }

        // if($buildingName = $request->buildingName){
        //     $query->where('buildingName','=',$buildingName);         
        // }

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
        try{
            $floor = DB::table('floors')
                ->where('companyCode', '=', $this->companyCode)  
                ->where('location_id', '=', $request->location_id)             
                ->where('branch_id', '=', $request->branch_id)             
                ->where('facility_id', '=', $request->facility_id)             
                ->where('building_id', '=', $request->building_id)             
                ->where('floorStage', '=', $request->floorStage)                 
                ->first();                      
                
            if($floor){
                throw new Exception("Floor stage is already Inserted");
            }

            $floor = new Floor;
            $floor->companyCode = $this->companyCode;
            $floor->location_id = $request->location_id;   
            $floor->branch_id = $request->branch_id;            
            $floor->facility_id = $request->facility_id;
            $floor->building_id = $request->building_id;
            $floor->floorStage = $request->floorStage;
            $floor->floorName = $request->floorName;    
            $image = $request->floorMap;  // your base64 encoded

            if($image){
                
                
                $format = $this->encodeImageFormat($image);
               
                if($format == "jpg"){
                    $image = str_replace('data:image/jpg;base64,', '', $request->floorMap);
                    $imageName =  $request->floorStage.".jpg";
                }
                if($format == "png"){
                    $image = str_replace('data:image/png;base64,', '', $request->floorMap);
                   $imageName =  $request->floorStage.".png";
                }
                if($format == "jpeg"){
                    $image = str_replace('data:image/jpeg;base64,', '', $request->floorMap);
                    $imageName =  $request->floorStage.".jpeg";
                }
                
                if($format == "jpeg" || $format == "png" || $format == "jpg"){
                    //$image = str_replace('data:image/png;base64,', '', $request->floorMap);
                    $image = str_replace(' ', '+', $image);
                    // $imageName =  $request->floorStage.".png";
                    //$picture   = date('His').'-'.$filename;                
                    $path = "Customers/".$this->companyCode."/Buildings/Floors";     
                    $imagePath = $path."/".$imageName;        
                    Storage::disk('public_uploads')->put($path."/".$imageName, base64_decode($image));    
                    $floor->floorMap = $imagePath;          
                }else{
                    throw new Exception("Please Select the Proper Image Format");
                }
                
            }            
            
            $floor->floorCords = $request->floorCords;                    
            $floor->save();

            $response = [
                "message" => "Floor name added successfully"
            ];
            $status = 201;   

       }catch(QueryException $e){
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
     * Display the specified resource.
     *
     * @param  \App\Models\Floor  $floor
     * @return \Illuminate\Http\Response
     */
    public function show(Floor $floor)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Floor  $floor
     * @return \Illuminate\Http\Response
     */
    public function edit(Floor $floor)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Floor  $floor
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {       
        try{

            $floor = Floor::find($id);

            if(!$floor){
                throw new Exception("floor id not found");
            }

            $floorDataFound = DB::table('floors')
                ->where('companyCode', '=', $this->companyCode)  
                ->where('location_id', '=', $request->location_id)             
                ->where('branch_id', '=', $request->branch_id)             
                ->where('facility_id', '=', $request->facility_id)             
                ->where('building_id', '=', $request->building_id)             
                ->where('floorStage', '=', $request->floorStage)             
                ->where('id','<>',$id)                     
                ->first();         
                
                
            if($floorDataFound){
                throw new Exception("Duplicate entry For floor stage");
            }
            
            // location log 23-05-2023
            $logController = new LocationLogController();
            $logController->storeLocationLog($request, $floor, 'floor');

            $floor = Floor::find($id);
            $floor->companyCode = $this->companyCode;
            $floor->location_id = $request->location_id;   
            $floor->branch_id = $request->branch_id;            
            $floor->facility_id = $request->facility_id;
            $floor->building_id = $request->building_id;
            $floor->floorStage = $request->floorStage;
            $floor->floorName = $request->floorName;    
            $image = $request->floorMap;  // your base64 encoded

            if($image){
                
                $format = $this->encodeImageFormat($image);
                $randomString = Str::random(10);
                if($format == "jpg"){
                    $image = str_replace('data:image/jpg;base64,', '', $request->floorMap);
                    $imageName =  $request->floorStage.".jpg".$randomString;
                }
                if($format == "png"){
                    $image = str_replace('data:image/png;base64,', '', $request->floorMap);
                   $imageName =  $request->floorStage.".png".$randomString;
                }
                if($format == "jpeg"){
                    $image = str_replace('data:image/png;base64,', '', $request->floorMap);
                    $imageName =  $request->floorStage.".jpeg".$randomString;
                }
                
                if($format == "jpeg" || $format == "png" || $format == "jpg"){
                    //$image = str_replace('data:image/png;base64,', '', $request->floorMap);
                    $image = str_replace(' ', '+', $image);
                    // $imageName =  $request->floorStage.".png";
                    //$picture   = date('His').'-'.$filename;                
                    $path = "Customers/".$this->companyCode."/Buildings/Floors";     
                    $imagePath = $path."/".$imageName;        
                    Storage::disk('public_uploads')->put($path."/".$imageName, base64_decode($image));    
                    $floor->floorMap = $imagePath;    
                }else{
                    throw new Exception("Please Select the Proper Image Format");
                }
                
            }            
            
            $floor->floorCords = $request->floorCords;                    
            $floor->update();

            $response = [
                "message" => "Floor name updated successfully"
            ];
            $status = 201;     
            
            // Event logs //31-05-2023
            $logController = new EventLogController();
            $eventDetails = [
                "floorName" => $request->floorName,
                "floorCords" => $request->floorCords,
                "floorStage" => $request->floorStage,
            ];
            
            $logController->addLog($request, 'Location Details', $eventDetails);

       }catch(QueryException $e){

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
     * @param  \App\Models\Floor  $floor
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {        
        $floor = Floor::find($id);
        if(!$floor){
            throw new CustomException("Floor name not found");
        } 

        if($floor){                 
            $floor->delete();
            $response = [
                "message" => "Floor name and related data deleted successfully"
            ];
            $status = 200;             
        }               
        return response($response,$status); 
    }
}
