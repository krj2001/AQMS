<?php
ini_set('display_errors',"1");
ini_set('memory_limit', '8056M'); //   8056
$dbuser="idealab_Aqms";
$dbpass="Aq1sw2de3fr4@aqms";
$host="localhost";
$db="idealab_database";
$con=new mysqli($host,$dbuser,$dbpass,$db);
$sql="SELECT * FROM `segregatedValues`";

if ($result=mysqli_query($con,$sql))
{
    // Return the number of rows in result set
    $rowcount=mysqli_num_rows($result);
    echo $rowcount;
    if($rowcount > 1000){
        $cnt = $rowcount - 1000;
        $deleteSql = "Delete FROM segregatedValues order by id ASC limit ".$cnt;
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

$sql="SELECT * FROM `segregatedValuesRDL`";

if ($result=mysqli_query($con,$sql))
{
    // Return the number of rows in result set
    $rowcount=mysqli_num_rows($result);
    echo $rowcount;
    if($rowcount > 1000){
        $cnt = $rowcount - 1000;
        $deleteSql = "Delete FROM segregatedValuesRDL order by id ASC limit ".$cnt;
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