<?php
ini_set('memory_limit', '1024M');
$dbuser="idealab_Aqms";
$dbpass="Aq1sw2de3fr4@aqms";
$host="localhost";
$db="idealab_database";
$con=new mysqli($host,$dbuser,$dbpass,$db);
$sql="SELECT * FROM aqmi_json_data";

if ($result=mysqli_query($con,$sql))
{
    // Return the number of rows in result set
    $rowcount=mysqli_num_rows($result);
    if($rowcount > 1000){
        $cnt = $rowcount - 1000;
        $deleteSql = "Delete FROM `aqmi_json_data` order by id ASC limit ".$cnt;
        $deleteSqlResult = mysqli_query($con,$deleteSql);
        if($deleteSqlResult){
            echo "deleted data";
        }else{
            echo "Not delete dataa";
        }
    }else{
            echo "Not delete dataa";
    }
    // Free result set
}


$sql="SELECT * FROM aqmi_json_dataRDL";

if ($result=mysqli_query($con,$sql))
{
    // Return the number of rows in result set
    $rowcount=mysqli_num_rows($result);
    if($rowcount > 1000){
        $cnt = $rowcount - 1000;
        $deleteSql = "Delete FROM `aqmi_json_dataRDL` order by id ASC limit ".$cnt;
        $deleteSqlResult = mysqli_query($con,$deleteSql);
        if($deleteSqlResult){
            echo "deleted data";
        }else{
            echo "Not delete dataa";
        }
    }else{
            echo "Not delete dataa";
    }
    // Free result set
}




?>