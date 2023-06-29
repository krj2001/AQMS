<?php

/**** Mail Class Begin ****/

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
include("getBodyTemplate.php");
require'vendor/autoload.php';

$sendingMailInfo = array();

class sendAlertMailAndAlertNumber{
    private $senderEmail = "developer5@rdltech.in";
    private $senderName = "AIDEA LABS"; 
    private $Password = "Ce=UmdQ7KFmV7nZ%%";
    private $Gateway = "smtp.gmail.com";
    private $secureType = "tls";

    private $Subject = "";    
    private $recipientEmail = "";
    private $BodyMessage = "";
    private $recepientName = "";

    private $Port = 587;
    private $Host = 'smtp.gmail.com';    

    function __construct($sendingMailInfo) {       
        $this->recipientEmail = $sendingMailInfo['recipientEmail'];      
        $this->recepientName = $sendingMailInfo['recepientName']; 
        $this->Subject = $sendingMailInfo['Subject']; 
        $this->BodyMessage = $sendingMailInfo['bodyMessage']; 
    }

    public function sendMails($mail){
        try{
   
            $mail->setFrom($this->senderEmail, $this->senderName);
            $mail->addAddress($this->recipientEmail,$this->recepientName);
            $mail->Subject = $this->Subject;
            $mail->isHTML(true);
            $mail->Body = $this->BodyMessage;
           
            
            /* SMTP parameters. */
            
            /* Tells PHPMailer to use SMTP. */
            // $mail->isSMTP();
            
            // $headers = "MIME-Version: 1.0" . "\r\n"; 
            // $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n"; 
            
            $mail->SMTPDebug = SMTP::DEBUG_SERVER; 
            /* SMTP server address. */
            $mail->Host = $this->Host;
         
            /* Use SMTP authentication. */
            $mail->SMTPAuth = TRUE;
            
            /* Set the encryption system. */
            $mail->SMTPSecure = $this->secureType;
            
            /* SMTP authentication username. */
            $mail->Username = $this->senderEmail;
            
            /* SMTP authentication password. */
            $mail->Password = $this->Password;
            
            /* Set the SMTP port. */
            $mail->Port = $this->Port;
            
            /* Finally send the mail. */
            $mail->send();
        
            echo "Message has been sent";
        }
        catch (Exception $e)
        {
            echo $e->errorMessage();
        }
        catch (\Exception $e)
        {
            echo $e->getMessage();
        }
    }
    
    public function SMS($msg,$contact_no){   
        print_r($msg);
        $user_name="rdl";
        $api_password="6c83405kwtpsd6wtg";
        $sender="KEWRDL";
        $to=$contact_no;
        $message=rawurlencode($msg);
        $priority="11";
        $entity_id="1201163177490663081";
        $tag_id="1207164690740453778";   
        $sms_http_api_url="http://sms.foosms.com/pushsms.php"."?username=".$user_name."&api_password=".$api_password."&sender=".$sender."&to=".$to."&priority=".$priority."&e_id=".$entity_id."&t_id=".$tag_id."&message=".$message;
        $url = $sms_http_api_url;
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);          
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $resp = curl_exec($curl);
        curl_close($curl);           
    }
}



header('Access-Control-Allow-Origin: *');
ini_set("display_errors",1);
error_reporting(E_ERROR);
include("includes/config.php");
include("includes/config_rempte_var.php");
date_default_timezone_set('Asia/Kolkata');
$time_in_24_hour_format_currentDateTime = date('Y-m-d H:i:s');//currentdatetime

computeTwa($mysqli,$time_in_24_hour_format_currentDateTime);

function computeTwa($mysqli,$time_in_24_hour_format_currentDateTime){
    date_default_timezone_set('Asia/Kolkata'); 
    $getSensorListSql = "select  DISTINCT sensorTag, device_id FROM `segregatedValues`  WHERE timeStamps >= DATE_SUB(NOW() ,INTERVAL 1 MINUTE)";
    //$getSensorListSql = "SELECT Distinct sensorTag,device_id FROM `segregatedValues` WHERE timeStamps >= DATE_SUB(NOW(),INTERVAL 1 MINUTE) and sensorTag = 'pm2.5_gas1'";
    $getResult = mysqli_query($mysqli,$getSensorListSql);
    
    if(mysqli_num_rows($getResult)>0){
        while($getRow = mysqli_fetch_assoc($getResult)){
            echo $getRow['sensorTag']."<br>";
            $sensorTag = $getRow['sensorTag'];
            $deviceId = $getRow['device_id'];
            
            //after getting list of of sensor id, get last avgval of all the sensor id to check alerts
            $getUniqueSensorLastAvgValSql = "select max(scaledVal) as MAX_VAL, MIN(scaledVal) as MIN_VAL, avg(scaledVal) as AVG_VAL, sensorTag FROM `segregatedValues` WHERE sensorTag = '$sensorTag' AND device_id = '$deviceId' AND timeStamps >= DATE_SUB(NOW(),INTERVAL 1 MINUTE)";
            
            $getUniqueSensorLastAvgValResult = mysqli_query($mysqli,$getUniqueSensorLastAvgValSql) or die(mysqli_error($con));
             
            if(mysqli_num_rows($getUniqueSensorLastAvgValResult)>0){
                //data found for last one minute, check for outofrange alert or waring/critical     
                while($sensorLastRow = mysqli_fetch_assoc($getUniqueSensorLastAvgValResult)){
                    $max = $sensorLastRow['MAX_VAL'];
                    $min = $sensorLastRow['MIN_VAL'];
                    $avg_val = $sensorLastRow['AVG_VAL'];
                    $dateTime = explode(" ",$time_in_24_hour_format_currentDateTime);
                    $uploadDate = $dateTime[0];
                    $uploadTime = $dateTime[1];
                    echo "Device_id : ".$deviceId."   Sensor id :".$sensorTag."Avg val.".$max." Date :".$uploadDate. " Time :".$uploadTime. "<br>";
                    $alertType = explode(",",alertMessage($mysqli,$sensorTag,$deviceId,$avg_val,$uploadDate,$uploadTime,$time_in_24_hour_format_currentDateTime));
                    $alert = $alertType[0]; // critical || warning || outofrange
                    $sevierity = $alertType[1];// High || Low || Normal
                    $sensor_id = $alertType[2];
                    $paramterName = $alertType[3];
                    // echo "hello $sensor_id  ".$paramterName."<br>";
                }
            }else{
                echo "No sensor data Found for last minute , and dont check TWA";
            }
        }
    }else{
        echo "No data found for last one minute";
    }
}

function calculateTwa($mysqli,$sensorId,$moving_window_width,$SensorTagShiftDuration,$deviceId,$status){
    
    $getSensorLastOneMinuteData = "SELECT COUNT(*) as cnt, AVG(avg_val) avg_val, sensor_id FROM `sampled_sensor_data_details_MinMaxAvg` where sensor_id = '$sensorId'  and time_stamp>= DATE_SUB(NOW(), INTERVAL 1 MINUTE)";
    $getSensorLastOneMinuteResult = mysqli_query($mysqli,$getSensorLastOneMinuteData);
    if(mysqli_num_rows($getSensorLastOneMinuteResult)>0){
        while($getRowData = mysqli_fetch_assoc($getSensorLastOneMinuteResult)){
           echo $getRowData['sensor_id']." ".$getRowData['avg_val']."<br>";
           $param_C_total= $getRowData['avg_val'];
           $total_N=$getRowData['cnt'];
        }
        echo "Total count with value:".$total_N." AVGVALUE: ".$param_C_total." MovingWindowWidth:".$moving_window_width." shifttagduration".$SensorTagShiftDuration."<br>";
        $sql="select * from twa_info where parameterName='$sensorId' and device_id='$deviceId'";
        $res=mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli));
        $n=mysqli_num_rows($res);
        $twa_prev = 0;
        
        
        if($row=mysqli_fetch_array($res))
        {
            $twa_prev=$row['twaValue'];
            $cur_date_time=date("Y-m-d H:i:s");
            $reset_time=$row['resetDateTime'];
            $time_gap=round(abs(strtotime($cur_date_time)-strtotime($reset_time))/60,2);//old one
            //$time_gap = round(abs(strtotime($cur_date_time) - strtotime($reset_time)) / 60,2);
            
            // if($time_gap>$SensorTagShiftDuration)
            // {
            //     echo "greater <br>";
            //     $twa_prev=0; 
            //     $sql="update twa_info set resetDateTime='$cur_date_time' where parameterName='$sensorId' and device_id = '$deviceId'";
            //     $res=mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli));
            // }
            // else{
            //     echo "not greater <br>";
            // }
        }
        
        //$param_ci=($total_N>0)?($param_C_total/$total_N):0;
        
        $param_ci = $param_C_total ? $param_C_total:0;
        echo "param ci".$param_ci."<br>";
        
        // $moving_window_width_seconds = $moving_window_width*60;
        // $SensorTagShiftDuration_seconds = $SensorTagShiftDuration;
        
        $twa=$twa_prev+(($param_ci*$moving_window_width)/$SensorTagShiftDuration);
        
        echo "twaPre ".$twa_prev."<br>";
        echo "moving_window_width".$moving_window_width;
        echo "SensorTagShiftDuration".$SensorTagShiftDuration;
        echo "twa".$twa;
        if($n>0)
        {
            $sql="update twa_info set twaValue='$twa' , status = '$status' where parameterName='$sensorId' and device_id = '$deviceId'";
            $res=mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli));
            echo "Updated twa for ".$sensorId."<br>";
        }
        else
        {
            $cur_date_time=date("Y-m-d H:i:s");
            $sql="insert into twa_info(device_id,parameterName,twaValue,resetDateTime,status) values('$deviceId','$sensorId','$twa','$cur_date_time','$status')";
            //echo "Insert :".$sql;
            $res=mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli));
            echo "Inserted twa for".$sensorId."<br>";
        }
        $cur_date_time=explode(" ",date("Y-m-d H:i:s"));
        $upload_date = $cur_date_time[0];
        $upload_time = $cur_date_time[1];
        if($twa){
            return $twa;
            //alertMessage($mysqli,$sensorTag,$deviceId,$twa,$upload_date,$upload_time);   
        }
            
    }else{
        //else part
    }
}

function alertMessage($conn,$sensorTag,$deviceId,$val,$uploadDate,$uploadTime,$time_in_24_hour_format_currentDateTime){
    
    $sql = "select * from  sensors where sensorTag='$sensorTag' and deviceId='$deviceId'";
    echo "<br>".$sql."<br>";
    echo "scaled value :".$val."<br>";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
        
    $alertType=array('Warning','outOfRange','Critical','Stel','TWA');
    $alertStatus = array('1','0');
    $alertStatusMessage = array('Cleared','NotCleared');
        
    $a_date = $uploadDate;
    $a_time = $uploadTime;
    
    $companyCode = $row['companyCode'];
    
    $deviceId = $row['deviceId']; 
    $sensorTag = $row['sensorTag'];
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
    
    $twaStartTime = $row['twaStartTime'];
    
    $twaDuration = $row['twaDuration'];
    
    $stelTWACheck = 0;
    
    echo "stelTWAFlag".$isStel."<br>";
    
    //First check for outofrange alert
    if($outofrangeAlertType  == "Both"){
        if(floatval($val) < floatval($row['outofrangeMinValue'])){
            $outofrangeMessage = $row['outofrangeLowAlert'];
            $outOfRangeAlertStatusFlag = 1;
            $stelTWACheck = 0;
            $severityStatus = "LOW";
        }
        else if(floatval($val) > floatval($row['outofrangeMaxValue'])){
            $outofrangeMessage = $row['outofrangeHighAlert'];
            $outOfRangeAlertStatusFlag = 1;
            $stelTWACheck = 0;
            $severityStatus = "HIGH";
        }
        else
        {
            $outOfRangeAlertStatusFlag = 0;
        }
    }else if($outofrangeAlertType  = "High"){
        if(floatval($val) > floatval($row['outofrangeMaxValue'])){
            $outofrangeMessage = $row['outofrangeHighAlert'];
            $outOfRangeAlertStatusFlag = 1;
            $stelTWACheck = 0;
            $severityStatus = "HIGH";
        }
        else
        {
            $outOfRangeAlertStatusFlag = 0;
        }
    }else{
        if(floatval($val) < floatval($row['outofrangeMinValue'])){
            $outofrangeMessage = $row['outofrangeLowAlert'];
            $outOfRangeAlertStatusFlag = 1;
            $stelTWACheck = 0;
            $severityStatus = "LOW";
        }
        else
        {
            $outOfRangeAlertStatusFlag = 0;
        }
    }
    
    //if not out of range check for critical
    if($outOfRangeAlertStatusFlag === 0){
        if($criticalAlertType  = "Both"){
            if(floatval($val) < floatval($row['criticalMinValue'])){
                $criticalMessage = $row['criticalLowAlert'];
                $criticalAlertStatusFlag = 1;
                $stelTWACheck = 1;
                $severityStatus = "LOW";
            }
            else if(floatval($val) > floatval($row['criticalMaxValue'])){
                $criticalMessage = $row['criticalHighAlert'];
                $criticalAlertStatusFlag = 1;
                $stelTWACheck = 1;
                $severityStatus = "HIGH";
            }
            else
            {
              $criticalAlertStatusFlag = 0;
            }
        }else if($criticalAlertType  = "High"){
            if(floatval($val) > floatval($row['criticalMaxValue'])){
                $criticalMessage = $row['criticalHighAlert'];
                $criticalAlertStatusFlag = 1;
                $stelTWACheck = 1;
                $severityStatus = "HIGH";
            }
            else
            {
                $criticalAlertStatusFlag = 0;
            }
        }else{
            if(floatval($val) > floatval($row['criticalMinValue'])){
                $criticalMessage = $row['criticalLowAlert'];
                $criticalAlertStatusFlag = 1;
                $stelTWACheck = 1;
                $severityStatus = "LOW";
            }
            else
            {
              $criticalAlertStatusFlag = 0;
            }
        }
    }
    
    //if not critical alert then check for warning alert
    if($criticalAlertStatusFlag === 0 && $outOfRangeAlertStatusFlag === 0){
        if($warningAlertType  == "Both"){
            if(floatval($val) < floatval($row['warningMinValue'])){
                $wariningMessage = $row['warningLowAlert'];
                $warningAlertStatusFlag = 1;
                $stelTWACheck = 1;
                $severityStatus = "LOW";
            }
            else if(floatval($val) > floatval($row['warningMaxValue'])){
                $wariningMessage = $row['warningHighAlert'];
                $warningAlertStatusFlag = 1;
                $stelTWACheck = 1;
                $severityStatus = "HIGH";
            }
            else
            {
                $normalStatus = 1;
                $warningAlertStatusFlag = 0;
            }
        }else if($warningAlertType  == "High"){
            if(floatval($val) > floatval($row['warningMaxValue'])){
                $wariningMessage =  $row['warningHighAlert'];
                $warningAlertStatusFlag = 1;
                $stelTWACheck = 1;
                $severityStatus = "HIGH";
            }
            else
            {
                $normalStatus = 1;
                $warningAlertStatusFlag = 0;
            }
        }else{
            if(floatval($val) < floatval($row['warningMinValue'])){
                $wariningMessage = $row['warningLowAlert'];
                $warningAlertStatusFlag = 1;
                $stelTWACheck = 1;
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
        $stelTWACheck = 1;
        echo "device id:".$deviceId." sensor tag ".$sensorTag." normal <br>";
            //updating unlatch to 1 and setting to clear if thresholds meets 
       
        if($alarmType == "UnLatch"){ //COMMENTED FOR TIME BEING IF ONLY LATCHED ALARM UPDATE
                $reason = "Values are Normal";
                $severityStatus = "NORMAL";
                $sql = "UPDATE alert_crons set status='$alertStatus[0]',statusMessage='$alertStatusMessage[0]',severity='$severityStatus', Reason='$reason' where sensorId='$sensorId'";
                $res=mysqli_query($conn, $sql) or die(mysqli_error($conn)); 
                
                //update relay output to enable if data is normal for unlatched
                $severity = "NORMAL";
                $alertTypes = "NORMAL";
                updateRelayOutputStatus($conn,$a_date,$a_time,$companyCode,$deviceId,$sensorId,$sensorTag,$alertTypes,$severity,$statusMessage,$hooterRelayStatus);
        }else{
            //update relay output to enable if data is normal for latched
                $severity = "NORMAL";
                $severityStatus = "NORMAL";
                $alertTypes = "NORMAL";
                // $sql = "UPDATE alert_crons set triggeredAlertFlag='0' where sensorId='$sensorId'";
                // $res=mysqli_query($conn, $sql) or die(mysqli_error($conn)); 
                //updateRelayOutputStatus($conn,$a_date,$a_time,$companyCode,$deviceId,$sensorId,$sensorTag,$alertTypes,$severity,$statusMessage,$hooterRelayStatus);
        }
    }
    else{
        if($outOfRangeAlertStatusFlag === 1){
            $alertTypes = $alertType[1];
            $severity = $severityStatus;
            $status = $alertStatus[1];
            $statusMessage = $alertStatusMessage[1];
            echo "Outofrange alert dont check TWA"."<br>";
        }
        
        if($criticalAlertStatusFlag === 1){
            $alertTypes = $alertType[2];
            $severity = $severityStatus;
            $status = $alertStatus[1];
            $statusMessage = $alertStatusMessage[1];
            echo "Critical alert TWA can be checked"."<br>";
        }
            
        if($warningAlertStatusFlag === 1){
            $alertTypes = $alertType[0];
            $severity = $severityStatus;
            $status = $alertStatus[1];
            $statusMessage = $alertStatusMessage[1];
            echo "Warning alert TWA can be checked"."<br>";
        }
    }
    
    if($stelTWACheck && $isStel){
        echo "TWA is enabled for this $sensorTag and its not outofrange so, twa can be checked"."<br>";
            
        //12 hours format
        //$userStartTime = "9:00 AM"; 
        //$userEndTime = "6:00 PM";
            
        //24 hours format
        $userStartTime = "14:10:00"; //get the start time from sensor tag
        //$userEndTime = "10:50:00"; //endtime hard code so not needed and get end time based on duration
            
        $SensorTagShiftDuration =  intVal($twaDuration/60); //duration
            
        //8hrs 480 min duration with 24 hours format
        //$userEndTime = date("H:i:s", strtotime($userStartTime) + strtotime("08:00:00") );
            
        //echo "User Defined StartTime :".$userStartTime."<br>";
        //echo "User Defined EndTime :".$userEndTime."<br>";
        
        $time_in_24_hour_format_startTime  = date("H:i:s", strtotime($twaStartTime));
        $time_in_24_hour_format_endTime  = date("H:i:s", strtotime($userEndTime));
            
        // echo $time_in_24_hour_format_startTime."<br>";
        // echo $time_in_24_hour_format_endTime."<br>";
            
        $time_in_24_hour_format_currentTime = date('H:i:s');
        
        $d1 = strtotime($time_in_24_hour_format_startTime);
        $d2 = strtotime($time_in_24_hour_format_endTime);
        $totalSecondsDiff = abs($d1-$d2);
            
        //echo "User defined Seconds: ".$totalSecondsDiff."<br>"; //seconds
        
        //echo "User defined Minutes: ".round(abs($d2 - $d1) / 60,2). " minute"."<br>";
        
        echo "Current time:".$time_in_24_hour_format_currentTime."<br>";      
        echo "twa start time:".$twaStartTime."<br>";
        //$time_in_24_hour_format_currentTime = "09:00:00";
        
        //echo "User Defined sensorTag duration :".$SensorTagShiftDuration."<br>";
        
        $curDate = $twaStartTime." + $SensorTagShiftDuration minute";
        $addingMinutes= strtotime($curDate);
        $addedEndTime = date('H:i:s', $addingMinutes);
        
        //echo "User defined end Time based on sensorTag duration :".$addedEndTime."<br>";
        
        $moving_window_width = 1;// 2 is taken as min
            
        /* code for based on duration specified between start and endTime
            $duration = 45;
            $start = strtotime('05:13:00'); 
            $end = strtotime($time_in_24_hour_format_currentTime);
            $mins = ($end - $start) / 60;
            $min = intVal($mins);
            echo $mins."<br>";
            if($min>$duration){
            	echo "Shift over";
            }else{
            	echo "shift under working";
            } 
        */
        
        if($time_in_24_hour_format_startTime!="" && $SensorTagShiftDuration!=""){
            $status = "time over";
            
            echo "Current Time is within specified duration:---------->   startTime:$twaStartTime     currentTime:$time_in_24_hour_format_currentTime   EndTime:$addedEndTime Specified duration:$SensorTagShiftDuration minutes"."<br>";
            
            $start = strtotime($twaStartTime); 
            $end = strtotime($time_in_24_hour_format_currentTime);
            $mins = ($end - $start) / 60;
            $min = intVal($mins);
            echo "Minutes from current time to twa startTime :".$mins."<br>";
            if($min>= 0 && $mins<=intVal($SensorTagShiftDuration)){
                
                $status = "shift under working";
            	echo "shift under working";
            	echo "<br>";
            	
            	$twaValue = calculateTwa($conn,$sensorId,$moving_window_width,$SensorTagShiftDuration,$deviceId,$status);
                
                $avg_val = $twaValue;
                echo "twaValue:".$avg_val."<br>";
                echo "twaLimit:".$twaLimit."<br>";
                
                if(floatval($avg_val)>floatval($twaLimit)){
                    echo "trigger twa alert"."<br>";
                    $alertTypes = $alertType[4];
                    $severity = "HIGH";
                    $status = $alertStatus[1];
                    $statusMessage = $alertStatusMessage[1];
                    $Message = $twaAlert;
                    echo "twa for sensor ".$sensor_id."<br>";
                    InsertAlertCronData($conn,$a_date,$a_time,$companyCode,$deviceId,$sensorId,$sensorTag,$alertTypes,$avg_val,$Message,$severity,$status,$statusMessage,$alarmType,$current_time,$sensorStatus,$notificationStatus,$hooterRelayStatus,$lab_id);           
            
                }else{
                    $sql = "UPDATE alert_crons set triggeredAlertFlag='0' where sensorId='$sensorId' and alertCategory = '3'";
                    $res=mysqli_query($conn, $sql) or die(mysqli_error($conn)); 
                    echo "dont trigger twa alert"."<br>";
                }
                
                
                
            }else{
                
                $status = "Shift over";
            	echo "Shift over";
            	$cur_date_time=date("Y-m-d H:i:s");
                $sql="update twa_info set twaValue='0', status = '$status', resetDateTime='$cur_date_time' where parameterName='$sensorId' and device_id = '$deviceId'";
                $res=mysqli_query($conn,$sql) or die(mysqli_error($conn));
                
                echo "Current time is not within duration"."<br>";
                echo "Dont execute twa"."<br>";
                
            	
            	
            	
            	
            	
            }
            
            echo "<br>";
            
             
            
            /*
            //old version calculating twa
            if(($time_in_24_hour_format_currentTime>=$time_in_24_hour_format_startTime)&&($time_in_24_hour_format_currentTime<=$addedEndTime)){
                //call the funtion
                
                // $start = strtotime($time_in_24_hour_format_startTime); 
                // $end = strtotime($addedEndTime);
                // $mins = ($end - $start) / 60;
                // $min = intVal($mins);
                // echo $mins."<br>";
                // if($min>$SensorTagShiftDuration){
                //     $status = "Shift over";
                // 	echo "Shift over";
                // }else{
                //     $status = "shift under working";
                // 	echo "shift under working";
                // }
                
                echo "Current Time is within specified duration:---------->   startTime:$twaStartTime     currentTime:$time_in_24_hour_format_currentTime   EndTime:$addedEndTime Specified duration:$SensorTagShiftDuration minutes"."<br>";
               
                //taking last 1 minute
                $twaValue = calculateTwa($conn,$sensorId,$moving_window_width,$SensorTagShiftDuration,$deviceId);
                
                $avg_val = $twaValue;
                echo "twaValue:".$avg_val."<br>";
                echo "twaLimit:".$twaLimit."<br>";
                
                if(floatval($avg_val)>$twaLimit){
                    echo "trigger twa alert"."<br>";
                    $alertTypes = $alertType[4];
                    $severity = "HIGH";
                    $status = $alertStatus[1];
                    $statusMessage = $alertStatusMessage[1];
                    $Message = $twaAlert;
                    echo "twa for sensor ".$sensor_id."<br>";
                    InsertAlertCronData($conn,$a_date,$a_time,$companyCode,$deviceId,$sensorId,$sensorTag,$alertTypes,$avg_val,$Message,$severity,$status,$statusMessage,$alarmType,$current_time,$sensorStatus,$notificationStatus,$hooterRelayStatus,$lab_id);           
            
                }else{
                    $sql = "UPDATE alert_crons set triggeredAlertFlag='0' where sensorId='$sensorId' and alertCategory = '3'";
                    $res=mysqli_query($conn, $sql) or die(mysqli_error($conn)); 
                    echo "dont trigger twa alert"."<br>";
                }
            }else{
                $cur_date_time=date("Y-m-d H:i:s");
                $sql="update twa_info set twaValue='0', status = '$status', resetDateTime='$cur_date_time' where parameterName='$sensorId' and device_id = '$deviceId'";
                $res=mysqli_query($conn,$sql) or die(mysqli_error($conn));
                
                echo "Current time is not within duration"."<br>";
                echo "Dont execute twa"."<br>";
            }
            
            */
            
            
            
            
        }else{
            echo "Both Starttime and duration is needed";
        }
        
    }else{
        echo "Dont check twa because either out of range or stell not enabled ";
    }
    
    echo "alertType: ".$alertTypes."<br>";
    //returning alertypes and sevierity
    if($alertTypes == ""){
        return "NORMAL".","."NORMAL".",".$sensorId.",".$parameterName;
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
        $alertSql = "select * from  alert_crons where companyCode='$companyCode' and deviceId='$deviceId' and sensorTag='$sensorTag' and status='0' and alertCategory = '3' and triggeredAlertFlag = '1' order by id desc Limit 1";
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
                echo "Device id: ".$deviceId." sensorTag: ".$sensorTag." alert:".$alertTypes."<br>";
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
       
    $triggeredAlertFlag = 1;
    $alertcategory = 3;
    
    $endTime = $a_date." ".$a_time;
    
    $sql = "UPDATE alert_crons set triggeredAlertFlag='0', alertTriggeredDuration='$endTime' where sensorId='$sensorId'  and alertCategory = '$alertcategory' order by id desc LIMIT 1";
    $res=mysqli_query($conn, $sql) or die(mysqli_error($conn)); 
    
     $alertSentenceCase = array("outOfRange"=>"Out Of Range","Critical"=>"Critical","Warning"=>"Warning","NORMAL"=>"Normal", "Stel"=>"Stel", "TWA"=>"TWA");
    echo "senstncecase:".$alertSentenceCase[$alertTypes];
    
    $sql_query = "INSERT INTO `alert_crons`(`a_date`, `a_time`, `companyCode`, `deviceId`, `sensorId`, `sensorTag`, `alertType`,`value`, `msg`, `severity`, `status`,`statusMessage`,`alarmType`,`alertStandardMessage`,`alertCategory`,`triggeredAlertFlag`) VALUES 
                      ('$a_date','$a_time','$companyCode','$deviceId','$sensorId','$sensorTag','$alertTypes','$val','$Message','$severity','$status','$statusMessage','$alarmType','$alertSentenceCase[$alertTypes]','$alertcategory','$triggeredAlertFlag')";
    echo $sql_query;
    $res=mysqli_query($conn, $sql_query) or die(mysqli_error($conn));
    
    //code to update lab hooter
    $updateLabHooter = "UPDATE lab_departments SET 	labHooterStatus = 1 WHERE id = '$lab_id'";
    $resLabHooter = mysqli_query($conn, $updateLabHooter) or die(mysqli_error($conn));
    
    // echo $Message."<br>";
    //if sensor is enabled with email notification only then mail has to be sent
    // commnted sending email for time being
    
    if($notificationStatus == 1){ 
        
        /* old code 12-16-2022 commented for writing utility
     
        $NotificationEnabledUsers = "SELECT * FROM `users` where empNotification = 1  and changePassword = 0 and sec_level_auth = 0 and companyCode='$companyCode'";
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
                
                if($alertTypes == "Warning"){
                    $template = "warning";
                                    
                    $returnedData = explode("&",getBodyAndSubjectForEmail($conn,$customerId,$template));
                    
                    if($returnedData[0] == 1){
                        $body = $returnedData[1];
                        $subject = $returnedData[2];    
                    }else{
                        $body = "";
                        $subject = $alertTypes." ".$sensorTag;
                    }
                }
                
                if($alertTypes == "outOfRange"){
                    $template = "outofrange";
                    $returnedData = explode("&",getBodyAndSubjectForEmail($conn,$customerId,$template));
                    
                    if($returnedData[0] == 1){
                        $body = $returnedData[1];
                        $subject = $returnedData[2];    
                    }else{
                        $body = "";
                        $subject = $alertTypes." ".$sensorTag;
                    }
                }
                
                if($alertTypes == "Critical"){
                    $template = "critical";
                                    
                    $returnedData = explode("&",getBodyAndSubjectForEmail($conn,$customerId,$template));
                    
                    if($returnedData[0] == 1){
                        $body = $returnedData[1];
                        $subject = $returnedData[2];    
                    }else{
                        $body = "";
                        $subject = $alertTypes." ".$sensorTag;
                    }
                }
                
                $mes = $Message." of ".$sensorTag;
                
                $mail = new PHPMailer(TRUE);
                
                $sendingMailInfo['recipientEmail'] = $email;
                $sendingMailInfo['recepientName'] = $name;
                $sendingMailInfo['Subject'] = $subject;
                $sendingMailInfo['bodyMessage'] = $body."<br>".
                                                    "<tr>
                                                        <td>Message : ".$mes."</td>
                                                    </tr><br>
                                                    <tr>
                                                        <td>SensorTag Name: ".$sensorTag."</td>
                                                    </tr><br>
                                                    <tr>  
                                                        <td>Device Name: ".$deviceName."</td>   
                                                    </tr><br>
                                                    <tr>  
                                                        <td>Lab Department Name: ".$labDepName."</td>   
                                                    </tr><br>
                                                    <tr>  
                                                        <td>Floor Name: ".$floorName."</td>   
                                                    </tr><br>
                                                    <tr>  
                                                        <td>Building Name: ".$buildingName."</td>   
                                                    </tr><br>
                                                     <tr>  
                                                        <td>Facility Name: ".$faciltiyName."</td>   
                                                    </tr><br>
                                                    <tr>  
                                                        <td>Branch Name: ".$branchName."</td>   
                                                    </tr><br>
                                                    <tr>  
                                                        <td>State Name: ".$stateName."</td>   
                                                    </tr><br>
                                                    <tr>
                                                        <td>Customer Name: ".$customerId."</td>   
                                                    </tr><br>";
                            
                $mailInfo = new sendAlertMailAndAlertNumber($sendingMailInfo); //Class sendAlertMail is written in Top
                
                $mailInfo->sendMails($mail);//function  of class sensAlertMail for sending emails  for users 
                
                //below sql code written to test whether to check mail funtion is working or else its not important 
                $sqlq = "INSERT INTO `alert_data`(`companyCode`,`machine_name`, `alert`,`a_date`,`a_time`) VALUES ('$companyCode','$email','$mes','$a_date','$a_time')";
                //echo $sqlq;
                $res=mysqli_query($conn, $sqlq) or die(mysqli_error($conn));
            }
        }else{} */
        
        include("getUsers.php");
        include("ApplicationLink.php");
        
        $data = getUsers($mysqli,$companyCode,$locationListParameters);
     
        $cnt = count($data);
      
        for($i=0;$i<$cnt;$i++){
           
            $email = $data[$i]["email"];
            $name = $data[$i]["name"];
            echo "ghfg ".$email." ".$name."<br>";
             
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
                
                if($alertTypes == "Warning"){
                    $template = "warning";
                    $returnedData = explode("&",getBodyAndSubjectForEmail($conn,$customerId,$template));
                    if($returnedData[0] == 1){
                        $body = $returnedData[1];
                        $subject = $returnedData[2];    
                    }else{
                        $body = "";
                        $subject = $alertTypes." ".$sensorTag;
                    }
                }
                
                if($alertTypes == "Out Of Range"){
                    $template = "outofrange";
                    $returnedData = explode("&",getBodyAndSubjectForEmail($conn,$customerId,$template));
                    if($returnedData[0] == 1){
                        $body = $returnedData[1];
                        $subject = $returnedData[2];    
                    }else{
                        $body = "";
                        $subject = $alertTypes." ".$sensorTag;
                    }
                }
                
                if($alertTypes == "Critical"){
                    $template = "critical";
                    $returnedData = explode("&",getBodyAndSubjectForEmail($conn,$customerId,$template));
                    if($returnedData[0] == 1){
                        $body = $returnedData[1];
                        $subject = $returnedData[2];    
                    }else{
                        $body = "";
                        $subject = $alertTypes." ".$sensorTag;
                    }
                }
                
                
                //  if($alertTypes == "TWA"){
                //     $template = "twa";
                //     $returnedData = explode("&",getBodyAndSubjectForEmail($conn,$customerId,$template));
                //     if($returnedData[0] == 1){
                //         $body = $returnedData[1];
                //         $subject = $returnedData[2]." ".$sensorTag;    
                //     }else{
                //         $body = "";
                //         $subject = $alertTypes." ".$sensorTag;
                //     }
                // }
                
                if($alertTypes == "TWA"){
                    $template = "twa";
                     echo getBodyAndSubjectForEmail($conn,$customerId,$template);
                    $returnedData = explode("&",getBodyAndSubjectForEmail($conn,$customerId,$template));
                    
                    if($returnedData[0] == 1){
                        $body = $returnedData[1];
                        $subject = $returnedData[2]." ".$sensorTag;    
                    }else{
                        $body = "";
                        $subject = $alertTypes." ".$sensorTag;
                    }
                }
                // $body = "twa";
                // $subject = "testing TWA";
                $mes = $Message." of ".$sensorTag;
                
                $mail = new PHPMailer(TRUE);
                
                $sendingMailInfo['recipientEmail'] = $email;
                $sendingMailInfo['recepientName'] = $name;
                $sendingMailInfo['Subject'] = $subject;
                $sendingMailInfo['bodyMessage'] = $body."<br>"."<br>".
                                                    "<tr>
                                                        <td>Message : ".$mes."</td>
                                                    </tr><br>
                                                    <tr>
                                                        <td>SensorTag Name: ".$sensorTag."</td>
                                                    </tr><br>
                                                    <tr>  
                                                        <td>Device Name: ".$deviceName."</td>   
                                                    </tr><br>
                                                    <tr>  
                                                        <td>Lab Department Name: ".$labDepName."</td>   
                                                    </tr><br>
                                                    <tr>  
                                                        <td>Floor Name: ".$floorName."</td>   
                                                    </tr><br>
                                                    <tr>  
                                                        <td>Building Name: ".$buildingName."</td>   
                                                    </tr><br>
                                                     <tr>  
                                                        <td>Facility Name: ".$faciltiyName."</td>   
                                                    </tr><br>
                                                    <tr>  
                                                        <td>Branch Name: ".$branchName."</td>   
                                                    </tr><br>
                                                    <tr>  
                                                        <td>State Name: ".$stateName."</td>   
                                                    </tr><br>
                                                    <tr>
                                                        <td>Customer Name: ".$customerId."</td>   
                                                    </tr><br><br>
                                                    <tr>
                                                        <td><a href=".$applicationLink.">Click here</a> to access the application.</td>
                                                    </tr><br>";
                            
                $mailInfo = new sendAlertMailAndAlertNumber($sendingMailInfo); //Class sendAlertMail is written in Top
                
                $mailInfo->sendMails($mail);//function  of class sensAlertMail for sending emails  for users 
                
                //below sql code written to test whether to check mail funtion is working or else its not important 
                $sqlq = "INSERT INTO `alert_data`(`companyCode`,`machine_name`, `alert`,`a_date`,`a_time`) VALUES ('$companyCode','$email','$mes','$a_date','$a_time')";
                //echo $sqlq;
                $res=mysqli_query($conn, $sqlq) or die(mysqli_error($conn));
        }
        
        
        
        
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


// SELECT   FLOOR(UNIX_TIMESTAMP(timestamp)/(15 * 60)) AS timekey
// FROM     table
// GROUP BY timekey;





?>