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
                        "SEVERE"=>array(401,9999));
                        
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
   // echo (($Ymax-$Ymin)/($Xmax-$Xmin))*($Xin-$Xmin)+$Ymin;
    return (($Ymax-$Ymin)/($Xmax-$Xmin))*($Xin-$Xmin)+$Ymin;
}


function getAQiStatus($parameterName,$parameterValue,$mysqli){
    $getAqiSensorStatusQuery ="SELECT * FROM `sensor_units` where sensorName = '$parameterName' limit 1";
    $getAqiSensorStatusResult = mysqli_query($mysqli,$getAqiSensorStatusQuery);
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
        
        if(round(floatVal($parameterValue),1)>=round(floatVal($SEVEREMIN),1) && round(floatVal($parameterValue),1)<=round(floatVal($SEVEREMAX),1)){
            return "SEVERE,".$SEVEREMIN.",".$SEVEREMAX;
        }
        else if(round(floatVal($parameterValue))>=round(floatVal($VERYPOORMIN)) && round(floatVal($parameterValue))<=round(floatVal($VERYPOORMAX))){
            return "VERYPOOR,".$VERYPOORMIN.",".$VERYPOORMAX;
        }
        else if(round(floatVal($parameterValue))>=round(floatVal($POORMIN)) && round(floatVal($parameterValue))<=round(floatVal($POORMAX))){
            return "POOR,".$POORMIN.",".$POORMAX;
        }
        else if(round(floatVal($parameterValue))>=round(floatVal($MODERATEMIN)) && round(floatVal($parameterValue))<=round(floatVal($MODERATEMAX))){
            return "MODERATE,".$MODERATEMIN.",".$MODERATEMAX;
        }
        else if(round(floatVal($parameterValue))>=round(floatVal($SATISFACTORYMIN)) && round(floatVal($parameterValue))<=round(floatVal($SATISFACTORYMAX))){
            return "SATISFACTORY,".$SATISFACTORYMIN.",".$SATISFACTORYMAX;
        }else if(round(floatVal($parameterValue))>=round(floatVal($GOODMIN)) && round(floatVal($parameterValue))<=round(floatVal($GOODMAX))){
            return "GOOD,".$GOODMIN.",".$GOODMAX;
        }
    }else{
        return "No sensor found";
    }
}

$parameterName = "CO";
$parameterValue = "0.35";

 //new implentation 1/13/2023
$sensorAqiStatus = getAQiStatus($parameterName,$parameterValue,$mysqli);

$paramaqiStatusRange = explode(",",$sensorAqiStatus);

echo "Parameter range".$paramaqiStatusRange[0].",".$paramaqiStatusRange[1].",".$paramaqiStatusRange[2]."<br>";

$aqi_min_max_range=$aqms_class_range[$paramaqiStatusRange[0]];

$aqi_for_cur_param=rangeMap($parameterValue,$aqi_min_max_range[1],$aqi_min_max_range[0],$paramaqiStatusRange[2],$paramaqiStatusRange[1],9999);

echo "Sensor aqi:".$aqi_for_cur_param;







 







?>