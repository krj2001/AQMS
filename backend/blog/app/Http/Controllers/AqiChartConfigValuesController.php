<?php

namespace App\Http\Controllers;
use App\Http\Controllers\UTILITY\DataUtilityController;
use App\Models\AqiChartConfigValues;
use Illuminate\Http\Request;

class AqiChartConfigValuesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = AqiChartConfigValues::query();            

       
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
        $aqiTemplate = AqiChartConfigValues::where('aqiTemplateName', $request->aqiTemplateName)->first();     
        
        if($aqiTemplate){ 
            $response = [
                "message" => "AQI Template name already exist"
            ];
            $status = 409;                
           
        }        
        else{
            $aqiTemplate = new AqiChartConfigValues;
            $aqiTemplate->aqiTemplateName = $request->aqiTemplateName;
            $aqiTemplate->aqiGoodMinScale = $request->aqiGoodMinScale;
            $aqiTemplate->aqiGoodMaxScale = $request->aqiGoodMaxScale;
            $aqiTemplate->aqiSatisfactoryMinScale = $request->aqiSatisfactoryMinScale;
            $aqiTemplate->aqiSatisfactoryMaxScale = $request->aqiSatisfactoryMaxScale;
            $aqiTemplate->aqiModerateMinScale = $request->aqiModerateMinScale;
            $aqiTemplate->aqiModerateMaxScale = $request->aqiModerateMaxScale;
            $aqiTemplate->aqiPoorMinScale = $request->aqiPoorMinScale;
            $aqiTemplate->aqiPoorMaxScale = $request->aqiPoorMaxScale;
            $aqiTemplate->aqiVeryPoorMinScale = $request->aqiVeryPoorMinScale;
            $aqiTemplate->aqiVeryPoorMaxScale = $request->aqiVeryPoorMaxScale;
            $aqiTemplate->aqiSevereMinScale = $request->aqiSevereMinScale;
            $aqiTemplate->aqiSevereMaxScale = $request->aqiSevereMaxScale;               
            $aqiTemplate->save();
            $response = [
                "message" => "AQI Template added successfully"
            ];
            $status = 201;           
       }
       return response($response,$status);    
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AqiChartConfigValues  $aqiChartConfigValues
     * @return \Illuminate\Http\Response
     */
    public function show(AqiChartConfigValues $aqiChartConfigValues)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AqiChartConfigValues  $aqiChartConfigValues
     * @return \Illuminate\Http\Response
     */
    public function edit(AqiChartConfigValues $aqiChartConfigValues)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AqiChartConfigValues  $aqiChartConfigValues
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AqiChartConfigValues $aqiChartConfigValues)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AqiChartConfigValues  $aqiChartConfigValues
     * @return \Illuminate\Http\Response
     */
    public function destroy(AqiChartConfigValues $aqiChartConfigValues)
    {
        //
    }
}
