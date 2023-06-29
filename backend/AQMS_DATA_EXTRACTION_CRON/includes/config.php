<?php
// $dbuser="idealab_Aqms";
// $dbpass="Aq1sw2de3fr4@aqms";
// $host="localhost";
// $db="idealab_database";
// $mysqli=new mysqli($host,$dbuser,$dbpass,$db);

// echo("Jana");
// exit;
$dbuser="root";
$dbpass="";
$host="localhost";
$db="lanandan_idealab";
$mysqli=new mysqli($host,$dbuser,$dbpass,$db);
if($mysqli)
{
echo "connected";
}
else
{
    
    echo "error";exit;
    
}
?>