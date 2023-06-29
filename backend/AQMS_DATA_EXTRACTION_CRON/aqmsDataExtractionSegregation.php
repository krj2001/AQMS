<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
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
    $time_in_24_hour_format_currentTime = date('Y-m-d H:i:s');//currentdatetime
 
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
    //$selectors=array($Company_sel,$loc_sel,$branch_sel,$facility_sel,$building_sel,$floor_sel,$lab_sel,$device_id_sel,$mode_sel);
    $query_filter="";
    foreach($selectors as $sel)
    {
        $query_filter=$query_filter."j_data LIKE '%".$sel."%' and ";   
    }
    
    $query_filter=$query_filter."j_data LIKE '%".$param_sel."%'";
        
    //$sql="select * from ".$data_table." where ".$query_filter. "order by id desc Limit 1";  
     $sql="select * from ".$data_table." WHERE  date_time  >= DATE_SUB('".$time_in_24_hour_format_currentTime."',INTERVAL 1 MINUTE)";
    
     //$sql="select DISTINCT date_time, j_data from ".$data_table." WHERE  date_time  >= DATE_SUB('".$time_in_24_hour_format_currentTime."',INTERVAL 1 MINUTE)";
    // echo $sql."</br>";
    
    $res=mysqli_query($conn,$sql) or die(mysqli_error($conn));
    //echo "num rows:".mysqli_num_rows($res);
    
    $insertDataArray = array();
    $date = "";
    $time = "";
    
    $MODE = "";
    
    $RSSI = "";
    
    while($row=mysqli_fetch_array($res))
    {
        $insertDataArray = []; 
        $id = $row['id'];
        $json_data=$row['j_data'];
        $json_data_obj=json_decode($json_data,true);
        $upload_date=$json_data_obj["DATE"];
        $upload_time=$json_data_obj["TIME"];
        $upload_date_time=$upload_date." ".$upload_time;
        $data_retreival_timestamp=strtotime($upload_date_time);
        $insertDataArray['CURRENTDATETIME'] = $current_sample_date_time;
        
        //Method 3 getting data of last one minute based current time 
        
        foreach($json_data_obj as $key => $value) 
        {
            //echo $json_data_obj;
            //{"DATE":"2022-09-02","TIME":"17:41:54","COMPANY":"A-TEST","LOCATION":"4","BRANCH":"3","FACILITY":"4","BULDING":"2","FLOOR":"2","LAB":"3","DEVICE_ID":"3","MODE":"2","ACCESS_CODE":"1003","pb_gas01":"0.5","NH3_gas1":"200","SO2_gas1":"40","O3_gas1":"50","pm2.5_gas1":"100","PM10_GAS2":"50","NO2_gas2":"40"}
            if($key !== "COMPANY" && $key !== "LOCATION" && $key !== "BRANCH" && $key !== "FACILITY" && $key !== "BULDING" && $key !== "FLOOR" && $key !== "LAB"){
                    
                if($key == "DATE"){
                    $insertDataArray[$key] = $value; 
                    $date = $value;
                }
                
                if($key == "TIME"){
                    $insertDataArray[$key] = $value;      
                    $time = $value;
                }
                
                if($key == "DEVICE_ID"){
                    $insertDataArray[$key] = $value;   
                    $deviceId = $value;
                }
                
                if($key == "ACCESS_CODE"){
                    $insertDataArray[$key] = $value;   
                    $accessCode = $value;
                }
                
                if($key == "MODE"){
                    $insertDataArray[$key] = $value;   
                    $MODE = $value;
                }
                
                if($key == "SD_CARD"){
                    $insertDataArray[$key] = $value;   
                    $SDCARD = $value;
                    echo "sdcardAbhishek".$SDCARD;
                }
                
                if($key == "RSSI"){
                    $insertDataArray[$key] = $value;   
                    $RSSI = $value;
                }
                
                
                if($MODE == 5){
                            echo "Sdcard ".$SDCARD." RSSI ".$RSSI;
                    $getLastOneMinuteData = "SELECT * FROM `deviceDebug` where time_stamp >= DATE_SUB(NOW(),INTERVAL 15 MINUTE)";
                    $getLastOneMinuteResult = mysqli_query($conn,$getLastOneMinuteData);
                    $getRowCount = mysqli_num_rows($getLastOneMinuteResult);
                    if($getRowCount>0){
                        
                    }else{
                        $debugValuesSql = "INSERT INTO `deviceDebug`(`deviceId`, `deviceMode`, `accessCode`, `sdCard`, `RSSI`, `RTC`,`current_date_time`) VALUES
                                                                    ('$deviceId','$MODE','$accessCode','1','$RSSI','$upload_date_time','$time_in_24_hour_format_currentTime')";
                        echo $debugValuesSql;
                        $debugValuesResult = mysqli_query($conn,$debugValuesSql) or die(mysqli_error($conn));
                        
                    }
                    
                    //updating connected status of device
                    $updateDevicestatusSql = "Update devices set disconnectedStatus = 0  where id = $deviceId";
                    $updateDeviceStatusResult = mysqli_query($conn,$updateDevicestatusSql) or die(mysqli_error($conn));
                    if($updateDeviceStatusResult){
                        echo "device disconnect status updated <br>";
                    }else{
                        
                    }
                }
                
                if($key !== "DATE" && $key !== "DEVICE_ID" && $key !== "MODE" && $key !== "TIME" && $key !== "ACCESS_CODE"){
                    
                    
                    // echo 'Key name: '.$key.' key Value:'.$value;  
                    $insertDataArray['sensors'][$key] = $value;   
                    $sensorTagName = $key;
                    $rawValue = $value;
                    $uploadDateTime = $date." ".$time;
                    //scaledvalue need to pass data while writing a function
                    
                    if($rawValue != ""){
                        // echo 'deviceId: '.$deviceId.' Mode:'.$MODE."<br>";  
                        if($MODE == 2){
                            //echo "Enabled mode"."<br>";
                            $scaledValue= scalingValue($conn,$deviceId,$sensorTagName,$rawValue);
                            
                            $fetchSegregatedDataCountSql = "select * from segregatedValues";
                            $fetchSegregatedDataCountResult = mysqli_query($conn,$fetchSegregatedDataCountSql) or die(mysqli_error($conn));
                            $rowCount = mysqli_num_rows($fetchSegregatedDataCountResult);
                            
                            if($rowCount > 10000){
                                
                                
                            }else{
                                $segregatedValuesSql = "INSERT INTO `segregatedValues`(`device_id`, `sensorTag`, `deviceMode`, `accessCode`, `val`,`scaledVal`,`current_date_time`, `upload_date_time`)
                                            VALUES ('$deviceId','$sensorTagName','$MODE','$accessCode','$rawValue','$scaledValue','$time_in_24_hour_format_currentTime','$uploadDateTime')";
                                //echo $segregatedValuesSql."</br>";              
                                
                                $segregatedValuesResult = mysqli_query($conn,$segregatedValuesSql) or die(mysqli_error($conn));
                                if($segregatedValuesResult){
                                    echo "Inserted Record ".$sensorTagName."<br>";
                                }else{
                                    echo "Record Not Inserted".$sensorTagName."<br>";
                                }
                            }
                        }
                        
                        if($MODE == 3){
                            echo "Disable mode";
                        }
                        
                        // if($MODE == 5){
                        //     echo "Sdcard ".$SDCARD." RSSI ".$RSSI;
                        //     $getLastOneMinuteData = "SELECT * FROM `deviceDebug` where time_stamp >= DATE_SUB(NOW(),INTERVAL 15 MINUTE)";
                        //     $getLastOneMinuteResult = mysqli_query($conn,$getLastOneMinuteData);
                        //     $getRowCount = mysqli_num_rows($getLastOneMinuteResult);
                        //     if($getRowCount>0){
                                
                        //     }else{
                        //         $debugValuesSql = "INSERT INTO `deviceDebug`(`deviceId`, `deviceMode`, `accessCode`, `sdCard`, `RSSI`, `RTC`,`current_date_time`) VALUES
                        //                                                     ('$deviceId','$MODE','$accessCode','1','$RSSI','$upload_date_time','$time_in_24_hour_format_currentTime')";
                        //         echo $debugValuesSql;
                        //         $debugValuesResult = mysqli_query($conn,$debugValuesSql) or die(mysqli_error($conn));
                                
                        //     }
                            
                        //     //updating connected status of device
                        //     $updateDevicestatusSql = "Update devices set disconnectedStatus = 0  where id = $deviceId";
                        //     $updateDeviceStatusResult = mysqli_query($conn,$updateDevicestatusSql) or die(mysqli_error($conn));
                        //     if($updateDeviceStatusResult){
                        //         echo "device disconnect status updated <br>";
                        //     }else{
                                
                        //     }
                        // }
                       
                    }
                }
            }
        }
        
        //Method 2 implemented by abhishek 05-09-2022
        
        // if(($data_retreival_timestamp>=$moving_window_start_p_timestamp)&&($data_retreival_timestamp<$moving_window_end_p_timestamp))
        // {
        //     $deviceId = "";
        //     $accessCode = "";
        //     $rawValue = "";
        //     $scaledValue = "";
        //     $mode = "";
        //     $uploadDateTime = $date." ".$time;
        //     foreach($json_data_obj as $key => $value) 
        //     {
        //         //{"DATE":"2022-09-02","TIME":"17:41:54","COMPANY":"A-TEST","LOCATION":"4","BRANCH":"3","FACILITY":"4","BULDING":"2","FLOOR":"2","LAB":"3","DEVICE_ID":"3","MODE":"2","ACCESS_CODE":"1003","pb_gas01":"0.5","NH3_gas1":"200","SO2_gas1":"40","O3_gas1":"50","pm2.5_gas1":"100","PM10_GAS2":"50","NO2_gas2":"40"}
        //         if($key !== "COMPANY" && $key !== "LOCATION" && $key !== "BRANCH" && $key !== "FACILITY" && $key !== "BULDING" && $key !== "FLOOR" && $key !== "LAB"){
                        
        //             if($key == "DATE"){
        //                 $insertDataArray[$key] = $value; 
        //                 $date = $value;
        //             }
                    
        //             if($key == "TIME"){
        //                 $insertDataArray[$key] = $value;      
        //                 $time = $value;
        //             }
                    
        //             if($key == "DEVICE_ID"){
        //                 $insertDataArray[$key] = $value;   
        //                 $deviceId = $value;
        //             }
                    
        //             if($key == "ACCESS_CODE"){
        //                 $insertDataArray[$key] = $value;   
        //                 $accessCode = $value;
        //             }
                    
        //             if($key == "MODE"){
        //                 $insertDataArray[$key] = $value;   
        //                 $mode = $value;
        //             }
                    
        //             if($key !== "DATE" && $key !== "DEVICE_ID" && $key !== "MODE" && $key !== "TIME" && $key !== "ACCESS_CODE"){
        //                 //  echo 'Key name: '.$key.' key Value:'.$value;  
        //                 $insertDataArray['sensors'][$key] = $value;   
        //                 $sensorTagName = $key;
        //                 $rawValue = $value;
                        
        //                 //scaledvalue need to pass data while writing a function
                            
        //                     $scaledValue= scalingValue($conn,$deviceId,$sensorTagName,$rawValue);
                            
        //                     $segregatedValuesSql = "INSERT INTO `segregatedValues`(`device_id`, `sensorTag`, `deviceMode`, `accessCode`, `val`,`scaledVal`,`current_date_time`, `upload_date_time`)
        //                                     VALUES ('$deviceId','$sensorTagName','$mode','$accessCode','$rawValue','$scaledValue','$time_in_24_hour_format_currentTime','$uploadDateTime')";
                                            
        //                     $segregatedValuesResult = mysqli_query($conn,$segregatedValuesSql) or die(mysqli_error($conn));
        //                     if($segregatedValuesResult){
        //                         echo "Inserted Record";
        //                     }else{
        //                         echo "Record Not Inserted";
        //                     } 
        //             }
        //         }
        //     }
        // }else{
        //     echo "Data not found";
        // }
        
        // print_r($insertDataArray);
        
        /*
            //method 1 implemented first by santhos sir 
            
            if($parameteInfo->parameter_tag==="PM10_GAS2")
            {
                // "upload date time:".$upload_date_time." rt $data_retreival_timestamp mst $moving_window_start_p_timestamp mest $moving_window_end_p_timestamp</br>";
            }
            
            $data_retreival_timestamp=strtotime($upload_date_time);
        
            if(($data_retreival_timestamp>=$moving_window_start_p_timestamp)&&($data_retreival_timestamp<$moving_window_end_p_timestamp))
            {
            
                $vals=$json_data_obj[($parameteInfo->parameter_tag)];
                $value = floatval($vals);
                
                //scalling the parameter values
                $val = scalingValue($conn,$parameteInfo->company,$parameteInfo->device_id,$parameteInfo->parameter_tag,$value);
                $max=($val>$max)?$val:$max;
                $min=($val<$min)?$val:$min;
                $par_val_sum=$par_val_sum+$val;
                $data_count=$data_count+1;
                $data_available="1";
                //echo $sql."</br>";
            }
        */
    }
    
    /* 
        //method 1 continuetion code 
    
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
        $ret = [
            'data' => $ret_data_array,
        ];
    
    */
    //return json_encode($ret);
}

function scalingValue($conn,$deviceId,$parameterTag,$val){

    $sql = "SELECT sensor_units.*, sensors.* FROM `sensor_units` INNER JOIN sensors ON sensor_units.id = sensors.sensorName WHERE sensors.sensorTag = '$parameterTag' and sensors.deviceId = '$deviceId'";
    echo $sql;
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
    
    echo "minRatedReading: ".$minRatedReading."<br>";
    echo "maxRatedReading: ".$maxRatedReading."<br>";
    echo "minRatedReadingScale".$minRatedReadingScale."<br>";
    echo "maxRatedReadingScale".$maxRatedReadingScale."<br>";
    echo "value:".$val;
    
    //scaled for only 4-20v output type
    if($outputType === "4-20v"){
        $scaledValue = (($maxRatedReadingScale-$minRatedReadingScale)/($maxRatedReading-$minRatedReading)) * ($val - $minRatedReading) + $minRatedReadingScale;    
        
    }else if($outputType === "0-10v"){
        $scaledValue = (($maxRatedReadingScale-$minRatedReadingScale)/($maxRatedReading-$minRatedReading)) * ($val - $minRatedReading) + $minRatedReadingScale;   
    }
    else{
        $scaledValue = $val;
    }
   // echo "CompanyCode: ".$row['companyCode']." MaxRateScale:      ".$maxRatedReadingScale."  MinRateScale:".$minRatedReadingScale."  MaxReading:".$maxRatedReading."  MinReading:".$minRatedReading." of SensorTag : ".$parameterTag." with id:".$row['id']."  RawValue: ".$val."  ScaledValue: ".$scaledValue."<br>";
   // $scaledValue = round($scaledValue1,2);
    
    return round($scaledValue,2);
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
            setParameterDataForCurrentWindow($conn,$parametrer_data,$sensor_id);
        }
    }
}

function setParameterDataForCurrentWindow($conn,$parameter_data,$sensor_id)
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
       $sql_query="insert into sampled_sensor_data_details(sensor_id,parameterName,last_val,max_val,min_val,avg_val,sample_date_time,param_unit) values($sensor_id,'$parameterName','$last_val','$max_val','$min_val','$avg_val','$sample_date_time','$param_unit')";
    }
    // echo $sql_query."</br>";
    $res=mysqli_query($conn, $sql_query) or die(mysqli_error($conn));
}

//program execution  starts from here
include("includes/config.php"); //connection to db
$data_table="aqmi_json_data";//fetching data from main table where data is pushed
//$moving_window_width=15*60;//in seconds
$moving_window_width=60;//in seconds     fetching last 2mins data
$device_mode="2"; //fetching the mode

// Commented below line by ashok
// getValuesForAParameter($mysqli,$data_table,$parameterInfo,$moving_window_width,$device_mode);


// Added below line by ashok
echo "App start \n";
$i = 0;

while(1){
    $i++;
    // echo $i;
    date_default_timezone_set('Asia/Kolkata');
    $txt =  "Date : ".  date('m/d/Y h:i:s')." \n";
    $txt .=  "Iteration : ". $i;  
    $filename='C:\xampp\htdocs\AQMS\backend\AQMS_DATA_EXTRACTION_CRON\cron_log/cron_'.date('m-d-Y_h-i-s').'.txt';
    file_put_contents($filename, $txt);
    getValuesForAParameter($mysqli,$data_table,$parameterInfo,$moving_window_width,$device_mode);
    if($i == 3){
        break;
    }
    sleep(20);
}





// extractAQMSParameterData($mysqli,$data_table,$moving_window_width,$device_mode);


?>