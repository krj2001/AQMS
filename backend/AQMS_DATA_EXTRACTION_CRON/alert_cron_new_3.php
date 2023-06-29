<?php
include("includes/config.php");
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$ANOMALY_DETECTION_TYPE=2;
/*ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1 );*/

//$MAX_OUTLIERS=4;

include("alert_functions.php");
$SENSORS=array();
$DATA_TABLES=array("temperature"=>"temperature_data_new",
                   "vibration"=>"vibration_data_new",
                   "energy"=>"energy_data_new");
                   
                 /*  $alert_type="mh_alert";
//send_alert(1,$alert_parametrs,$alert_type);

$alert_msg="Vibration high ";

$msg=$alert_msg."severity level:".$severity." - RDL TECHNOLOGY PVT LTD";

$alert_parametrs[0]="MNT ALERT ".$msg;
send_alert(2,$alert_parametrs,$alert_type);*/

function anomaly_detection_using_svm_outlier_filter($csv_data,$min_thr_val)
{

$cmd2="python3 svm_outlier_classifier.py $csv_data $min_thr_val 2>&1 &";
$op=shell_exec($cmd2);
echo $op;
$outliers=explode(":",$op)[1];
echo $outliers;
return $outliers;

}


date_default_timezone_set('Asia/Kolkata');

$cur_date=date("Y-m-d");
$cur_time=date("H:i:s");

$machine_names=array("Shearing Machine","Punching Machine","RG35","RG80","RG100","Pretreatment Machine","Powder Coating Machine","Spot Welding 1","Spot Welding 2","Spot Welding 3","Overhead Spot Welding 1","Overhead Spot Welding 2");



//send_maintanance_schedule_alerts();

for($i=0;$i<count($machine_names);$i++)
{

echo "machine name:".$machine_names[$i]."</br>";
$query="select * from sensors where machine_name='".$machine_names[$i]."'";
$res=mysqli_query($mysqli,$query) or die(mysqli_error($mysqli));
$SENSORS=array();
$sensr_count=0;
while($rw=mysqli_fetch_array($res))
{
$SENSORS[$sensr_count++]=$rw['sensor'];

}

$tot_sensors=count($SENSORS);


print_r($SENSORS);


for($j=0;$j<$tot_sensors;$j++)
{

     $sens_value=0;
    $sens_max=10000;
    $sens_min=-10000;
    $alert_msg="";
    $sensor=$SENSORS[$j];
    $sensor_par=strtolower(explode(" ",$sensor)[0]);
    $sens_data_table=$DATA_TABLES[$sensor_par];
    
   
    
    //$min_max_thr_en=$ALER_DATA_RANGE[$sensor];
    
    $sql="select * from threshhold where machine_name='".$machine_names[$i]."' and sensor='".$sensor."'";
    $res_q=mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli));
    
    if($rww=mysqli_fetch_array($res_q))
    {
    
    
    print_r($rww);
    
    $filt_type=$rww['filter_type'];
    $filt_p=strtolower(explode(" ",$filt_type)[0]);
    $alert_type=($filt_p==="outlier")?2:1;
    $sens_max=$rww['max'];
    $sens_min=$rww['min'];
    $outliers_max=$rww['max_outliers'];
    $alert_msg=$rww['message'];
    
    
    
    echo "</br>machine name:".$machine_names[$i]." msg:".$alert_msg."</br>";
    
 /*$sql="select * from ".$sens_data_table." where date_t='$cur_date' and machine_name='$machine_names[$i]' order by id DESC limit 1";
 
$res=mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli));


if($row=mysqli_fetch_array($res))
{
$sens_value=round($row['value'],2);
}*/


echo "machine name:".$machine_names[$i]." sensor:".$sensor_par." </br>";


$sens_value=getLatestSensorDataForAMachine($machine_names[$i],$sensor_par,$sensor,$i);

if($sens_value!=-100000)
{

echo "sens value:".$sens_value."</br>";

if($alert_type==1)
{

echo "filter:".$filt_p."</br>";


if($filt_p==="low")
{
$alert_msg=($sens_value>$sens_max)?$alert_msg:"Normal";
}
if($filt_p==="high")
{
$alert_msg=($sens_value<$sens_min)?$alert_msg:"Normal";
}

if($filt_p==="band")
{

$isAboveUpperLimit=($sens_value>$sens_max);

$isBelowLoweLimit=($sens_value<$sens_min);

if($isAboveUpperLimit)
{

$alert_msg=$alert_msg.":".$sensor."[Above Upper limit]"; 

}
else if($isBelowLoweLimit)
{

$alert_msg=$alert_msg.":".$sensor."[Below Lower limit]";

}

$alert_msg=($isAboveUpperLimit||$isBelowLoweLimit)?$alert_msg:"Normal";

}


//$alert_msg=$sensor." ".$alert;



echo $alert_msg;
$severity="Major";
}
else if($alert_type===2)
{

echo "type 2";

$collect_dataset_from= date('Y-m-d', strtotime('-2 days'));


/*$sql="select * from ".$sens_data_table." where date_t >='$collect_dataset_from' and machine_name='".$machine_names[$i]."' order by id DESC";

echo "Machine Name:".$machine_names[$i]."</br>";
$myFile="sensor_dataset_for_anomaly_det.csv";
$file = fopen($myFile, 'w');
fputcsv($file, array('Date',"value"));
$res1=mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli));
$hr_index=0;
while($row1=mysqli_fetch_array($res1))
{
$hr_index=$hr_index+1;
$hr=$row1['hr'];
$date_time=$row1['date_t'];
if(strpos($hr,"PM")!==FALSE)
{
$hr_val=explode("PM",$hr)[0];

if($hr_val<12)
{
 $hr_val=$hr_val+12;
}

}
else
{

//$hr_val=explode("AM",$hr)[0];

}

$date_time=$date_time." ".$hr_val.":00:00";

$sens_value=$row1['value'];

fputcsv($file, array($hr_index,$sens_value));


}

fclose($file);

chmod($myFile,0777);

echo $myFile;*/

$myFile=createDatasetForSVM($machine_names[$i],$collect_dataset_from,$sensor_par,$i);

echo "</br>Data file:".$myFile."</br>";






$outliers=anomaly_detection_using_svm_outlier_filter($myFile,$sens_min);

echo "</br>outliers:".$outliers."</br>";

$alert_msg=($outliers>=$outliers_max)?$alert_msg:"Normal";

$severity="Major";


}
echo $alert_msg;

echo "alert msg 11:".$alert_msg;
set_alert($alert_msg,$sensor,$sens_value,$cur_date,$cur_time,$machine_names[$i],$severity);
}
}
}
}

send_service_due_alerts();

send_certificate_due_alerts();

send_maintanance_schedule_alerts();


function send_service_due_alerts()
{

global $mysqli;
$cur_date=date("Y-m-d");

$cur_hr_min=date("H:i:00");

$alert_tm="06:00:00";

if($cur_hr_min==$alert_tm)
{
$cur_date_time=date("Y-m-d H:i:s");
$sq="select * from alert_enabled_users where alert_type='amc_alert'";
$rs=mysqli_query($mysqli,$sq) or die(mysqli_error($mysqli));
$service_alert_lead_days=0;

if($rw=mysqli_fetch_array($rs))
{

$service_alert_lead_days=$rw['days_before'];

}

$sql="select * from ser_date where datez>='$cur_date'";


$res=mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli));

while($row=mysqli_fetch_array($res))
{

$service_start_date=$row['datez'];

$alert_date=date("Y-m-d",strtotime("-".$service_alert_lead_days." days",strtotime($service_start_date)));

if($alert_date==$cur_date)
{

$asset_name=$row['machine'];
$vendor=$row['name'];
$alert_parametrs=array();
$subject="Upcoming AMC Service Alert";
$msg="There is an AMC alert set for the Asset ".$asset_name." by the vendor ".$vendor." on date ".$service_start_date." - RDL TECHNOLOGY PVT LTD";

$alert_parametrs[0]=$subject;
$alert_parametrs[1]=$msg;
$alert_type="amc_alert";
send_alert(1,$alert_parametrs,$alert_type);
$alert_parametrs[0]=$msg;
send_alert(2,$alert_parametrs,$alert_type);
}
}
}
}


function send_maintanance_schedule_alerts()
{

global $mysqli;


$cur_date=date("Y-m-d");

$cur_hr_min=date("H:i:00");

echo "mnt alert:".$cur_hr_min;

$alert_tm="06:00:00";

if($cur_hr_min==$alert_tm)
{
$cur_date_time=date("Y-m-d H:i:s");
$sq="select * from alert_enabled_users where alert_type='mnt_alert'";
$rs=mysqli_query($mysqli,$sq) or die(mysqli_error($mysqli));
$service_alert_lead_days=0;

if($rw=mysqli_fetch_array($rs))
{

$service_alert_lead_days=$rw['days_before'];

}

$sql="select * from schedule where datefrom>='$cur_date'";

echo $sql;




$res=mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli));

while($row=mysqli_fetch_array($res))
{

$service_start_date=$row['datefrom'];

$alert_date=date("Y-m-d",strtotime("-".$service_alert_lead_days." days",strtotime($service_start_date)));

if($alert_date==$cur_date)
{

$asset_name=$row['machine'];
$vendor=$row['name'];
$severity=$row['servicity'];
$alert_parametrs=array();
$subject="Upcoming Maintainance Alert";
//$msg="There is an AMC alert set for the Asset ".$asset_name." by the vendor ".$vendor." on date ".$service_start_date." - RDL TECHNOLOGY PVT LTD";

$alert_msg="Maintainance ".$asset_name."On ".$service_start_date;

///$alert_msg="Maintainance ";

$msg=$alert_msg."severity level :".$severity." - RDL TECHNOLOGY PVT LTD"; 

$alert_parametrs[0]=$subject;
$alert_parametrs[1]=$msg;
$alert_type="mnt_alert";


send_alert(1,$alert_parametrs,$alert_type);
$alert_parametrs[0]="MNT ALERT ".$msg;
send_alert(2,$alert_parametrs,$alert_type);
}
}
}
}







function send_certificate_due_alerts()
{

global $mysqli;
$cur_date=date("Y-m-d");
$cur_hr_min=date("H:i:00");
$alert_tm="06:02:00";
if($cur_hr_min==$alert_tm)
{
$cur_date_time=date("Y-m-d H:i:s");
$sq="select * from alert_enabled_users where alert_type='cert_alert'";
$rs=mysqli_query($mysqli,$sq) or die(mysqli_error($mysqli));
$service_alert_lead_days=0;

if($rw=mysqli_fetch_array($rs))
{

$service_alert_lead_days=$rw['days_before'];

}

$sql="select * from ser_date where datez>='$cur_date'";


$res=mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli));

while($row=mysqli_fetch_array($res))
{

$service_start_date=$row['datez'];

$alert_date=date("Y-m-d",strtotime("-".$service_alert_lead_days." days",strtotime($service_start_date)));

if($alert_date==$cur_date)
{

$asset_name=$row['machine'];
$vendor=$row['name'];
$alert_parametrs=array();
$subject="Upcoming Certificate Inspection Alert";
$msg="There is an Certificate Inspection  for the Asset ".$asset_name." by the vendor ".$vendor." on date ".$service_start_date." - RDL TECHNOLOGY PVT LTD";
$alert_parametrs[0]=$subject;
$alert_parametrs[1]=$msg;
$alert_type="cert_alert";
send_alert(1,$alert_parametrs,$alert_type);
$alert_parametrs[0]=$msg;
send_alert(2,$alert_parametrs,$alert_type);
}
}
}
}



function set_alert($alert_msg,$sensor,$sen_value,$date,$time,$machine_name,$severity)
{

global $mysqli;

$sensor_t=explode(" ",$sensor)[0];

if($alert_msg=="Normal"){


$sq="select * from alert_cron where machine_name='$machine_name' and sensor='$sensor' ORDER BY id DESC LIMIT 1";

echo $sq."</br>";


$re=mysqli_query($mysqli,$sq);
$cnt=mysqli_num_rows($re);
$row=mysqli_fetch_array($re);
$latest_alert_st=$row['status'];


if($latest_alert_st=="Not Cleared")
{
$sql="insert into alert_cron(machine_name,sensor,actual,message,severity,a_date,a_time,status) values('$machine_name','$sensor','$sen_value','".$sensor_t." Normal','Low','$date','$time','Cleared')";
echo $sql."</br>";
$res=mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli));
}
}
else{

$sq="select * from alert_cron where machine_name='$machine_name' and sensor='$sensor' ORDER BY id DESC LIMIT 1";

echo $sq."</br>";
$re=mysqli_query($mysqli,$sq);
$cnt=mysqli_num_rows($re);
$row=mysqli_fetch_array($re);
$latest_alert_st=$row['status'];

$n=mysqli_num_rows($re);

if(($latest_alert_st=="Cleared") || ($n<1))
{

$sql="insert into alert_cron(machine_name,sensor,actual,message,severity,a_date,a_time,status) values('$machine_name','$sensor','$sen_value','$alert_msg','$severity','$date','$time','Not Cleared')";
echo $sql;
$res=mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli));
$subject=$sensor." Over Cross Alert";

$msg=$alert_msg."severity level:".$severity." - RDL TECHNOLOGY PVT LTD"; 

/*$mail_alert="santhoshshetty671990@gmail.com";

echo "</br> sub:".$subject." msg:".$msg." mail:".$mail_alert."</br>";


if(isset($mail_alert))
{
send_alert_email($subject,$msg,$mail_vib);
}*/

$alert_parametrs=array();
$subject=$subject;
$msg=$msg;
$alert_parametrs[0]=$subject;
$alert_parametrs[1]=$msg;
$alert_type="mh_alert";
send_alert(1,$alert_parametrs,$alert_type);
$alert_parametrs[0]="MNT ALERT ".$msg;
send_alert(2,$alert_parametrs,$alert_type);
}

}
}



function createDatasetForSVM($machine_name,$collect_dataset_from,$sensor,$m_index)
{

$data_index=0;

global $mysqli;

include('iot4_datalogger_constants.php');

$required_fields_names=array("machine_db_tables","json_data_mapping_table");

$required_fields=array($machine_db_tables,$json_data_mapping_table);

for($i=0;$i<count($required_fields);$i++)
{

if(!isset($required_fields[$i]))
{

die("function createDatasetForSVM(....) --- error ".$required_fields_names[$i]." not set");

}


}



                                               

$data_table=$machine_db_tables[$m_index];
$selectors=explode(",",$json_data_mapping_table[$machine_name][$sensor]["SELECTORS"]);
$data_key=$json_data_mapping_table[$machine_name][$sensor]["DATA_KEY"];
$DATA_TYPE=$json_data_mapping_table[$machine_name][$sensor]["DATA_TYPE"];

$query="select * from ".$data_table." ";
$json_data_field="j_data";
for($i=0;$i<count($selectors);$i++)
{
$sp=($i>0)?"and":"";
$query=$query." ".$sp." where ".$json_data_field." LIKE '%".$selectors[$i]."%'"; 
}


$myFile="sensor_dataset_for_anomaly_det.csv";
$file = fopen($myFile, 'w');
fputcsv($file, array('Date',"value"));


$query=$query. " and dt>='$collect_dataset_from'";

echo $query."</br>";
$res=mysqli_query($mysqli,$query) or die(mysqli_error($mysqli));

$prev_data=0;

while($row=mysqli_fetch_array($res))
{

$data_index=$data_index+1;
$json_data=$row['j_data'];
$json_obj=json_decode($json_data,true);
$sens_data=$json_obj[$data_key];


if($DATA_TYPE===$DATA_TYPE_INCREMENTAL)
{
$sens_data=($prev_data>0)?max(($sens_data-$prev_data),0):0;
$prev_data=$sens_data;
}
$date_time=$row['dt']." ".$row['tm'];
fputcsv($file, array($data_index,$sens_data));

}

fclose($file);

chmod($myFile,0777);

echo $myFile;

return $myFile;

}

function getPreviousAlertTimeForAMachine($machine_name,$sensor)
{

global $mysqli;

$sq="select * from alert_cron where machine_name='$machine_name' and sensor='$sensor' ORDER BY id DESC LIMIT 1";

echo $sq."</br>";

$re=mysqli_query($mysqli,$sq);
$cnt=mysqli_num_rows($re);

$def_prev_alert__time_stamp=strtotime(date("Y-m-d H:i:s"))-2*60*60;

if($row=mysqli_fetch_array($re))
{
return ($row['a_date'].",".$row['a_time']);
}
else
{
return (date("Y-m-d",$def_prev_alert__time_stamp)).",".(date("H:i:s",$def_prev_alert__time_stamp));
}
}



function getLatestSensorDataForAMachine($machine_name,$sensor,$sensor_1,$m_index)
{

$data_index=0;

global $mysqli;

include('iot4_datalogger_constants.php');

$required_fields_names=array("machine_db_tables","json_data_mapping_table");

$required_fields=array($machine_db_tables,$json_data_mapping_table);

for($i=0;$i<count($required_fields);$i++)
{

if(!isset($required_fields[$i]))
{

die("function getLatestSensorDataForAMachine(....) --- error ".$required_fields_names[$i]." not set");

}


}



                                               

$data_table=$machine_db_tables[$m_index];
$selectors=explode(",",$json_data_mapping_table[$machine_name][$sensor]["SELECTORS"]);
$data_key=$json_data_mapping_table[$machine_name][$sensor]["DATA_KEY"];
$DATA_TYPE=$json_data_mapping_table[$machine_name][$sensor]["DATA_TYPE"];

$query_1="select * from ".$data_table." where ";
$json_data_field="j_data";
for($i=0;$i<count($selectors);$i++)
{
$sp=($i>0)?"and":"";
$query_1=$query_1." ".$sp." ".$json_data_field." LIKE '%".$selectors[$i]."%'"; 
}

$prevAlertDateTime=getPreviousAlertTimeForAMachine($machine_name,$sensor_1);

echo "last alert time:".$prevAlertDateTime;
$pDate=explode(",",$prevAlertDateTime)[0];
$pTime=explode(",",$prevAlertDateTime)[1];


$query=$query_1. " and cast(concat(dt,' ',tm) as datetime) > cast('".$pDate." ".$pTime."' as datetime)  order by id DESC limit 2";
echo $query."</br>";
$res=mysqli_query($mysqli,$query) or die(mysqli_error($mysqli));
$prev_data=0;
$sens_data=-100000;





if($row=mysqli_fetch_array($res))
{
print_r($row);
$data_index=$data_index+1;
$json_data=$row['j_data'];
$json_obj=json_decode($json_data,true);

$sens_data=$json_obj[$data_key];





if($DATA_TYPE===$DATA_TYPE_INCREMENTAL)
{

$day_lag_date=date("Y-m-d H:i:s",strtotime("-1 day",strtotime($pDate." ".$pTime)));

$query=$query_1. " and cast(concat(dt,' ',tm) as datetime) = cast('$day_lag_date' as datetime)  order by id DESC limit 1";

$res=mysqli_query($mysqli,$query) or die(mysqli_error($mysqli));

$row1=mysqli_fetch_array($res);

$json_data=$row1['j_data'];
$json_obj=json_decode($json_data,true);

$prev_data=$json_obj[$data_key];
$sens_data=($prev_data>0)?max(($sens_data-$prev_data),0):0;
$prev_data=$sens_data;

}


$sens_data=map_sensor_value($sensor,$sens_data);

}

return $sens_data;

}

function map_sensor_value($sensor,$sensor_value)
{

$map_function_ranges=array("ph1"=>array(6.9,6.2,12.21,11.33,-2.8125),
                          "ph2"=>array(6.5,6.2,11.40,10.82,0.6035),
                          "temperature1"=>array(57.2,34.2,13.22,9.46,-23.64),
                          "temperature2"=>array(49.4,28.2,10.24,5.69,2.80),
                          "waterlevel1"=>array(2,1,2,1,0),
                          "waterlevel2"=>array(2,1,2,1,0));
                          
 if(!array_key_exists($sensor,$map_function_ranges))
 {
 
 $map_range=array(2,1,2,1,0);
 }
 else
 {                        
                          
 $map_range=$map_function_ranges[$sensor];

 }
 
 //print_r($map_range);
                          
$c_max= $map_range[0]; 

$c_min= $map_range[1];  

$s_max= $map_range[2]; 

$s_min= $map_range[3];

$offset= $map_range[4];                       
                          
$converted_value=(($c_max-$c_min)/($s_max-$s_min))*$sensor_value+$offset;
                          
return round($converted_value,3);


}




?>
