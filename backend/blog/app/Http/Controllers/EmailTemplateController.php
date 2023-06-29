<?php

namespace App\Http\Controllers;

use App\Models\emailTemplate;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Database\QueryException;
use App\Exceptions\CustomException;
use App\Http\Controllers\UtilityController;
use App\Http\Controllers\UTILITY\ReportsDataUtilityController;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\UTILITY\DataUtilityController;


class EmailTemplateController extends Controller
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
     
    public function emailTemplateFetch(Request $request)
    {
      $query = DB::table('email_templates')
              ->where('companyCode', '=', $this->companyCode) 
              ->get();
    
        $response =  array("data"=>$query);
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\emailTemplate  $emailTemplate
     * @return \Illuminate\Http\Response
     */
    public function show(emailTemplate $emailTemplate)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\emailTemplate  $emailTemplate
     * @return \Illuminate\Http\Response
     */
    public function edit(emailTemplate $emailTemplate)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\emailTemplate  $emailTemplate
     * @return \Illuminate\Http\Response
     */
    public function emailTemplate(Request $request)
    {
       try{
                $id = $request->id;
                $emailTemplate = emailTemplate::find($id);
                
                  $emailTempletDataFound = DB::table('email_templates')
                    ->where('companyCode', '=', $this->companyCode) 
                    ->where('id','<>',$id)
                    ->first(); 
                    
                    if($emailTempletDataFound){
                    throw new CustomException("companyCode name already exist");
                }
    
            if($emailTemplate){
                $emailTemplate->companyCode =$this->companyCode;
                $emailTemplate->calibrartionSubject = $request->calibrartionSubject;   
                $emailTemplate->calibrartionBody = $request->calibrartionBody;          
                $emailTemplate->bumpTestSubject = $request->bumpTestSubject;
                $emailTemplate->bumpTestBody = $request->bumpTestBody;   
                $emailTemplate->stelSubject = $request->stelSubject;          
                $emailTemplate->stelBody = $request->stelBody;
                $emailTemplate->twaSubject = $request->twaSubject;   
                $emailTemplate->twaBody = $request->twaBody;          
                $emailTemplate->warningSubject = $request->warningSubject;
                $emailTemplate->warningBody = $request->warningBody;   
                $emailTemplate->criticalSubject = $request->criticalSubject;          
                $emailTemplate->criticalBody = $request->criticalBody;
                $emailTemplate->outOfRangeSubject = $request->outOfRangeSubject;
                $emailTemplate->outOfRangeBody = $request->outOfRangeBody;   
                $emailTemplate->periodicitySubject = $request->periodicitySubject;          
                $emailTemplate->periodicityBody = $request->periodicityBody;       
                $emailTemplate->update();
            
                $affectedColumns = [];
                foreach ($emailTemplate->getChanges() as $attribute => $value) {
                    if ($attribute !== 'updated_at') {
                        $affectedColumns[$attribute] = $value;
                    }
                }
                
                $response = [
                    "message" => "Email Templates updated successfully",
                ];
                $status = 201;  
                
                // Event logs //31-05-2023
                $logController = new EventLogController();
                $logController->addLog($request, 'Email Config', $affectedColumns);
            }
            else{
                $emailTemplate = new emailTemplate;
                $emailTemplate->companyCode =$this->companyCode;
                $emailTemplate->calibrartionSubject = $request->calibrartionSubject;   
                $emailTemplate->calibrartionBody = $request->calibrartionBody;          
                $emailTemplate->bumpTestSubject = $request->bumpTestSubject;
                $emailTemplate->bumpTestBody = $request->bumpTestBody;   
                $emailTemplate->stelSubject = $request->stelSubject;          
                $emailTemplate->stelBody = $request->stelBody;
                $emailTemplate->twaSubject = $request->twaSubject;   
                $emailTemplate->twaBody = $request->twaBody;          
                $emailTemplate->warningSubject = $request->warningSubject;
                $emailTemplate->warningBody = $request->warningBody;   
                $emailTemplate->criticalSubject = $request->criticalSubject;          
                $emailTemplate->criticalBody = $request->criticalBody;
                $emailTemplate->outOfRangeSubject = $request->outOfRangeSubject;
                $emailTemplate->outOfRangeBody = $request->outOfRangeBody;   
                $emailTemplate->periodicitySubject = $request->periodicitySubject;          
                $emailTemplate->periodicityBody = $request->periodicityBody;       
                $emailTemplate->save();
            
                $response = [
                    "message" => "EmailTemplate name added successfully"
                ];
                $status = 201;  
            }
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
     * @param  \App\Models\emailTemplate  $emailTemplate
     * @return \Illuminate\Http\Response
     */
    public function destroy(emailTemplate $emailTemplate)
    {
        //
    }
}
