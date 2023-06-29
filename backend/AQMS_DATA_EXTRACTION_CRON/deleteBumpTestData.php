<?php

// ini_set('memory_limit', '4096M'); //possible
ini_set('memory_limit', '1024M');
$dbuser="idealab_Aqms";
$dbpass="Aq1sw2de3fr4@aqms";
$host="localhost";
$db="idealab_database";
$con=new mysqli($host,$dbuser,$dbpass,$db);
$sql="SELECT * FROM segregatedBumptestValues";

if($result=mysqli_query($con,$sql))
{
    // Return the number of rows in result set
    $rowcount=mysqli_num_rows($result);
    if($rowcount > 1000){
        // $cnt = $rowcount - 10;
        echo $rowcount;
        $deleteSql = "Delete FROM segregatedBumptestValues order by id ASC limit ".$rowcount;
        echo $deleteSql;
        $deleteSqlResult = mysqli_query($con,$deleteSql) or die(mysqli_error($con));
        if($deleteSqlResult){
            echo "deleted data";
        }else{
            echo "Not delete dataa";
        }
    }else{
            echo "Not delete data";
    }
    // Free result set
}




?>