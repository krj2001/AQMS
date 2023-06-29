<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
ini_set("display_errors",1);
include("includes/config.php"); //connection to db

error_reporting(1);

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, DELETE, PUT, PATCH, OPTIONS');
    header('Access-Control-Allow-Headers: token, Content-Type');
    header('Access-Control-Max-Age: 1728000');
    header('Content-Length: 0');
    header('Content-Type: text/plain');
    die();
}

$json = file_get_contents('php://input');
$data = json_decode($json);
$sensorTagNameReq = $data->sensorTagName;



$mode = 4;

date_default_timezone_set('Asia/Kolkata');
$time_in_24_hour_format_currentTime = date('Y-m-d H:i:s');//currentdatetime

$sql="select * from bumpTest_aqmi_json_data WHERE  date_time  >= DATE_SUB('".$time_in_24_hour_format_currentTime."',INTERVAL 15 SECOND) Limit 1";
//select * from bumpTest_aqmi_json_data WHERE  j_data Like "%pm2.5_gas1%" and date_time  >= DATE_SUB('".$time_in_24_hour_format_currentTime."',INTERVAL 1500 SECOND) Limit 1
$res=mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli));

$insertDataArray = array();
$date = "";
$time = "";
$MODE = "";

$data_available="NA";
$ret_data_array=array();
$ret_data_array["MAX"]="NA";
$ret_data_array["MIN"]="NA";
$ret_data_array["AVG"]="NA";
$ret_data_array["LAST"]="NA";
$ret_data_array["UNIT"]="NA";
$ret_data_array["PARAMETER_NAME"]=$sensorTagNameReq;
$ret_data_array["DATETIME"]=$time_in_24_hour_format_currentTime;

if(mysqli_num_rows($res)>0){
    while($row=mysqli_fetch_array($res))
    {
        $insertDataArray = []; 
        $id = $row['id'];
        $json_data=$row['j_data'];
        $json_data_obj=json_decode($json_data,true);
        
        print_r($json_data_obj);
        $upload_date=$json_data_obj["DATE"];
        $upload_time=$json_data_obj["TIME"];
        $deviceId = $json_data_obj["DEVICE_ID"];
        $upload_date_time=$upload_date." ".$upload_time;
        $data_retreival_timestamp=strtotime($upload_date_time);
        $vals=$json_data_obj[($sensorTagNameReq)];
        $rawValue = floatval($vals);
        $scaledValue= scalingValue($mysqli,$deviceId,$sensorTagNameReq,$rawValue);
        $ret_data_array["LAST"] = $scaledValue;
        $ret = [
            'data' => $ret_data_array,
        ];
        print_r($ret);
        
        //Method 3 getting data of last one minute based current time 
        /*
        foreach($json_data_obj as $key => $value) 
        {
            
            //{"DATE":"2022-09-02","TIME":"17:41:54","COMPANY":"A-TEST","LOCATION":"4","BRANCH":"3","FACILITY":"4","BULDING":"2","FLOOR":"2","LAB":"3","DEVICE_ID":"3","MODE":"2","ACCESS_CODE":"1003","pb_gas01":"0.5","NH3_gas1":"200","SO2_gas1":"40","O3_gas1":"50","pm2.5_gas1":"100","PM10_GAS2":"50","NO2_gas2":"40"}
            if($key !== "COMPANY" && $key !== "LOCATION" && $key !== "BRANCH" && $key !== "FACILITY" && $key !== "BULDING" && $key !== "FLOOR" && $key !== "LAB"){
                    
                if($key == "DATE"){
                    $insertDataArray[$key] = $value; 
                    $date = $value;
                }
                
                if($key == "TIME"){
                    $insertDataArray[$key] = $value;      
                    $time = $value;
                }
                
                if($key == "DEVICE_ID"){
                    $insertDataArray[$key] = $value;   
                    $deviceId = $value;
                }
                
                if($key == "ACCESS_CODE"){
                    $insertDataArray[$key] = $value;   
                    $accessCode = $value;
                }
                
                if($key == "MODE"){
                    $insertDataArray[$key] = $value;   
                    $MODE = $value;
                }
                
                if($key == $sensorTagNameReq){
                    $sensorTagName = $key;
                    $rawValue = $value;
                    $dataAvailable = 1;
                    $insertDataArray[$key] = $value;  
                        $scaledValue= scalingValue($mysqli,$deviceId,$sensorTagName,$rawValue);
                        if($MODE == $mode){
                            echo "Bumptest mode";
                            // $insertDataArray["LAST"] = $scaledValue;
                        }else{
                            echo "Other modde";
                    `   }  
                
                }else{
                    
                }
            }
        }*/
    } 
}else{
    print_r($ret_data_array);
}





function scalingValue($conn,$deviceId,$parameterTag,$val){

    $sql = "SELECT sensor_units.*, sensors.* FROM `sensor_units` INNER JOIN sensors ON sensor_units.id = sensors.sensorName WHERE sensors.sensorTag = '$parameterTag' and sensors.deviceId = '$deviceId'";
   
    $result = mysqli_query($conn,$sql);
    $row = mysqli_fetch_assoc($result);
    
    $outputType = $row['sensorType'];
    
    //Reading range values of a sensor
    $minRatedReading = $row['minRatedReading']; //Xmin 
    $maxRatedReading = $row['maxRatedReading']; //Xmax
    
    //scalling values of sensor
    $minRatedReadingScale = $row['minRatedReadingScale'];//Ymin
    $maxRatedReadingScale = $row['maxRatedReadingScale'];//Ymax
  
    //((Ymax-Ymin)/(Xmax-Xmin)) * ($val - Xmin) + Ymin  //formula for scalling 
    //$x = ((100-1)/(55-45)) * (52 - 45) + 1;
    
    $scaledValue = "0";
    
    //scaled for only 4-20v output type
    if($outputType === "4-20v"){
        $scaledValue = (($maxRatedReadingScale-$minRatedReadingScale)/($maxRatedReading-$minRatedReading)) * ($val - $minRatedReading) + $minRatedReadingScale;    
    }else if($outputType === "0-10v"){
        $scaledValue = (($maxRatedReadingScale-$minRatedReadingScale)/($maxRatedReading-$minRatedReading)) * ($val - $minRatedReading) + $minRatedReadingScale;    
    }
    else{
        $scaledValue = $val;
    }
   // echo "CompanyCode: ".$row['companyCode']." MaxRateScale:      ".$maxRatedReadingScale."  MinRateScale:".$minRatedReadingScale."  MaxReading:".$maxRatedReading."  MinReading:".$minRatedReading." of SensorTag : ".$parameterTag." with id:".$row['id']."  RawValue: ".$val."  ScaledValue: ".$scaledValue."<br>";
    return $scaledValue;
}




?>