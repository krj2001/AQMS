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
         // "upload date time:".$upload_date_time." rt $data_retreival_timestamp mst $moving_window_start_p_timestamp mest $moving_window_end_p_timestamp</br>";
    }
    
   
    $data_retreival_timestamp=strtotime($upload_date_time);
    
    
    
  
    
if(($data_retreival_timestamp>=$moving_window_start_p_timestamp)&&($data_retreival_timestamp<$moving_window_end_p_timestamp))
    {
        
        $val=$json_data_obj[($parameteInfo->parameter_tag)];
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
        
        
        print_r($parametrer_data);
        
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
    
    if(mysqli_num_rows($res)>0)
    {
        
        $sql_query="update sampled_sensor_data_details set last_val='$last_val',max_val='$max_val',min_val='$min_val',avg_val='$avg_val',param_unit='$param_unit' where parameterName='".($parameterName)."' and sensor_id=".$sensor_id." and sample_date_time='$sample_date_time'";
        
    }
    else
    {
       $sql_query="insert into sampled_sensor_data_details(device_id,sensor_id,parameterName,last_val,max_val,min_val,avg_val,sample_date_time,param_unit) values($device_id,$sensor_id,'$parameterName','$last_val','$max_val','$min_val','$avg_val','$sample_date_time','$param_unit')";
        
    }
    
   // echo $sql_query."</br>";
    
    $res=mysqli_query($conn, $sql_query) or die(mysqli_error($conn));
    
    
}

include("includes/config.php");

$data_table="aqmi_json_data";

// $moving_window_width=15*60;//in seconds

$moving_window_width=120;//in seconds

$device_mode="4";


extractAQMSParameterData($mysqli,$data_table,$moving_window_width,$device_mode);


?>