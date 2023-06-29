<?php


ini_set("display_errors",1);
error_reporting(1);
ini_set('memory_limit', '1024M');

include("aqms_aqi_utilities_new.php");

date_default_timezone_set('Asia/Kolkata');

/**** Mail Class Begin ****/

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require'vendor/autoload.php';

$sendingMailInfo = array();

//extracting data from aqmi_json_table basedd on sensortag

function computeAQMOAQI($conn)
{
    //get only outdor aqmo devices
    $deviceListSql="SELECT * FROM devices where deviceMode = 'enabled' and deviceCategory='AQMO'";

    $deviceListResult = mysqli_query($conn,$deviceListSql)or die(mysqli_error($conn));
    if(mysqli_num_rows($deviceListResult) > 0){
        while($deviceRow = mysqli_fetch_assoc($deviceListResult)){
            $sensorListSql = "select * from sensors where deviceId = ".$deviceRow['id']." order by id desc";
            if($deviceRow['id']==="112")
            {
                //echo $sensorListSql;
            }
            $sensorListResult=mysqli_query($conn,$sensorListSql) or die(mysqli_error($conn));
            $parameterValueListForCurDevice=array();
            if(mysqli_num_rows($sensorListResult) > 0){
                while($row=mysqli_fetch_array($sensorListResult))
                {
                    $sensor_id=$row["id"];
                    $companyCode = $row['companyCode'];
                    $deviceId = $row['deviceId'];
                    $sensorId = $row["id"];
                    $sensorTag = $row['sensorTag'];
                    $hooterRelayStatus = $row['hooterRelayStatus'];
                    $parameterName=$row['sensorNameUnit'];
                    
                    $location=$row['location_id'];
                    $branch=$row['branch_id'];
                    $facility=$row['facility_id'];
                    $building=$row['building_id'];
                    $floor=$row['floor_id'];
                    $lab=$row['lab_id'];
                    $dateTime = "";
                    $sql="select * from sampled_sensor_data_details_MinMaxAvg where device_id='$deviceId' and parameterName='$parameterName' AND time_stamp >= DATE_SUB(NOW(),INTERVAL 1 MINUTE)";
                    $ResultSet=mysqli_query($conn,$sql) or die(mysqli_error($conn));
                    if(mysqli_num_rows($ResultSet)>0)
                    {
                        $rowDt=mysqli_fetch_array($ResultSet);
                        $parameterValueListForCurDevice[$parameterName][$rowDt["sensor_id"]]=$rowDt["avg_val"]; //storing value based on sensor parameter and sensortag
                        $dateTime=$rowDt["sample_date_time"];
                    }
                }
                
                //AQI to be calculated on if device contains pm2.5 pr pm10  
                //echo "date       ".$dateTime."  date";
                $aqi_chart_standard="NPCB";
                $parameterCount = count($parameterValueListForCurDevice);
                
                echo "Count:  ".$parameterCount."<br>";
                print_r($parameterValueListForCurDevice);
                echo "<br>";
                
                if($parameterCount>=3){
                    //echo "Found";
                    if(array_key_exists("PM2.5", $parameterValueListForCurDevice) || array_key_exists("PM10", $parameterValueListForCurDevice))
                    {
                        
                        // echo "<br>";
                        // echo "device Contains pm2.5 and pm10, so aqi can be calculated";
                        
                        $DeviceSensorAQIList = computeAQi($conn,$aqi_chart_standard,$parameterValueListForCurDevice);
                        
                        print_r($DeviceSensorAQIList);
                        print_r(max($DeviceSensorAQIList));
                        echo "<br>";
                        
                        
                        
                        // $aqiValue=computeAQi($conn,$aqi_chart_standard,$parameterValueListForCurDevice);
                        // echo "aqi=".$aqiValue."</br>";
                        
                        
                        $sql="select * from Aqi_values_per_device where companyCode='$companyCode' and locationId=$location and branchId=$branch and facilityId=$facility and buildingId=$building and floorId=$floor and labId=$lab and deviceId=$deviceId and sampled_date_time='$dateTime'";
                        echo $sql;
                        $resSelAqi=mysqli_query($conn,$sql) or die(mysqli_error($conn));
                         
                        if(mysqli_num_rows($resSelAqi)>0)
                        {
                            print_r($DeviceSensorAQIList);
                            $maxAqi = max($DeviceSensorAQIList);
                            $sql="update Aqi_values_per_device set AqiValue='$maxAqi' where companyCode='$companyCode' and locationId='$location' and branchId='$branch' and facilityId=$facility and buildingId='$building' and floorId='$floor' and labId='$lab' and deviceId='$deviceId' and sampled_date_time='$dateTime'";
                            // echo "update"."<br>";
                            //$res=mysqli_query($conn,$sql) or die(mysqli_error($conn));
                            
                            if($res){
                                foreach($DeviceSensorAQIList as $id => $val){
                                    echo "sensor_id:".$id." value".$val."<br>";
                                    
                                    $updateSensorAqi="update Aqi_values_per_deviceSensor set AqiValue='$val' where companyCode='$companyCode' and locationId='$location' and branchId='$branch' and facilityId=$facility and buildingId='$building' and floorId='$floor' and labId='$lab' and deviceId='$deviceId' and sensorId = '$id' and sampled_date_time='$dateTime'";
                                    $updateSensorAqiResult = mysqli_query($conn,$updateSensorAqi) or die(mysqli_error($conn));
                                    if($updateSensorAqiResult){
                                        echo "Sensor aqi Updated";  
                                    }else{
                                        echo "Somthing went wrong, Sensor aqi not Updated";  
                                    }
                                }
                            }else{
                                echo "Something went wrong updating device aqi";
                            }
                        }
                        else
                        {
                            $maxAqi = max($DeviceSensorAQIList);
                            $dt=date("Y-m-d");
                            $tm=date("H:i:s");
                            $dateTime =$dt." ".$tm;
                            $sql="insert into Aqi_values_per_device(companyCode,locationId,branchId,facilityId,buildingId,floorId,labId,deviceId,AqiValue,sampled_date_time) values('$companyCode','$location','$branch','$facility','$building','$floor','$lab','$deviceId','$maxAqi','$dateTime')";
                            echo "insert"."<br>";
                            //$res=mysqli_query($conn,$sql) or die(mysqli_error($conn));
                            
                            if($res){
                                foreach($DeviceSensorAQIList as $id => $val){
                                    echo "sensor_id:".$id." value".$val."<br>";
                                    
                                    $insertSensorAqi = "insert into Aqi_values_per_deviceSensor(companyCode,locationId,branchId,facilityId,buildingId,floorId,labId,deviceId,sensorId,AqiValue,sampled_date_time) values('$companyCode','$location','$branch','$facility','$building','$floor','$lab','$deviceId','$id','$val','$dateTime')";
                                    //$insertSensorAqiResult = mysqli_query($conn,$insertSensorAqi) or die(mysqli_error($conn));;
                                        
                                    if($insertSensorAqiResult){
                                        echo "Sensor aqi Inserted";  
                                    }else{
                                        echo "Something went wrong, while inserting sensor Aqi";
                                    }
                                    
                                }
                            }else{
                                echo "Something went wrong inserting device aqi";
                            }
                        }
                    }
                    else
                    {
                        // print_r($parameterValueListForCurDevice);
                        echo "Key not Found";
                    }
                }else{
                    echo "Count is less";
                }
                
                
                // echo "<br>";
            }
            else{
                //echo "No sensors for ".$deviceRow['deviceName']."<br>"; 
            }
        }
    }else{
        //echo "No devices"."<br>";
    }
}









include("includes/config.php");

date_default_timezone_set('Asia/Kolkata');

$data_table="aqmi_json_data";

//$moving_window_width=15*60;//in seconds

$moving_window_width=120;//in seconds

$device_mode="2";



computeAQMOAQI($mysqli);

//delete data if there is more than 5000 data in aqmijson
ini_set('memory_limit', '1024M');




?>