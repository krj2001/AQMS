<?php
session_start();
include("config.php");
if(isset($_SESSION['role'])){
    $role = $_SESSION['role'];
}


?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Production Monitoring System</title>
    <link rel="stylesheet" href="files/bootstrap.min.css">
    <link rel="stylesheet" href="<?php include('includes/config.php');

                                                $ret='select * from theme';
                                                $stmt = $mysqli->query($ret);
                                                if($rows = $stmt->fetch_assoc())
                                                {
                                                    $num=0;
                                                 $num=$rows['file'];
                                                 
                                                }
                                                echo $num; ?>">
    <link rel="stylesheet" href="files/font-awesome.min.css">
    <link rel="stylesheet" href="files/css/font-awesome.min.css">
    <script src="files/jquery-3.5.1.js"></script>
    <script src="files/popper.min.js"></script>
    <link rel="stylesheet" href="files/dataTables.bootstrap4.min.css"/>
    <script src="files/jquery.dataTables.min.js"></script>
    <script src="files/dataTables.bootstrap4.min.js"></script> 
    <script src="files/animate.min.css"></script>  
    <script src="files/bootstrap.min.js"></script>
    <script src="css/a076d05399.js"></script>
    <script src="files/PieSeries.min.js" integrity="sha256-PIgNE6kFbCi/eB57z8KB75dr3hy6cxqS/Exi4Are5cQ=" crossorigin="anonymous"></script>
    <script type="text/javascript" src="files/loader.js"></script>
         

    <!--<script src="keyboard.js"></script> -->
    <link href="jquery-ui.min.css" rel="stylesheet">
    <!-- <script src="jquery-latest-slim.min.js"></script>-->
    <!--<link href="keyboard.css" rel="stylesheet">-->
    <!--<script src="jquery.keyboard.js"></script>-->
    <script src="demo.js"></script>
 

    <script type="text/javascript">
        $(document).ready(function(){
            // Get current page URL
            var url = window.location.href;
            
             // remove # from URL
            url = url.substring(0, (url.indexOf("#") == -1) ? url.length : url.indexOf("#"));
            
             // remove parameters from URL
            url = url.substring(0, (url.indexOf("?") == -1) ? url.length : url.indexOf("?"));
            
             // select file name
            url = url.substr(url.lastIndexOf("/") + 1);
             
             // If file name not avilable
            if(url == ''){
                url = 'analytics.html';
            }
             
             // Loop all menu items
            $('.components li').each(function(){
            
                //select href
                var href = $(this).find('a').attr('href');
                  // Check filename
                if(url == href){
                   // Add active class
                   $(this).addClass('active');
                }
            });
            $("#sidebar1").hide();
            $("#sidebar").show();
            $("#menu1").click(function() {
                $("#sidebar1").hide();
                $("#sidebar").show();
               // $("#col").toggleClass("fa-bars");
               // $("#col").toggleClass("fa-arrows-alt-h");
            });
            $("#menu2").click(function() {
                $("#sidebar").hide();
                $("#sidebar1").show();
                
                // $("#col").toggleClass("fa-bars");
                //$("#col").toggleClass("fa-arrows-alt-h");
            });
        });
        function toggleFullScreen() {
        //alert("heloo");
          if ((document.fullScreenElement && document.fullScreenElement !== null) ||    
           (!document.mozFullScreen && !document.webkitIsFullScreen)) {
            if (document.documentElement.requestFullScreen) {  
              document.documentElement.requestFullScreen(); 
              $(".customer_table_show").css({"margin-top": "-50px"});
            } else if (document.documentElement.mozRequestFullScreen) {  
              document.documentElement.mozRequestFullScreen();  
            } else if (document.documentElement.webkitRequestFullScreen) {  
              document.documentElement.webkitRequestFullScreen(Element.ALLOW_KEYBOARD_INPUT);  
            }  
          }
          else{  
            if (document.cancelFullScreen) {  
              document.cancelFullScreen();  
            } else if (document.mozCancelFullScreen) {  
              document.mozCancelFullScreen();  
            } else if (document.webkitCancelFullScreen) {  
              document.webkitCancelFullScreen();  
            }  
          }  
        }
    </script>
</head>

<body>
    <div class="wrapper">
        <!-- Sidebar  -->
        <nav id="sidebar" >
            <!--<div class="sidebar-header">
                <h3>Bootstrap Sidebar</h3>
            </div>-->
            <?php if($role == "Admin"){ ?>
                <ul class="list-unstyled components">
                    <!-- <button onclick="toggleFullScreen()" style="margin-left:8px;"><i class='fas fa-expand'></i></button>  -->
                   <img src="">
                   <li onclick="toggleFullScreen()" title="Full Screen"><a href="#" ><i class='fas fa-expand'></i></a></li>  
                   <li title="Main Dashboard"><a href="building_management.php"><i class="fas fa-pie-chart" aria-hidden="true"></i></a></li>
                   <!--<li title="Energy Monitoring"><a href="building_management.php"><i class="fa fa-bolt"></i></a></li>-->
                   <!--<li title="Energy Monitoring"><a href="energy_monitoring_u.php"><i class="fa fa-bolt"></i></a></li>-->
                   <!--<li><a href="preventive_maintance.php"><i class="fas fa-microchip"></i></a></li>-->
                   <!--<li><a href="main_dashboard.php"><i class="fas fa-clock"></i></a></li>
                   <li><a href="upload_map.php"><i class="fa fa-atom"></i></a></li>
                   <li><a href="add_machine_map.php"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i></a></li> -->
                   <!--   <li><a href="dashboard.php"><i class="fa fa-building"></i></a></li> -->
                   <!--<li title="Load Schedule"><a href="load_schedule.php"><i class="fa fa-tasks"></i></a></li>-->
                   <li><a href="floor_map.php"><i class="fa fa-map"></i></a></li>
                   <!-- <li><a href="setcommand.php"><i class="fa fa-terminal"></i></a></li> -->
                   <!--<li><a href="devicemanager.php"><i class="fa fa-microchip"></i></a></li>-->
                   <!--<li><a href="building_management.php"><i class="fa fa-cog"></i></a></li>-->
                   <!--<li><a href="assets.php"><i class="fas fa-building"></i></a></li>-->
                   <li><a href="devices.php"><i class="fa fa-wifi"></i></a></li>
                   <!--<li><a href="user_tab.php"><i class="fa fa-user"></i></a></li>-->
                   <!-- <li><a href="tab3.php"><i class="fa fa-microchip"></i></a></li>
                   <li><a href="amc_add.php"><i class="fa fa-cog"></i></a></li>-->
                   <li><a href="userresetpassword.php"><i class="fas fa-key"></i></a></li>
                   <!--<li title="Reports"><a href="energy_consumption_report.php"><i class="fa fa-file"></i></a></li>-->
                   <!--<li title="Reports"><a href="machine_report_generator.php"><i class="fa fa-file"></i></a></li>-->
                   <li title="Logout"><a href="logout.php"><i class="fas fa-sign-out-alt"></i></a></li>
                   <!--<li><a href="fuel_topup_report.php"><i class="fa fa-file"></i></a></li>-->
                   <!--<li><a href="user_dashboard.php"><i class="fa fa-wrench"></i> -->
                   <!--   <li><a href="tracer.php"><i class="fa fa-map-marker"></i>Tracer</a></li>
                   <li><a href="view.php"><i class="fa fa-map-marker"></i>Testing</a></li>
                   <!------------------------------------->
                </ul>
            <?php }
             if($role == "Customer"){
            ?>
            <ul class="list-unstyled components">
                   <!-- <button onclick="toggleFullScreen()" style="margin-left:8px;"><i class='fas fa-expand'></i></button>  -->
                   <img src="">
                   <li onclick="toggleFullScreen()" title="Full Screen"><a href="#" ><i class='fas fa-expand'></i></a></li>  
                   <li><a href="building_management.php"><i class="fas fa-pie-chart"></i></a></li>
                   
                    <li><a href="user_tab.php"><i class="fa fa-user"></i></a></li>
                     <li><a href="assets.php"><i class="fas fa-building"></i></a></li>
                   <li><a href="devices.php"><i class="fa fa-wifi"></i></a></li>
                    <li title="Reports"><a href="machine_report_generator.php"><i class="fa fa-file"></i></a></li>
                     <li title="Logout"><a href="logout.php"><i class="fas fa-sign-out-alt"></i></a></li>
                </ul>
            <?php } ?>
            <?php if($role == "Building_user"){
            ?>
            <ul class="list-unstyled components">
                   <!-- <button onclick="toggleFullScreen()" style="margin-left:8px;"><i class='fas fa-expand'></i></button>  -->
                   <img src="">
                   <li onclick="toggleFullScreen()" title="Full Screen"><a href="#" ><i class='fas fa-expand'></i></a></li>  
                   <li><a href="building_management.php"><i class="fas fa-pie-chart"></i></a></li>
                    <li title="Logout"><a href="logout.php"><i class="fas fa-sign-out-alt"></i></a></li>
                </ul>
            <?php } ?>
        </nav>
   
        
        <div id="content">
            <!--<nav class="navbar navbar-expand-lg" id="header" style="background-color: #5d5d5d;display:none;">-->
            <!--    <div class="container-fluid">-->
                     
            <!--        <button type="button" id="sidebarCollapse1" class="btn btn-default">-->
            <!--            <i class='fas fa-arrows-alt-h' id="col" style='font-size:30px'></i>-->
            <!--        </button> -->
            <!--         <button class="btn btn-dark d-inline-block d-lg-none ml-auto" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">-->
            <!--            <i class="fas fa-align-justify"></i>-->
            <!--        </button> -->

            <!--        <div class="collapse navbar-collapse" id="navbarSupportedContent">-->
            <!--            <ul class="nav navbar-nav ml-auto">-->
            <!--                <li class="nav-item active">-->
            <!--                    <a class="nav-link" href="#">Page</a>-->
            <!--                </li>-->
            <!--                <li class="nav-item">-->
            <!--                    <a class="nav-link" href="#">Page</a>-->
            <!--                </li>-->
            <!--                <li class="nav-item">-->
            <!--                    <a class="nav-link" href="#">Page</a>-->
            <!--                </li>-->
            <!--                <li class="nav-item">-->
            <!--                    <a class="nav-link" href="#">Page</a>-->
            <!--                </li>-->

            <!--                <li class="nav-item">-->
            <!--                    <div class="row" style="margin-left:-40px;"></div>-->
                                
                                
            <!--                    <a class="nav-link" href="index.php" style="color: #00FFFF">Logout</a>-->
            <!--                </li>-->
            <!--                <li class="nav-item">-->
            <!--                    <a class="nav-link" href="#">Page</a>-->
            <!--                </li>-->
            <!--            </ul>-->
            <!--        </div>-->
            <!--    </div>-->
            <!--</nav>-->
            <style type="text/css">
                .bclr{
                    /*color: rgba(51, 153, 255, 1.0);*/
                    color: rgba(120,120,120, 1.0);
                }
                table{
                     text-align: center;
                }
            </style>
      