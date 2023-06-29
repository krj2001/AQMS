<?php

			include('includes/config1.php');
			error_reporting(E_ERROR | E_PARSE);
			$pname=$_GET["mname"];
			$curdate=date('Y-m-d');

if($pname=="Shearing Machine")
{
$uptmetable="lvd_json_table";
$atr="LVD";


}
else if($pname=="Punching Machine")
{
$uptmetable="mk360_json_table";
$atr="EMK36";

}
else if($pname=="RG35")
{

$uptmetable="rg35_json_table";
$atr="RG-35";

}
else if($pname=="RG80")
{
$uptmetable="rg80_json_table";
$atr="RG-80";

}
else if($pname=="RG100")
{
$atr="RG-100";

$uptmetable="rg100_json_table";

}




            $query3="select * from lvd_json_table where j_data LIKE '%LVD%' and j_data LIKE '%DI_WEIGH_SCALE%' order by id desc limit 1";
           

            $res3=mysqli_query($con,$query3) or die(mysqli_error($con));
           
            while($row3=mysqli_fetch_array($res3))
            {
               
                $data=$row3['j_data'];
               
                $json_obj=json_decode($data,true);
          

                $scrap=$json_obj['D1'];

                //echo $kw ;               
              
            }
echo $scrap;
 ?>