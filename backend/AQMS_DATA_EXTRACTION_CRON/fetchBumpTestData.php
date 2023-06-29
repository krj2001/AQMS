<?php
/** connection **/
$dbuser="idealab_Aqms";
$dbpass="Aq1sw2de3fr4@aqms";
$host="localhost";
$db="idealab_database";
$con=new mysqli($host,$dbuser,$dbpass,$db);


$json = file_get_contents('php://input');
$data = json_decode($json);
$sensorTagName = $data->sensorTagName;

date_default_timezone_set('Asia/Kolkata');
$time_in_24_hour_format_currentTime = date('Y-m-d H:i:s');//current datetime

$getUniqueSensorLastAvgValSql = "select  avg(last_val) as AvgVal FROM `sampled_sensor_data_details_MinMaxAvg`  WHERE sample_date_time  >= DATE_SUB('".$time_in_24_hour_format_currentTime."',INTERVAL 15 MINUTE) and sensor_id = $sensor_id";
$getUniqueSensorLastAvgValResult = mysqli_query($con,$getUniqueSensorLastAvgValSql) or die(mysqli_error($con));
 
if(mysqli_num_rows($getUniqueSensorLastAvgValResult)>0){
    while($sensorLastRow = mysqli_fetch_assoc($getUniqueSensorLastAvgValResult)){
        $avg_val = $sensorLastRow['AvgVal'];
        
       
        $dateTime = explode(" ",$time_in_24_hour_format_currentTime);
        $uploadDate = $dateTime[0];
        $uploadTime = $dateTime[1];
        // echo "Sensor id :".$sensor_id."Avg val.".$avg_val." Date :".$uploadDate. " Time :".$uploadTime. "<br>";
        alertMessage($con,$sensor_id,$avg_val,$uploadDate,$uploadTime);
    }
}else{
    echo "No sensor Found";
}



?>