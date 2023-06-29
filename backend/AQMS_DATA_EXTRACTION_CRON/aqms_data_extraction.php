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
        print_r(json_encode($ret));
    } 
}else{
     print_r(json_encode($ret_data_array));
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
    
    $scaledValue = 10;
    
    //scaled for only 4-20v output type
    if($outputType === "4-20v"){
        $scaledValue = (($maxRatedReadingScale-$minRatedReadingScale)/($maxRatedReading-$minRatedReading)) * ($val - $minRatedReading) + $minRatedReadingScale;    
    }else if($outputType === "0-10v"){
        $scaledValue = (($maxRatedReadingScale-$minRatedReadingScale)/($maxRatedReading-$minRatedReading)) * ($val - $minRatedReading) + $minRatedReadingScale;    
    }
    else{
        $scaledValue = $val;
    }
   //echo "CompanyCode: ".$row['companyCode']." MaxRateScale:      ".$maxRatedReadingScale."  MinRateScale:".$minRatedReadingScale."  MaxReading:".$maxRatedReading."  MinReading:".$minRatedReading." of SensorTag : ".$parameterTag." with id:".$row['id']."  RawValue: ".$val."  ScaledValue: ".$scaledValue."<br>";
    return $scaledValue;
}




?>