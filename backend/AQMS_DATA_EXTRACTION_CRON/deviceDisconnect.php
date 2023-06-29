<?php
   
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
// include("getBodyTemplate.php");
require'vendor/autoload.php';

$sendingMailInfo = array();

class sendAlertMailAndAlertNumber{
    private $senderEmail = "developer5@rdltech.in";
    private $senderName = "AIDEA LABS"; 
    private $Password = "Ce=UmdQ7KFmV7nZ%%";
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
}
    
    
    /* DATABSE CONNCETION */
    
    $dbuser="idealab_Aqms";
    $dbpass="Aq1sw2de3fr4@aqms";
    $host="localhost";
    $db="idealab_database";
    $con=new mysqli($host,$dbuser,$dbpass,$db);
    
    
    /*  CURRENT TIMESTAMP OF SERVER */
    /*$sql = "SELECT CURRENT_TIMESTAMP";
    $result = mysqli_query($con,$sql) or die(mysqli_error($con));
    
    $row = $result->fetch_assoc();
    $datetime = $row['CURRENT_TIMESTAMP'];
    echo $datetime;*/


    /* CURRENT TIMESTAMP  INDIA/KOLKATTA  */
    $date = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
    $dateTime = $date->format('Y-m-d H:i:s');    // Format the date and time as a string
    $date = explode(" ",$dateTime)[0];
    $time = explode(" ",$dateTime)[1];
    
    
    function getDevices($con)
    {
        $device = "select * FROM `devices` WHERE disconnectedStatus = 1 and disconnectedTime >= DATE_SUB(NOW(),INTERVAL 1 MINUTE)";
        $res = mysqli_query($con ,$device) or die(mysqli_error($con));
        $rowCount = mysqli_num_rows($res);
        
        return $rowCount;
    }
    
    if(getDevices($con) > 0){
        echo "Total disconnected devices :".getDevices($con)."<br>";
        // echo "Current date " . $date."<br>";
        // echo "Current time " . $time."<br>";
        insertDevice($con,$date,$time);
        
    }else{
        echo "no New Disconnected Devices found!";
        
    }
    
    
    function insertDevice($con,$date,$time)
    {
        $device = "select * FROM `devices` WHERE disconnectedTime >= DATE_SUB(NOW(),INTERVAL 1 MINUTE)";
        $res = mysqli_query($con ,$device) or die(mysqli_error($con));
        $rowCount =mysqli_num_rows($res);

        if($rowCount > 0){
            while($row = mysqli_fetch_assoc($res)){
                $companyCode = $row['companyCode'];
                $deviceId = $row['id'];
                $deviceName = getDeviceName($deviceId, $con);
                echo $deviceName;
                
                $latestDevice = "select * FROM `alert_crons` WHERE deviceId = '$deviceId' and alertType = 'deviceDisconnected' and status = 0 and triggeredAlertFlag = 1";
                $query = mysqli_query($con ,$latestDevice) or die(mysqli_error($con));
                $dCount =mysqli_num_rows($query);
            
                if($dCount <= 0){
                    $sql_query = "INSERT INTO `alert_crons`(`a_date`, `a_time`, `companyCode`, `deviceId`, `sensorTag`, `alertType`, `value`, `msg`, `status`,`statusMessage`,`alarmType`,`alertStandardMessage`,`alertCategory`,`triggeredAlertFlag`) VALUES 
                        ('$date','$time','$companyCode','$deviceId','$deviceName','deviceDisconnected','NA','Device is Disconnected','0','NotCleared','UnLatch','Device is Disconnected','4','1')";
                          
                    $result = mysqli_query($con, $sql_query) or die(mysqli_error($con));
                    
                    if($result){
                        echo "Inserted, Device Id : ".$deviceId."<br>";
                        sendEmail($con, $deviceId, $companyCode);
                    }
                    
                }else{
                    echo "device :".$deviceId." is alreday logged"."<br>";
                }
            }
        }
        echo "Not Inserted";
    }
  
  
  
    function updateDevice($con)
    {
        //last one minutes deviceID
        $lastDeviceIdSql = "SELECT distinct device_id FROM `sampled_sensor_data_details_MinMaxAvg` WHERE time_stamp >= DATE_SUB(NOW(),INTERVAL 1 MINUTE)";
        $lastDevice = mysqli_query($con,$lastDeviceIdSql) or die(mysqli_error($con));
        $rowDataCount =mysqli_num_rows($lastDevice);
        
        if($rowDataCount > 0){
            while($row = mysqli_fetch_assoc($lastDevice)){
            $deviceId[] = $row['device_id'];
            }
        }else{
            echo "no devices found";
        }
        
        echo "<br>"."Update Device begins"."<br>";
        print_r($deviceId);
        echo "<br>";

        if($deviceId){
          $count = count($deviceId);
           
            for($i=0; $i<=$count; $i++)
            {
                $alertCronDevice = "select * FROM `alert_crons` WHERE deviceId = '$deviceId[$i]' and alertType = 'deviceDisconnected' and status = '0' and triggeredAlertFlag = '1'";
                $query = mysqli_query($con ,$alertCronDevice) or die(mysqli_error($con));
                $count =mysqli_num_rows($query);
                
                if($count > 0){
                    while($row1 = mysqli_fetch_assoc($query)){
                        
                        $id = $row1['id'];
                        $update = "UPDATE `alert_crons` SET `status` = '1', `triggeredAlertFlag` = '0', `statusMessage` = 'Cleared' WHERE `alert_crons`.`id` = '$id'";
                        $result = mysqli_query($con, $update) or die(mysqli_error($con));
                        
                        if($result){
                            echo "device :".$id." is updated"."<br>";
                        }
                    }
                }else{
                    echo "no new disconnected devices are found";
                }
            }
        }
    }
    
    
    $a = updateDevice($con);
    echo $a;
  
  
    // to get device name  
    function getDeviceName($id, $con){
        
        $device = "SELECT * FROM `devices` WHERE id = '$id'";
        $device1 = mysqli_query($con, $device) or die(mysqli_error($con));
        $rowDataCount = mysqli_num_rows($device1);
        
        if($rowDataCount > 0){
            while($row = mysqli_fetch_assoc($device1)){
                $name = $row['deviceName'];
            }
        }else{
            $name = "NA";
            
        }
        
        return $name;
    }
    
    
    
    function sendMail($con, $deviceId, $companyCode) {
        
        $locationListParameters = deviceLocationDetails($con, $deviceId);
        print_r($locationListParameters);      
                  
        $companyCode = 'A-TEST';
        
        $users = getUsers($con, $companyCode, $locationListParameters);
        print_r($users);    
        
         $getSensorDetails = "SELECT c.customerId, l.stateName, b.branchName, f.facilityName, bui.buildingName, fl.floorName, ld.labDepName, d.deviceName FROM customers as c
                INNER JOIN locations as l ON c.customerId = l.companyCode 
                INNER JOIN branches as b ON c.customerId = b.companyCode AND l.id = b.location_id 
                INNER JOIN facilities as f ON c.customerId = f.companyCode AND l.id = f.location_id AND b.id = f.branch_id 
                INNER JOIN buildings as bui ON c.customerId = bui.companyCode AND  l.id = bui.location_id  AND b.id = bui.branch_id AND f.id = bui.facility_id
                INNER JOIN floors as fl ON c.customerId = fl.companyCode AND  l.id = fl.location_id  AND  b.id = fl.branch_id AND  f.id = fl.facility_id AND bui.id = fl.building_id
                INNER JOIN lab_departments as ld ON c.customerId = ld.companyCode AND  l.id = ld.location_id  AND  b.id = ld.branch_id AND  f.id = ld.facility_id AND bui.id = ld.building_id AND fl.id = ld.floor_id 
                INNER JOIN devices as d ON c.customerId = d.companyCode AND  l.id = d.location_id  AND  b.id = d.branch_id AND  f.id = d.facility_id AND bui.id = d.building_id AND fl.id = d.floor_id AND ld.id = d.lab_id
                where c.customerId = '$companyCode' AND d.id = '$deviceId'";
                
            $getSensorResult = mysqli_query($con, $getSensorDetails);
            $getSensorRow = mysqli_fetch_assoc($getSensorResult);
    
            $customerId = $getSensorRow['customerId'];
            $branchName = $getSensorRow['branchName'];
            $stateName = $getSensorRow['stateName'];
            $buildingName =  $getSensorRow['buildingName'];
            $faciltiyName = $getSensorRow['facilityName'];
            $floorName = $getSensorRow['floorName'];
            $labDepName = $getSensorRow['labDepName'];
            $deviceName = $getSensorRow['deviceName'];
    
    
        for($i=0; $i<count($users); $i++)
        {
            $email = $users[$i]["email"];
            $name = $users[$i]["name"];
           
            $mail = new PHPMailer(TRUE);
            
            $sendingMailInfo['recipientEmail'] = $email;
            $sendingMailInfo['recepientName'] = $name;
            $sendingMailInfo['Subject'] = "Device Disconnected";
            $sendingMailInfo['bodyMessage'] ="<b>Device is disconnected <b><br>.
                                            <tr>  
                                                <td>Device Name: ".$deviceName."</td>   
                                            </tr><br>
                                            <tr>  
                                                <td>Lab Department Name: ".$labDepName."</td>   
                                            </tr><br>
                                            <tr>  
                                                <td>Floor Name: ".$floorName."</td>   
                                            </tr><br>
                                            <tr>  
                                                <td>Building Name: ".$buildingName."</td>   
                                            </tr><br>
                                             <tr>  
                                                <td>Facility Name: ".$faciltiyName."</td>   
                                            </tr><br>
                                            <tr>  
                                                <td>Branch Name: ".$branchName."</td>   
                                            </tr><br>
                                            <tr>  
                                                <td>State Name: ".$stateName."</td>   
                                            </tr><br>
                                            <tr>
                                                <td>Customer Name: ".$customerId."</td>   
                                            </tr><br><br>
                                            <tr>
                                                <td><a href=".$applicationLink.">Click here</a> to access the application.</td>
                                            </tr><br>";
            
            $mailInfo = new sendAlertMailAndAlertNumber($sendingMailInfo);     // Sending email
            $mailInfo->sendMails($mail);
        }
    }

  
    function deviceLocationDetails($con, $deviceId) {
        
        $device = "SELECT * FROM devices where id = '$deviceId'";
        $result = mysqli_query($con, $device);
        $row = mysqli_fetch_assoc($result);
        
        $locationListParameters = [
            "loc_id" => $row['location_id'],
            "bra_id" => $row['branch_id'],
            "fac_id" => $row['facility_id'],
            "bui_id" => $row['building_id'],
            "flr_id" => $row['floor_id'],
            "lab_id" => $row['lab_id'],
        ];
        
        return $locationListParameters;
    }
    
    

    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
?>