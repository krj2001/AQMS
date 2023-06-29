<?php

ini_set("display_errors",1);
error_reporting(1);

$dbuser="aqms_root";
$dbpass="Aq1sw2de3fr4@aqms";
$host="localhost";
$db="aqms_database";
$conn = mysqli_connect($host,$dbuser,$dbpass,$db);

/**** Mail Class Begin ****/

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

$sendingMailInfo = array();

// $sendingMailInfo['recipientEmail'] = "abhishek@rdltech.in";
// $sendingMailInfo['recepientName'] = "prajwal";
// $sendingMailInfo['Subject'] = "Warning";
// $sendingMailInfo['bodyMessage'] = "Calibration due date is coming near";

class mail{
    private $senderEmail = "abhishekshenoy97@gmail.com";
    private $senderName = "Abhishek"; 
    private $Password = "ivraxsndgkhumjyh";
    private $Gateway = "smtp.gmail.com";
    private $secureType = "tls";

    private $Subject = "";    
    private $recipientEmail = "";
    private $BodyMessage = "";
    private $recepientName = "abhi";

    private $Port = 587;
    private $Host = 'smtp.gmail.com';    

    function __construct($sendingMailInfo) {       
        $this->recipientEmail = $sendingMailInfo['recipientEmail'];      
        $this->recepientName = $sendingMailInfo['recepientName']; 
        $this->Subject = $sendingMailInfo['Subject']; 
        $this->BodyMessage = $sendingMailInfo['bodyMessage']; 
    }

    public function sendMails($mail){
        try {
   
            $mail->setFrom($this->senderEmail, $this->senderName);
            $mail->addAddress($this->recipientEmail,$this->recepientName);
            $mail->Subject = $this->Subject;
            $mail->Body = $this->BodyMessage;
            
            /* SMTP parameters. */
            
            /* Tells PHPMailer to use SMTP. */
             // $mail->isSMTP();
            
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

// $mail = new PHPMailer(TRUE);
// $mailInfo = new mail($sendingMailInfo);
// $mailInfo->sendMails($mail);


//sending bumptest due date to user to email

$getSensosrTags = "SELECT DISTINCT(sensorTagName) FROM `bump_test_results`";
$resultsSensorTags=mysqli_query($conn,$getSensosrTags) or die(mysqli_error($conn));
    
if(mysqli_num_rows($resultsSensorTags)>0){
    while($row= mysqli_fetch_assoc($resultsSensorTags)){
        $sensorTag = $row['sensorTagName'];
        $sensortagDetails = "SELECT customers.customerId, locations.stateName, branches.branchName, facilities.facilityName, buildings.buildingName, floors.floorName, lab_departments.labDepName, devices.deviceName, sensors.sensorTag , bump_test_results.nextDueDate FROM customers 
            INNER JOIN locations ON customers.customerId = locations.companyCode 
            INNER JOIN branches ON customers.customerId = branches.companyCode AND locations.id = branches.location_id 
            INNER JOIN facilities ON customers.customerId = facilities.companyCode AND locations.id = facilities.location_id AND branches.id = facilities.branch_id 
            INNER JOIN buildings ON customers.customerId = buildings.companyCode AND  locations.id = buildings.location_id  AND branches.id = buildings.branch_id AND facilities.id = buildings.facility_id
            INNER JOIN floors ON customers.customerId = floors.companyCode AND  locations.id = floors.location_id  AND  branches.id = floors.branch_id AND  facilities.id = floors.facility_id AND buildings.id = floors.building_id
            INNER JOIN lab_departments ON customers.customerId = lab_departments.companyCode AND  locations.id = lab_departments.location_id  AND  branches.id = lab_departments.branch_id AND  facilities.id = lab_departments.facility_id AND buildings.id = lab_departments.building_id AND floors.id = lab_departments.floor_id 
            INNER JOIN devices ON customers.customerId = devices.companyCode AND  locations.id = devices.location_id  AND  branches.id = devices.branch_id AND  facilities.id = devices.facility_id AND buildings.id = devices.building_id AND floors.id = devices.floor_id AND lab_departments.id = devices.lab_id
            INNER JOIN sensors ON customers.customerId = sensors.companyCode AND  locations.id = sensors.location_id  AND  branches.id = sensors.branch_id AND  facilities.id = sensors.facility_id AND buildings.id = sensors.building_id AND floors.id = sensors.floor_id AND lab_departments.id = sensors.lab_id AND devices.id = sensors.deviceid
            INNER JOIN bump_test_results ON sensors.sensorTag = bump_test_results.sensorTagName
            where sensors.sensorTag = '$sensorTag' ORDER BY bump_test_results.id DESC LIMIT 1"; 
        $result2 = mysqli_query($conn,$sensortagDetails);
        $data = mysqli_fetch_assoc($result2);
        $companyCode = $data['customerId'];
        $dueDate = $data["nextDueDate"];
        
        $fromDate = date("Y-m-d");
        $toDate = $dueDate;
        
        $diff =  strtotime($toDate) - strtotime($fromDate);
        
        $dt = abs(round($diff/86400));
        
        // if($dt == 2){
        	$NotificationEnabledUsers = "SELECT * FROM `users` where empNotification = 1 AND companyCode = '$companyCode'";
            $NEUResults = mysqli_query($conn,$NotificationEnabledUsers);
            if(mysqli_num_rows($NEUResults) > 0){
                while($row = mysqli_fetch_array($NEUResults)){
                    $notificationStatus = $row['empNotification'];
                    $email = $row['email'];
                    $name = $row['name'];
                    $contactNo = $row['mobileno'];
                    
                    $sendingMailInfo['recipientEmail'] = $email;
                    $sendingMailInfo['recepientName'] = $name;
                    $sendingMailInfo['Subject'] = $alertTypes;
                    $sendingMailInfo['bodyMessage'] = "Bump test due date is comming nearly";
                    
                    // $mail = new PHPMailer(TRUE);
                    // $mailInfo = new mail($sendingMailInfo); //Class sendAlertMail is written in Top
                    // $mailInfo->sendMails($mail);//function  of class sensAlertMail for sending emails  for users 
                    
                    echo "Done: mail sent";
                }
            }
        // }else{}
    }
}


//CALIBRATION DUE DATE sending mails

// $getSensosrTagsC = "SELECT DISTINCT(sensorTag) FROM `calibration_test_results`";
// $resultsSensorTagsC=mysqli_query($conn,$getSensosrTagsC) or die(mysqli_error($conn));
    
// if(mysqli_num_rows($resultsSensorTagsC)>0){
//     while($row= mysqli_fetch_assoc($resultsSensorTagsC)){
//         $sensorTag = $row['sensorTag'];
//         $sensortagDetails = "SELECT customers.customerId, locations.stateName, branches.branchName, facilities.facilityName, buildings.buildingName, floors.floorName, lab_departments.labDepName, devices.deviceName, sensors.sensorTag , calibration_test_results.nextDueDate FROM customers 
//                     INNER JOIN locations ON customers.customerId = locations.companyCode 
//                     INNER JOIN branches ON customers.customerId = branches.companyCode AND locations.id = branches.location_id 
//                     INNER JOIN facilities ON customers.customerId = facilities.companyCode AND locations.id = facilities.location_id AND branches.id = facilities.branch_id 
//                     INNER JOIN buildings ON customers.customerId = buildings.companyCode AND  locations.id = buildings.location_id  AND branches.id = buildings.branch_id AND facilities.id = buildings.facility_id
//                     INNER JOIN floors ON customers.customerId = floors.companyCode AND  locations.id = floors.location_id  AND  branches.id = floors.branch_id AND  facilities.id = floors.facility_id AND buildings.id = floors.building_id
//                     INNER JOIN lab_departments ON customers.customerId = lab_departments.companyCode AND  locations.id = lab_departments.location_id  AND  branches.id = lab_departments.branch_id AND  facilities.id = lab_departments.facility_id AND buildings.id = lab_departments.building_id AND floors.id = lab_departments.floor_id 
//                     INNER JOIN devices ON customers.customerId = devices.companyCode AND  locations.id = devices.location_id  AND  branches.id = devices.branch_id AND  facilities.id = devices.facility_id AND buildings.id = devices.building_id AND floors.id = devices.floor_id AND lab_departments.id = devices.lab_id
//                     INNER JOIN sensors ON customers.customerId = sensors.companyCode AND  locations.id = sensors.location_id  AND  branches.id = sensors.branch_id AND  facilities.id = sensors.facility_id AND buildings.id = sensors.building_id AND floors.id = sensors.floor_id AND lab_departments.id = sensors.lab_id AND devices.id = sensors.deviceid
//                     INNER JOIN calibration_test_results ON sensors.sensorTag = calibration_test_results.sensorTag
//                     where sensors.sensorTag = '$sensorTag' ORDER BY calibration_test_results.id DESC LIMIT 1"; 
//         $result2 = mysqli_query($conn,$sensortagDetails);
//         $data = mysqli_fetch_assoc($result2);
//         $companyCode = $data['customerId'];
//         $dueDate = $data["nextDueDate"];
        
       
//         $fromDate = date("Y-m-d");
//         $toDate = $dueDate;
        
//         $diff =  strtotime($toDate) - strtotime($fromDate);
        
//         $dt = abs(round($diff/86400));
        
//         // if($dt == 2){
//         	$NotificationEnabledUsers = "SELECT * FROM `users` where empNotification = 1 AND companyCode = '$companyCode'";
//             $NEUResults = mysqli_query($conn,$NotificationEnabledUsers);
//             if(mysqli_num_rows($NEUResults) > 0){
//                 while($row = mysqli_fetch_array($NEUResults)){
//                     $notificationStatus = $row['empNotification'];
//                     $email = $row['email'];
//                     $name = $row['name'];
//                     $contactNo = $row['mobileno'];
                 
//                     $mail = new PHPMailer(TRUE);
                    
//                     $sendingMailInfo['recipientEmail'] = "abhishek@rdltech.in";
//                     $sendingMailInfo['recepientName'] = $name;
//                     $sendingMailInfo['Subject'] = $alertTypes;
//                     $sendingMailInfo['bodyMessage'] = "Calibration test due date is comming nearly";
                                
//                     $mailInfo = new sendAlertMailAndAlertNumber($sendingMailInfo); //Class sendAlertMail is written in Top
                    
//                     $mailInfo->sendMails($mail);//function  of class sensAlertMail for sending emails  for users 
                    
//                     echo "Done";
//                 }
//             }
//         // }else{}
//     }
// }







// $sql = "SELECT customers.customerId, locations.stateName, branches.branchName, facilities.facilityName, buildings.buildingName, floors.floorName, lab_departments.labDepName, devices.deviceName, sensors.sensorTag , bump_test_results.nextDueDate FROM customers 
// INNER JOIN locations ON customers.customerId = locations.companyCode 
// INNER JOIN branches ON customers.customerId = branches.companyCode AND locations.id = branches.location_id 
// INNER JOIN facilities ON customers.customerId = facilities.companyCode AND locations.id = facilities.location_id AND branches.id = facilities.branch_id 
// INNER JOIN buildings ON customers.customerId = buildings.companyCode AND  locations.id = buildings.location_id  AND branches.id = buildings.branch_id AND facilities.id = buildings.facility_id
// INNER JOIN floors ON customers.customerId = floors.companyCode AND  locations.id = floors.location_id  AND  branches.id = floors.branch_id AND  facilities.id = floors.facility_id AND buildings.id = floors.building_id
// INNER JOIN lab_departments ON customers.customerId = lab_departments.companyCode AND  locations.id = lab_departments.location_id  AND  branches.id = lab_departments.branch_id AND  facilities.id = lab_departments.facility_id AND buildings.id = lab_departments.building_id AND floors.id = lab_departments.floor_id 
// INNER JOIN devices ON customers.customerId = devices.companyCode AND  locations.id = devices.location_id  AND  branches.id = devices.branch_id AND  facilities.id = devices.facility_id AND buildings.id = devices.building_id AND floors.id = devices.floor_id AND lab_departments.id = devices.lab_id
// INNER JOIN sensors ON customers.customerId = sensors.companyCode AND  locations.id = sensors.location_id  AND  branches.id = sensors.branch_id AND  facilities.id = sensors.facility_id AND buildings.id = sensors.building_id AND floors.id = sensors.floor_id AND lab_departments.id = sensors.lab_id AND devices.id = sensors.deviceid
// INNER JOIN bump_test_results ON sensors.sensorTag = bump_test_results.sensorTagName
// where customers.customerId = 'A-TEST' AND sensors.sensorTag = 'O3_gas1' ORDER BY bump_test_results.id DESC LIMIT 1";


?>