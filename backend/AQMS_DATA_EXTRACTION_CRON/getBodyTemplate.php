<?php
$dbuser="idealab_Aqms";
$dbpass="Aq1sw2de3fr4@aqms";
$host="localhost";
$db="idealab_database";
$conn = new mysqli($host,$dbuser,$dbpass,$db);
$b_companyCode = "A-TEST";
$template = "twa";
echo getBodyAndSubjectForEmail($conn,$b_companyCode,$template);



function getBodyAndSubjectForEmail($conn,$b_companyCode,$template){
    $found = 0;
    $body = "NA";
    $subject = "NA";
    $sqlGetBodySubject = "SELECT * FROM email_templates where companyCode = '$b_companyCode' LIMIT 1";
    $sqlGetBodySubjectResult = mysqli_query($conn,$sqlGetBodySubject);
    $result = mysqli_num_rows($sqlGetBodySubjectResult);
    if($result){
        $found = 1;
        // `calibrartionSubject`, `calibrartionBody`, `bumpTestSubject`, `bumpTestBody`, `stelSubject`, `stelBody`, `twaSubject`, `twaBody`, `warningSubject`, `warningBody`, `criticalSubject`, `criticalBody`, `outOfRangeSubject`, `outOfRangeBody`,
        while($row = mysqli_fetch_assoc($sqlGetBodySubjectResult)){
            if($template == "calibration"){
                $body = $row['calibrartionBody'];
                $subject = $row['calibrartionSubject'];
            }
            if($template == "bumptest"){
                $body = $row['bumpTestBody'];
                $subject = $row['bumpTestSubject'];
            }
            if($template == "stel"){
                $body = $row['stelBody'];
                $subject = $row['stelSubject'];
            }
            if($template == "twa"){
                $body = $row['twaBody'];
                $subject = $row['twaSubject'];
            }
            if($template == "warning"){
                $body = $row['warningBody'];
                $subject = $row['warningSubject'];
            }
            if($template == "outofrange"){
                $body = $row['outOfRangeBody'];
                $subject = $row['outOfRangeSubject'];
            }
            if($template == "critical"){
                $body = $row['criticalBody'];
                $subject = $row['criticalSubject'];
            }
            if($template == "calibrationPeriodicity"){
                $body = $row['periodicityBody']."Calibration";
                $subject = $row['periodicitySubject']."Calibration";
                // Check whether the field is empty or not, if it is then set default value in each above 'if' block  // 27-02-2023
            }
        }
    }else{
        
    }
    echo $found."&".$body."&".$subject;
    return $found."&".$body."&".$subject;
}



?>