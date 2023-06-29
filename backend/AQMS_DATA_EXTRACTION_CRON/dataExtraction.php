<?php


ini_set("display_errors",1);
error_reporting(1);


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
    public $sensor_id;
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
    $ret_data_array['LAB_ID'] = $parameteInfo->lab;
    $ret_data_array['UPLOAD_DATE_TIME'] = "";
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
    $sql="select * from ".$data_table." where ".$query_filter." order by id";
    // echo $sql;
    // $sensorOutput = $parameterInfo->sensorOutput;
    
    // echo $parameteInfo->parameter_tag." === ".$parameterInfo->sensorOutput;
    $res=mysqli_query($conn,$sql) or die(mysqli_error($conn));
    //echo "num rows:".mysqli_num_rows($res);
    while($row=mysqli_fetch_array($res))
    {
        $json_data=$row['j_data'];
       
        $json_data_obj=json_decode($json_data,true);
        $upload_date=$json_data_obj["DATE"];
        $upload_time=$json_data_obj["TIME"];
        $deviceMode = $json_data_obj["MODE"];
        $upload_date_time=$upload_date." ".$upload_time;
        $ret_data_array['UPLOAD_DATE_TIME'] = $json_data_obj["TIME"];
        
        // if($json_data_obj["TIME"] != ""){
            
        //     //below code to update device dissconnected status to 1 when there is no data when it is more than 5 minutes
            
        //     // $ret_data_array['UPLOAD_DATE_TIME'] = $json_data_obj["TIME"];
            
        //     // $C_DATE = date("Y-m-d h:i:s");
        //     // $deviceId = $json_data_obj["DEVICE_ID"];
            
            
        //     // $from_time = strtotime($C_DATE); 
        //     // $to_time = strtotime($upload_date_time); 
        //     // $diff_minutes = round(abs($from_time - $to_time) / 60,2);
            
        //     // if($diff_minutes > 2){
        //     //     $updateDeviceDisconnectedStatusQuery = "Update `devices` SET disconnectedStatus = '1' where id ='$deviceId'";
        //     //     $updateDeviceDisconnectedStatusResults = mysqli_query($conn,$updateDeviceDisconnectedStatusQuery);
        //     //     // if($json_data_obj["DEVICE_ID"] == 3){
        //     //     //     echo $row['id']."=>"."   DEVICEID:".$json_data_obj["DEVICE_ID"]."  UPLOADDATETIME:".$upload_date_time."  CURDATETIME:".$C_DATE."  DIFFERENCE:".$diff_minutes." <br><br>";
        //     //     // }    
        //     // }
            
            
        // }
        
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
             
            //after scalling, sending alert and inserting to alert table and exploding return type and sevierity thats is returned from alertMessage  function if parameter doesnot meets reading range
            $alertType = explode(",",alertMessage($conn,$parameteInfo->company,$parameteInfo->device_id,$parameteInfo->parameter_tag,$upload_date,$upload_time,$val));
            
            $alert = $alertType[0]; // critical || warning || outofrange
            $sevierity = $alertType[1];// High || Low || Normal
            
            $max=($val>$max)?$val:$max;
            $min=($val<$min)?$val:$min;
            $par_val_sum=$par_val_sum+$val;
            $data_count=$data_count+1;
            $data_available="1";
        }else{
            
        }
    }

    if($data_count>0)
    {
        $avrg=$par_val_sum/$data_count;
    }
    
    
    //if data is available inserted to sampled sensor details
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
    }else{
        
        
    }

    $ret_data_array["DATA_AVAILABLE"]=$data_available;
    
    return $ret_data_array;
}


function scalingValue($conn,$companyCode,$deviceId,$parameterTag,$val){

    $sql = "SELECT sensor_units.*, sensors.* FROM `sensor_units` INNER JOIN sensors ON sensor_units.id = sensors.sensorName WHERE sensors.sensorTag = '$parameterTag' and sensors.deviceId = '$deviceId' and sensors.companyCode = '$companyCode'";
    // echo $sql;
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
    //echo "value:      ".$maxRatedReadingScale." ".$minRatedReadingScale." ".$maxRatedReading." ".$minRatedReading;
    //scaled for only 4-20v output type
    if($outputType === "4-20v"){
        $scaledValue = (($maxRatedReadingScale-$minRatedReadingScale)/($maxRatedReading-$minRatedReading)) * ($val - $minRatedReading) + $minRatedReadingScale;    
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
            
            InsertAlertCronData($conn,$a_date,$a_time,$companyCode,$deviceId,$sensorId,$sensorTag,$alertTypes,$val,$Message,$severity,$status,$statusMessage,$alarmType,$current_time,$sensorStatus,$notificationStatus,$hooterRelayStatus,$lab_id);           
            
        }
        
        //set alert for twa commented for timebeing
        if(intval($val)>$twaLimit){
            $alertTypes = $alertType[4];
            $severity = "HIGH";
            $status = $alertStatus[1];
            $statusMessage = $alertStatusMessage[1];
            $Message = $twaAlert;
            InsertDataToAlertTable($conn,$a_date,$a_time,$companyCode,$deviceId,$sensorId,$sensorTag,$alertTypes,$val,$Message,$severity,$status,$statusMessage,$alarmType,$current_time,$notificationStatus,$lab_id);      
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
                updateRelayOutputStatus($conn,$a_date,$a_time,$companyCode,$deviceId,$sensorId,$sensorTag,$alertTypes,$severity,$statusMessage,$hooterRelayStatus);
        }
    }
    else{
            if($criticalAlertStatusFlag === 1){
                $alertTypes = $alertType[2];
                $severity = $severityStatus;
                $status = $alertStatus[1];
                $statusMessage = $alertStatusMessage[1];
                InsertAlertCronData($conn,$a_date,$a_time,$companyCode,$deviceId,$sensorId,$sensorTag,$alertTypes,$val,$criticalMessage,$severity,$status,$statusMessage,$alarmType,$current_time,$sensorStatus,$notificationStatus,$hooterRelayStatus,$lab_id);
                
            }
        
            if($outOfRangeAlertStatusFlag === 1){
                $alertTypes = $alertType[1];
                $severity = $severityStatus;
                $status = $alertStatus[1];
                $statusMessage = $alertStatusMessage[1];
                InsertAlertCronData($conn,$a_date,$a_time,$companyCode,$deviceId,$sensorId,$sensorTag,$alertTypes,$val,$outofrangeMessage,$severity,$status,$statusMessage,$alarmType,$current_time,$sensorStatus,$notificationStatus,$hooterRelayStatus,$lab_id);           
            }
        
            if($warningAlertStatusFlag === 1){
                $alertTypes = $alertType[0];
                $severity = $severityStatus;
                $status = $alertStatus[1];
                $statusMessage = $alertStatusMessage[1];
                InsertAlertCronData($conn,$a_date,$a_time,$companyCode,$deviceId,$sensorId,$sensorTag,$alertTypes,$val,$wariningMessage,$severity,$status,$statusMessage,$alarmType,$current_time,$sensorStatus,$notificationStatus,$hooterRelayStatus,$lab_id);
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
function InsertAlertCronData($conn,$a_date,$a_time,$companyCode,$deviceId,$sensorId,$sensorTag,$alertTypes,$val,$Message,$severity,$status,$statusMessage,$alarmType,$current_time,$sensorStatus,$notificationStatus,$hooterRelayStatus,$lab_id){
   
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
                    InsertDataToAlertTable($conn,$a_date,$a_time,$companyCode,$deviceId,$sensorId,$sensorTag,$alertTypes,$val,$Message,$severity,$status,$statusMessage,$alarmType,$current_time,$notificationStatus,$lab_id);   
                }
            }
            else{
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
    
    //echo $sql_query;
    $res=mysqli_query($conn, $sql_query) or die(mysqli_error($conn));
    
    //code to update lab hooter
    $updateLabHooter = "UPDATE lab_departments SET 	labHooterStatus = 1 WHERE id = '$lab_id'";
    $resLabHooter = mysqli_query($conn, $updateLabHooter) or die(mysqli_error($conn));
    
    
   // echo $Message."<br>";
    if($notificationStatus == 1){ //if sensor is enabled with email notification only then mail has to be sent
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
                            
                $mailInfo = new sendAlertMailAndAlertNumber($sendingMailInfo); //Class sendAlertMail is written in Top
                
                //$mailInfo->sendMails($mail);//function  of class sensAlertMail for sending emails  for users 
                
                //below sql code written to test whether to check mail funtion is working or else its not important 
                $sqlq = "INSERT INTO `alert_data`(`companyCode`,`machine_name`, `alert`,`a_date`,`a_time`) VALUES ('$companyCode','$email','$mes','$a_date','$a_time')";
                $res=mysqli_query($conn, $sqlq) or die(mysqli_error($conn));
            }
        }else{} 
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
    
    $getLastSensorStatus = "SELECT `id`, `a_date`, `a_time`, `companyCode`, `deviceId`, `sensorId`, `sensorTag`, `alertType`, `severity`, `statusMessage`, `relayOutputStatus`, `created_at`, `updated_at` FROM `relay_output_results` WHERE sensorId = '$sensorId' order by id desc LIMIT 1";
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
        $insertRelaySql = "INSERT INTO `relay_output_results`(`a_date`, `a_time`, `companyCode`, `deviceId`, `sensorId`, `sensorTag`, `alertType`, `severity`, `statusMessage`, `relayOutputStatus`) 
                VALUES ('$a_date','$a_time','$companyCode','$deviceId','$sensorId','$sensorTag','$alertTypes','$severity','$statusMessage','$relayOutputStatus')";
        $insertRelayResult = mysqli_query($conn, $insertRelaySql) or die(mysqli_error($conn)); 
       
    }else{}
}

//extracting data from aqmi_json_table basedd on sensortag
function extractAQMSParameterData($conn,$data_table,$moving_window_width,$mode)
{
    //where sensorNameUnit = 'PM10'
    
    $sql="select * from sensors where deviceId = 3  order by id asc";
    $res=mysqli_query($conn,$sql) or die(mysqli_error($conn));
    while($row=mysqli_fetch_array($res))
    {
        $sensor_id=$row["id"];
        $companyCode = $row['companyCode'];
        
        $deviceId = $row['deviceId'];
        $sensorId = $row["id"];
        $sensorTag = $row['sensorTag'];
        $hooterRelayStatus = $row['hooterRelayStatus'];
        
        $parameterInfo=new AQMi_Parameter_info();
        $parameterInfo->id=$row['id'];
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
        $parameterInfo->sensorOutput = $row['sensorOutput'];
        
        
        //sampling sensor details
        $parametrer_data=getValuesForAParameter($conn,$data_table,$parameterInfo,$moving_window_width,$mode);
        echo json_encode($parametrer_data);
        echo "<br><br>";
        
        // print_r(json_encode($parametrer_data)); //commented for testing
        
        if($parametrer_data["DATA_AVAILABLE"]==="1")
        {
            setParameterDataForCurrentWindow($conn,$parametrer_data,$sensor_id,$parameterInfo->device_id);
            //update disconnected status of device to 0 when data available
            $updateDeviceDisconnectedStatusQuery = "Update `devices` SET disconnectedStatus = '0' where id ='$deviceId'";
            $updateDeviceDisconnectedStatusResults = mysqli_query($conn,$updateDeviceDisconnectedStatusQuery);
        }else{
            
            $updateDeviceDisconnectedStatusQuery = "Update `devices` SET disconnectedStatus = '1' where id ='$deviceId'";
            $updateDeviceDisconnectedStatusResults = mysqli_query($conn,$updateDeviceDisconnectedStatusQuery);
        }
        
        //Computing stel values for evry 15mins
        //compute_STEL($conn,$data_table,$parameterInfo,$moving_window_width);
        
        /** computing twa between mentioned time begin **/

        date_default_timezone_set('Asia/Kolkata');

        $userStartTime = "9:00 AM";
        $userEndTime = "6:00 PM";

        $time_in_24_hour_format_startTime  = date("H:i:s", strtotime($userStartTime));
        $time_in_24_hour_format_endTime  = date("H:i:s", strtotime($userEndTime));

        // echo $time_in_24_hour_format_startTime."<br>";
        // echo $time_in_24_hour_format_endTime."<br>";

        $time_in_24_hour_format_currentTime = date('H:i:s');

        // echo $time_in_24_hour_format_currentTime."<br>";

        if(($time_in_24_hour_format_currentTime>=$time_in_24_hour_format_startTime)&&($time_in_24_hour_format_currentTime<=$time_in_24_hour_format_endTime)){
            //call the funtion
            //compute_tWA($conn,$data_table,$parameterInfo,$moving_window_width,$shift_dur_min);
        }else{
            //echo "Dont execute twa"."<br>";
        }
        
        /** computing twa between mentioned time end **/
    }
}

//Data entering into sample sensor details after every two minutes based on sensor tag
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
   // echo $sql;
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


function compute_STEL($conn,$data_table,$parameteInfo,$moving_window_width)
{
    $moving_window_width = 1;  //1 is taken as min
    $mode="2";
    $moving_window_end_p_timestamp=strtotime(date("Y-m-d H:i:s"));
    $moving_window_start_p_timestamp=$moving_window_end_p_timestamp-$moving_window_width*60;
    //echo $moving_window_end_p_timestamp." ".$moving_window_start_p_timestamp."<br>";
    $param_C_total=0;
    $total_N=0;
    
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
    
   // echo $sql."<br>";
    
    $res=mysqli_query($conn,$sql) or die(mysqli_error($conn));
    
     while($row=mysqli_fetch_array($res))
    {
        $json_data=$row['j_data'];
        $json_data_obj=json_decode($json_data,true);
        $upload_date=$json_data_obj["DATE"];
        $upload_time=$json_data_obj["TIME"];
        $deviceMode = $json_data_obj["MODE"];
        $upload_date_time=$upload_date." ".$upload_time;
        // echo $row['j_data']."<br>";
        
        
        if($parameteInfo->parameter_tag==="PM10_GAS2")
        {
             //"upload date time:".$upload_date_time." rt $data_retreival_timestamp mst $moving_window_start_p_timestamp mest $moving_window_end_p_timestamp</br>";
        }
        
        $data_retreival_timestamp=strtotime($upload_date_time);
    
        if(($data_retreival_timestamp>=$moving_window_start_p_timestamp)&&($data_retreival_timestamp<=$moving_window_end_p_timestamp))
        {
            $value=$json_data_obj[($parameteInfo->parameter_tag)];
            $val = scalingValue($conn,$parameteInfo->company,$parameteInfo->device_id,$parameteInfo->parameter_tag,$value);
            $param_C_total= $param_C_total+$val;
            $total_N=$total_N+1;
           // echo $param_C_total."<br>";
        }
    }
    if($total_N>0)
    {
        $val = $param_C_total/$total_N;
        $alertType = explode(",",alertMessage($conn,$parameteInfo->company,$parameteInfo->device_id,$parameteInfo->parameter_tag,$upload_date,$upload_time,$val));
        return ($param_C_total/$total_N);
       
    }
    else
    {
       return 0;  
    }
}


function compute_tWA($conn,$data_table,$parameteInfo,$moving_window_width,$shift_dur_min)
{
    $moving_window_width = 5;// 2 is taken as min
    $shift_dur_min = 480;
    $mode="2";
    $moving_window_end_p_timestamp=strtotime(date("Y-m-d H:i:s"));
    $moving_window_start_p_timestamp=$moving_window_end_p_timestamp-$moving_window_width*60;
    //echo $moving_window_end_p_timestamp." ".$moving_window_start_p_timestamp;
    
    $param_C_total=0;
    $total_N=0;
    
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
    
    $sum=0;
    
     while($row=mysqli_fetch_array($res))
    {  
       
        $json_data=$row['j_data'];
        $json_data_obj=json_decode($json_data,true);
        $upload_date=$json_data_obj["DATE"];
        $upload_time=$json_data_obj["TIME"];
        $deviceMode = $json_data_obj["MODE"];
        $upload_date_time=$upload_date." ".$upload_time;
        
      
        
        if($parameteInfo->parameter_tag==="PM10_GAS2")
        {
             //"upload date time:".$upload_date_time." rt $data_retreival_timestamp mst $moving_window_start_p_timestamp mest $moving_window_end_p_timestamp</br>";
        }
        
        
        $data_retreival_timestamp=strtotime($upload_date_time);
        if(($data_retreival_timestamp>=$moving_window_start_p_timestamp)&&($data_retreival_timestamp<=$moving_window_end_p_timestamp))
        {
            $value=$json_data_obj[($parameteInfo->parameter_tag)];
            
            $val = scalingValue($conn,$parameteInfo->company,$parameteInfo->device_id,$parameteInfo->parameter_tag,$value);
            
            $param_C_total= $param_C_total+$val;
            $total_N=$total_N+1;
        }
    }
    
    $twa_prev=0;
    $sensor_id = $parameteInfo->id;
    $parameterName = $parameteInfo->parameter_tag;
    
    $sql="select * from twa_info where sens_id='$sensor_id'";
    $res=mysqli_query($conn,$sql) or die(mysqli_error($conn));
    $n=mysqli_num_rows($res);
    
    if($row=mysqli_fetch_array($res))
    {
        $twa_prev=$row['twaValue'];
        $cur_date_time=date("Y-m-d H:i:s");
        $reset_time=$row['resetDateTime'];
        $time_gap=strtotime($cur_date_time)-strtotime($reset_time);
        
        if($time_gap>$shift_dur_min)
        {
           $twa_prev=0; 
           $sql="update twa_info set resetDateTime='$cur_date_time' where sens_id='$sensor_id'";
           $res=mysqli_query($conn,$sql) or die(mysqli_error($conn));
        }
    }
    
    $param_ci=($total_N>0)?($param_C_total/$total_N):0;
    $twa=$twa_prev+(($param_ci*$moving_window_width)/$shift_dur_min);
    if($n>0)
    {
        $sql="update twa_info set twaValue='$twa' where sens_id='$sensor_id'";
        $res=mysqli_query($conn,$sql) or die(mysqli_error($conn));
    }
    else
    {
        $sql="insert into twa_info(sens_id,parameterName,twaValue,resetDateTime) values('$sensor_id','$parameterName','$twa','$cur_date_time')";
        $res=mysqli_query($conn,$sql) or die(mysqli_error($conn));
    }
    if($twa){
        alertMessage($conn,$parameteInfo->company,$parameteInfo->device_id,$parameteInfo->parameter_tag,$upload_date,$upload_time,$val);   
    }
    return  $twa;
}

include("includes/config.php");

date_default_timezone_set('Asia/Kolkata');

$data_table="aqmi_json_data";

//$moving_window_width=15*60;//in seconds

$moving_window_width=120;//in seconds

$device_mode="2";


extractAQMSParameterData($mysqli,$data_table,$moving_window_width,$device_mode);

//delete data if there is more than 5000 data in aqmijson
$dbuser="idealab_Aqms";
$dbpass="Aq1sw2de3fr4@aqms";
$host="localhost";
$db="idealab_database";
$con=new mysqli($host,$dbuser,$dbpass,$db);
$sql="SELECT * FROM aqmi_json_data";

if ($result=mysqli_query($con,$sql))
{
    // Return the number of rows in result set
    $rowcount=mysqli_num_rows($result);
    if($rowcount > 5000){
        $cnt = $rowcount - 5000;
        $deleteSql = "Delete FROM `aqmi_json_data` order by id ASC limit ".$cnt;
        $deleteSqlResult = mysqli_query($con,$deleteSql);
        if($deleteSqlResult){
            echo "deleted data"."<br>";
        }else{
            echo "Not delete dataa"."<br>";
        }
    }else{
            echo "Not delete dataa"."<br>";
    }
    // Free result set
}



?>