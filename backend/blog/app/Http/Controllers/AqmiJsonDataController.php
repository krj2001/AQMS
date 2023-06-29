<?php

namespace App\Http\Controllers;

use App\Models\AqmiJsonData;
use App\Http\Controllers\UTILITY\DataUtilityController;
use Illuminate\Http\Request;
use DateTime;

class AqmiJsonDataController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = AqmiJsonData::select('j_data');    
        $this->searchedKey = $request->searchKey == "" ? "" : $request->searchKey;            
  
         
        if($this->searchedKey != ""){            
            $query->whereRaw(sql:"j_data LIKE '%". $this->searchedKey['BULDING'] ."%'");
            $query->whereRaw(sql:"j_data LIKE '%". $this->searchedKey['FLOOR'] ."%'");
        }
        //$query->whereRaw(sql:"j_data LIKE '%\"FLOOR\":\"". $s ."\"%'");   
        
        $responseData = array();
        $getData = new DataUtilityController($request,$query);
        $response = $getData->getData();

        //array contain n number objects where response array contains key value data
        $data = count($response['data']);


        //getting index 0 object data of key value j_data
        $getData = $response['data'][0]->j_data;           
        
        //decoding the object 
        $obj = json_decode($getData); 
        
        //accessing data of object    
        //$obj->DEVICE_ID;




        // $id = $response[0]->j_data; array contains objects with index and object can be accessed after decoding
        // $obj = json_decode($id);

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
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $AqmiJsonData = new AqmiJsonData;
        $AqmiJsonData->j_data = $request->getContent();
        $date = new DateTime('Asia/Kolkata');      
        $AqmiJsonData->date_time = $date->format('Y-m-d H:i:s');
        $AqmiJsonData->save();      
        
        $response = [
            "message"=>"Data Pushed successfully"
        ];
        $status = 200;        

        return response($response,$status);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AqmiJsonData  $aqmiJsonData
     * @return \Illuminate\Http\Response
     */
    public function show(AqmiJsonData $aqmiJsonData)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AqmiJsonData  $aqmiJsonData
     * @return \Illuminate\Http\Response
     */
    public function edit(AqmiJsonData $aqmiJsonData)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AqmiJsonData  $aqmiJsonData
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AqmiJsonData $aqmiJsonData)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AqmiJsonData  $aqmiJsonData
     * @return \Illuminate\Http\Response
     */
    public function destroy(AqmiJsonData $aqmiJsonData)
    {
        //
    }
}
