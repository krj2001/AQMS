<?php
ini_set("display_errors",1);
error_reporting(1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

$mail = new PHPMailer(TRUE);

$sendingMailInfo = array();


$sendingMailInfo['recipientEmail'] = "abhishekshenoy7@gmail.com";
$sendingMailInfo['recepientName'] = "abhishek";
$sendingMailInfo['Subject'] = "Warning";
$sendingMailInfo['bodyMessage'] = "hello"; // only message 



class mail{
    private $senderEmail = "developer5@rdltech.in";
    private $senderName = "vaishak"; 
    private $Password = "Ce=UmdQ7KFmV7nZ%%";
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

$mailInfo = new mail($sendingMailInfo);

$mailInfo->sendMails($mail);








?>