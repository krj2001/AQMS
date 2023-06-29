<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
ini_set("display_errors",1);



ini_set("display_errors",1);
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
$sensorTagName = $data->sensorTagName;

/**** Mail Class Begin ****/

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require'vendor/autoload.php';

$sendingMailInfo = array();

class sendAlertMailAndAlertNumber{
    private $senderEmail = "abhishekshenoy97@gmail.com";
    private $senderName = "Abhishek"; 
    private $Password = "20081997@rdl123";
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
            $mail->Body = $this->BodyMessage;
            
            /* SMTP parameters. */
            
            /* Tells PHPMailer to use SMTP. */
            // $mail->isSMTP();
            
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

/**** Mail Class End ****/

class AQMi_Parameter_info
{
    public $company;
    public $location;
    public $branch;
    public $facility;
    public $building;
    public $floor;
    public $lab;
    public $device_id;
    public $parameter_name;
    public $parameter_tag;
}

/*
$conn--->mysqli connection object
$data_table--->table name(the original table from which datas need to be sampled)
$moving_window_width--->(sampling interval in seconds)
$mode-->hw data mode("1"---->FIRMWARE UPGRADATION MODE,"2"----->ENABLED MODE,"3"--->DISABLED MODE,"4"-------->BUMP TEST MODE);
returns MAX,MIN,AVG,LAST parameter values wityhin the sampling interval along with DATA_AVAILABLE("1" or "0") status
*/

function getValuesForAParameter($conn,$data_table,$parameteInfo,$moving_window_width,$mode)
{
    /********************compute data extraction window ***********************/
 
    date_default_timezone_set('Asia/Kolkata');
 
    $CUR_DATE=date("Y-m-d");
    $current_time_stamp_in_sec=date("i")*60+date("s");
    $moving_window_start_p_sec=(floor($current_time_stamp_in_sec/$moving_window_width)*$moving_window_width);  // time window start point
    $moving_window_end_p_sec=(ceil($current_time_stamp_in_sec/$moving_window_width)*$moving_window_width); // time window  end  point
    if($moving_window_start_p_sec==$moving_window_end_p_sec)
    {
        $moving_window_end_p_sec=$moving_window_end_p_sec+$moving_window_width;
    }
    
    $moving_window_start_p_min=floor($moving_window_start_p_sec/60);
    $moving_window_start_p_s=$moving_window_start_p_sec%60;
    
    $moving_window_start_p_min=floor($moving_window_start_p_sec/60);
    $moving_window_start_p_s=$moving_window_start_p_sec%60;
    
    $moving_window_end_p_min=floor($moving_window_end_p_sec/60);
    $moving_window_end_p_s=$moving_window_end_p_sec%60;
    
    $hr_value_offset=floor($moving_window_end_p_min/60);
    
    $moving_window_end_p_min=($moving_window_end_p_min%60);
    
    $moving_window_start_p_min=(strlen($moving_window_start_p_min)<2)?("0".$moving_window_start_p_min):$moving_window_start_p_min;
    
    $moving_window_start_p_s=(strlen($moving_window_start_p_s)<2)?("0".$moving_window_start_p_s):$moving_window_start_p_s;
    
    $moving_window_end_p_min=(strlen($moving_window_end_p_min)<2)?("0".$moving_window_end_p_min):$moving_window_end_p_min;
    
    $moving_window_end_p_s=(strlen($moving_window_end_p_s)<2)?("0".$moving_window_end_p_s):$moving_window_end_p_s;
    
   /* echo $CUR_DATE ." ".(date("H")).":". $moving_window_start_p_min.":".$moving_window_start_p_s."</br>";
    
    echo $CUR_DATE ." ".(date("H")+$hr_value_offset).":". $moving_window_end_p_min.":".$moving_window_end_p_s."</br>";*/
    
    $end_hr_value=date("H")+$hr_value_offset;
    
    $days_increment=floor($end_hr_value/24);  //  for day shift
    
    $days_incre_cmd=($days_increment>1)?("+".$days_increment." days"):("+".$days_increment." day");
    
    $CUR_DATE=date("Y-m-d",strtotime($days_incre_cmd,strtotime($CUR_DATE." 00:00:00")));
    
    $moving_window_start_p_timestamp=strtotime($CUR_DATE ." ".(date("H")).":".$moving_window_start_p_min.":".$moving_window_start_p_s);
    
    $moving_window_end_p_timestamp=strtotime($CUR_DATE ." ".($end_hr_value).":". $moving_window_end_p_min.":".$moving_window_end_p_s);
    
    $current_sample_date_time=$CUR_DATE." ".date("H").":".$moving_window_start_p_min.":00";
        
    /*******************************************************************************/

    $max=0;
    $min=9999999;
    $par_val_sum=0;
    $data_count=0;
    
    $data_available="0";
    
    $ret_data_array=array();
  
    $ret_data_array["MAX"]="NA";
    $ret_data_array["MIN"]="NA";
    $ret_data_array["AVG"]="NA";
    $ret_data_array["LAST"]="NA";
    $ret_data_array["UNIT"]="NA";
    $ret_data_array["ALERTTYPE"]="NA";
    $ret_data_array["PARAMETER_NAME"]=$parameteInfo->parameter_name;
    $ret_data_array["DATETIME"]=$current_sample_date_time;
    
    $Company_sel="\"COMPANY\":\"".($parameteInfo->company)."\"";
    $loc_sel="\"LOCATION\":\"".($parameteInfo->location)."\"";
    $branch_sel="\"BRANCH\":\"".($parameteInfo->branch)."\"";
    $facility_sel="\"FACILITY\":\"".($parameteInfo->facility)."\"";
    $building_sel="\"BULDING\":\"".($parameteInfo->building)."\"";
    $floor_sel="\"FLOOR\":\"".($parameteInfo->floor)."\"";
    $lab_sel="\"LAB\":\"".($parameteInfo->lab)."\"";
    $device_id_sel="\"DEVICE_ID\":\"".($parameteInfo->device_id)."\"";
    $param_sel="\"".($parameteInfo->parameter_tag)."\"";
    $mode_sel="\"MODE\":\"".$mode."\"";
    $selectors=array($Company_sel,$loc_sel,$branch_sel,$facility_sel,$building_sel,$floor_sel,$lab_sel,$device_id_sel,$mode_sel);
    $query_filter="";
    foreach($selectors as $sel)
    {
        $query_filter=$query_filter."j_data LIKE '%".$sel."%' and ";   
    }
    $query_filter=$query_filter."j_data LIKE '%".$param_sel."%'";
    $sql="select * from ".$data_table." where ".$query_filter;
   
    $res=mysqli_query($conn,$sql) or die(mysqli_error($conn));
    //echo "num rows:".mysqli_num_rows($res);
    while($row=mysqli_fetch_array($res))
    {
        $json_data=$row['j_data'];
        $json_data_obj=json_decode($json_data,true);
        $upload_date=$json_data_obj["DATE"];
        $upload_time=$json_data_obj["TIME"];
        $upload_date_time=$upload_date." ".$upload_time;
        
        
        if($parameteInfo->parameter_tag==="PM10_GAS2")
        {
             //"upload date time:".$upload_date_time." rt $data_retreival_timestamp mst $moving_window_start_p_timestamp mest $moving_window_end_p_timestamp</br>";
        }
        $data_retreival_timestamp=strtotime($upload_date_time);
    
        if(($data_retreival_timestamp>=$moving_window_start_p_timestamp)&&($data_retreival_timestamp<$moving_window_end_p_timestamp))
        {
                $value=$json_data_obj[($parameteInfo->parameter_tag)];
           
                //scalling the parameter values
                $val = scalingValue($conn,$parameteInfo->company,$parameteInfo->device_id,$parameteInfo->parameter_tag,$value);
                 
                //after scalling, sending alert and inserting and return type and sevierity if parameter doesnot meets reading range
                $alertType = explode(",",alertMessage($conn,$parameteInfo->company,$parameteInfo->device_id,$parameteInfo->parameter_tag,$upload_date,$upload_time,$val));
                
                $alert = $alertType[0]; // critical || warning || outofrange
                $sevierity = $alertType[1];// High || Low || Normal
                
                $max=($val>$max)?$val:$max;
                $min=($val<$min)?$val:$min;
                $par_val_sum=$par_val_sum+$val;
                $data_count=$data_count+1;
                
                $data_available="1";
        }
    }

    if($data_count>0)
    {
        $avrg=$par_val_sum/$data_count;
    }

    if($data_available==="1")
    {
        $ret_data_array["MAX"]=$max;
        $ret_data_array["MIN"]=$min;
        $ret_data_array["AVG"]=$avrg;
        $ret_data_array["LAST"]=$val;
        $ret_data_array["ALERTTYPE"] = $alert;
        $ret_data_array["SEVIERITY"] = $sevierity;
        $ret_data_array["UNIT"]="ug/m3";
        $ret_data_array["DATETIME"]=$current_sample_date_time;
    }

    $ret_data_array["DATA_AVAILABLE"]=$data_available;
    
    $ret = [
        'data' => $ret_data_array,
    ];
    
  
   return json_encode($ret);
}


function scalingValue($conn,$companyCode,$deviceId,$parameterTag,$val){
 
    $sql = "SELECT sensor_units.*, sensors.* FROM `sensor_units` INNER JOIN sensors ON sensor_units.id = sensors.sensorName WHERE sensors.sensorTag = '$parameterTag' and sensors.deviceId = '$deviceId' and sensors.companyCode = '$companyCode'";
    
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
    
    $scaledValue = 0;
    
    //scaled for only 4-20v output type
    if($outputType === "4-20v"){
        $scaledValue =  (($maxRatedReadingScale-$minRatedReadingScale)/($maxRatedReading-$minRatedReading)) * ($val - $minRatedReading) + $minRatedReadingScale;    
    }
    else{
        $scaledValue = $val;
    }
    
    return $scaledValue;
}

$FLAG = 0; //set for alert message funtion
$INSERTFLAG = 0;


function alertMessage($conn,$companyCode,$deviceId,$parameterTag,$upload_date,$upload_time,$val){
    $sql = "select * from  sensors where companyCode='$companyCode' and deviceId='$deviceId' and sensorTag='$parameterTag'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
        
    $alertType=array('Warning','outOfRange','Critical');
    $alertStatus = array('1','0');
    $alertStatusMessage = array('Cleared','NotCleared');
        
    $a_date = $upload_date;
    $a_time = $upload_time;
        
    $from_time = strtotime($upload_date." ".$upload_time);
    
    $companyCode = $companyCode;
    $deviceId = $row['deviceId'];
    $sensorTag = $parameterTag;
    $sensorId = $row['id'];
    
    $warningAlertType = $row['warningAlertType'];
    $outofrangeAlertType = $row['outofrangeAlertType'];
    $criticalAlertType = $row['criticalAlertType'];
    
    $sensorStatus = $row['sensorStatus'];
    $notificationStatus = $row['notificationStatus'];
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
        // if($alarmType == "UnLatch"){ //COMMENTED FOR TIME BEING IF ONLY LATCHED ALARM UPDATE
            $reason = "Values are Normal";
            $severityStatus = "NORMAL";
            $sql = "UPDATE alert_crons set status='$alertStatus[0]',statusMessage='$alertStatusMessage[0]',severity='$severityStatus', Reason='$reason' where sensorId='$sensorId'";
            //$res=mysqli_query($conn, $sql) or die(mysqli_error($conn));    
        // }
    }
    else{
        if($criticalAlertStatusFlag === 1){
            $alertTypes = $alertType[2];
            $severity = $severityStatus;
            $status = $alertStatus[1];
            $statusMessage = $alertStatusMessage[1];
            
            //InsertAlertCronData($conn,$a_date,$a_time,$companyCode,$deviceId,$sensorId,$sensorTag,$alertTypes,$val,$criticalMessage,$severity,$status,$statusMessage,$alarmType,$current_time,$sensorStatus,$notificationStatus);
            
            //below code commented because its not used at present and it returns total minutes of last inserted data  which was implemented with santhosh sir
            // $min = explode(", ", getLastAlertData($conn,$companyCode,$deviceId,$parameterTag,$alertType[2],$ALERT_MIN_IMTERVAL_MINUTES,$upload_date,$upload_time));
            // $total_min = $min[0];
            // $to_time = $min[1];
         
            // if($total_min > $ALERT_MIN_IMTERVAL_MINUTES){//by default minutes will be more and this code is kept for future use
            //     InsertAlertCronData($conn,$a_date,$a_time,$companyCode,$deviceId,$sensorId,$sensorTag,$alertTypes,$val,$criticalMessage,$severity,$status,$statusMessage,$alarmType,$current_time);
            // }
        }
        
        if($outOfRangeAlertStatusFlag === 1){
            $alertTypes = $alertType[1];
            $severity = $severityStatus;
            $status = $alertStatus[1];
            $statusMessage = $alertStatusMessage[1];
           // InsertAlertCronData($conn,$a_date,$a_time,$companyCode,$deviceId,$sensorId,$sensorTag,$alertTypes,$val,$outofrangeMessage,$severity,$status,$statusMessage,$alarmType,$current_time,$sensorStatus,$notificationStatus);           
        }
        
        if($warningAlertStatusFlag === 1){
            $alertTypes = $alertType[0];
            $severity = $severityStatus;
            $status = $alertStatus[1];
            $statusMessage = $alertStatusMessage[1];
            //InsertAlertCronData($conn,$a_date,$a_time,$companyCode,$deviceId,$sensorId,$sensorTag,$alertTypes,$val,$wariningMessage,$severity,$status,$statusMessage,$alarmType,$current_time,$sensorStatus,$notificationStatus);
        }   
        
    }
    
    //returning alertypes and sevierity
    if($alertTypes == ""){
        return "NORMAL".","."NORMAL";
    }else{
        return $alertTypes.",".$severityStatus;    
    }
    
    
}


//this function is of no use since we are getting last data when inserting record
function getLastAlertData($conn,$companyCode,$deviceId,$parameterTag,$alertType,$ALERT_MIN_IMTERVAL_MINUTES,$upload_date,$upload_time){ //function to get last data to check whether it is unlatched
    
    $total_min = "";
    
    $ALERT_MIN_IMTERVAL_MINUTES = 2;
    
    $alertSql = "select * from  alert_crons where companyCode='$companyCode' and deviceId='$deviceId' and sensorTag='$parameterTag' and status='0'  order by id desc Limit 1";
    $alertResult = mysqli_query($conn, $alertSql);
    $alertRow = mysqli_fetch_assoc($alertResult);
    $tot_rows=mysqli_num_rows($alertResult);
    
    if($tot_rows>0)
    {
        $from_time = strtotime($upload_date." ".$upload_time);
        $to_time = strtotime($alertRow['a_date']." ".$alertRow['a_time']);
        $total_min = round(abs($from_time - $to_time) / 60,2);
    }
    else
    {
        $total_min=$ALERT_MIN_IMTERVAL_MINUTES+1;
        $FLAG = 1;
    }
    //echo "flagStatus:".$FLAG."<br>";
  
    return $total_min.",".$to_time; 
    
}

$emailFlag = 1;

//this function is to check last inserted uncleared alert data, if uncleared dont insert, if cleared insert the data based on sevierity and alertType
function InsertAlertCronData($conn,$a_date,$a_time,$companyCode,$deviceId,$sensorId,$sensorTag,$alertTypes,$val,$Message,$severity,$status,$statusMessage,$alarmType,$current_time,$sensorStatus,$notificationStatus){
    
     if($sensorStatus == 0){
         //if sensorStatus == 0, means sensor is disabled and alert should not be inserted.......
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
                    InsertDataToAlertTable($conn,$a_date,$a_time,$companyCode,$deviceId,$sensorId,$sensorTag,$alertTypes,$val,$Message,$severity,$status,$statusMessage,$alarmType,$current_time,$notificationStatus);   
                }
            }
            else{
                InsertDataToAlertTable($conn,$a_date,$a_time,$companyCode,$deviceId,$sensorId,$sensorTag,$alertTypes,$val,$Message,$severity,$status,$statusMessage,$alarmType,$current_time,$notificationStatus);
            }    
        }
        else{
            InsertDataToAlertTable($conn,$a_date,$a_time,$companyCode,$deviceId,$sensorId,$sensorTag,$alertTypes,$val,$Message,$severity,$status,$statusMessage,$alarmType,$current_time,$notificationStatus);
        } 
     }
}

//funtion to insert data and send mail.
function InsertDataToAlertTable($conn,$a_date,$a_time,$companyCode,$deviceId,$sensorId,$sensorTag,$alertTypes,$val,$Message,$severity,$status,$statusMessage,$alarmType,$current_time,$notificationStatus){
       
        $sql_query = "INSERT INTO `alert_crons`(`a_date`, `a_time`, `companyCode`, `deviceId`, `sensorId`, `sensorTag`, `alertType`,`value`, `msg`, `severity`, `status`,`statusMessage`,`alarmType`) VALUES 
                          ('$a_date','$a_time','$companyCode','$deviceId','$sensorId','$sensorTag','$alertTypes','$val','$Message','$severity','$status','$statusMessage','$alarmType')";

        $res=mysqli_query($conn, $sql_query) or die(mysqli_error($conn));
        
        if($notificationStatus == 1){ //if sensor is enabled with email notification only then mail has to be sent
            $NotificationEnabledUsers = "SELECT * FROM `users` where empNotification = 1";
            $NEUResults = mysqli_query($conn,$NotificationEnabledUsers);
            if(mysqli_num_rows($NEUResults) > 0){
                while($row = mysqli_fetch_array($NEUResults)){
                    $notificationStatus = $row['empNotification'];
                    $email = $row['email'];
                    $name = $row['name'];
                    $contactNo = $row['mobileno'];
                 
                    // $mail = new PHPMailer(TRUE);
                    
                    // $sendingMailInfo['recipientEmail'] = $email;
                    // $sendingMailInfo['recepientName'] = $name;
                    // $sendingMailInfo['Subject'] = $alertTypes;
                    // $sendingMailInfo['bodyMessage'] = $Message." of ".$sensorTag;
                                
                    //$mailInfo = new sendAlertMailAndAlertNumber($sendingMailInfo); //Class sendAlertMail is written in Top
                    
                    //$mailInfo->sendMails($mail);//function  of class sensAlertMail for sending emails  for users 
                    
                    
                    //for timebeing testing by inserting data to table
                    
                    $mes = $Message." of ".$sensorTag;
                    $sqlq = "INSERT INTO `alert_data`(`machine_name`, `alert`) VALUES ('$email','$mes')";
                    $res=mysqli_query($conn, $sqlq) or die(mysqli_error($conn));
                }
            }else{} 
        }
        
        
}


function extractAQMSParameterData($conn,$data_table,$moving_window_width,$mode)
{
    $sql="select * from sensors order by id asc";
    $res=mysqli_query($conn,$sql) or die(mysqli_error($conn));
    
    while($row=mysqli_fetch_array($res))
    {
        $sensor_id=$row["id"];
        $parameterInfo=new AQMi_Parameter_info();
        $parameterInfo->company=$row['companyCode'];
        $parameterInfo->location=$row['location_id'];
        $parameterInfo->branch=$row['branch_id'];
        $parameterInfo->facility=$row['facility_id'];
        $parameterInfo->building=$row['building_id'];
        $parameterInfo->floor=$row['floor_id'];
        $parameterInfo->lab=$row['lab_id'];
        $parameterInfo->device_id=$row['deviceId'];
        $parameterInfo->parameter_name=$row['sensorNameUnit'];
        $parameterInfo->parameter_tag=$row['sensorTag'];
        $parametrer_data=getValuesForAParameter($conn,$data_table,$parameterInfo,$moving_window_width,$mode);
        
        //print_r($parametrer_data);
        
       if($parametrer_data["DATA_AVAILABLE"]==="1")
       {
            setParameterDataForCurrentWindow($conn,$parametrer_data,$sensor_id,$parameterInfo->device_id);
       }
    }
}

function setParameterDataForCurrentWindow($conn,$parameter_data,$sensor_id,$device_id)
{
    
    $last_val=$parameter_data["LAST"];
    $max_val=$parameter_data["MAX"];
    $min_val=$parameter_data["MIN"];
    $avg_val=$parameter_data["AVG"];
    $sample_date_time=$parameter_data["DATETIME"];
    $param_unit=$parameter_data["UNIT"];
    $alertType = $parameter_data["ALERTTYPE"];
    $sevierity = $parameter_data["SEVIERITY"];
    $parameterName=$parameter_data["PARAMETER_NAME"];
    $sql="select * from sampled_sensor_data_details where parameterName='".($parameterName)."' and sensor_id=".$sensor_id." and sample_date_time='$sample_date_time'";
    
    $res=mysqli_query($conn,$sql) or die(mysqli_error($conn));
    
    if(mysqli_num_rows($res)>0){
        $sql_query="update sampled_sensor_data_details set last_val='$last_val',max_val='$max_val',min_val='$min_val',avg_val='$avg_val',alertType='$alertType', sevierity='$sevierity' ,param_unit='$param_unit' where parameterName='".($parameterName)."' and sensor_id=".$sensor_id." and sample_date_time='$sample_date_time'";
    }
    else
    {
       $sql_query="insert into sampled_sensor_data_details(device_id,sensor_id,parameterName,last_val,max_val,min_val,avg_val,sample_date_time,param_unit,alertType,sevierity) values($device_id,$sensor_id,'$parameterName','$last_val','$max_val','$min_val','$avg_val','$sample_date_time','$param_unit','$alertType','$sevierity')";
    }
 
    $res=mysqli_query($conn, $sql_query) or die(mysqli_error($conn));
}

function getSampledValuesForAParameter($conn,$data_table,$aqmi_par_id,$sampling_Interval_min,$cur_date_time,$backInterval_min)
{
    //$sql_timezone_lag_mins=13*60+30;
    $back_interval_in_minutes=$backInterval_min;
    $grouping_interval_in_minutes=$sampling_Interval_min;
    $date_from=date("Y-m-d H:i:s",strtotime($cur_date_time)-$backInterval_min*60);
    $sql="SELECT sample_date_time as DATE_TIME,sensor_id,parameterName as parameter,FLOOR(UNIX_TIMESTAMP(time_stamp)/(".$grouping_interval_in_minutes." * 60)) AS timekey,MAX(max_val) as par_max,MIN(min_val) as par_min,AVG(avg_val)  as par_avg,last_val as par_last FROM ".$data_table." WHERE sensor_id=$aqmi_par_id and time_stamp >=(NOW() - INTERVAL (".$back_interval_in_minutes.") MINUTE) GROUP BY timekey";
 
    $res=mysqli_query($conn,$sql) or die(mysqli_error($conn));
    $row_1=array();
    while ($row=mysqli_fetch_assoc($res))
    {
        $row_1[]=$row;
    }
    return json_encode($row_1);   
}


include("includes/config.php");
date_default_timezone_set('Asia/Kolkata');
$data_table="aqmi_json_data";
$moving_window_width=60;//in seconds
//$moving_window_width=120;//in seconds
$device_mode="4";
$parameterInfo=new AQMi_Parameter_info();

$sql2="select * from sensors where sensorTag ='$sensorTagName'";
$result=mysqli_query($mysqli,$sql2) or die(mysqli_error($mysqli));

$deviceSensor = array();

if(mysqli_num_rows($result)>0){
    $row = mysqli_fetch_array($result);
    $parameterInfo->company=$row['companyCode'];
    $parameterInfo->location=$row['location_id'];
    $parameterInfo->branch=$row['branch_id'];
    $parameterInfo->facility=$row['facility_id'];
    $parameterInfo->building=$row['building_id'];
    $parameterInfo->floor=$row['floor_id'];
    $parameterInfo->lab=$row['lab_id'];
    $parameterInfo->device_id=$row['deviceId'];
    $parameterInfo->parameter_name=$row['sensorNameUnit'];
    $parameterInfo->parameter_tag=$row['sensorTag'];
    
    
    print_r(getValuesForAParameter($mysqli,$data_table,$parameterInfo,$moving_window_width,$device_mode));
}else{
    $res = [
        "data"=>"No data found"
        ];
    echo json_encode($res);
}


// print json_encode($row['sensorOutput']);

// $parameterInfo->company="A-TEST";
// $parameterInfo->location=4;
// $parameterInfo->branch=3;
// $parameterInfo->facility=4;
// $parameterInfo->building=2;
// $parameterInfo->floor=2;
// $parameterInfo->lab=3;
// $parameterInfo->device_id=3;
// $parameterInfo->parameter_name="O3";
// $parameterInfo->parameter_tag="O3_gas1";

// print json_encode($row['sensorNameUnit']);






extractAQMSParameterData($mysqli,$data_table,$moving_window_width,$device_mode);


// $data_table="sampled_sensor_data_details";

// $aqmi_par_id=43;

// $sampling_Interval_min=15; //in min grping

// $date=date("Y-m-d H:i:s");

// $backInterval_min=1*60; 

//echo getSampledValuesForAParameter($mysqli,$data_table,$aqmi_par_id,$sampling_Interval_min,$date,$backInterval_min);


?>