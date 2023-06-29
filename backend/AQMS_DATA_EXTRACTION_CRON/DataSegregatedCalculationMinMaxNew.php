<?php
$dbuser="idealab_Aqms";
$dbpass="Aq1sw2de3fr4@aqms";
$host="localhost";
$db="idealab_database";
$con=new mysqli($host,$dbuser,$dbpass,$db);
date_default_timezone_set('Asia/Kolkata');
$time_in_24_hour_format_currentTime = date('Y-m-d H:i:s');//currentdatetime
$getDeviceListSql = "select  DISTINCT device_id FROM `segregatedValues`  WHERE timeStamps  >= DATE_SUB(NOW(),INTERVAL 2 MINUTE)";
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
                            
                            echo "Last val ".$lastVal." Max: ".$max." Min: ".$min." Avg: ".$avg." SensorTag: ".$sensorTag." device id: ".$device_id."<br>";
                            
                            
                            $upload_date_time = $data['upload_date_time'];
                            
                            $dateTime = explode(" ",$upload_date_time);
                            $uploadDate = $dateTime[0];
                            $uploadTime = $dateTime[1];
                            $alertType = explode(",",alertMessage($con,$device_id,$sensorTag,$lastVal,$uploadDate,$uploadTime));
                            
                            $alert = $alertType[0]; // critical || warning || outofrange
                            $sevierity = $alertType[1];// High || Low || Normal
                            $sensor_id = $alertType[2];
                            $paramterName = $alertType[3];
                            
                            
                            echo "id &nbsp :".$sensor_id."&nbsp&nbsp Max       &nbsp : ".$max."&nbsp&nbsp Min &nbsp : ".$min."&nbsp&nbsp Avg: &nbsp".$avg."&nbsp&nbsp lastval: &nbsp".$data['scaledVal']."&nbsp&nbsp SensorTag: &nbsp".$sensorTag."&nbsp&nbsp device id: &nbsp".$device_id."  &nbsp alert  &nbsp:".$alert." &nbsp severity &nbsp :".$sevierity."<br>";
                            
                          
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
                    
                    //update connected status to 1 if data found 
                    $updateDevicestatusSql = "Update devices set disconnectedStatus = 0 where id = $deviceId";
                    $updateDeviceStatusResult = mysqli_query($con,$updateDevicestatusSql) or die(mysqli_error($con));
                    if($updateDeviceStatusResult){
                        echo "device disconnect status updated <br>";
                    }else{
                        
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



function alertMessage($con,$deviceId,$parameterTag,$val,$uploadDate,$uploadTime){
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
                //updateRelayOutputStatus($conn,$a_date,$a_time,$companyCode,$deviceId,$sensorId,$sensorTag,$alertTypes,$severity,$statusMessage,$hooterRelayStatus);
        }else{
            //update relay output to enable if data is normal for latched
                $severity = "NORMAL";
                $severityStatus = "NORMAL";
                $alertTypes = "NORMAL";
                //updateRelayOutputStatus($conn,$a_date,$a_time,$companyCode,$deviceId,$sensorId,$sensorTag,$alertTypes,$severity,$statusMessage,$hooterRelayStatus);
        }
    }
    else{
            if($criticalAlertStatusFlag === 1){
                $alertTypes = $alertType[2];
                $severity = $severityStatus;
                $status = $alertStatus[1];
                $statusMessage = $alertStatusMessage[1];
               // InsertAlertCronData($conn,$a_date,$a_time,$companyCode,$deviceId,$sensorId,$sensorTag,$alertTypes,$val,$criticalMessage,$severity,$status,$statusMessage,$alarmType,$current_time,$sensorStatus,$notificationStatus,$hooterRelayStatus,$lab_id);
                
            }
        
            if($outOfRangeAlertStatusFlag === 1){
                $alertTypes = $alertType[1];
                $severity = $severityStatus;
                $status = $alertStatus[1];
                $statusMessage = $alertStatusMessage[1];
                //InsertAlertCronData($conn,$a_date,$a_time,$companyCode,$deviceId,$sensorId,$sensorTag,$alertTypes,$val,$outofrangeMessage,$severity,$status,$statusMessage,$alarmType,$current_time,$sensorStatus,$notificationStatus,$hooterRelayStatus,$lab_id);           
            }
        
            if($warningAlertStatusFlag === 1){
                $alertTypes = $alertType[0];
                $severity = $severityStatus;
                $status = $alertStatus[1];
                $statusMessage = $alertStatusMessage[1];
                //InsertAlertCronData($conn,$a_date,$a_time,$companyCode,$deviceId,$sensorId,$sensorTag,$alertTypes,$val,$wariningMessage,$severity,$status,$statusMessage,$alarmType,$current_time,$sensorStatus,$notificationStatus,$hooterRelayStatus,$lab_id);
            }
    }
    
    //returning alertypes and sevierity
    if($alertTypes == ""){
        return "NORMAL".","."NORMAL";
    }else{
        return $alertTypes.",".$severityStatus.",".$sensorId.",".$parameterName;    
    }
}

$emailFlag = 1;

//this function is to check last inserted uncleared alert data, if uncleared dont insert, if cleared insert the data based on sevierity and alertType
function InsertAlertCronData($conn,$a_date,$a_time,$companyCode,$deviceId,$sensorId,$sensorTag,$alertTypes,$val,$Message,$severity,$status,$statusMessage,$alarmType,$current_time,$sensorStatus,$notificationStatus,$hooterRelayStatus,$lab_id){
   
    if($sensorStatus == 0){
         //if sensorStatus == 0, means sensor is disabled and alert should not be inserted.......
         echo $sensorTag." is disabled";
    }else{
        $alertSql = "select * from  alert_crons where companyCode='$companyCode' and deviceId='$deviceId' and sensorTag='$sensorTag' and status='0'  order by id desc Limit 1";
        $alertResult = mysqli_query($conn, $alertSql);
        $alertRow = mysqli_fetch_assoc($alertResult);
        $tot_rows=mysqli_num_rows($alertResult);
        
        if($tot_rows > 0){
            if($alertTypes  == $alertRow['alertType']){
                if($severity == $alertRow['severity']){
                    //Not to insert alert data if already present and status set to 0
                }
                else{
                    InsertDataToAlertTable($conn,$a_date,$a_time,$companyCode,$deviceId,$sensorId,$sensorTag,$alertTypes,$val,$Message,$severity,$status,$statusMessage,$alarmType,$current_time,$notificationStatus,$lab_id);   
                }
            }
            else{
                
                //not same alert so inserting 
                
                InsertDataToAlertTable($conn,$a_date,$a_time,$companyCode,$deviceId,$sensorId,$sensorTag,$alertTypes,$val,$Message,$severity,$status,$statusMessage,$alarmType,$current_time,$notificationStatus,$lab_id);
            }    
        }
        else{
            InsertDataToAlertTable($conn,$a_date,$a_time,$companyCode,$deviceId,$sensorId,$sensorTag,$alertTypes,$val,$Message,$severity,$status,$statusMessage,$alarmType,$current_time,$notificationStatus,$lab_id);
        } 
    }
    
    //disable relaystatus if the value is not normal
    updateRelayOutputStatus($conn,$a_date,$a_time,$companyCode,$deviceId,$sensorId,$sensorTag,$alertTypes,$severity,$statusMessage,$hooterRelayStatus);
}

//funtion to insert alert data and send mail.
function InsertDataToAlertTable($conn,$a_date,$a_time,$companyCode,$deviceId,$sensorId,$sensorTag,$alertTypes,$val,$Message,$severity,$status,$statusMessage,$alarmType,$current_time,$notificationStatus,$lab_id){
       
    $sql_query = "INSERT INTO `alert_crons`(`a_date`, `a_time`, `companyCode`, `deviceId`, `sensorId`, `sensorTag`, `alertType`,`value`, `msg`, `severity`, `status`,`statusMessage`,`alarmType`) VALUES 
                      ('$a_date','$a_time','$companyCode','$deviceId','$sensorId','$sensorTag','$alertTypes','$val','$Message','$severity','$status','$statusMessage','$alarmType')";
    
    echo $sql_query;
    $res=mysqli_query($conn, $sql_query) or die(mysqli_error($conn));
    
    //code to update lab hooter
    $updateLabHooter = "UPDATE lab_departments SET 	labHooterStatus = 1 WHERE id = '$lab_id'";
    $resLabHooter = mysqli_query($conn, $updateLabHooter) or die(mysqli_error($conn));
    
    
   // echo $Message."<br>";
   
   //if sensor is enabled with email notification only then mail has to be sent
   
    // commnted sending email for time being
    
    
    if($notificationStatus == 1){ 
     
        /* 
     
        $NotificationEnabledUsers = "SELECT * FROM `users` where empNotification = 1  and companyCode='$companyCode'";
        $NEUResults = mysqli_query($conn,$NotificationEnabledUsers);
        if(mysqli_num_rows($NEUResults) > 0){
            while($row = mysqli_fetch_array($NEUResults)){
                $notificationStatus = $row['empNotification'];
                $email = $row['email'];
                $name = $row['name'];
                $contactNo = $row['mobileno'];
                
                //for timebeing testing by inserting data to table
                
                $getSensorDetails = "SELECT customers.customerId, locations.stateName, branches.branchName, facilities.facilityName, buildings.buildingName, floors.floorName, lab_departments.labDepName, devices.deviceName, sensors.sensorTag FROM customers 
                        INNER JOIN locations ON customers.customerId = locations.companyCode 
                        INNER JOIN branches ON customers.customerId = branches.companyCode AND locations.id = branches.location_id 
                        INNER JOIN facilities ON customers.customerId = facilities.companyCode AND locations.id = facilities.location_id AND branches.id = facilities.branch_id 
                        INNER JOIN buildings ON customers.customerId = buildings.companyCode AND  locations.id = buildings.location_id  AND branches.id = buildings.branch_id AND facilities.id = buildings.facility_id
                        INNER JOIN floors ON customers.customerId = floors.companyCode AND  locations.id = floors.location_id  AND  branches.id = floors.branch_id AND  facilities.id = floors.facility_id AND buildings.id = floors.building_id
                        INNER JOIN lab_departments ON customers.customerId = lab_departments.companyCode AND  locations.id = lab_departments.location_id  AND  branches.id = lab_departments.branch_id AND  facilities.id = lab_departments.facility_id AND buildings.id = lab_departments.building_id AND floors.id = lab_departments.floor_id 
                        INNER JOIN devices ON customers.customerId = devices.companyCode AND  locations.id = devices.location_id  AND  branches.id = devices.branch_id AND  facilities.id = devices.facility_id AND buildings.id = devices.building_id AND floors.id = devices.floor_id AND lab_departments.id = devices.lab_id
                        INNER JOIN sensors ON customers.customerId = sensors.companyCode AND  locations.id = sensors.location_id  AND  branches.id = sensors.branch_id AND  facilities.id = sensors.facility_id AND buildings.id = sensors.building_id AND floors.id = sensors.floor_id AND lab_departments.id = sensors.lab_id AND devices.id = sensors.deviceid
                        where customers.customerId = '$companyCode' AND sensors.sensorTag = '$sensorTag'";
                        
                $getSensorResult = mysqli_query($conn, $getSensorDetails);
                $getSensorRow = mysqli_fetch_assoc($getSensorResult);
                $tot_rows=mysqli_num_rows($getSensorResult);
                
                $customerId = $getSensorRow['customerId'];
                $branchName = $getSensorRow['branchName'];
                $stateName = $getSensorRow['stateName'];
                $buildingName =  $getSensorRow['buildingName'];
                $faciltiyName = $getSensorRow['facilityName'];
                $floorName = $getSensorRow['floorName'];
                $labDepName = $getSensorRow['labDepName'];
                $deviceName = $getSensorRow['deviceName'];
                $sensorTag = $getSensorRow['sensorTag'];
                
                $mes = $Message." of ".$sensorTag;
                
                $mail = new PHPMailer(TRUE);
                
                $sendingMailInfo['recipientEmail'] = $email;
                $sendingMailInfo['recepientName'] = $name;
                $sendingMailInfo['Subject'] = $alertTypes." ".$sensorTag;
                $sendingMailInfo['bodyMessage'] = " <tr>
                                                        <td>Message : ".$mes."</td>
                                                    </tr>
                                                    <tr>
                                                        <td>SensorTag Name: ".$sensorTag."</td>
                                                    </tr>
                                                    <tr>  
                                                        <td>Device Name: ".$deviceName."</td>   
                                                    </tr>
                                                    <tr>  
                                                        <td>Lab Department Name: ".$labDepName."</td>   
                                                    </tr>
                                                    <tr>  
                                                        <td>Floor Name: ".$floorName."</td>   
                                                    </tr>
                                                    <tr>  
                                                        <td>Building Name: ".$buildingName."</td>   
                                                    </tr>
                                                     <tr>  
                                                        <td>Facility Name: ".$facilityName."</td>   
                                                    </tr>
                                                    <tr>  
                                                        <td>Branch Name: ".$branchName."</td>   
                                                    </tr>
                                                    <tr>  
                                                        <td>State Name: ".$stateName."</td>   
                                                    </tr>
                                                    <tr>
                                                        <td>Customer Name: ".$customerId."</td>   
                                                    </tr>";
                            
               // $mailInfo = new sendAlertMailAndAlertNumber($sendingMailInfo); //Class sendAlertMail is written in Top
                
                //$mailInfo->sendMails($mail);//function  of class sensAlertMail for sending emails  for users 
                
                //below sql code written to test whether to check mail funtion is working or else its not important 
                $sqlq = "INSERT INTO `alert_data`(`companyCode`,`machine_name`, `alert`,`a_date`,`a_time`) VALUES ('$companyCode','$email','$mes','$a_date','$a_time')";
                echo $sqlq;
                $res=mysqli_query($conn, $sqlq) or die(mysqli_error($conn));
            }
        }else{} */
    } 
    
    
}

//updating hooter relay status based sensor parameter status
function updateRelayOutputStatus($conn,$a_date,$a_time,$companyCode,$deviceId,$sensorId,$sensorTag,$alertTypes,$severity,$statusMessage,$hooterRelayStatus){
   
    $statusMessage = "";
    $relayOutputStatus = "";
  
    if($alertTypes === "NORMAL"){
        $statusMessage = "ENABLED";
        $relayOutputStatus = "1";
        $enableRelay = "UPDATE `sensors` SET `hooterRelayStatus`='1', `relayMessage`='$statusMessage' where  id = '$sensorId'";
        $enableRes = mysqli_query($conn, $enableRelay) or die(mysqli_error($conn));
        
    }else{
        $statusMessage = "DISABLED";
        $relayOutputStatus = "0";
        $disableRelay = "UPDATE `sensors` SET `hooterRelayStatus`='0', `relayMessage`='$statusMessage' where  id = '$sensorId'";
        $disableRes = mysqli_query($conn, $disableRelay) or die(mysqli_error($conn));
    }
    
    $insertRelayStatus = "";
    
    $getLastSensorStatus = "SELECT `id`, `a_date`, `a_time`, `companyCode`, `deviceId`, `sensorId`, `sensorTag`, `alertType`, `severity`, `statusMessage`, `relayOutputStatus`, `created_at`, `updated_at` FROM `relay_output_resultsTest` WHERE sensorId = '$sensorId' order by id desc LIMIT 1";
    $getLastSensorStatusResult = mysqli_query($conn, $getLastSensorStatus) or die(mysqli_error($conn));
    $getLastSensorStatusRow = mysqli_fetch_assoc($getLastSensorStatusResult);
    $tot_rows=mysqli_num_rows($getLastSensorStatusResult);
    
    if($tot_rows>0){
        if($statusMessage === $getLastSensorStatusRow["statusMessage"]){
            // if($getLastSensorStatusRow["alertType"] !== "SensorFailure"){
            //      //if relay is disabled and alerttype is sensorfailure then insert record 
            //      $insertRelayStatus = 1;
            // }else{
            //     //dont insert relay status record
            // }
        }else{
            $insertRelayStatus = 1;
        }
    }else{
        $insertRelayStatus = 1;
    }
    
    //enter relay status record
    if($insertRelayStatus == 1){
        $insertRelaySql = "INSERT INTO `relay_output_resultsTest`(`a_date`, `a_time`, `companyCode`, `deviceId`, `sensorId`, `sensorTag`, `alertType`, `severity`, `statusMessage`, `relayOutputStatus`) 
                VALUES ('$a_date','$a_time','$companyCode','$deviceId','$sensorId','$sensorTag','$alertTypes','$severity','$statusMessage','$relayOutputStatus')";
        $insertRelayResult = mysqli_query($conn, $insertRelaySql) or die(mysqli_error($conn)); 
       
    }else{}
}





?>