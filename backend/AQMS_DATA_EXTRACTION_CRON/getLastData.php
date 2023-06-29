<?php
ini_set('memory_limit', '1024M');
$dbuser="idealab_Aqms";
$dbpass="Aq1sw2de3fr4@aqms";
$host="localhost";
$db="idealab_database";
$con=new mysqli($host,$dbuser,$dbpass,$db);
date_default_timezone_set('Asia/Kolkata');

$back_interval_in_minutes=120;
$grouping_interval_in_minutes=1;
// $date_from=date("Y-m-d H:i:s",strtotime($cur_date_time)-$backInterval_min*60);
$sql="SELECT sample_date_time as DATE_TIME,sensor_id,parameterName as parameter,FLOOR(UNIX_TIMESTAMP(time_stamp)/(".$grouping_interval_in_minutes." * 60)) AS timekey,MAX(max_val) as par_max,MIN(min_val) as par_min,AVG(avg_val)  as par_avg,last_val as par_last FROM sampled_sensor_data_details_MinMaxAvg WHERE sensor_id='46' and time_stamp >=(NOW() - INTERVAL (".$back_interval_in_minutes.") MINUTE) GROUP BY timekey";

$res=mysqli_query($con,$sql) or die(mysqli_error($con));
$row_1=array();
while ($row=mysqli_fetch_assoc($res))
{
    $row_1[]=$row;
}
print_r($row_1);


?>