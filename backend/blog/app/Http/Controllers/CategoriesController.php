<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use App\Http\Controllers\UTILITY\DataUtilityController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Http\Controllers\UtilityController;

class CategoriesController extends Controller
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
        $query = Categories::query(); 
        $query->where('companyCode','=',$this->companyCode);
        
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
            $categoryDataFound = DB::table('categories')        
                            ->where('categoryName', '=', $request->categoryName)   
                            ->where('companyCode', '=', $this->companyCode)              
                            ->first();   

            if($categoryDataFound){
                throw new Exception("Duplicate entry for category name");
            }   
            $categories = new Categories;
            $categories->companyCode = $this->companyCode;
            $categories->categoryName = $request->categoryName;
            $categories->categoryDescription = $request->categoryDescription;
           
            $categories->save();
            $response = [
                "message" => "Category name added successfully"
            ];
            $status = 201;   

        }catch(Exception $e){
            $response = [
                "error" => $e->getMessage()
            ];
            $status = 409;     
        }
       
        
        return response($response,$status);    
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Categories  $categories
     * @return \Illuminate\Http\Response
     */
    public function show(Categories $categories)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Categories  $categories
     * @return \Illuminate\Http\Response
     */
    public function edit(Categories $categories)
    {
        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Categories  $categories
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id)
    {
        try{
            $categories = Categories::find($id);
            if(!$categories){
                throw new Exception("Category name not found");
            }

            $categoryDataFound = DB::table('categories')        
            ->where('categoryName', '=', $request->categoryName)  
            ->where('companyCode', '=', $this->companyCode)   
            ->where('id','<>',$id)              
            ->first();                      

            if($categoryDataFound){
                throw new Exception("Duplicate entry for category name");
            }

            $categories = Categories::find($id);
            if($categories){
                $categories->companyCode = $this->companyCode;
                $categories->categoryName = $request->categoryName;
                $categories->categoryDescription = $request->categoryDescription;
            
                $categories->update();
                $response = [
                    "message" => "Category name updated successfully"
                ];
                $status = 201;   
                
            }

        }catch(Exception $e){
            $response = [
                "error" => $e->getMessage()
            ];
            $status = 409;     
        }
       
        
        return response($response,$status);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Categories  $categories
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try{
            $categories = Categories::find($id);
            if(!$categories){
                throw new Exception("Category name not found");
            }else{
                $categories->delete();
                $response = [
                    "message" => "Category name deleted successfully"
                ];
                $status = 200;                   
            }

        }catch(Exception $e){
            $response = [
                "error" => $e->getMessage()
            ];
            $status = 409;     
        }       
        
        return response($response,$status);
    }
}
