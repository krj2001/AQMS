<?php

ini_set("display_errors",1);
error_reporting(1);

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
    //echo $sql."</br>";
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
            $val=$json_data_obj[($parameteInfo->parameter_tag)];
            alertMessage($conn,$parameteInfo->company,$parameteInfo->device_id,$parameteInfo->parameter_tag,$upload_date,$upload_time,$val);
            $max=($val>$max)?$val:$max;
            $min=($val<$min)?$val:$min;
            $par_val_sum=$par_val_sum+$val;
            $data_count=$data_count+1;
            
            $data_available="1";
            //echo $sql."</br>";
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
        $ret_data_array["UNIT"]="ug/m3";
        $ret_data_array["DATETIME"]=$current_sample_date_time;
        
    }

    $ret_data_array["DATA_AVAILABLE"]=$data_available;
    return $ret_data_array;
}

$FLAG = 0; //set for allert message funtio

function alertMessage($conn,$companyCode,$deviceId,$parameterTag,$upload_date,$upload_time,$val){
    
        $sql = "select * from  sensors where companyCode='$companyCode' and deviceId='$deviceId' and sensorTag='NH3_gas1'";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        
        
        $alertType=array('Warning','outOfRange','Critical');
        $alertStatus = array('Cleared','notCleared');
        
        $a_date = $upload_date;
        $a_time = $upload_time;
        
        $from_time = strtotime($upload_date." ".$upload_time);
        
        $companyCode = $companyCode;
        $deviceId = $row['deviceId'];
        $sensorTag = $parameterTag;
        $sensorId = $row['id'];
        
        $warningAlertType = $row['warningAlertType'];
        $outofrangeAlertType = $row['outofrangeAlertType'];
        $criticalAlertType = $row['$criticalAlertType'];
        
        $wariningMessage = "";
        $outofrangeMessage = "";
        $criticalMessage = "";
        
        $criticalAlertStatusFlag = 0;
        $warningAlertStatusFlag = 0;
        $outOfRangeAlertStatusFlag = 0;
        
        
        $ALERT_MIN_IMTERVAL_MINUTES = 2;
        
       
       //critical
        if($criticalAlertType  = "Both"){
            if(intval($val) < intval($row['criticalMinValue'])){
                $criticalMessage = $row['criticalLowAlert'];
                $criticalAlertStatusFlag = 1;
            }
            else if(intval($val) > intval($row['criticalMaxValue'])){
                $criticalMessage = $row['criticalHighAlert'];
                $criticalAlertStatusFlag = 1;
            }
            else
            {
              $criticalAlertStatusFlag = 0;
            }
        }else if($criticalAlertType  = "high"){
            if(intval($val) > intval($row['criticalMaxValue'])){
                $criticalMessage = $row['criticalHighAlert'];
                $criticalAlertStatusFlag = 1;
            }
            else
            {
                $criticalAlertStatusFlag = 0;
            }
        }else{
            if(intval($val) > intval($row['criticalMinValue'])){
                $criticalMessage = $row['criticalLowAlert'];
                $criticalAlertStatusFlag = 1;
            }
            else
            {
              $criticalAlertStatusFlag = 0;
            }
        }
        
        
        //outofrangealerts
        if($criticalAlertStatusFlag == 0){
            if($outofrangeAlertType  == "Both"){
                if(intval($val) < intval($row['outofrangeMinValue'])){
                    $outofrangeMessage = $row['outofrangeLowAlert'];
                    $outOfRangeAlertStatusFlag = 1;
                }
                else if(intval($val) > intval($row['outofrangeMaxValue'])){
                    $outofrangeMessage = $row['outofrangeHighAlert'];
                    $outOfRangeAlertStatusFlag = 1;
                }
                else
                {
                     $outOfRangeAlertStatusFlag = 0;
                }
            }else if($outofrangeAlertType  = "high"){
                if(intval($val) > intval($row['outofrangeMaxValue'])){
                    $outofrangeMessage = $row['outofrangeHighAlert'];
                    $outOfRangeAlertStatusFlag = 1;
                }
                else
                {
                    $outOfRangeAlertStatusFlag = 0;
                }
            }else{
                if(intval($val) < intval($row['outofrangeMinValue'])){
                    $outofrangeMessage = $row['outofrangeLowAlert'];
                    $outOfRangeAlertStatusFlag = 1;
                }
                else
                {
                    $outOfRangeAlertStatusFlag = 0;
                }
            }
        }
        
        
        if( $outOfRangeAlertStatusFlag == 0){
            //warning alerts
            if($warningAlertType  == "Both"){
                if(intval($val) < intval($row['warningMinValue'])){
                    $wariningMessage = $row['warningLowAlert'];
                    $warningAlertStatusFlag = 1;
                }
                else if(intval($val) > intval($row['warningMaxValue'])){
                    $wariningMessage = $row['warningHighAlert'];
                    $warningAlertStatusFlag = 1;
                }
                else
                {
                    $warningAlertStatusFlag = 0;
                }
            }else if($warningAlertType  == "high"){
                if(intval($val) > intval($row['warningMaxValue'])){
                    $wariningMessage =  $row['warningHighAlert'];
                    $warningAlertStatusFlag = 1;
                }
                else
                {
                    $warningAlertStatusFlag = 0;
                }
            }else{
                if(intval($val) < intval($row['warningMinValue'])){
                    $wariningMessage = $row['warningLowAlert'];
                    $warningAlertStatusFlag = 1;
                }
                else
                {
                   $warningAlertStatusFlag = 0;
                }
            }
        }
        
        
        
        
       
        //if value doesnt meet thershold then alertstausflag is set to 1 else 0
        
        echo "actual_val".$val."<br>";
        echo $row['warningMinValue']." max value <br>";
        echo "status:".$alertStatusFlag."<br>";
        echo $outofrangeMessage;
        
        if($criticalAlertStatusFlag === 0 &&  $warningAlertStatusFlag === 0 && $outOfRangeAlertStatusFlag === 0){
            echo "status : normal";
            //updating unlatch to 1 and setting to clear if thresholds meets 
            $sql = "UPDATE alert_cron set unLatch='1',status='$alertStatus[0]'  where sensorTag='NH3_gas1'";
            $res=mysqli_query($conn, $sql) or die(mysqli_error($conn));
        }
        else{
            if($criticalAlertStatusFlag === 1){
                $alertTypes = $alertType[2];
                $severity = $criticalAlertType;
                $status = $alertStatus[1];
                
                $min = explode(", ", getLastAlertData($conn,$companyCode,$deviceId,$parameterTag,$alertType[2],$ALERT_MIN_IMTERVAL_MINUTES,$upload_date,$upload_time));
                $total_min = $min[0];
                $to_time = $min[1];
                
                if($parameterTag == "NH3_gas1"){
                    if($total_min > $ALERT_MIN_IMTERVAL_MINUTES){
                        if($to_time === $from_time){
                            if($FLAG == 1){
                                InsertAlertCronData($conn,$a_date,$a_time,$companyCode,$deviceId,$sensorId,$sensorTag,$alertType,$criticalMessage,$severity,$status,$current_time);
                            }
                            else{
                                  
                            }
                        }
                        else{
                            InsertAlertCronData($conn,$a_date,$a_time,$companyCode,$deviceId,$sensorId,$sensorTag,$alertTypes,$criticalMessage,$severity,$status,$current_time);
                        }
                    }
                } 
            }
            
            if($outOfRangeAlertStatusFlag === 1){
                $alertTypes = $alertType[1];
                $severity = $outofrangeAlertType;
                $status = $alertStatus[1];
                            
                $min = explode(", ", getLastAlertData($conn,$companyCode,$deviceId,$parameterTag,$alertType[1],$ALERT_MIN_IMTERVAL_MINUTES,$upload_date,$upload_time));
                $total_min = $min[0];
                $to_time = $min[1];
                
                if($parameterTag == "NH3_gas1"){
                    if($total_min > $ALERT_MIN_IMTERVAL_MINUTES){
                        if($to_time === $from_time){
                            if($FLAG == 1){
                                InsertAlertCronData($conn,$a_date,$a_time,$companyCode,$deviceId,$sensorId,$sensorTag,$alertTypes,$outofrangeMessage,$severity,$status,$current_time);
                            }
                            else{
                                      
                            }    
                          
                        }
                        else{
                            InsertAlertCronData($conn,$a_date,$a_time,$companyCode,$deviceId,$sensorId,$sensorTag,$alertTypes,$outofrangeMessage,$severity,$status,$current_time);
                        }
                    }
                }
            }
            
            if($warningAlertStatusFlag === 1){
                $alertTypes = $alertType[1];
                $severity = $outofrangeAlertType;
                $status = $alertStatus[1];
                            
                $min = explode(", ", getLastAlertData($conn,$companyCode,$deviceId,$parameterTag,$alertType[1],$ALERT_MIN_IMTERVAL_MINUTES,$upload_date,$upload_time));
                $total_min = $min[0];
                $to_time = $min[1];
                
                if($parameterTag == "NH3_gas1"){
                    if($total_min > $ALERT_MIN_IMTERVAL_MINUTES){
                        if($to_time === $from_time){
                            if($FLAG == 1){
                                InsertAlertCronData($conn,$a_date,$a_time,$companyCode,$deviceId,$sensorId,$sensorTag,$alertTypes,$wariningMessage,$severity,$status,$current_time);
                            }
                            else{
                                      
                            }    
                          
                        }
                        else{
                            InsertAlertCronData($conn,$a_date,$a_time,$companyCode,$deviceId,$sensorId,$sensorTag,$alertTypes,$wariningMessage,$severity,$status,$current_time);
                        }
                    }
                }
            }
        }
}

function getLastAlertData($conn,$companyCode,$deviceId,$parameterTag,$alertType,$ALERT_MIN_IMTERVAL_MINUTES,$upload_date,$upload_time){ //function to get last data to check whether it is unlatched
    
    $total_min = "";
    
    $ALERT_MIN_IMTERVAL_MINUTES = 2;
    
    $alertSql = "select * from  alert_cron where companyCode='$companyCode' and deviceId='$deviceId' and sensorTag='NH3_gas1' and unLatch='0' and alertType='$alertType' order by id desc";
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
    
    return $total_min.",".$to_time; 
    
}

function InsertAlertCronData($conn,$a_date,$a_time,$companyCode,$deviceId,$sensorId,$sensorTag,$alertTypes,$Message,$severity,$status,$current_time){
    $sql_query = "INSERT INTO `alert_cron`(`a_date`, `a_time`, `companyCode`, `deviceId`, `sensorId`, `sensorTag`, `alertType`, `msg`, `severity`, `status`, `time_stamp`) VALUES 
                              ('$a_date','$a_time','$companyCode','$deviceId','$sensorId','$sensorTag','$alertTypes','$Message','$severity','$status','$current_time')";
    $res=mysqli_query($conn, $sql_query) or die(mysqli_error($conn));
    
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
    $parameterName=$parameter_data["PARAMETER_NAME"];
    $sql="select * from sampled_sensor_data_details where parameterName='".($parameterName)."' and sensor_id=".$sensor_id." and sample_date_time='$sample_date_time'";
    
    $res=mysqli_query($conn,$sql) or die(mysqli_error($conn));
    
    if(mysqli_num_rows($res)>0){
        $sql_query="update sampled_sensor_data_details set last_val='$last_val',max_val='$max_val',min_val='$min_val',avg_val='$avg_val',param_unit='$param_unit' where parameterName='".($parameterName)."' and sensor_id=".$sensor_id." and sample_date_time='$sample_date_time'";
    }
    else
    {
       $sql_query="insert into sampled_sensor_data_details(device_id,sensor_id,parameterName,last_val,max_val,min_val,avg_val,sample_date_time,param_unit) values($device_id,$sensor_id,'$parameterName','$last_val','$max_val','$min_val','$avg_val','$sample_date_time','$param_unit')";
    }
    // echo $sql_query."</br>";
    $res=mysqli_query($conn, $sql_query) or die(mysqli_error($conn));
}

function getSampledValuesForAParameter($conn,$data_table,$aqmi_par_id,$sampling_Interval_min,$cur_date_time,$backInterval_min)
{
    //$sql_timezone_lag_mins=13*60+30;
    $back_interval_in_minutes=$backInterval_min;
    $grouping_interval_in_minutes=$sampling_Interval_min;
    $date_from=date("Y-m-d H:i:s",strtotime($cur_date_time)-$backInterval_min*60);
    $sql="SELECT sample_date_time as DATE_TIME,sensor_id,parameterName as parameter,FLOOR(UNIX_TIMESTAMP(sample_date_time)/(".$grouping_interval_in_minutes." * 60)) AS timekey,MAX(max_val) as par_max,MIN(min_val) as par_min,AVG(avg_val)  as par_avg,last_val as par_last FROM ".$data_table." WHERE sensor_id=$aqmi_par_id and sample_date_time >(NOW() - INTERVAL ".$back_interval_in_minutes." MINUTE) GROUP BY timekey";
    //echo $sql;
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

//$moving_window_width=15*60;//in seconds

$moving_window_width=120;//in seconds

$device_mode="4";


extractAQMSParameterData($mysqli,$data_table,$moving_window_width,$device_mode);


$data_table="sampled_sensor_data_details";

$aqmi_par_id=43;

$sampling_Interval_min=60;

$date=date("Y-m-d H:i:s");

$backInterval_min=24*60;

echo getSampledValuesForAParameter($mysqli,$data_table,$aqmi_par_id,$sampling_Interval_min,$date,$backInterval_min);


?>