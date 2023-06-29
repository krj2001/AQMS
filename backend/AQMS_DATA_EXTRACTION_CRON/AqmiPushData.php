<?php
include("includes/config.php");
$data = trim(file_get_contents("php://input"));//object data of hardware directly inserted
date_default_timezone_set('Asia/Kolkata');
$decodedData = json_decode($data);

$ID = $decodedData->DEVICE_ID;
$CONFIG = $decodedData->CONFIG;
$channel = $decodedData->CH;
$status = $decodedData->STATUS;
$macAddressReq = $decodedData->MAC;
$mode = $decodedData->MODE;
$tagid = $decodedData->TAG;

if($ID != ""){
   //get the device mode 
   
      $getDevice = "select * from devices where id ='$ID'";
      $getResult = mysqli_query($mysqli,$getDevice) or die(mysqli_error($mysqli));
      if(mysqli_num_rows($getResult) > 0){
          
            $updateDeviceStatus = "update devices set disconnectedStatus = 0 where id ='$ID'";
            $updateResult = mysqli_query($mysqli,$updateDeviceStatus) or die(mysqli_error($mysqli)); //updating disconnected to 0 which means device is connected
                
            $result = mysqli_fetch_assoc($getResult);
            $deviceMode = $result['deviceMode'];  //check for device mode when pushing data from device, devicemode will be set in application
            $modeChangedTime = $result['modeChangedDateTime'];
             
            //if enabled push to aqmin_json_table
            if($deviceMode == "enabled" && $mode == "2"){
                $dt=date("Y-m-d");
                $tm=date("H:i:s");
                $dateTime =$dt." ".$tm;
                //get date time 
                
                //after enable mode or bumptest mode selected for pushing data wait for 1 min to send the data for residusal gas (NR)
                
                $start = strtotime($modeChangedTime); 
                $end = strtotime($dateTime);
                $mins = ($end - $start) / 60;
                $min = intVal($mins);
               
                if($min>=1){
                    $sql = "INSERT INTO `aqmi_json_data`(`date_time`,`j_data`)VALUES('$dateTime','$data')";
                    $result=mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli));
                    if($result){
                        // $labHooterSql = "SELECT l.labHooterStatus FROM lab_departments l INNER JOIN devices d ON l.id = d.lab_id where d.id = $ID";
                        // $result=mysqli_query($mysqli,$labHooterSql) or die(mysqli_error($mysqli));
                        // if(mysqli_num_rows($result)>0){
                        //       $res = mysqli_fetch_Assoc($result);
                        //       $status = $res["labHooterStatus"];
                        //       if($status == 1){
                        //           echo $ID."-AT-RLON";
                        //       }else{
                        //           echo $ID."-AT-RLOFF";   
                        //       }
                        // }else{
                        //     echo "success";    
                        // }
                        echo "Success";
                }else{
                    echo "Kanwal";
                }
                }else{
                    echo "Kanwals";
                }
            }
            
            else if($deviceMode == "debug"  && $mode == "5"){
                $dt=date("Y-m-d");
                $tm=date("H:i:s");
                $dateTime =$dt." ".$tm;
                    
                $sql = "INSERT INTO `aqmi_json_data`(`date_time`,`j_data`)VALUES('$dateTime','$data')";
                $result=mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli));
                if($result){
                    echo "success";
                }else{
                    echo "Kanwal";
                }
            }
            
            //if enabled push to bumpTest_aqmi_json_data
            else if($deviceMode == "bumpTest" && $mode == "6"){
                $dt=date("Y-m-d");
                $tm=date("H:i:s");
                $dateTime =$dt." ".$tm;
                
                $start = strtotime($modeChangedTime); 
                $end = strtotime($dateTime);
                $mins = ($end - $start) / 60;
                $min = intVal($mins);
                
                //after enable mode or bumptest mode selected for pushing data wait for 1 min to send the data for residusal gas (NR)
                if($min>=1){
                    $sql = "INSERT INTO `bumpTest_aqmi_json_data`(`date_time`,`j_data`)VALUES('$dateTime','$data')";
                    $result=mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli));
                    if($result){
                        echo "success";
                    }else{
                        echo "Kanwal";
                    }
                }else{
                    echo "Kanwal";
                }
            }
            
            //firmwareUpgradation or config 26/11/22 extra added
            else if($deviceMode == "firmwareUpgradation" || $deviceMode == "config"){
                
                $updateDeviceConfigurationStatus = "update devices set configurationProcessStatus = 1 where id ='$ID'";
                $updateResult = mysqli_query($mysqli,$updateDeviceConfigurationStatus) or die(mysqli_error($mysqli)); //updating disconnected to 0 which means device is connected
                if($updateResult){
                    echo $ID."-AT-MODE";    
                }else{
                    echo "Kanwal";
                }
            }
            
            else{
                echo $ID."-AT-MODE";
            }
      }else{
          echo "No Data Found";
      }
   
}else{
   echo "Please send body Data";
}

?>