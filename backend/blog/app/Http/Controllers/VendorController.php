<?php

namespace App\Http\Controllers;
use App\Http\Controllers\UtilityController;
use App\Http\Controllers\UTILITY\DataUtilityController;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;


class VendorController extends Controller
{
    
    protected $companyCode = "";    

    function __construct(Request $request) {
        $getData = new UtilityController($request);
        $this->companyCode = $getData->getCompanyCode();        
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
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
        try{
            $vendor = DB::table('vendors')
            ->where('email','=',$request->emailId)
            ->where('companyCode','=',$this->companyCode)            
            ->first();
            if($vendor){
                throw new Exception("Duplicate Entry data found");
            }
            else{
                $vendor = new Vendor;
                $vendor->vendorName = $request->vendorName;
                $vendor->companyCode = $this->companyCode;
                $vendor->phone = $request->phoneNumber;
                $vendor->email = $request->emailId;
                $vendor->address = $request->address;
                $vendor->contactPerson = $request->contactPerson;
                $vendor->save();
                return response()->json(['message'=>'Vendor Added Successfully'],200);
            }
        }catch(Exception $e){
            $response = [
                "error" =>$e->getMessage()
            ];
            $status = 406; 
        }

        return response($response,$status);
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Vendor  $vendor
     * @return \Illuminate\Http\Response
     */
    public function show(Vendor $vendor)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Vendor  $vendor
     * @return \Illuminate\Http\Response
     */
    public function edit(Vendor $vendor)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Vendor  $vendor
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {       
        try{
            $vendor = Vendor::find($id); 
            if(!$vendor){
                throw new Exception("Data not found");
            }
            $vendorDataFound = DB::table('vendors')
            ->where('email','=',$request->emailId)
            ->where('companyCode','=',$this->companyCode)
            ->where('id','<>',$id)
            ->first();
            if($vendorDataFound){
                throw new Exception("Duplicate Entry data found");
            }   
            $vendor = Vendor::find($id);           
            if($vendor){   
                $vendor->vendorName = $request->vendorName;
                $vendor->companyCode = $this->companyCode;
                $vendor->phone = $request->phoneNumber;  
                $vendor->email = $request->emailId;
                $vendor->address = $request->address;  
                $vendor->contactPerson = $request->contactPerson;
                $vendor->update(); 

                $response = [
                    "message" => "Data Updated successfully", 
                ];
                $status = 200; 
            }
        }catch(Exception $e){
            $response = [
                "error" =>$e->getMessage()
            ];
            $status = 401; 
        }
        return response($response, $status);
    } 
   

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Vendor  $vendor
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try{
            $vendor = Vendor::find($id);   
            if(!$vendor){
                throw new Exception("Data not found");
            } 
            $vendor->delete();
            $response = [
                "message" => "Data Deleted successfully"
            ];
            $status = 200;
            
        }catch(Exception $e){
            $response = [
                "error" =>$e->getMessage()
            ];
            $status = 401;
        }    
        
        return response($response,$status);
       
    }



    public function vendorCustomData(Request $request){
        
        //includes search, sort, and pagination which is page data

        $query = Vendor::query();
        $query->where('companyCode','=',$this->companyCode); 
        // if($s = $request->input(key:'s')){
        //     $query->whereRaw(sql:"vendorName LIKE '%". $s ."%'")
        //         ->orWhereRaw(sql:"email LIKE  '%". $s ."%'");
        // }

        // if($sort = $request->input(key:'sort')){
        //     $query->orderBy('id',$sort);
        // }

        $getData = new DataUtilityController($request,$query);
        $response = $getData->getData();
        $status = 200;
        
        return response($response,$status);
    }












}
