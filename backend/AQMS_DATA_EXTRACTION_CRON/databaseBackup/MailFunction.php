<?php
ini_set("display_errors",1);
error_reporting(1);

//sends sql file to mail

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

$mail = new PHPMailer(TRUE);

$sendingMailInfo = array();


$sendingMailInfo['recipientEmail'] = "puneethraj138@gmail.com";
$sendingMailInfo['recepientName'] = "Abhishek";
$sendingMailInfo['Subject'] = "DatabaseBackup";
//$sendingMailInfo['bodyMessage'] = "hello"; // only message 

$sendingMailInfo['bodyMessage'] = "";

$file="idealab_database.sql";

if (file_exists($file)) {
    $sendingMailInfo['bodyMessage'] = "Database sql Backup file";
    $sendingMailInfo['fileAttached'] = $file;
}

class mail{
    private $senderEmail = "abhishekshenoy7@gmail.com";
    private $senderName = "Abhishek"; 
    private $Password = "ivraxsndgkhumjyh";
    private $Gateway = "smtp.gmail.com";
    private $secureType = "tls";

    private $Subject = "";    
    private $recipientEmail = "";
    private $file = "";
    private $BodyMessage = "";
    private $recepientName = "abhi";

    private $Port = 587;
    private $Host = 'smtp.gmail.com';    

    function __construct($sendingMailInfo) {       
        $this->recipientEmail = $sendingMailInfo['recipientEmail'];      
        $this->recepientName = $sendingMailInfo['recepientName']; 
        $this->Subject = $sendingMailInfo['Subject']; 
        $this->BodyMessage = $sendingMailInfo['bodyMessage']; 
        $this->file = $sendingMailInfo['fileAttached'];
    }

    public function sendMails($mail){
        try {
   
            $mail->setFrom($this->senderEmail, $this->senderName);
            $mail->addAddress($this->recipientEmail,$this->recepientName);
            $mail->Subject = $this->Subject;
            $mail->Body = $this->BodyMessage;
            $mail->AddAttachment($this->file);
            
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