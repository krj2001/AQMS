<?php

namespace App\Http\Controllers;

use App\Models\aidealabCompany;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Exceptions\CustomException;

class AidealabCompanyController extends Controller
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
     
     
     
     
    public function index()
    {
        //
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
     * @param  \App\Models\aidealabCompany  $aidealabCompany
     * @return \Illuminate\Http\Response
     */
    public function show(aidealabCompany $aidealabCompany)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\aidealabCompany  $aidealabCompany
     * @return \Illuminate\Http\Response
     */
    public function edit(aidealabCompany $aidealabCompany)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\aidealabCompany  $aidealabCompany
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try{
            $aidealabCompany = DB::table('aidealab_companies')
                ->where('companyName', '=', $this->companyCode)
                ->first();
                
            if(!$aidealabCompany){
                throw new CustomException("aidealabCompany name not found");
            }  
            
          $updateSettings =  DB::table('aidealab_companies')
                    ->where('companyName', '=', $this->companyCode)
                    ->update([
                        'dataRetentionPeriodInterval'=>$request->dataRetentionPeriodInterval,
                        'periodicBackupInterval'=>$request->periodicBackupInterval
                    ]);
            if($updateSettings){
                $response = [
                    "message" => "aidealabCompany name  updated successfully",
                ];
                $status = 200; 
            }else{
                 $response = [
                    "message" => "something went wrong",
                ];
                $status = 200; 
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
     * @param  \App\Models\aidealabCompany  $aidealabCompany
     * @return \Illuminate\Http\Response
     */
    public function destroy(aidealabCompany $aidealabCompany)
    {
        //
    }
}
