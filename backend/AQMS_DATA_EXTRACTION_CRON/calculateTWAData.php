<?php

/**** Mail Class Begin ****/

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require'vendor/autoload.php';

$sendingMailInfo = array();

class sendAlertMailAndAlertNumber{
    private $senderEmail = "abhishek@rdltech.in";
    private $senderName = "Abhishek"; 
    private $Password = "3**mg8S2";
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

computeTwa($mysqli);

function computeTwa($mysqli){
    date_default_timezone_set('Asia/Kolkata');
    
    $getLastoneMinuteDistinctSensors = "SELECT Distinct sensorTag,device_id FROM `segregatedValues` WHERE timeStamps >= DATE_SUB(NOW(),INTERVAL 1 MINUTE)";
    $getResult = mysqli_query($mysqli,$getLastoneMinuteDistinctSensors);
    $time_in_24_hour_format_currentDateTime = date('Y-m-d H:i:s');//currentdatetime
    if(mysqli_num_rows($getResult)>0){
        while($getRow = mysqli_fetch_assoc($getResult)){
            //echo $getRow['sensorTag']."<br>";
            $sensorTag = $getRow['sensorTag'];
            $deviceId = $getRow['device_id'];
            
            // $getSensorTagTime = "select * from "
            
            //12 hours format
            //$userStartTime = "9:00 AM"; 
            //$userEndTime = "6:00 PM";
            
            $userStartTime = "15:00:00"; //get the start time from sensor tag
            $userEndTime = "10:50:00";
            
            $SensorTagShiftDuration =  120;
            
            //8hrs 480 min duration with 24 hours format
            //$userEndTime = date("H:i:s", strtotime($userStartTime) + strtotime("08:00:00") );
            
            //echo "User Defined StartTime :".$userStartTime."<br>";
            //echo "User Defined EndTime :".$userEndTime."<br>";
            
            $time_in_24_hour_format_startTime  = date("H:i:s", strtotime($userStartTime));
            $time_in_24_hour_format_endTime  = date("H:i:s", strtotime($userEndTime));
            
            // echo $time_in_24_hour_format_startTime."<br>";
            // echo $time_in_24_hour_format_endTime."<br>";
            
            $time_in_24_hour_format_currentTime = date('H:i:s');
            
            $d1 = strtotime($time_in_24_hour_format_startTime);
            $d2 = strtotime($time_in_24_hour_format_endTime);
            $totalSecondsDiff = abs($d1-$d2);
            
            //echo "User defined Seconds: ".$totalSecondsDiff."<br>"; //seconds
            
            //echo "User defined Minutes: ".round(abs($d2 - $d1) / 60,2). " minute"."<br>";
            
            //echo "Current time:".$time_in_24_hour_format_currentTime."<br>";
            
            //$time_in_24_hour_format_currentTime = "09:00:00";
            
           
            
            //echo "User Defined sensorTag duration :".$SensorTagShiftDuration."<br>";
            
            $curDate = $userStartTime." + $SensorTagShiftDuration minute";
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
            
            if(($time_in_24_hour_format_currentTime>=$time_in_24_hour_format_startTime)&&($time_in_24_hour_format_currentTime<=$addedEndTime)){
                //call the funtion
                echo "Current Time is within specified duration:---------->   startTime:$userStartTime     currentTime:$time_in_24_hour_format_currentTime   EndTime:$addedEndTime Specified duration:$SensorTagShiftDuration minutes"."<br>";
                
                //taking last 1 minute
                $getSensorLastOneMinuteData = "SELECT * FROM `segregatedValues` WHERE timeStamps >= DATE_SUB(NOW(),INTERVAL 1 MINUTE) and sensorTag = '$sensorTag'";
                $getSensorLastOneMinuteResult = mysqli_query($mysqli,$getSensorLastOneMinuteData);
                if(mysqli_num_rows($getSensorLastOneMinuteResult)>0){
                    while($getRowData = mysqli_fetch_assoc($getSensorLastOneMinuteResult)){
                       echo $getRowData['sensorTag']." ".$getRowData['scaledVal']." ".$getRowData['upload_date_time']."<br>";
                       $param_C_total= $param_C_total+$getRowData['scaledVal'];
                       $total_N=$total_N+1;
                    }
                    echo "Total count with value:".$total_N." ".$param_C_total."<br>";
                    $sql="select * from twa_info where parameterName='$sensorTag' and device_id='$deviceId'";
                    $res=mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli));
                    $n=mysqli_num_rows($res);
                    
                    if($row=mysqli_fetch_array($res))
                    {
                        $twa_prev=$row['twaValue'];
                        $cur_date_time=date("Y-m-d H:i:s");
                        $reset_time=$row['resetDateTime'];
                        $time_gap=round(abs(strtotime($cur_date_time)-strtotime($reset_time))/60,2);//old one
                        //$time_gap = round(abs(strtotime($cur_date_time) - strtotime($reset_time)) / 60,2);
                        
                        if($time_gap>$SensorTagShiftDuration)
                        {
                            echo "greater <br>";
                            $twa_prev=0; 
                            $sql="update twa_info set resetDateTime='$cur_date_time' where parameterName='$sensorTag' and device_id = '$deviceId'";
                            $res=mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli));
                        }
                        else{
                            echo "not greater <br>";
                        }
                    }
                    
                    $param_ci=($total_N>0)?($param_C_total/$total_N):0;
                    $twa=$twa_prev+(($param_ci*$moving_window_width)/$SensorTagShiftDuration);
                    if($n>0)
                    {
                        $sql="update twa_info set twaValue='$twa' where parameterName='$sensorTag' and device_id = '$deviceId'";
                        $res=mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli));
                        echo "Updated twa for ".$sensorTag."<br>";
                    }
                    else
                    {
                        $cur_date_time=date("Y-m-d H:i:s");
                        $sql="insert into twa_info(device_id,parameterName,twaValue,resetDateTime) values('$deviceId','$sensorTag','$twa','$cur_date_time')";
                        //echo "Insert :".$sql;
                        $res=mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli));
                        echo "Inserted twa for".$sensorTag."<br>";
                    }
                    $cur_date_time=explode(" ",date("Y-m-d H:i:s"));
                    $upload_date = $cur_date_time[0];
                    $upload_time = $cur_date_time[1];
                    if($twa){
                        alertMessage($mysqli,$sensorTag,$deviceId,$twa,$upload_date,$upload_time);   
                    }
                    
                    
                        
                }else{
                    //else part
                }
            }else{
                $cur_date_time=date("Y-m-d H:i:s");
                $sql="update twa_info set twaValue='0', resetDateTime='$cur_date_time' where parameterName='$sensorTag' and device_id = '$deviceId'";
                $res=mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli));
                echo "time exceeded"."<br>";
                //echo "Dont execute twa"."<br>";
            }
        }
    }else{
        echo "No data";
    }
}


function alertMessage($conn,$sensorTag,$deviceId,$val,$uploadDate,$uploadTime){
    $sql = "select * from  sensors where sensorTag='$sensorTag' and deviceId='$deviceId'";
    echo $sql."<br>";
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
    
    if($isStel == 1){
        
        //set alert for twa commented for timebeing
        if(intval($val)>$twaLimit){
            $alertTypes = $alertType[4];
            $severity = "HIGH";
            $status = $alertStatus[1];
            $statusMessage = $alertStatusMessage[1];
            $Message = $twaAlert;
            InsertAlertCronData($conn,$a_date,$a_time,$companyCode,$deviceId,$sensorId,$sensorTag,$alertTypes,$val,$wariningMessage,$severity,$status,$statusMessage,$alarmType,$current_time,$sensorStatus,$notificationStatus,$hooterRelayStatus,$lab_id);      
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
        $alertSql = "select * from  alert_cronsOLD2 where deviceId='$deviceId' and sensorTag='$sensorTag' and status='0'  order by id desc Limit 1";
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
       
    $sql_query = "INSERT INTO `alert_cronsOLD2`(`a_date`, `a_time`, `companyCode`, `deviceId`, `sensorId`, `sensorTag`, `alertType`,`value`, `msg`, `severity`, `status`,`statusMessage`,`alarmType`) VALUES 
                      ('$a_date','$a_time','$companyCode','$deviceId','$sensorId','$sensorTag','$alertTypes','$val','$Message','$severity','$status','$statusMessage','$alarmType')";
    
    echo $sql_query;
    $res=mysqli_query($conn, $sql_query) or die(mysqli_error($conn));
    
    //code to update lab hooter
    $updateLabHooter = "UPDATE lab_departments SET 	labHooterStatus = 1 WHERE id = '$lab_id'";
    $resLabHooter = mysqli_query($conn, $updateLabHooter) or die(mysqli_error($conn));
    
    
   // echo $Message."<br>";
   
   //if sensor is enabled with email notification only then mail has to be sent
   
    // commnted sending email for time being
    
    /*
    
    if($notificationStatus == 1){ 
     
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
                            
                //$mailInfo = new sendAlertMailAndAlertNumber($sendingMailInfo); //Class sendAlertMail is written in Top
                
                //$mailInfo->sendMails($mail);//function  of class sensAlertMail for sending emails  for users 
                
                //below sql code written to test whether to check mail funtion is working or else its not important 
                $sqlq = "INSERT INTO `alert_data`(`companyCode`,`machine_name`, `alert`,`a_date`,`a_time`) VALUES ('$companyCode','$email','$mes','$a_date','$a_time')";
                //echo $sqlq;
                $res=mysqli_query($conn, $sqlq) or die(mysqli_error($conn));
            }
        }else{} 
    } */
    
    
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