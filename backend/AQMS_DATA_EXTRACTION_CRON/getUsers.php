<?php

include("includes/config.php");
date_default_timezone_set('Asia/Kolkata');
$locationListParameters = array();

//SELECT * FROM `sensors` WHERE id = 46
$sql = "SELECT * FROM `sensors` WHERE id = '$sensorId'";
echo $sql;

$result=mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli));
$cnt = mysqli_num_rows($result);
if($cnt>0){
    while($row = mysqli_fetch_assoc($result)){
        $locationListParameters["loc_id"] = $row['location_id'];
        $locationListParameters["bra_id"] = $row['branch_id'];
        $locationListParameters["fac_id"] = $row['facility_id'];
        $locationListParameters["bui_id"] = $row['building_id'];
        $locationListParameters["flr_id"] = $row['floor_id'];
        $locationListParameters["lab_id"] = $row['lab_id'];
       
    }
}else{
    
}

// $data = getUsers($mysqli,$companyCode,$locationListParameters);
// $cnt = count($data);
// for($i=0;$i<$cnt;$i++){
//     echo $data[$i]["email"]." ".$data[$i]["name"]."<br>";
// }

function getUsers($mysqli,$companyCode,$locationListParameters){
    print_r($locationListParameters);
    $listArray = array();
    $userArray = array();
    $sql="SELECT * FROM `users` where empNotification = 1  and changePassword = 0 and sec_level_auth = 0 and companyCode='$companyCode'";
    $result=mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli));
    if(mysqli_num_rows($result) >0){
        while($row= mysqli_fetch_assoc($result)){  
            $email = $row["email"];
            $location_id = $row["location_id"];
            $branch_id = $row["branch_id"]; 
            $facility_id = $row["facility_id"]; 
            $building_id = $row["building_id"]; 
            $floor_id = $row["floor_id"]; 
            $lab_id = $row["lab_id"];
            
            echo "Email: ".$row["email"]."  Location: ".$location_id." Branch: ".$branch_id." Facility: ".$facility_id." Building: ".$building_id." Floor: ".$floor_id."Lab: ".$lab_id."<br><br>";
            
            if($location_id == $locationListParameters['loc_id'] &&  $branch_id == $locationListParameters['bra_id'] && $facility_id == $locationListParameters['fac_id'] &&  $building_id == $locationListParameters['bui_id'] && $floor_id == $locationListParameters['flr_id'] && $lab_id == $locationListParameters['lab_id']){
              //lab   
              $userArray["email"] = $row["email"];
              $userArray["name"] = $row["name"];
              $listArray[] = $userArray;
            }else if($location_id == $locationListParameters['loc_id'] &&  $branch_id == $locationListParameters['bra_id'] && $facility_id == $locationListParameters['fac_id'] &&  $building_id == $locationListParameters['bui_id'] && $floor_id == $locationListParameters['flr_id'] && $lab_id == ""){
              //floor 
              $userArray["email"] = $row["email"];
              $userArray["name"] = $row["name"];
              $listArray[] = $userArray;
            }else if($location_id == $locationListParameters['loc_id'] &&  $branch_id == $locationListParameters['bra_id'] && $facility_id == $locationListParameters['fac_id'] &&  $building_id == $locationListParameters['bui_id'] && $floor_id == "" && $lab_id == ""){
                //building
                $userArray["email"] = $row["email"];
                $userArray["name"] = $row["name"];
                $listArray[] = $userArray;
            }else if($location_id == $locationListParameters['loc_id'] &&  $branch_id == $locationListParameters['bra_id'] && $facility_id == $locationListParameters['fac_id'] &&  $building_id == "" && $floor_id == "" && $lab_id == ""){
                //fac
                 $userArray["email"] = $row["email"];
                  $userArray["name"] = $row["name"];
                  $listArray[] = $userArray;
            }else if($location_id === $locationListParameters['loc_id'] &&  $branch_id === $locationListParameters['bra_id'] && $facility_id == "" &&  $building_id == "" && $floor_id == "" && $lab_id == ""){
                //bra
                echo "Email: ".$row["email"]."  Location: ".$location_id." Branch: ".$branch_id." Facility: ".$facility_id." Building: ".$building_id." Floor: ".$floor_id."Lab: ".$lab_id."<br><br>";
                $userArray["email"] = $row["email"];
                  $userArray["name"] = $row["name"];
                  $listArray[] = $userArray;
            }else if($location_id == $locationListParameters['loc_id'] &&  $branch_id == "" && $facility_id == "" &&  $building_id == "" && $floor_id == "" && $lab_id == ""){
                //loc
                 $userArray["email"] = $row["email"];
                  $userArray["name"] = $row["name"];
                  $listArray[] = $userArray;
            }else if($location_id == "" &&  $branch_id == "" && $facility_id == "" &&  $building_id == "" && $floor_id == "" && $lab_id == ""){
                //all empty
                $userArray["email"] = $row["email"];
                $userArray["name"] = $row["name"];
                $listArray[] = $userArray;
            }
        }
    }else{
        return 0;
    }
    return $listArray;
}






?>