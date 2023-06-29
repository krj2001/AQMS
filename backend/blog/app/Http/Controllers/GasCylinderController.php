<?php

namespace App\Http\Controllers;

use App\Models\GasCylinder;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\UtilityController;
use App\Http\Controllers\UTILITY\ReportsDataUtilityController;
use App\Http\Controllers\UTILITY\DataUtilityController;


class GasCylinderController extends Controller
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
           //   $query = ApplicationVersion::query();             

    $query = DB::table('gas_cylinders')
            ->where('companyCode', '=', $this->companyCode);            
    

        $getData = new ReportsDataUtilityController($request,$query);
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
                $gasCylinderNameDataFound = DB::table('gas_cylinders')
                ->where('gasCylinderName', '=', $request->gasCylinderName)              
                ->first(); 

        if($gasCylinderNameDataFound){
            throw new CustomException("Duplicate Entry found");
        }        
        try{
            $GasCylinder = new GasCylinder;
            $GasCylinder->expiryDate = $request->expiryDate;
            $GasCylinder->gasCylinderName = $request->gasCylinderName;         
            $GasCylinder->save();
            $response = [
                "message" => "Gas Cylinder added successfully"
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
     * @param  \App\Models\GasCylinder  $gasCylinder
     * @return \Illuminate\Http\Response
     */
    public function show(GasCylinder $gasCylinder)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\GasCylinder  $gasCylinder
     * @return \Illuminate\Http\Response
     */
    public function edit(GasCylinder $gasCylinder)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\GasCylinder  $gasCylinder
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try{
            $GasCylinder = GasCylinder::find($id);
            if(!$GasCylinder){
                throw new CustomException("Gas Cylinder name not found");
            }  
            
            
            $gasCylinderDataFound = DB::table('gas_cylinders')
                ->where('companyCode', '=', $this->companyCode)            
                ->where('gasCylinderName', '=', $request->gasCylinderName)       
                ->where('id','<>',$id)
                ->first(); 
            
            if($gasCylinderDataFound){
                throw new CustomException("Gas Cylinder name already exist");
            }
            
            $GasCylinder->companyCode = $this->companyCode;
            $GasCylinder->expiryDate = $request->expiryDate;   
            $GasCylinder->gasCylinderName = $request->gasCylinderName;   
            $GasCylinder->update();
            $response = [
                "message" => "Gas Cylinder  updated successfully"
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
     * @param  \App\Models\GasCylinder  $gasCylinder
     * @return \Illuminate\Http\Response
     */
    public function destroy(GasCylinder $gasCylinder,$id)
    {
      $gasCylinder = GasCylinder::find($id);
        if(!$gasCylinder){
            throw new CustomException("Gas Cylinder name not found");
        }

        if($gasCylinder){                 
            $gasCylinder->delete();
            $response = [
                "message" => "Gas Cylinder and related data deleted successfully"
            ];
            $status = 200;             
        }       
        
        return response($response,$status);   
    }
}
