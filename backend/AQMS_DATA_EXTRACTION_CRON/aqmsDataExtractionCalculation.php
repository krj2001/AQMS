<?php
$dbuser="idealab_Aqms";
$dbpass="Aq1sw2de3fr4@aqms";
$host="localhost";
$db="idealab_database";
$con=new mysqli($host,$dbuser,$dbpass,$db);
$sql="SELECT DISTINCT upload_date_time FROM `segregatedValues` upload_date_time >= DATE_SUB(NOW(),INTERVAL 50 MINUTE)";

if ($result=mysqli_query($con,$sql))
{
    // Return the number of rows in result set
    $rowcount=mysqli_num_rows($result);
    echo $rowcount;
    // Free result set
    
}


?>