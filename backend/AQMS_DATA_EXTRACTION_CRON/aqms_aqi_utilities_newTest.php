<?php


header('Access-Control-Allow-Origin: *');
ini_set('display_errors', 1);
error_reporting(E_ERROR);
include("includes/config.php");



$aqms_class_range=array("GOOD"=>array(0,50),
                        "SATISFACTORY"=>array(51,100),
                        "MODERATELY POLLUTED"=>array(101,200),
                        "POOR"=>array(201,300),
                        "VERY POOR"=>array(301,400),
                        "SEVERE"=>array(401,500));
                        
$aqms_class_value_clr=array("GOOD"=>"#10CD1A",
                        "SATISFACTORY"=>"#BCF389",
                        "MODERATELY POLLUTED"=>"#F5F31B",
                        "POOR"=>"#F8C91E",
                        "VERY POOR"=>"#E60B0F",
                        "SEVERE"=>"#890C0C");

$aqi_map_for_parametrs=array("PM10"=>array("GOOD"=>array(0,50),
                                           "SATISFACTORY"=>array(51,100),
                                           "MODERATELY POLLUTED"=>array(101,250),
                                           "POOR"=>array(251,350),
                                           "VERY POOR"=>array(351,400),
                                           "SEVERE"=>array(401,9999)),
                            "PM2.5"=>array("GOOD"=>array(0,30),
                                           "SATISFACTORY"=>array(31,60),
                                           "MODERATELY POLLUTED"=>array(61,90),
                                           "POOR"=>array(91,120),
                                           "VERY POOR"=>array(121,254),
                                           "SEVERE"=>array(255,9999)),
                                           
                            "NO2"=>array("GOOD"=>array(0,40),
                                           "SATISFACTORY"=>array(41,60),
                                           "MODERATELY POLLUTED"=>array(61,100),
                                           "POOR"=>array(101,200),
                                           "VERY POOR"=>array(201,400),
                                           "SEVERE"=>array(401,9999)),
                            "SO2"=>array("GOOD"=>array(0,40),
                                           "SATISFACTORY"=>array(41,60),
                                           "MODERATELY POLLUTED"=>array(61,360),
                                           "POOR"=>array(361,600),
                                           "VERY POOR"=>array(601,1600),
                                           "SEVERE"=>array(1601,9999)),
                            "CO"=>array("GOOD"=>array(0,1),
                                           "SATISFACTORY"=>array(1.1,2.0),
                                           "MODERATELY POLLUTED"=>array(2.1,3.0),
                                           "POOR"=>array(3.0,17),
                                           "VERY POOR"=>array(17,34),
                                           "SEVERE"=>array(35,9999)),
                            "O3"=>array("GOOD"=>array(0,50),
                                           "SATISFACTORY"=>array(51,100),
                                           "MODERATELY POLLUTED"=>array(101,160),
                                           "POOR"=>array(161,200),
                                           "VERY POOR"=>array(201,240),
                                           "SEVERE"=>array(241,9999)),
                            "NH3"=>array("GOOD"=>array(0,200),
                                           "SATISFACTORY"=>array(201,400),
                                           "MODERATELY POLLUTED"=>array(401,800),
                                           "POOR"=>array(801,1200),
                                           "VERY POOR"=>array(1201,1600),
                                           "SEVERE"=>array(1601,9999)),
                            "Pb"=>array("GOOD"=>array(0,0.5),
                                           "SATISFACTORY"=>array(0.5,1.0),
                                           "MODERATELY POLLUTED"=>array(1.1,2.0),
                                           "POOR"=>array(2.1,3.0),
                                           "VERY POOR"=>array(3.1,3.5),
                                           "SEVERE"=>array(3.6,9999)));
                                         
                                         
$valid_parameter_data_range=array("PM10"=>array(1,500),
                                "PM2.5"=>array(-1,500),
                                "NO2"=>array(-1,500),
                                "SO2"=>array(-1,300),
                                "CO"=>array(-1,60),
                                "O3"=>array(-1,300),
                                "NH3"=>array(-1,2000),
                                "Pb"=>array(-1,10));
                                           
                                           

function getAqiChartScalingInfo($conn,$aqi_chart_standard,$aqi_class,$aqi_parameter)
{
    $sql="select * from AQI_CHART_PARAMETER_SCALINGS where CHART_STANDARD='$aqi_chart_standard' and CLASSIFICATION_LABEL='$aqi_class' and AQI_PARAMETER='$aqi_parameter'";
    // echo $sql."</br>";
    $res=mysqli_query($conn,$sql) or die(mysqli_error($conn));
    $ret_arr=array();
    $scaling_info=array(100000,200000);
    if($row=mysqli_fetch_array($res))
    {
       $scaling_info[0]=$row['MIN_VAL'];
       $scaling_info[1]=$row['MAX_VAL'];
    }
       
    $ret_arr[$aqi_parameter][$aqi_class]=$scaling_info;
       
    //print_r($ret_arr);
       
    return $ret_arr;
}


function getAqmsClassForAqi($con,$chart_standard,$aqi)
{
    
    $aqms_class_range=getAqiChartClassificationInfo($con,$chart_standard);
    //print_r($aqms_class_range);
    // echo "</br>"."aqi:".$aqi."</br>";
    foreach($aqms_class_range as $key=>$value)
    {
       // print_r($value);
       // echo $aqi;
        if(($aqi>=$value[0])&&($aqi<=$value[1]))
        {
            //echo $aqi."kkk:".$key;
            return $key;
        }
    }
    return null;
}


function getParameterConentraitonToAQi($conn,$aqi_chart_standard,$preffered_aqi,$parameter_name)
{
    $aqms_class_range=getAqiChartClassificationInfo($conn,$aqi_chart_standard);
    //print_r($aqms_class_range);
    $aqms_class=getAqmsClassForAqi($conn,$aqi_chart_standard,$preffered_aqi);
    //echo "aqi class:".$aqms_class;
    
    $aqi_range=$aqms_class_range[$aqms_class];
    $par_conc_range=getAqiChartScalingInfo($conn,$aqi_chart_standard,$aqms_class,$parameter_name)[$parameter_name][$aqms_class];
    //print_r($par_conc_rang);
    
    $par_aqi=rangeMap($preffered_aqi,$par_conc_range[1],$par_conc_range[0],$aqi_range[1],$aqi_range[0],9999);
    return (round($par_aqi,2));
}
 
 
function getAllParameterConcToAqi($con,$pref_aqi,$parameters,$chart_standard)
{
    global $aqms_class_value_clr;
    $parameters_list=explode(",",$parameters);
    $aqms_class_value=getAqiChartClassificationInfo($con,$chart_standard);
    $json_data=array();
    $json_data["AQI_CLASS"]= getAqmsClassForAqi($con,$chart_standard,$pref_aqi);
  
    $json_data["AQI_COLOR_CODE"]=$aqms_class_value_clr[$json_data["AQI_CLASS"]];
   
    foreach($parameters_list as $key)
    {
       $json_data[$key]=getParameterConentraitonToAQi($con,$chart_standard,$pref_aqi,$key);
    }
    return json_encode($json_data);
}



function getInavalidDataForASensor($par)
{
    global $valid_parameter_data_range;
    $range=$valid_parameter_data_range[$par];
    $index=rand(0,1);
    $w=round(50*rand(0,10)/10);
    $minrange=$index>0?$range[$index]:($range[$index]-$w);
    $maxrange=$index>0?($range[$index]+$w):($range[$index]);
   //echo "min:". $minrange." max:".$maxrange."</br>";
    return rand($minrange,$maxrange);
}
  
  
  
  
//Abhishekshenoy below functions for aqi implementation 11/18/2022

function rangeMap($Xin,$Ymax,$Ymin,$Xmax,$Xmin,$INF)
{
    // if(($Xmax===$INF)||($Ymax===$INF))
    // {
    //     return $Ymin;
    // }
    
    // if(($Xmax-$Xmin)<=0)
    // {
    //     return false;
    // }
    
    echo "ymax:".$Ymax."<br>";
    echo "ymin:".$Ymin."<br>";
    echo "xmax:".$Xmax."<br>";
    echo "xmin:".$Xmin."<br>";
    echo "val:".$Xin."<br>";
    
    return (($Ymax-$Ymin)/($Xmax-$Xmin))*($Xin-$Xmin)+$Ymin;
}


function getAQiStatus($parameterName,$parameterValue,$conn){
    $getAqiSensorStatusQuery ="SELECT * FROM `sensor_units` where sensorName = '$parameterName' limit 1";
    echo $getAqiSensorStatusQuery."<br>";
    $getAqiSensorStatusResult = mysqli_query($conn,$getAqiSensorStatusQuery);
    if(mysqli_num_rows($getAqiSensorStatusResult)>0){
        $result = mysqli_fetch_assoc($getAqiSensorStatusResult);
                            
        //GOOD
        $GOODMIN = $result['parmGoodMinScale'];
        $GOODMAX = $result['parmGoodMaxScale'];
        
        //SATISFACTORY
        $SATISFACTORYMIN = $result['parmSatisfactoryMinScale'];
        $SATISFACTORYMAX = $result['parmSatisfactoryMaxScale'];
        
        //MODERATE
        $MODERATEMIN = $result['parmModerateMinScale'];
        $MODERATEMAX = $result['parmModerateMaxScale'];
        
        //POOR
        $POORMIN = $result['parmPoorMinScale'];
        $POORMAX = $result['parmPoorMaxScale'];
        
        //VERY POOR
        $VERYPOORMIN = $result['parmVeryPoorMinScale'];
        $VERYPOORMAX = $result['parmVeryPoorMaxScale'];
        
        //SEVERE
        $SEVEREMIN = $result['parmSevereMinScale'];
        $SEVEREMAX = $result['parmSevereMaxScale'];
        
        // if($parameterValue>=$SEVEREMIN && $parameterValue<=$SEVEREMAX){
        if($parameterValue>=$SEVEREMIN){
            return "SEVERE,".$SEVEREMIN.",".$SEVEREMAX;
        }
        else if($parameterValue>=$VERYPOORMIN && $parameterValue<=$VERYPOORMAX){
            return "VERY POOR,".$VERYPOORMIN.",".$VERYPOORMAX;
        }
        else if($parameterValue>=$POORMIN && $parameterValue<=$POORMAX){
            return "POOR,".$POORMIN.",".$POORMAX;
        }
        else if($parameterValue>=$MODERATEMIN && $parameterValue<=$MODERATEMAX){
            return "MODERATELY POLLUTED,".$MODERATEMIN.",".$MODERATEMAX;
        }
        else if($parameterValue>=$SATISFACTORYMIN && $parameterValue<=$SATISFACTORYMAX){
            return "SATISFACTORY,".$SATISFACTORYMIN.",".$SATISFACTORYMAX;
        }else if($parameterValue>=$GOODMIN && $parameterValue<=$GOODMAX){
            return "GOOD,".$GOODMIN.",".$GOODMAX;
        }
    }else{
        return "No sensor found";
    }
}

function getAqiChartClassificationInfo($conn,$aqi_chart_standard)
{
   $sql="select * from AQI_CHART_DETAILS where CHART_STANDARD='$aqi_chart_standard'";
   $res=mysqli_query($conn,$sql) or die(mysqli_error($conn));
   $classification_info=array();
   while($row=mysqli_fetch_array($res))
   {
       $classification_label=$row['CLASSIFICATION_LABEL'];
       $classification_info[$classification_label]=array($row['MIN_VALUE'],$row['MAX_VALUE']);
   }
   
   return $classification_info;
}
  
function getAqiChartScalingInfoForParameter($conn,$aqi_chart_standard,$parameterValue,$aqi_parameter)
{
   
   $sql="select * from AQI_CHART_PARAMETER_SCALINGS where CHART_STANDARD='$aqi_chart_standard' and AQI_PARAMETER='$aqi_parameter' and (MIN_VAL <= $parameterValue) and (MAX_VAL >= $parameterValue)";
   echo $sql."</br>";
   $res=mysqli_query($conn,$sql) or die(mysqli_error($conn));
   $ret_arr=array();
   $scaling_info=array(100000,200000);
   
   if($row=mysqli_fetch_array($res))
   {
        $scaling_info[0]=$row['MIN_VAL'];
        $scaling_info[1]=$row['MAX_VAL'];
        $clasification=$row['CLASSIFICATION_LABEL'];
        $ret_arr["RANGE"]=$scaling_info;
        $ret_arr["AQI_CLASS"]=$clasification;
   }
   
   //print_r($ret_arr);
   return $ret_arr;
}
 
/*
  $paramValueList-->associative array containing parametername=>parametervalue for a particular aqmi device
  $aqi_chart_standard-->aqi chart standard
  @returns-->overall aqi index for a AQMI device
*/
  
function computeAQi($conn,$aqi_chart_standard,$paramValueList)
{
    $aqms_class_range=array("GOOD"=>array(0,50),
                        "SATISFACTORY"=>array(51,100),
                        "MODERATELY POLLUTED"=>array(101,200),
                        "POOR"=>array(201,300),
                        "VERY POOR"=>array(301,400),
                        "SEVERE"=>array(401,500));
    
    
    echo "<br>";
    echo "Testing parameter List begin <br>";
    print_r($paramValueList);
    echo "Testing parameter List end <br>";
    
    // $aqms_class_range=getAqiChartClassificationInfo($conn,$aqi_chart_standard); 
    $aqi_sum=0;
    $sensorList = array();
    foreach($paramValueList as $parameterName=>$parameterValue)
    {
    
        //echo "<br>".$parameterName."<br>";
        //calculating based on paramter but individual sensor tag
        foreach($parameterValue as $parameterId => $parameterValue){
            //echo "parameterName: ".$parameterName."  sensor id :".$parameterId." value:".$parameterValue;   

            //old  implementation
            // $par_conc_range_and_class_info=getAqiChartScalingInfoForParameter($conn,$aqi_chart_standard,$parameterValue,$parameterName);
            // $param_min_max_range=$par_conc_range_and_class_info['RANGE'];
            // $param_aqi_class=$par_conc_range_and_class_info['AQI_CLASS'];
         
            //$aqi_min_max_range=$aqms_class_range[$param_aqi_class];
            //$aqi_for_cur_param=rangeMap($parameterValue,$aqi_min_max_range[1],$aqi_min_max_range[0],$param_min_max_range[1],$param_min_max_range[0],9999);
            
            
            //new implentation 1/13/2023
            $sensorAqiStatus = getAQiStatus($parameterName,$parameterValue,$conn);

            $paramaqiStatusRange = explode(",",$sensorAqiStatus);
            
          
            
            $aqi_min_max_range=$aqms_class_range[$paramaqiStatusRange[0]];
            
            $aqi_for_cur_param=rangeMap($parameterValue,$aqi_min_max_range[1],$aqi_min_max_range[0],$paramaqiStatusRange[2],$paramaqiStatusRange[1],9999);

             echo "parameterName: ".$parameterName."  sensor id :".$parameterId." value:".$parameterValue."Parameter range".$paramaqiStatusRange[0].",".$paramaqiStatusRange[1].",".$paramaqiStatusRange[2]."Sensor aqi:".$aqi_for_cur_param."<br>";
            // echo "Sensor aqi:".$aqi_for_cur_param;
            
            //individual sensor aqi is dumped into array and if aqi not found or empty it is dumped as zero and finaly max of array aqi has to be taken 
            if($aqi_for_cur_param == ""){
                $sensorList[$parameterId] = 0;    
            }else{
                $sensorList[$parameterId] = round($aqi_for_cur_param,1);
            }
        }
        
        
        //calculated based on unique parameter
        /*
            $par_conc_range_and_class_info=getAqiChartScalingInfoForParameter($conn,$aqi_chart_standard,$parameterValue,$parameterName);
            $param_min_max_range=$par_conc_range_and_class_info['RANGE'];
            $param_aqi_class=$par_conc_range_and_class_info['AQI_CLASS'];
            $aqi_min_max_range=$aqms_class_range[$param_aqi_class];
            $aqi_for_cur_param=rangeMap($parameterValue,$aqi_min_max_range[1],$aqi_min_max_range[0],$param_min_max_range[1],$param_min_max_range[0],9999);
            
            //individual sensor aqi is dumped into array and if aqi not found or empty it is dumped as zero and finaly max of array aqi has to be taken 
            if($aqi_for_cur_param == ""){
                $sensorList[] = 0;    
            }else{
                $sensorList[] = $aqi_for_cur_param;
            }
            
            //individual sendor aqi is added which is not needed as per the requirement
            $aqi_sum=$aqi_sum+$aqi_for_cur_param;
        */
        
        
    }
    
    //getting avg of aqi
    //$totalParameters=count($paramValueList);
    //$overall_aqi=($totalParameters>0)?($aqi_sum/$totalParameters):0;
    // return (round($overall_aqi,2)); 
    
    //echo "sensroAQI";
    //gettingmax of aqi
    //print_r($sensorList);
    //$maxAqiIndivualSensor= max($sensorList);
    // echo $maxAqiIndivualSensor;
    
    return $sensorList;  //returning array of sensorListAqi
     
}
  

  
  
  
  
  
        
    
?>