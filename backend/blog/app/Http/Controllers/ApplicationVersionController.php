<?php

namespace App\Http\Controllers;

use App\Models\ApplicationVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Exceptions\CustomException;
use App\Http\Controllers\UTILITY\DataUtilityController;
use App\Http\Controllers\UTILITY\AppDataUtilityController;
use App\Exports\ApplicationVersionExport;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as BaseExcel;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailNotify;
use App\Http\Controllers\UtilityController;

use Exception;
class ApplicationVersionController extends Controller
{
    protected $companyCode = ""; 
    protected $table = "";  
    protected $alertColor; 
    
    function __construct(Request $request) {
        $getData = new UtilityController($request);
        $this->companyCode = $getData->getCompanyCode();  
        $this->alertColor = $getData->getAlertColors();  
         $this->userId = $getData->getUserId();
    }
    
        function fetchVerifiedEmailUsers($userEmail){
        
          $verifiedUser = DB::table('users')
              ->where('email', '=' , $userEmail)
              ->where('isverified', 1)
              ->first();
    
            if($verifiedUser){
                return $userEmail;
            }
            else {
                return "unverifiedUser@test.com";
            }
    }
    
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     
    public function index(Request $request)
     {
            //   $query = ApplicationVersion::query();             

         $query = DB::table('application_versions')->orderBy('id','DESC');
                  
             


        $getData = new AppDataUtilityController($request,$query);
        
        $response = $getData->getData();

        
        $status = 200;
        return response($response,$status);  
    }
    
    
        public function ApplicationVersionExport(Request $request)
            {
                    //   $query = ApplicationVersion::query();             
        
                 $query = DB::table('application_versions')
                  ->select(DB::raw('DATE_FORMAT(created_at, "%d-%b-%Y") as date'),DB::raw('TIME(created_at) as time'),'versionNumber','summary');
                 return Excel::download(new ApplicationVersionExport($query), 'ApplicationVerionReport.xlsx');
            }
            
            
    public function emailApplicationVersion(Request $request)
    {
        $query = DB::table('application_versions')
            ->select(DB::raw('DATE_FORMAT(created_at, "%d-%b-%Y") as date'),DB::raw('TIME(created_at) as time'),'versionNumber','summary');
          
        //  return Excel::download(new ApplicationVersionExport($query), 'ApplicationVerionReport.xlsx');
        
                        
        $attachment =  Excel::raw(new ApplicationVersionExport($query), BaseExcel::XLSX);
    
        $url = env('APPLICATION_URL');
        $email = $request->header('Userid');
        $data = [
            'meassage' => 'Application Reports',
            'url' => $url
        ];
        
        Mail::send('ApplicationVersion', $data, function($messages) use ($email,$attachment){
            $messages->to($email);
            $messages->subject('Application Reports');    
            $messages->attachData($attachment, 'ApplicationVerionReport.xlsx',[
                 ]);
        });
        
           $response = [      
                "message"=>"Reports data sent Successfully"
            ];
            
            $status = 200;
            
         return response($response, $status);
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
         
        $versionNumberDataFound = DB::table('application_versions')
                ->where('versionNumber', '=', $request->versionNumber)              
                ->first(); 

        if($versionNumberDataFound){
            throw new CustomException("Duplicate Entry found");
        }        
        try{
            $ApplicationVersion = new ApplicationVersion;
            $ApplicationVersion->versionNumber = $request->versionNumber;
            $ApplicationVersion->summary = $request->summary;         
            $ApplicationVersion->save();
            $response = [
                "message" => "Application Version added successfully"
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
     * @param  \App\Models\ApplicationVersion  $applicationVersion
     * @return \Illuminate\Http\Response
     */
    public function show(ApplicationVersion $applicationVersion)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ApplicationVersion  $applicationVersion
     * @return \Illuminate\Http\Response
     */
    public function edit(ApplicationVersion $applicationVersion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ApplicationVersion  $applicationVersion
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
         try{
            $ApplicationVersion = ApplicationVersion::find($id);
            if(!$ApplicationVersion){
                throw new CustomException("Application Version name not found");
            }  
            
            
            $ApplicationVersionDataFound = DB::table('application_versions')           
                ->where('versionNumber', '=', $request->versionNumber)       
                ->where('id','<>',$id)
                ->first(); 
            
            if($ApplicationVersionDataFound){
                throw new CustomException("Application Version already exist");
            }
            
            $ApplicationVersion->versionNumber = $request->versionNumber;   
            $ApplicationVersion->summary = $request->summary;   
            $ApplicationVersion->update();
            $response = [
                "message" => "Application Version  updated successfully"
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
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ApplicationVersion  $applicationVersion
     * @return \Illuminate\Http\Response
     */
    public function destroy(ApplicationVersion $applicationVersion,$id)
    {
       $ApplicationVersion = ApplicationVersion::find($id);
        if(!$ApplicationVersion){
            throw new CustomException("Application Version name not found");
        }

        if($ApplicationVersion){                 
            $ApplicationVersion->delete();
            $response = [
                "message" => "Application Version and related data deleted successfully"
            ];
            $status = 200;             
        }       
        
        return response($response,$status);  
    }
}
