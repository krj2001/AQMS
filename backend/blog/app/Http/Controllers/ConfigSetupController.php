<?php

namespace App\Http\Controllers;
use App\Http\Controllers\UTILITY\DataUtilityController;
use App\Models\ConfigSetup;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Database\QueryException;
use App\Http\Controllers\UtilityController;
use Illuminate\Support\Facades\DB;

class ConfigSetupController extends Controller
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
        try{
            $query = ConfigSetup::query();
            
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
        try { 

            $configSetup = new ConfigSetup;
            $configSetup->companyCode=$this->companyCode;
            $configSetup->accessType = $request->accessType;
            $configSetup->accessPointName = $request->accessPointName;
            $configSetup->ssId = $request->ssId;
            $configSetup->accessPointPassword = $request->accessPointPassword;
            
            //secondary details            
            $configSetup->accessPointNameSecondary = $request->accessPointNameSecondary;
            $configSetup->ssIdSecondary = $request->ssIdSecondary;
            $configSetup->accessPointPasswordSecondary = $request->accessPointPasswordSecondary;

            $configSetup->ftpAccountName = $request->ftpAccountName;
            $configSetup->userName = $request->userName;
            $configSetup->ftpPassword = $request->ftpPassword;

            $configSetup->port = $request->port;
            $configSetup->serverUrl = $request->serverUrl;
            $configSetup->folderPath = $request->folderPath;
            $configSetup->serviceProvider = $request->serviceProvider;
            $configSetup->apn = $request->apn;

        
           

            $configSetup->save();
           
            $response = [
                "message" => "Access Point added successfully"
            ];
            $status = 200; 

        }catch (QueryException $e) {
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
     * @param  \App\Models\ConfigSetup  $configSetup
     * @return \Illuminate\Http\Response
     */
    public function show(ConfigSetup $configSetup)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ConfigSetup  $configSetup
     * @return \Illuminate\Http\Response
     */
    public function edit(ConfigSetup $configSetup)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ConfigSetup  $configSetup
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {  

            $configSetupDataFound = ConfigSetup::find($id);
    
            if(!$configSetupDataFound){
                throw new Exception("Data not found");
            }       
    
            $configSetup = ConfigSetup::find($id);    
            if($configSetup){       

                $configSetup->accessType = $request->accessType;
                $configSetup->accessPointName = $request->accessPointName;
                $configSetup->ssId = $request->ssId;
                $configSetup->accessPointPassword = $request->accessPointPassword;
                
                //secondary details            
                $configSetup->accessPointNameSecondary = $request->accessPointNameSecondary;
                $configSetup->ssIdSecondary = $request->ssIdSecondary;
                $configSetup->accessPointPasswordSecondary = $request->accessPointPasswordSecondary;

                $configSetup->ftpAccountName =$request->ftpAccountName;
                $configSetup->userName = $request->userName;
                $configSetup->ftpPassword = $request->ftpPassword;
                $configSetup->port = $request->port;
                $configSetup->serverUrl = $request->serverUrl;
                $configSetup->folderPath = $request->folderPath;

                $configSetup->serviceProvider = $request->serviceProvider;
                $configSetup->apn =$request->apn;
                $configSetup->save();
                
                
                // Event logs //31-05-2023
                $affectedColumns = [];
                foreach ($configSetup->getChanges() as $attribute => $value) {
                    if ($attribute !== 'updated_at') {
                        $affectedColumns[$attribute] = $value;
                    }
                }
                
                $logController = new EventLogController();
                $logController->addLog($request, 'Device Config', $affectedColumns);
                
                $response = [
                    "message" => "Config Setup  updated successfully",
                    "data" => $affectedColumns
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ConfigSetup  $configSetup
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try{         
            $configSetup = ConfigSetup::find($id);    
            if(!$configSetup){
                throw new Exception("Data not found");
            }
            $configSetup = ConfigSetup::find($id);    
            if($configSetup){         
                $configSetup->delete();
                $response = [
                    "message" => "config Setup and related data deleted successfully"
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
    

}
