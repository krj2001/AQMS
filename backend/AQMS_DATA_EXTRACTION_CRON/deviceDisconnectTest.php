<?php


/**** Mail Class Begin ****/

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
include("getBodyTemplate.php");
require'vendor/autoload.php';


$sendingMailInfo = array();

class sendAlertMailAndAlertNumber{
    private $senderEmail = "vaishaksuvarna10@gmail.com";
    private $senderName = "AIDEA LABS"; 
    private $Password = "rxjcxmhclqsfkdos";
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


/** connection **/
$dbuser = "idealab_Aqms";
$dbpass = "Aq1sw2de3fr4@aqms";
$host = "localhost";
$db = "idealab_database";
$con = new mysqli($host,$dbuser,$dbpass,$db);


    // include("getUsers.php");
    // include("ApplicationLink.php");
        
    // $deviceId = 173;
    
    // $locationListParameters = deviceLocationDetails($con, $deviceId);
    // print_r($locationListParameters);      
              
    // $companyCode = 'A-TEST';
    
    // $users = getUsers($con, $companyCode, $locationListParameters);
    // print_r($users);    
    
    //  $getSensorDetails = "SELECT c.customerId, l.stateName, b.branchName, f.facilityName, bui.buildingName, fl.floorName, ld.labDepName, d.deviceName FROM customers as c
    //         INNER JOIN locations as l ON c.customerId = l.companyCode 
    //         INNER JOIN branches as b ON c.customerId = b.companyCode AND l.id = b.location_id 
    //         INNER JOIN facilities as f ON c.customerId = f.companyCode AND l.id = f.location_id AND b.id = f.branch_id 
    //         INNER JOIN buildings as bui ON c.customerId = bui.companyCode AND  l.id = bui.location_id  AND b.id = bui.branch_id AND f.id = bui.facility_id
    //         INNER JOIN floors as fl ON c.customerId = fl.companyCode AND  l.id = fl.location_id  AND  b.id = fl.branch_id AND  f.id = fl.facility_id AND bui.id = fl.building_id
    //         INNER JOIN lab_departments as ld ON c.customerId = ld.companyCode AND  l.id = ld.location_id  AND  b.id = ld.branch_id AND  f.id = ld.facility_id AND bui.id = ld.building_id AND fl.id = ld.floor_id 
    //         INNER JOIN devices as d ON c.customerId = d.companyCode AND  l.id = d.location_id  AND  b.id = d.branch_id AND  f.id = d.facility_id AND bui.id = d.building_id AND fl.id = d.floor_id AND ld.id = d.lab_id
    //         where c.customerId = '$companyCode' AND d.id = '$deviceId'";
            
    //     $getSensorResult = mysqli_query($con, $getSensorDetails);
    //     $getSensorRow = mysqli_fetch_assoc($getSensorResult);

    //     $customerId = $getSensorRow['customerId'];
    //     $branchName = $getSensorRow['branchName'];
    //     $stateName = $getSensorRow['stateName'];
    //     $buildingName =  $getSensorRow['buildingName'];
    //     $faciltiyName = $getSensorRow['facilityName'];
    //     $floorName = $getSensorRow['floorName'];
    //     $labDepName = $getSensorRow['labDepName'];
    //     $deviceName = $getSensorRow['deviceName'];


    // for($i=0; $i<count($users); $i++)
    // {
        $email = "developer5@rdltech.in";
        $name = "vaishak";
        $url = "http://localhost/AirQualityMonitoringSystem";
       
        $mail = new PHPMailer(TRUE);
        
        $sendingMailInfo['recipientEmail'] = $email;
        $sendingMailInfo['recepientName'] = $name;
        $sendingMailInfo['Subject'] = "Device Disconnected";
        $sendingMailInfo['bodyMessage'] ="<tr>
                                            <td>Please find the attached Calibration report below:</td>
                                        </tr>
                                        <tr>
                                            <td><a href=".$url.">Click here</a> to access the application.</td>
                                        </tr>";
                    
        
        $mailInfo = new sendAlertMailAndAlertNumber($sendingMailInfo);     // Sending email
        $mailInfo->sendMails($mail);
    // }

  
    // function deviceLocationDetails($con, $deviceId) {
        
    //     $device = "SELECT * FROM devices where id = '$deviceId'";
    //     $result = mysqli_query($con, $device);
    //     $row = mysqli_fetch_assoc($result);
        
    //     $locationListParameters = [
    //         "loc_id" => $row['location_id'],
    //         "bra_id" => $row['branch_id'],
    //         "fac_id" => $row['facility_id'],
    //         "bui_id" => $row['building_id'],
    //         "flr_id" => $row['floor_id'],
    //         "lab_id" => $row['lab_id'],
    //     ];
        
    //     return $locationListParameters;
    // }
    
    
   
    
    
    
    
    
    
    
    
    
    
    
    


?>