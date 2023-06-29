<?php
$dbuser="idealab_Aqms";
$dbpass="Aq1sw2de3fr4@aqms";
$host="localhost";
$db="idealab_database";
$con=new mysqli($host,$dbuser,$dbpass,$db);
date_default_timezone_set('Asia/Kolkata');
$time_in_24_hour_format_currentTime = date('Y-m-d H:i:s');//currentdatetime
$getDeviceListSql = "select  DISTINCT device_id FROM `segregatedValues`  WHERE timeStamps  >= DATE_SUB(NOW(),INTERVAL 1 MINUTE)";
$getDeviceListResult = mysqli_query($con,$getDeviceListSql) or die(mysqli_error($conn));
if(mysqli_num_rows($getDeviceListResult) > 0){
    while($deviceRow = mysqli_fetch_assoc($getDeviceListResult)){
        $deviceId = $deviceRow['device_id'];
        // if device enabled send the sensor data
        $fetchDeviceModeSql = "select * from devices where id = '$deviceId'";
        $fetchDeviceModeResult = mysqli_query($con, $fetchDeviceModeSql);
        
        if(mysqli_num_rows($fetchDeviceModeResult)>0){
            $result = mysqli_fetch_assoc($fetchDeviceModeResult);
            if($result['deviceMode'] == "enabled"){
               // echo "Device id: ".$result['id']." deviceMode ".$result['deviceMode']."<br>";
                $getSensorTagValuesSql = "select max(scaledVal) as MAX_VAL, MIN(scaledVal) as MIN_VAL, avg(scaledVal) as AVG_VAL, device_id, sensorTag FROM `segregatedValues` WHERE device_id = '$deviceId' AND timeStamps >= DATE_SUB(NOW(),INTERVAL 1 MINUTE) GROUP BY sensorTag";
                $getSensorTagValuesResult = mysqli_query($con, $getSensorTagValuesSql) or die(mysqli_error($conn));
                if(mysqli_num_rows($getSensorTagValuesResult)>0){
                    while($sensorRow = mysqli_fetch_assoc($getSensorTagValuesResult)){
                        $max = $sensorRow['MAX_VAL'];
                        $min = $sensorRow['MIN_VAL'];
                        $avg = $sensorRow['AVG_VAL'];
                        $device_id = $sensorRow['device_id'];
                        $sensorTag = $sensorRow['sensorTag'];
                        $lastVal = "";
                        //echo "Max: ".$max." Min: ".$min." Avg: ".$avg." SensorTag: ".$sensorTag." device id: ".$device_id."<br>";
                        $getLastSensorValuesSql = "SELECT * FROM `segregatedValues` where sensorTag = '$sensorTag' order by id desc limit 1";
                        $getLastSensorValuesResult = mysqli_query($con, $getLastSensorValuesSql);
                        if(mysqli_num_rows($getLastSensorValuesResult) > 0){
                            $data = mysqli_fetch_assoc($getLastSensorValuesResult);
                            $lastVal = $data['scaledVal'];
                            $upload_date_time = $data['upload_date_time'];
                            $alertType = explode(",",alertMessage($con,$device_id,$sensorTag,$lastVal));
                            
                            $alert = $alertType[0]; // critical || warning || outofrange
                            $sevierity = $alertType[1];// High || Low || Normal
                            $sensor_id = $alertType[2];
                            $paramterName = $alertType[3];
                            
                            
                            //echo "id &nbsp :".$sensor_id."&nbsp&nbsp Max       &nbsp : ".$max."&nbsp&nbsp Min &nbsp : ".$min."&nbsp&nbsp Avg: &nbsp".$avg."&nbsp&nbsp lastval: &nbsp".$data['scaledVal']."&nbsp&nbsp SensorTag: &nbsp".$sensorTag."&nbsp&nbsp device id: &nbsp".$device_id."  &nbsp alert  &nbsp:".$alert." &nbsp severity &nbsp :".$sevierity."<br>";
                            
                            if($sensor_id != ""){
                                $insertIntoSampleDetailsSql = "INSERT INTO `sampled_sensor_data_details_MinMaxAvg`(`device_id`, `sensor_id`, `parameterName`, `last_val`, `max_val`, `min_val`, `avg_val`, `sample_date_time`, `current_date_time`,`alertType`, `sevierity`,`param_unit`) VALUES
                                    ('$device_id','$sensor_id','$paramterName','$lastVal','$max','$min','$avg','$upload_date_time','$time_in_24_hour_format_currentTime','$alert','$sevierity','ppm')";  
                                $insertIntoSampleDetailsResult = mysqli_query($con,$insertIntoSampleDetailsSql) or die(mysqli_error($con));
                                if($insertIntoSampleDetailsResult){
                                    echo "Inserted &nbsp &nbsp &nbsp Date &nbsp :".$data['upload_date_time']."&nbsp&nbsp Max       &nbsp : ".$max."&nbsp&nbsp Min &nbsp : ".$min."&nbsp&nbsp Avg: &nbsp".$avg."&nbsp&nbsp lastval: &nbsp".$data['scaledVal']."&nbsp&nbsp SensorTag: &nbsp".$sensorTag."&nbsp&nbsp device id: &nbsp".$device_id."  &nbsp alert  &nbsp:".$alert." &nbsp severity &nbsp :".$sevierity."<br>";
                                }else{
                                    echo "Not inserted";
                                }
                            }
                        }else{
                            echo "No data";
                        }
                    }
                }else{
                    echo "No sensorData found";
                }
            }else{
                echo "Device mode is not enabled"."<br>";
            }
        }else{
            echo "No device found  ".$deviceId."<br>";
        }
    }
}else{
    echo "No devices found";
}



function alertMessage($con,$deviceId,$parameterTag,$val){
    $sql = "select * from  sensors where  deviceId='$deviceId' and sensorTag='$parameterTag'";
    echo $sql."<br>";
    
    $result = mysqli_query($con, $sql);
    $row = mysqli_fetch_assoc($result);
        
    $alertType=array('Warning','outOfRange','Critical','Stel','TWA');
    $alertStatus = array('1','0');
    $alertStatusMessage = array('Cleared','NotCleared');
        
    $a_date = $upload_date;
    $a_time = $upload_time;
        
    $from_time = strtotime($upload_date." ".$upload_time);
    
    $companyCode = $companyCode;
    $deviceId = $row['deviceId'];
    $sensorTag = $parameterTag;
    $sensorId = $row['id'];
    $parameterName = $row['sensorNameUnit'];
    
    $warningAlertType = $row['warningAlertType'];
    $outofrangeAlertType = $row['outofrangeAlertType'];
    $criticalAlertType = $row['criticalAlertType'];
    
    $sensorStatus = $row['sensorStatus'];
    $notificationStatus = $row['notificationStatus'];
    $hooterRelayStatus = $row['hooterRelayStatus'];
    
    $alarmType = $row['alarm'];
    
    $wariningMessage = "";
    $outofrangeMessage = "";
    $criticalMessage = "";
    
    $criticalAlertStatusFlag = 0;
    $warningAlertStatusFlag = 0;
    $outOfRangeAlertStatusFlag = 0;
    
    $normalStatus = 0;
    $newData = 0;
    $ALERT_MIN_IMTERVAL_MINUTES = 2;
   
    $alertTypes = ""; 
    $lab_id = $row['lab_id'];
    
    $isStel = $row['isStel'];
    
    $stelLimit = $row['stelLimit'];
    $stelALert = $row['stelAlert'];
    
    $twaLimit = $row['twaLimit'];
    $twaAlert = $row['twaAlert'];
    
    //set alert for stel and twa
    if($isStel == 1){
        //set alert for stel
        if(intval($val)>$stelLimit){
            
            $alertTypes = $alertType[3];
            $severity = "HIGH";
            $status = $alertStatus[1];
            $statusMessage = $alertStatusMessage[1];
            $Message = $stelALert;
        }
        
        //set alert for twa commented for timebeing
        if(intval($val)>$twaLimit){
            $alertTypes = $alertType[4];
            $severity = "HIGH";
            $status = $alertStatus[1];
            $statusMessage = $alertStatusMessage[1];
            $Message = $twaAlert;
        }
    }
    
    //First check for outofrange alert
    if($outofrangeAlertType  == "Both"){
        if(intval($val) < intval($row['outofrangeMinValue'])){
            $outofrangeMessage = $row['outofrangeLowAlert'];
            $outOfRangeAlertStatusFlag = 1;
            $severityStatus = "LOW";
        }
        else if(intval($val) > intval($row['outofrangeMaxValue'])){
            $outofrangeMessage = $row['outofrangeHighAlert'];
            $outOfRangeAlertStatusFlag = 1;
            $severityStatus = "HIGH";
        }
        else
        {
            $outOfRangeAlertStatusFlag = 0;
        }
    }else if($outofrangeAlertType  = "high"){
        if(intval($val) > intval($row['outofrangeMaxValue'])){
            $outofrangeMessage = $row['outofrangeHighAlert'];
            $outOfRangeAlertStatusFlag = 1;
            $severityStatus = "HIGH";
        }
        else
        {
            $outOfRangeAlertStatusFlag = 0;
        }
    }else{
        if(intval($val) < intval($row['outofrangeMinValue'])){
            $outofrangeMessage = $row['outofrangeLowAlert'];
            $outOfRangeAlertStatusFlag = 1;
            $severityStatus = "LOW";
        }
        else
        {
            $outOfRangeAlertStatusFlag = 0;
        }
    }
    
    if($outOfRangeAlertStatusFlag === 0){
        if($criticalAlertType  = "Both"){
            if(intval($val) < intval($row['criticalMinValue'])){
                $criticalMessage = $row['criticalLowAlert'];
                $criticalAlertStatusFlag = 1;
                $severityStatus = "LOW";
            }
            else if(intval($val) > intval($row['criticalMaxValue'])){
                $criticalMessage = $row['criticalHighAlert'];
                $criticalAlertStatusFlag = 1;
                 $severityStatus = "HIGH";
            }
            else
            {
              $criticalAlertStatusFlag = 0;
            }
        }else if($criticalAlertType  = "high"){
            if(intval($val) > intval($row['criticalMaxValue'])){
                $criticalMessage = $row['criticalHighAlert'];
                $criticalAlertStatusFlag = 1;
                 $severityStatus = "HIGH";
            }
            else
            {
                $criticalAlertStatusFlag = 0;
            }
        }else{
            if(intval($val) > intval($row['criticalMinValue'])){
                $criticalMessage = $row['criticalLowAlert'];
                $criticalAlertStatusFlag = 1;
                $severityStatus = "LOW";
            }
            else
            {
              $criticalAlertStatusFlag = 0;
            }
        }
    }
    
    //if check for critical alert is pass then check for warning alert
    if($criticalAlertStatusFlag === 0 && $outOfRangeAlertStatusFlag === 0){
        if($warningAlertType  == "Both"){
            if(intval($val) < intval($row['warningMinValue'])){
                $wariningMessage = $row['warningLowAlert'];
                $warningAlertStatusFlag = 1;
                $severityStatus = "LOW";
            }
            else if(intval($val) > intval($row['warningMaxValue'])){
                $wariningMessage = $row['warningHighAlert'];
                $warningAlertStatusFlag = 1;
                $severityStatus = "HIGH";
            }
            else
            {
                $normalStatus = 1;
                $warningAlertStatusFlag = 0;
            }
        }else if($warningAlertType  == "high"){
            if(intval($val) > intval($row['warningMaxValue'])){
                $wariningMessage =  $row['warningHighAlert'];
                $warningAlertStatusFlag = 1;
                 $severityStatus = "HIGH";
            }
            else
            {
                $normalStatus = 1;
                $warningAlertStatusFlag = 0;
            }
        }else{
            if(intval($val) < intval($row['warningMinValue'])){
                $wariningMessage = $row['warningLowAlert'];
                $warningAlertStatusFlag = 1;
                 $severityStatus = "LOW";
            }
            else
            {
               $normalStatus = 1;
               $warningAlertStatusFlag = 0;
            }
        }
    }
   
    if($normalStatus === 1){
    //updating unlatch to 1 and setting to clear if thresholds meets 
        if($alarmType == "UnLatch"){ //COMMENTED FOR TIME BEING IF ONLY LATCHED ALARM UPDATE
                $reason = "Values are Normal";
                $severityStatus = "NORMAL";
                // $sql = "UPDATE alert_crons set status='$alertStatus[0]',statusMessage='$alertStatusMessage[0]',severity='$severityStatus', Reason='$reason' where sensorId='$sensorId'";
                // $res=mysqli_query($conn, $sql) or die(mysqli_error($conn)); 
                
                //update relay output to enable if data is normal for unlatched
                $severity = "NORMAL";
                $alertTypes = "NORMAL";
                // updateRelayOutputStatus($conn,$a_date,$a_time,$companyCode,$deviceId,$sensorId,$sensorTag,$alertTypes,$severity,$statusMessage,$hooterRelayStatus);
        }else{
            //update relay output to enable if data is normal for latched
                $severity = "NORMAL";
                $severityStatus = "NORMAL";
                $alertTypes = "NORMAL";
                // updateRelayOutputStatus($conn,$a_date,$a_time,$companyCode,$deviceId,$sensorId,$sensorTag,$alertTypes,$severity,$statusMessage,$hooterRelayStatus);
        }
    }
    else{
            if($criticalAlertStatusFlag === 1){
                $alertTypes = $alertType[2];
                $severity = $severityStatus;
                $status = $alertStatus[1];
                $statusMessage = $alertStatusMessage[1];
                //InsertAlertCronData($conn,$a_date,$a_time,$companyCode,$deviceId,$sensorId,$sensorTag,$alertTypes,$val,$criticalMessage,$severity,$status,$statusMessage,$alarmType,$current_time,$sensorStatus,$notificationStatus,$hooterRelayStatus,$lab_id);
                
            }
        
            if($outOfRangeAlertStatusFlag === 1){
                $alertTypes = $alertType[1];
                $severity = $severityStatus;
                $status = $alertStatus[1];
                $statusMessage = $alertStatusMessage[1];
               // InsertAlertCronData($conn,$a_date,$a_time,$companyCode,$deviceId,$sensorId,$sensorTag,$alertTypes,$val,$outofrangeMessage,$severity,$status,$statusMessage,$alarmType,$current_time,$sensorStatus,$notificationStatus,$hooterRelayStatus,$lab_id);           
            }
        
            if($warningAlertStatusFlag === 1){
                $alertTypes = $alertType[0];
                $severity = $severityStatus;
                $status = $alertStatus[1];
                $statusMessage = $alertStatusMessage[1];
               // InsertAlertCronData($conn,$a_date,$a_time,$companyCode,$deviceId,$sensorId,$sensorTag,$alertTypes,$val,$wariningMessage,$severity,$status,$statusMessage,$alarmType,$current_time,$sensorStatus,$notificationStatus,$hooterRelayStatus,$lab_id);
            }
    }
    
    //returning alertypes and sevierity
    if($alertTypes == ""){
        return "NORMAL".","."NORMAL";
    }else{
        return $alertTypes.",".$severityStatus.",".$sensorId.",".$parameterName;    
    }
}








?>