<?php
$dbuser="idealab_Aqms";
$dbpass="Aq1sw2de3fr4@aqms";
$host="localhost";
$db="idealab_database";
$con=new mysqli($host,$dbuser,$dbpass,$db);

date_default_timezone_set('Asia/Kolkata');
$time_in_24_hour_format_currentTime = date('Y-m-d H:i:s');//current datetime

$sql="SELECT distinct id, deviceName, deviceMode FROM devices";

if ($result=mysqli_query($con,$sql))
{
    // Return the number of rows in result set
    $rowcount=mysqli_num_rows($result);
    if($rowcount > 0){
        // echo $rowcount;
        while($row = mysqli_fetch_assoc($result)){
            $deviceId = $row['id'];
            $deviceName = $row['deviceName'];
            
            // echo "ID:".$deviceId." Device name ".$deviceName."<br>";
            
            //$getDeviceCountSql = "select current_date_time FROM `segregatedValues`  WHERE  device_id='$deviceId' Limit 1";
            $getDeviceCountSql = "select * FROM `sampled_sensor_data_details_MinMaxAvg` WHERE time_stamp >= DATE_SUB(NOW(),INTERVAL 60 SECOND) AND device_id = '$deviceId'";
            $getDeviceResult = mysqli_query($con,$getDeviceCountSql) or die(mysqli_error($con));
            
            $rowDataCount =mysqli_num_rows($getDeviceResult);
            if($rowDataCount>0){
                echo "ID:".$deviceId." Device name ".$deviceName." Connected <br>";
                
            }else{
                // echo "ID:".$deviceId." Device name ".$deviceName."  disConnected and not found in Sample segregated table <br>";
                checkBumpTestLastData($con,$deviceId,$deviceName);
            }
        }   
    }else{
            echo "No data";
    }
}

function updateDeviceStatus($con,$deviceId){
    echo "disconnect update device $deviceId <br>";
    
    //$currentDateTime = date('Y-m-d H:i:s');
    
    $updateDevicestatusSql = "Update devices set disconnectedStatus = 1 where id = $deviceId";
    $updateDeviceStatusResult = mysqli_query($con,$updateDevicestatusSql) or die(mysqli_error($con));
    if($updateDeviceStatusResult){
        echo "device disconnect status updated <br>";
    }  
}

   

function checkBumpTestLastData($con,$deviceId,$deviceName){
    
    $getDeviceCountSql = "select * FROM `segregatedBumptestValues` WHERE timeStamps >= DATE_SUB(NOW(),INTERVAL 60 SECOND) AND device_id = '$deviceId'";
    $getDeviceResult = mysqli_query($con,$getDeviceCountSql) or die(mysqli_error($con));
    
    $rowDataCount =mysqli_num_rows($getDeviceResult);
    if($rowDataCount>0){
        echo "ID:".$deviceId." Device name ".$deviceName." Connected and found in bumptest <br>";
        
    }else{
        // echo "ID:".$deviceId." Device name ".$deviceName."  disConnected and not found in bumptest <br>";
        //updateDeviceStatus($con,$deviceId);
         checkDebugModeData($con,$deviceId,$deviceName);
    }
    
    /*
    
    $getDeviceCountSql = "select current_date_time FROM `segregatedBumptestValues`  WHERE  device_id='$deviceId' Limit 1";
    $getDeviceResult = mysqli_query($con,$getDeviceCountSql) or die(mysqli_error($con));
    
    $rowDataCount =mysqli_num_rows($getDeviceResult);
    if($rowDataCount>0){
        while($row = mysqli_fetch_assoc($getDeviceResult)){
            //echo "ID:".$deviceId." Device name ".$deviceName."dateTime ".$row['current_date_time']."  Connected <br>";  
            
            $currentDateTime = $time_in_24_hour_format_currentTime;
            $lastDateTime = $row['current_date_time'];
            
            $d1 = strtotime($currentDateTime);
            $d2 = strtotime($lastDateTime);
            $totalSecondsDiff = abs($d1-$d2);
            
            //$totalMinutesDiff = round($totalSecondsDiff/60); 
            //echo $totalMinutesDiff;
            
            if($totalSecondsDiff > 3000){
                //disconnect the device
                echo "ID:".$deviceId." Device name ".$deviceName."dateTime ".$row['current_date_time']."  DisConnected and not found in bumptest segregated table <br>"; 
                updateDeviceStatus($con,$deviceId); //check whether device is connected in bumptest..
            }else{
                //connect the device
                echo "ID:".$deviceId." Device name ".$deviceName."dateTime ".$row['current_date_time']." Dont DisConnected <br>"; 
                
            }
        }
    }else{
        updateDeviceStatus($con,$deviceId);
    } */
    
}


function checkDebugModeData($con,$deviceId,$deviceName){
    $getDeviceCountSql = "select * FROM `deviceDebug` WHERE time_stamp >= DATE_SUB(NOW(),INTERVAL 60 SECOND) AND deviceId = '$deviceId'";
    $getDeviceResult = mysqli_query($con,$getDeviceCountSql) or die(mysqli_error($con));
    
    $rowDataCount =mysqli_num_rows($getDeviceResult);
    if($rowDataCount>0){
        echo "ID:".$deviceId." Device name ".$deviceName." Connected and found in debug Mode <br>";
        
    }else{
        // echo "ID:".$deviceId." Device name ".$deviceName."  disConnected and not found in debug Mode <br>";
        // updateDeviceStatus($con,$deviceId);
        updateDeviceStatusLatest($con);
    } 
}
 
    // MODIFIED BY VAISHAK 17-03-2023
    function updateDeviceStatusLatest($con)
    {
        $deviceSql = "SELECT * FROM `devices` where disconnectedStatus = 0";
        $device = mysqli_query($con,$deviceSql) or die(mysqli_error($con));
        
        $rowDataCount =mysqli_num_rows($device);
        
        if($rowDataCount>0){
            
            while($row = mysqli_fetch_assoc($device)){
                $deviceId[] = $row['id'];
            } 
        }
        echo "Active devices :";
        print_r($deviceId);
        echo "<br><br>";
        
        //last one minutes deviceID
        $lastDeviceIdSql = "SELECT distinct device_id FROM `sampled_sensor_data_details_MinMaxAvg` WHERE time_stamp >= DATE_SUB(NOW(),INTERVAL 60 SECOND)";
        $lastDevice = mysqli_query($con,$lastDeviceIdSql) or die(mysqli_error($con));
        
        $rowDataCount1 =mysqli_num_rows($lastDevice);
        
        $lastDeviceId = array();
        if($rowDataCount1>0){
            
            while($row1 = mysqli_fetch_assoc($lastDevice)){
                $lastDeviceId[] = $row1['device_id'];
            } 
        }
        echo "sampled_sensor_data_details_MinMaxAvg";
        print_r($lastDeviceId);
        echo "<br><br>";
        
        $differentValues = array_diff($deviceId, $lastDeviceId);
        
        if($differentValues){
            echo "differentValues found";
            print_r($differentValues);
            echo "<br><br>";
            
            $sql = "SELECT CURRENT_TIMESTAMP"; // to get current date & time of sql
            $result = mysqli_query($con,$sql) or die(mysqli_error($con));
            $row = $result->fetch_assoc();
            $datetime = $row['CURRENT_TIMESTAMP'];
              
            for($i=0; $i<count($differentValues); $i++)
            {
                $updateDeviceStatusSql = "UPDATE devices SET disconnectedStatus = 1, disconnectedTime = '$datetime' WHERE id = $differentValues[$i]";
                $updateDeviceStatusResult = mysqli_query($con,$updateDeviceStatusSql) or die(mysqli_error($con));
                
                if($updateDeviceStatusResult){
                    echo "device disconnect status updated for device .$differentValues[$i]<br>";
                    // print_r($differentValues[$i]);
                }
            }
        }else{
            echo "No differentValues found";
        } 
    }

?>