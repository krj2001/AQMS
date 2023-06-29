<?php


ini_set("display_errors",1);
error_reporting(1);
include("getBodyTemplate.php");


/**** Mail Class Begin ****/

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require'vendor/autoload.php';

$sendingMailInfo = array();

class sendAlertMailAndAlertNumber{
    private $senderEmail = "abhishek@rdltech.in";
    private $senderName = "Abhishek"; 
    private $Password = "3**mg8S2";
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
    
    public function SMS($msg,$contact_no){   
        print_r($msg);
        $user_name="rdl";
        $api_password="6c83405kwtpsd6wtg";
        $sender="KEWRDL";
        $to=$contact_no;
        $message=rawurlencode($msg);
        $priority="11";
        $entity_id="1201163177490663081";
        $tag_id="1207164690740453778";   
        $sms_http_api_url="http://sms.foosms.com/pushsms.php"."?username=".$user_name."&api_password=".$api_password."&sender=".$sender."&to=".$to."&priority=".$priority."&e_id=".$entity_id."&t_id=".$tag_id."&message=".$message;
        $url = $sms_http_api_url;
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);          
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $resp = curl_exec($curl);
        curl_close($curl);           
    }
}

/**** Mail Class End ****/

// $mail = new PHPMailer(TRUE);
                
// $sendingMailInfo['recipientEmail'] = $email;
// $sendingMailInfo['recepientName'] = $name;
// $sendingMailInfo['Subject'] = $alertTypes." ".$sensorTag;
// $sendingMailInfo['bodyMessage'] = "Hello";



// $date1=date("2022-07-26");
// $date2=date("2022-07-30");


// $date1 = "2022-07-26";
// $date2 = "2022-07-30";
// $diff = abs(strtotime($date2) - strtotime($date1));
// $days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));

//echo $days;


$dbuser="idealab_Aqms";
$dbpass="Aq1sw2de3fr4@aqms";
$host="localhost";
$db="idealab_database";

$conn = mysqli_connect($host, $dbuser, $dbpass, $db);

date_default_timezone_set('Asia/Kolkata');

$CUR_DATE=date("Y-m-d");  
$CUR_TIME = date("H:i:s");

$sendMessageNotifications = 0; //default set to false for sending email notification

//listing out the sensor tag of bumptest duedate sending mails
$sensorTagList = "SELECT sensor_units.sensorCategoryName, sensor_units.sensorName, sensor_units.bumpTestRequired, sensors.companyCode, sensors.sensorTag FROM sensor_units INNER JOIN sensors ON sensor_units.id = sensors.sensorName where sensor_units.bumpTestRequired = 'ON'";
$sensorTagListResult = mysqli_query($conn,$sensorTagList) or die(mysqli_error($conn));
if(mysqli_num_rows($sensorTagListResult)>0){
    while($row = mysqli_fetch_assoc($sensorTagListResult)){
        $s_companyCode = $row['companyCode'];
        $sensorTag = $row['sensorTag'];
        $bumpTestRequired = $row['bumpTestRequired'];
        $sensorName = $row['sensorName'];
        //echo $s_companyCode." sensorTag :".$sensorTag." ".$bumpTestRequired." sensorName ".$sensorName."<br>";
        
        //listing out bumptest DATA LASTDUE DATE
        
        //bumptest query begin
        
        $bumpTestSql = "SELECT sensors.companyCode,sensors.sensorStatus, sensors.notificationStatus, bump_test_results.nextDueDate, bump_test_results.sensorTagName FROM sensors INNER JOIN bump_test_results ON sensors.sensorTag = bump_test_results.sensorTagName and sensors.companyCode = bump_test_results.companyCode WHERE bump_test_results.sensorTagName = '$sensorTag' and bump_test_results.companyCode = '$s_companyCode' order by bump_test_results.id desc LIMIT 1";
        $bumpTestRes=mysqli_query($conn,$bumpTestSql) or die(mysqli_error($conn));
        $bumpTestRowData = mysqli_fetch_assoc($bumpTestRes);
        $count = mysqli_num_rows($bumpTestRes);
        $b_companyCode = $bumpTestRowData['companyCode'];
        $sensorTagName = $bumpTestRowData['sensorTagName'];
        $sensorStatus = $bumpTestRowData['sensorStatus'];
        $notificationStatus = $bumpTestRowData['notificationStatus'];
        $lastDueDate = $bumpTestRowData['nextDueDate'];
        
        //$date = str_replace('/', '-', $lastDueDate);
        $DUE_Date = date('Y-m-d', strtotime($lastDueDate));
        
       
        if($count>0){
            // $diff = abs(strtotime($DUE_Date) - strtotime($CUR_DATE));
            // $days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
            
            $today = new DateTime($CUR_DATE); // today date

            $date1 = new DateTime($DUE_Date); // next n days 
            
            $days = $today->diff($date1)->format("%r%a");
            
            //sending mail based notification status and sensor status enabled  
            if($sensorStatus == 1){
                if($notificationStatus == 1){
                   echo "Companycode: ".$b_companyCode." sensorTag: ".$sensorTagName." sensorStatus: ".$sensorStatus." notificationstatus: ".$notificationStatus." last due date: ".$lastDueDate." Left Days ".$days."<br><br>"; 
                   
                   $usersQuery = "SELECT customers.expireDateReminder, users.email  FROM customers INNER JOIN users ON customers.customerId = users.companyCode WHERE users.companyCode = '$b_companyCode' and users.empNotification = 1  and users.changePassword = 0 and users.sec_level_auth = 0";
                   $userResult=mysqli_query($conn,$usersQuery) or die(mysqli_error($conn));
                   if(mysqli_num_rows($userResult)>0){
                       while($row = mysqli_fetch_assoc($userResult)){
                           $email = $row['email'] ;
                           $expireDateReminder = $row['expireDateReminder'];
                           $message = "";
                            
                                //echo "Email :".$email." sensor tag ->".$sensorTagName." expidateDays->  ".$expireDateReminder."      Days :".$days."<br><br>";
                                if($days == $expireDateReminder){
                                    //send notifications when exact days left
                                    echo $days." Left from due date"."<br>";
                                    $sendMessageNotifications = 1;
                                    $message = $days." Days Left for bumptest";
                                        
                                        
                                }else if($days > 0 && $days < $expireDateReminder){
                                    //n days before send notifications   
                                    echo $days." left  before 0 to ".$expireDateReminder." <br>";
                                    $sendMessageNotifications = 1;
                                    $message = $days." Days Left for bumptest";
                                    
                                    
                                }else if($days == 0){
                                    //same day send notifications
                                    echo "current day bumptest has to be done ".$expireDateReminder."<br>";
                                    $sendMessageNotifications = 1;
                                    $message = "Due date for bumptest is today";
                                    
                                   
                                }elseif($days == -1){
                                    echo "day exceeded   ".$expireDateReminder."<br>";
                                    //send notification if one day exceeded
                                    $sendMessageNotifications = 1;
                                    $message = "Due date exceeded by one day for bumptest";
                                   
                                   
                                }elseif($days < -1){
                                   echo "day exceeded  more than 1".$expireDateReminder."<br>";
                                   //not to send notification unless futher future implementations told
                                   
                                   
                                }else{
                                    echo "Not needed  $days ".$expireDateReminder."<br>";
                                }
                                
                                if($sendMessageNotifications == 1){
                                    
                                    $getSensorDetails = "SELECT customers.customerId, locations.stateName, branches.branchName, facilities.facilityName, buildings.buildingName, floors.floorName, lab_departments.labDepName, devices.deviceName, sensors.sensorTag FROM customers 
                                                INNER JOIN locations ON customers.customerId = locations.companyCode 
                                                INNER JOIN branches ON customers.customerId = branches.companyCode AND locations.id = branches.location_id 
                                                INNER JOIN facilities ON customers.customerId = facilities.companyCode AND locations.id = facilities.location_id AND branches.id = facilities.branch_id 
                                                INNER JOIN buildings ON customers.customerId = buildings.companyCode AND  locations.id = buildings.location_id  AND branches.id = buildings.branch_id AND facilities.id = buildings.facility_id
                                                INNER JOIN floors ON customers.customerId = floors.companyCode AND  locations.id = floors.location_id  AND  branches.id = floors.branch_id AND  facilities.id = floors.facility_id AND buildings.id = floors.building_id
                                                INNER JOIN lab_departments ON customers.customerId = lab_departments.companyCode AND  locations.id = lab_departments.location_id  AND  branches.id = lab_departments.branch_id AND  facilities.id = lab_departments.facility_id AND buildings.id = lab_departments.building_id AND floors.id = lab_departments.floor_id 
                                                INNER JOIN devices ON customers.customerId = devices.companyCode AND  locations.id = devices.location_id  AND  branches.id = devices.branch_id AND  facilities.id = devices.facility_id AND buildings.id = devices.building_id AND floors.id = devices.floor_id AND lab_departments.id = devices.lab_id
                                                INNER JOIN sensors ON customers.customerId = sensors.companyCode AND  locations.id = sensors.location_id  AND  branches.id = sensors.branch_id AND  facilities.id = sensors.facility_id AND buildings.id = sensors.building_id AND floors.id = sensors.floor_id AND lab_departments.id = sensors.lab_id AND devices.id = sensors.deviceid
                                                where customers.customerId = '$b_companyCode' AND sensors.sensorTag = '$sensorTagName'";
                        
                                    $getSensorResult = mysqli_query($conn, $getSensorDetails);
                                    $getSensorRow = mysqli_fetch_assoc($getSensorResult);
                                    $tot_rows=mysqli_num_rows($getSensorResult);
                
                                    $customerId = $getSensorRow['customerId'];
                                    $branchName = $getSensorRow['branchName'];
                                    $stateName = $getSensorRow['stateName'];
                                    $buildingName =  $getSensorRow['buildingName'];
                                    $faciltiyName = $getSensorRow['facilityName'];
                                    $floorName = $getSensorRow['floorName'];
                                    $labDepName = $getSensorRow['labDepName'];
                                    $deviceName = $getSensorRow['deviceName'];
                                    $sensorTag = $getSensorRow['sensorTag'];
                
                                    $mes = $message." for ".$sensorTag;
                                    echo $mes." ".$customerId;
                
                                    $mail = new PHPMailer(TRUE);
                                    
                                    //get user defined body message
                                    
                                    $template = "bumptest";
                                    
                                    $returnedData = explode("&",getBodyAndSubjectForEmail($conn,$customerId,$template));
                                    
                                    if($returnedData[0] == 1){
                                        $body = $returnedData[1];
                                        $subject = $returnedData[2];    
                                    }else{
                                        $body = "";
                                        $subject = "Bumptest Due date for ";
                                    }
                                    
                                    $mail = new PHPMailer(TRUE);
                                    
                                    $sendingMailInfo['recipientEmail'] = $email;
                                    $sendingMailInfo['recepientName'] = $name;
                                    $sendingMailInfo['Subject'] = $subject.$sensorTag;
                                    $sendingMailInfo['bodyMessage'] = $body."<br>".
                                                                            "<tr>
                                                                                <td>Message : ".$mes."</td>
                                                                            </tr>
                                                                            </br>
                                                                            <tr>
                                                                                <td>SensorTag Name: ".$sensorTag."</td>
                                                                            </tr>
                                                                            </br>
                                                                            <tr>  
                                                                                <td>Device Name: ".$deviceName."</td>   
                                                                            </tr>
                                                                            </br>
                                                                            <tr>  
                                                                                <td>Lab Department Name: ".$labDepName."</td>   
                                                                            </tr>
                                                                            </br>
                                                                            <tr>  
                                                                                <td>Floor Name: ".$floorName."</td>   
                                                                            </tr>
                                                                            </br>
                                                                            <tr>  
                                                                                <td>Building Name: ".$buildingName."</td>   
                                                                            </tr>
                                                                            </br>
                                                                            <tr>  
                                                                                <td>Facility Name: ".$facilityName."</td>   
                                                                            </tr>
                                                                            </br>
                                                                            <tr>  
                                                                                <td>Branch Name: ".$branchName."</td>   
                                                                            </tr>
                                                                            </br>
                                                                            <tr>  
                                                                                <td>State Name: ".$stateName."</td>   
                                                                            </tr>
                                                                            </br>
                                                                            <tr>
                                                                                <td>Customer Name: ".$customerId."</td>   
                                                                            </tr>
                                                                            </br>";
                                                
                                    $mailInfo = new sendAlertMailAndAlertNumber($sendingMailInfo); //Class sendAlertMail is written in Top
                                    
                                    $mailInfo->sendMails($mail);//function  of class sensAlertMail for sending emails  for users 
                                    //  print_r($sendingMailInfo['bodyMessage']);
                                    
                                    //below sql code written to test whether to check mail funtion is working or else its not important 
                                    $sqlq = "INSERT INTO `alert_data`(`companyCode`,`machine_name`, `alert`,`a_date`,`a_time`) VALUES ('$b_companyCode','$email','$mes','$CUR_DATE','$CUR_TIME')";
                                    echo $sqlq;
                                    $res=mysqli_query($conn, $sqlq) or die(mysqli_error($conn));

                                }
        
                                
                           
                        }
                   }else{
                       //else part for emptnotification data not found
                       
                   }
                }else{
                    //else part for notification status disabled
                }
            }else{
               //else part for sensor status disabled
            }
        }else{
            // echo "No data Found";
        }
        
        //bumptest query end
        
        
        
    }
        
}else{
    echo "No results Found";
}



































?>