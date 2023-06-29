<?php
include("includes/config.php");
ini_set("display_errors",1);
error_reporting(1);
ini_set('memory_limit', '1024M');
date_default_timezone_set('Asia/Kolkata');
include("aqms_aqi_utilities_newTest.php");

$sensorArray = array();
$parameterValueListForCurDevice=array();
$getLabAqmoDevices = "SELECT  devices.companyCode,devices.deviceName,devices.location_id,devices.branch_id,devices.facility_id,devices.building_id,devices.floor_id,devices.lab_id as lab_id,sensors.sensorTag,sensors.id, sensors.sensorNameUnit FROM customers 
                        INNER JOIN locations ON customers.customerId = locations.companyCode 
                        INNER JOIN branches ON customers.customerId = branches.companyCode AND locations.id = branches.location_id 
                        INNER JOIN facilities ON customers.customerId = facilities.companyCode AND locations.id = facilities.location_id AND branches.id = facilities.branch_id 
                        INNER JOIN buildings ON customers.customerId = buildings.companyCode AND  locations.id = buildings.location_id  AND branches.id = buildings.branch_id AND facilities.id = buildings.facility_id
                        INNER JOIN floors ON customers.customerId = floors.companyCode AND  locations.id = floors.location_id  AND  branches.id = floors.branch_id AND  facilities.id = floors.facility_id AND buildings.id = floors.building_id
                        INNER JOIN lab_departments ON customers.customerId = lab_departments.companyCode AND  locations.id = lab_departments.location_id  AND  branches.id = lab_departments.branch_id AND  facilities.id = lab_departments.facility_id AND buildings.id = lab_departments.building_id AND floors.id = lab_departments.floor_id 
                        INNER JOIN devices ON customers.customerId = devices.companyCode AND  locations.id = devices.location_id  AND  branches.id = devices.branch_id AND  facilities.id = devices.facility_id AND buildings.id = devices.building_id AND floors.id = devices.floor_id AND lab_departments.id = devices.lab_id
                        INNER JOIN sensors ON customers.customerId = sensors.companyCode AND  locations.id = sensors.location_id  AND  branches.id = sensors.branch_id AND  facilities.id = sensors.facility_id AND buildings.id = sensors.building_id AND floors.id = sensors.floor_id AND lab_departments.id = sensors.lab_id AND devices.id = sensors.deviceid
                        where devices.deviceCategory = 'AQMO'  and sensors.isAQI=1 and sensors.sensorNameUnit  IN ('PM10','pM2.5','no2','o3','co','nh3','so2','pb')";
                        
$getLabAqmoDevicesResult = mysqli_query($mysqli, $getLabAqmoDevices)  or die(mysqli_error($mysqli));
echo "AQMO device list : ";

echo "<br><br>";
$tot_rows=mysqli_num_rows($getLabAqmoDevicesResult);
echo "<br>$tot_rows<br>";
if($tot_rows>0){
    while($getSensorRow = mysqli_fetch_assoc($getLabAqmoDevicesResult)){
        print_r($getSensorRow['companyCode']);
        echo "<br>";
        $customerId = $getSensorRow['companyCode'];
        $location=$getSensorRow['location_id'];
        $branch=$getSensorRow['branch_id'];
        $facility=$getSensorRow['facility_id'];
        $building=$getSensorRow['building_id'];
        $floor=$getSensorRow['floor_id'];
        $lab=$getSensorRow['lab_id'];
        $deviceName = $getSensorRow['deviceName'];
        $sensorTag = $getSensorRow['sensorTag'];
        $sensor_id = $getSensorRow['id'];
        $sensorUnit = $getSensorRow['sensorNameUnit'];
        echo "Customer name:".$customerId.", Location:".$location.", Branch:".$branch." Facility:".$facility." Building:".$building." Floor:".$floor." Lab:".$lab." deviceName:".$deviceName."  sensorNameUnit:".$sensorUnit." sensor_id:".$sensor_id." sensorTag:".$sensorTag."<br><br>";
        $sensorArray[$lab][$sensorUnit][] = $sensor_id;
    }
    
    echo "Sensor list 46";
    echo "<br>";
    print_r($sensorArray);
    echo "<br>";
    echo "<br><br><br>";
    foreach($sensorArray as $key => $val){
        echo $key." Count of sensor category : ".count($val)."<br>";
        //count of aqmo sensors to be more than 2
        
        $labWithSensor= array();
        $calculate = false;
        if(count($val)>2){
            $calculate = true;
            foreach($val as $v => $value){
                foreach($value as $id){
                    echo"<br>";
                    echo"Line no 59";
                    echo $key." ".$v."  ".$id."<br>";
                    $labWithSensor[$key][]= $id;
                    
                    // Modified => alertType != 'outOfRange' by vaishak 31-03-2023
                    $sql="select * from sampled_sensor_data_details_MinMaxAvg where sensor_id='$id' and alertType != 'outOfRange' and parameterName='$v' AND time_stamp >= DATE_SUB(NOW(),INTERVAL 1 MINUTE)";
                    $ResultSet=mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli));
                    
                    if(mysqli_num_rows($ResultSet)>0) {
                        $rowDt=mysqli_fetch_array($ResultSet);
                        $parameterValueListForCurDevice[$key][$v][$rowDt["sensor_id"]]=$rowDt["avg_val"]; //storing value based on sensor parameter and sensortag
                        $dateTime=$rowDt["sample_date_time"];
                      
                    } else {
                        $parameterValueListForCurDevice[$key][$v][$id]=0;
                    }
                }
            }
        }else{
            echo "Dont calculate aqi, because paramter count is less than 3";
            echo "<br>";
        }
        // echo "<br>";
        // print_r($labWithSensor);
        // echo "<br>";
    }
    
     echo "value based on sensor parameter and sensortag - Line 112";
     
     print_r($parameterValueListForCurDevice);
     echo "<br>";
     echo "Line no 106";
     echo "<br>";
     echo $dateTime."<br>";
     foreach($parameterValueListForCurDevice as $key => $labsensorsval){
         
        $aqi_chart_standard="NPCB";
        $parameterCount = count($labsensorsval);
                
        echo "lab sensor list 124 <br>";
        print_r($labsensorsval);
        echo "<br>";
        
        // if($parameterCount>2){
        if($calculate == true){ // 11-3-2023
            echo "claculate aqi, because paramter is greater than 3";
            echo "<br>";
            echo "Lab Sensor Value list :";
            echo "<br>";
            print_r($labsensorsval);
            echo "<br>";
            
            if(array_key_exists("PM2.5", $labsensorsval) || array_key_exists("PM10", $labsensorsval))
            {
                echo "Found pm2.5";
                $DeviceSensorAQIList = computeAQi($mysqli,$aqi_chart_standard,$labsensorsval);
                echo "Aqi list begin 140:<br>";
                print_r($DeviceSensorAQIList);
                echo "Aqi list Ended 142:<br>";
                echo "Max AQI Sensor :<br>";
               // print_r(max($DeviceSensorAQIList));
                echo "<br>";
                
                // echo array_search(max($DeviceSensorAQIList), $DeviceSensorAQIList);
                
                $maxvalueSensorIndex = array_search(max($DeviceSensorAQIList), $DeviceSensorAQIList);
                
                echo $maxvalueSensorIndex;
                
                if($maxvalueSensorIndex){
                    echo "Inserting aqi for device"."<br>";
                    $maxAqi = max($DeviceSensorAQIList);
                    $dt=date("Y-m-d");
                    $tm=date("H:i:s");
                    $dateTime =$dt." ".$tm;
                        
                    $getLocationDetails = "select * from sensors where id = '$maxvalueSensorIndex' limit 1";
                    $getLocationDetailsResult = mysqli_query($mysqli,$getLocationDetails) or die(mysqli_error($mysqli));
                    if(mysqli_num_rows($getLocationDetailsResult)){
                        echo "Location details found";
                        $row = mysqli_fetch_assoc($getLocationDetailsResult);
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
                    
                        $sql="insert into Aqi_values_per_device(companyCode,locationId,branchId,facilityId,buildingId,floorId,labId,deviceId,AqiValue,sampled_date_time) values('$companyCode','$location','$branch','$facility','$building','$floor','$lab','$deviceId','$maxAqi','$dateTime')";
                        //echo "insert"."<br>";
                        $res=mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli));
                        if($res){
                            echo "Aqi for device  inserted";
                        }else{
                            echo "something went wrong while inserting aqi for device";
                        }
                    }
                }
                
                //copy this below code
                foreach($DeviceSensorAQIList as $id => $val){
                    echo "sensor_id:".$id." value".$val."<br>";
                    
                    $getLocationDetails = "select * from sensors where id = '$id' limit 1";
                    $getLocationDetailsResult = mysqli_query($mysqli,$getLocationDetails) or die(mysqli_error($mysqli));
                    if(mysqli_num_rows($getLocationDetailsResult)){
                        echo "Location details found";
                        $row = mysqli_fetch_assoc($getLocationDetailsResult);
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
                    
                        $insertSensorAqi = "insert into Aqi_values_per_deviceSensor(companyCode,locationId,branchId,facilityId,buildingId,floorId,labId,deviceId,sensorId,AqiValue,sampled_date_time) values('$companyCode','$location','$branch','$facility','$building','$floor','$lab','$deviceId','$id','$val','$dateTime')";
                        $insertSensorAqiResult = mysqli_query($mysqli,$insertSensorAqi);
                            
                        if($insertSensorAqiResult){
                            echo "Sensor aqi Inserted";  
                        }else{
                            echo "Something went wrong, while inserting sensor Aqi";
                        }
                    }
                }
                
            }else{
                echo "Dont calculate AQI, because no paramter with pm10 or pm2.5";
            }
            
        }else{
            echo "Dont calculate aqi, because paramter count is less than 3";
            echo "<br>";
        }
    }
}else{
    echo "No data found";
}


?>