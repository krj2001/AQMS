<html lang="en">
<head>
   <title>AQMS DATA SIMULATION</title>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <!--<meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">-->
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  
  
   <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    

<?php



include("includes/config.php");

$aqmi_parameters=array("PM10","PM2.5","SO2","NH3","CO","O3","NO2","Pb");
date_default_timezone_set('Asia/Kolkata');
//$date=date("Y-m-d");
function clean($string) {
   $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.   
   return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
}
// echo("AQMS".$aqmi_parameters);
// exit;

// var_dump($aqmi_parameters);
?>

<script>

//var uploadInterval=0.5;


//     console.log(url);
function uploadData(aqmis)
{
    console.log(aqmis);
    // console.log("started");
    var aqmi = aqmis;
    var url="<?php echo $base_url;  ?>"+"/RDLAQMSSIMULATOR/upload_dataNew.php";
    console.log(url);

    $.ajax({
        method:"POST",
        url: url,
        data:{
            DEVICE_ID:aqmi
        },
        success: function(result){
        console.log(result);
        
        //alert('uploaded');
        //$("#msg_id").html("Uploading...");
    }});
}

var parameterList="";
$(document).ready(function()
{
    function createParameterUI(parameter,index,parameterTag)  
    {
          var param_ui_html_str="<div class=\"row my-4\">"+
          "<div class=\"col-sm-1 col-md-1 col-lg-1\">"+
          "<label>"+parameter+"</label>"+
          "</div>"+
          "<div class=\"col-sm-1 col-md-1 col-lg-1\">"+
          "<label>ID</label>"+
          "</div>"+
          "<div class=\"col-sm-2 col-md-2 col-lg-2\">"+
          "<input type=\"text\" class=\"form-control\"  value=\""+parameterTag+"\" name=\""+parameter+"\" id=\"par_id_"+index+"\"></input>"+
          "</div>"+
          "<div class=\"col-sm-2 col-md-2 col-lg-2\">"+
          "<label>Value</label></div>"+
          "<div class=\"col-sm-2 col-md-2 col-lg-2\">"+
          "<input type=\"number\" class=\"form-control "+ parameter+"\" step=\"0.001\" name=\""+parameter+"\" id=\""+index+"_v\"></input>"+
          "</div>"+
          "<div class=\"col-sm-1 col-md-1 col-lg-1\">"+
          "<input type=\"checkbox\" class=\"form-control\"  name=\"params[]\" id=\""+index+"_ch\"></input>"+
          "</div>"+
          "<div class=\"col-sm-1 col-md-1 col-lg-1\">"+
          "<input type=\"checkbox\"  class=\"form-control faulty_sensor_1\"  name=\"params2[]\" id=\""+index+"_ch_fl\"></input>"+
          "</div>"+
          "</div>";
          
            //data-par=\""+parameter+"\"
            // console.log("AQMS"+param_ui_html_str);
            return param_ui_html_str;
    }
    
    function loadParameter(url,data_Obj,tokenId,parID,companyCode)
    {
        $.ajax({
            url:url, 
            type:'POST',
            processData: false,
            contentType: 'application/json',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('Authorization', 'Bearer t-'+tokenId);
               // if(companyCode!="")
               // {
                xhr.setRequestHeader('CompanyCode', companyCode);
                //}
            },
            data:JSON.stringify(data_Obj),
            dataType:"json",
            success: function (response) {
                var str="";
                if(parID==1)
                {
                    str="<option>SELECT CUSTOMER</option>";
                    for(var i=0;i<response.data.length;i++)
                    {
                       str=str+"<option value='"+response.data[i].customerId+"'>"+response.data[i].customerName+"</option>";
                    }
                    $("#company").html(str);                
                    
                }
                
                if(parID==2)
                {
                    str="<option>SELECT LOCATION</option>";
                    for(var i=0;i<response.data.length;i++)
                    {
                       str=str+"<option value='"+response.data[i].id+"'>"+response.data[i].stateName+"</option>";
                    }
                  
                    $("#location").html(str);
                }
                 
                if(parID==3)
                {
                    str="<option>SELECT BRANCH</option>";
                    
                    for(var i=0;i<response.data.length;i++)
                    {
                       str=str+"<option value='"+response.data[i].id+"'>"+response.data[i].branchName+"</option>";
                    }
                  
                    $("#branch").html(str);
                }
                 
                if(parID==4)
                { 
                    str="<option>SELECT FACILITY</option>";
                    for(var i=0;i<response.data.length;i++)
                    {
                       str=str+"<option value='"+response.data[i].id+"'>"+response.data[i].facilityName+"</option>";
                    }
                    $("#facility").html(str);
                }
                
                if(parID==5)
                {
                    str="<option>SELECT BUILDING</option>";
                    for(var i=0;i<response.data.length;i++)
                    {
                        str=str+"<option value='"+response.data[i].id+"'>"+response.data[i].buildingName+"</option>";
                    }
                    $("#building").html(str);
                  
                }
                if(parID==6)
                {
                    str="<option>SELECT FLOOR</option>";
                    for(var i=0;i<response.data.length;i++)
                    {
                        str=str+"<option value='"+response.data[i].id+"'>"+response.data[i].floorName+"</option>";
                    }
                    $("#floor").html(str);
                  
                }
                
                if(parID==7)
                {
                    str="<option>SELECT LAB</option>";
                    for(var i=0;i<response.data.length;i++)
                    {
                        str=str+"<option value='"+response.data[i].id+"'>"+response.data[i].labDepName+"</option>";
                    }
                    $("#lab").html(str);
                }
                 
                if(parID==8)
                {
                    str="<option>SELECT DEVICE</option>";
                    for(var i=0;i<response.data.length;i++)
                    {
                        str=str+"<option value='"+response.data[i].id+"'>"+response.data[i].deviceName+"</option>";
                    }
                    $("#aqmi").html(str);
                }
                 
                if(parID==9)
                {
                     
                    /* alert(response.Digital.data.length);
                    alert(response.Analog.data.length);*/
                    //  str="<option>SELECT DEVICE</option>";
                    /*for(var i=0;i<response.Digital.data.length;i++)         
                    {
                      alert(response.Digital.data[i].sensorTag);
                      alert(JSON.stringify(response.Digital.data[i]));
                      // str=str+"<option value='"+response.data[i].deviceName+"'>"+response.data[i].deviceName+"</option>";
                    }*/
                  
                    var html_str="";
                    parameterList="";
                  
                    for(var i=0;i<response.Analog.data.length;i++)
                    {
                        if(i<response.Analog.data.length-1)
                        {
                           parameterList=parameterList+response.Analog.data[i].sensorNameUnit+",";
                        }
                        else
                        {
                            parameterList=parameterList+response.Analog.data[i].sensorNameUnit;
                        }
                        html_str=html_str+ createParameterUI(response.Analog.data[i].sensorNameUnit,i,response.Analog.data[i].sensorTag);
                    }
                 
                    //param_sec_id_err
                 
                    $("#param_input_ui_id").html(html_str);
                    $("#param_sec_id").show();
                    $("#param_sec_id_err").hide();
                    if(html_str=="")
                    {
                        html_str="<h4 style=\"color:red\">NO  Sensors deployed for this device....!</h4>";
                        $("#param_sec_id").hide();
                        $("#param_sec_id_err").show();
                        $("#param_sec_id_err").html(html_str);
                     
                    }
                    
                    //  $("#aqmi").html(str);
                  
                }
            },
            error: function (msg) { 
                alert(msg.Message);
            },
        });
    }
     
    function loadParameter_GET(url,data_Obj,tokenId,parID)
    {
        $.ajax({
        url:url, 
        type:'GET',
        beforeSend: function (xhr) {
            xhr.setRequestHeader('Authorization', 'Bearer t-'+tokenId);
         },
        data:data_Obj,
        dataType:"json",
        success: function (response) {
            
            var str="";
            if(parID==1)
            {
                 
                str="<option>SELECT CUSTOMER</option>";
                
                for(var i=0;i<response.data.length;i++)
                {
                      
                       str=str+"<option value='"+response.data[i].customerId+"'>"+response.data[i].customerName+"</option>";
                }
                 
                $("#company").html(str);
              
            }
        }});
    }
     
    
    
    function loadCompany()
    {
        
        var api="<?php echo $base_url;  ?>"+"/aideaLabs/api/customers";
        // console.log("Hi"+api);
        return;
        var dataObj={};
        var jsonObj={email:'abhishekshenoy7@gmail.com',password:'123456'};
        var str=JSON.stringify(jsonObj);
        // console.log("Hi"+ str)
        var loginApi="<?php echo $base_url;  ?>"+"/aideaLabs/api/login";

        $.ajax({
        url:loginApi, 
        data:JSON.stringify(jsonObj),
        type:'POST',
        dataType:'json',
        processData: false,
        contentType: 'application/json',
        success:function(result){
           //alert(result.user_token);
           loadParameter_GET(api,dataObj,result.user_token,1)
         }
        });
        
    }
    
    function loadlocation(dataObj)
    {
        var api="<?php echo $base_url;  ?>"+"/aideaLabs/api/search";
        var companyId = $("#company").val();
        //alert(companyId);
        var jsonObj={email:'abhishekshenoy7@gmail.com',password:'123456'};
        var str=JSON.stringify(jsonObj);
        var loginApi="<?php echo $base_url;  ?>"+"/aideaLabs/api/login";
        $.ajax({
        url:loginApi, 
        data:JSON.stringify(jsonObj),
        type:'POST',
        dataType:'json',
        processData: false,
        contentType: 'application/json',
        success:function(result){
           //alert(result.user_token);
           loadParameter(api,dataObj,result.user_token,2,companyId);
        }});
    }
    
    function loadBranch(dataObj)
    {
        var api="<?php echo $base_url;  ?>"+"/aideaLabs/api/search";
        var companyId = $("#company").val();
        //alert(companyId);
        var jsonObj={email:'abhishekshenoy7@gmail.com',password:'123456'};
        var str=JSON.stringify(jsonObj);
        var loginApi="<?php echo $base_url;  ?>"+"/aideaLabs/api/login";
        $.ajax({
        url:loginApi, 
        data:JSON.stringify(jsonObj),
        type:'POST',
        dataType:'json',
        processData: false,
        contentType: 'application/json',
        success:function(result){
           //lert(result.user_token);
           loadParameter(api,dataObj,result.user_token,3,companyId);
        }});
    }
    
    function loadFacility(dataObj)
    {
        var api="<?php echo $base_url;  ?>"+"/aideaLabs/api/search";
        var companyId = $("#company").val();
        //alert(companyId);
        var jsonObj={email:'abhishekshenoy7@gmail.com',password:'123456'};
        var str=JSON.stringify(jsonObj);
        var loginApi="<?php echo $base_url;  ?>"+"/aideaLabs/api/login";
        $.ajax({
        url:loginApi, 
        data:JSON.stringify(jsonObj),
        type:'POST',
        dataType:'json',
        processData: false,
        contentType: 'application/json',
        success:function(result){
           //alert(result.user_token);
           loadParameter(api,dataObj,result.user_token,4,companyId);
        }});
    }
    
    function loadBuilding(dataObj)
    {
        var api="<?php echo $base_url;  ?>"+"/aideaLabs/api/search";
        var companyId = $("#company").val();
        //alert(companyId);
        var jsonObj={email:'abhishekshenoy7@gmail.com',password:'123456'};
        var str=JSON.stringify(jsonObj);
        var loginApi="<?php echo $base_url;  ?>"+"/aideaLabs/api/login";
        $.ajax({
        url:loginApi, 
        data:JSON.stringify(jsonObj),
        type:'POST',
        dataType:'json',
        processData: false,
        contentType: 'application/json',
        success:function(result){
           //alert(result.user_token);
           loadParameter(api,dataObj,result.user_token,5,companyId);
        }});
    }
    
    function loadFloor(dataObj)
    {
        var api="<?php echo $base_url;  ?>"+"/aideaLabs/api/search";
        var companyId = $("#company").val();
        //alert(companyId);
        var jsonObj={email:'abhishekshenoy7@gmail.com',password:'123456'};
        var str=JSON.stringify(jsonObj);
        var loginApi="<?php echo $base_url;  ?>"+"/aideaLabs/api/login";
        $.ajax({
        url:loginApi, 
        data:JSON.stringify(jsonObj),
        type:'POST',
        dataType:'json',
        processData: false,
        contentType: 'application/json',
        success:function(result){
           //alert(result.user_token);
           loadParameter(api,dataObj,result.user_token,6,companyId);
        }});
    }
    
    function loadLab(dataObj)
    {
        var api="<?php echo $base_url;  ?>"+"/aideaLabs/api/search";
        var companyId = $("#company").val();
        //alert(companyId);
        var jsonObj={email:'abhishekshenoy7@gmail.com',password:'123456'};
        var str=JSON.stringify(jsonObj);
        var loginApi="<?php echo $base_url;  ?>"+"/aideaLabs/api/login";
        $.ajax({
        url:loginApi, 
        data:JSON.stringify(jsonObj),
        type:'POST',
        dataType:'json',
        processData: false,
        contentType: 'application/json',
        success:function(result){
           //alert(result.user_token);
           loadParameter(api,dataObj,result.user_token,7,companyId);
        }});
    }
    
    function loadDevice(dataObj)
    {
        var api="<?php echo $base_url;  ?>"+"/aideaLabs/api/search";
        var companyId = $("#company").val();
        //alert(companyId);
        var jsonObj={email:'abhishekshenoy7@gmail.com',password:'123456'};
        var str=JSON.stringify(jsonObj);
        var loginApi="<?php echo $base_url;  ?>"+"/aideaLabs/api/login";
        $.ajax({
        url:loginApi, 
        data:JSON.stringify(jsonObj),
        type:'POST',
        dataType:'json',
        processData: false,
        contentType: 'application/json',
        success:function(result){
           //alert(result.user_token);
           loadParameter(api,dataObj,result.user_token,8,companyId);
        }});
    }
    
    function loadDeviceParameters(dataObj)
    {
        var api="<?php echo $base_url;  ?>"+"/aideaLabs/api/search";
        var companyId = $("#company").val();
        //alert(companyId);
        var jsonObj={email:'abhishekshenoy7@gmail.com',password:'123456'};
        var str=JSON.stringify(jsonObj);
        var loginApi="<?php echo $base_url;  ?>"+"/aideaLabs/api/login";
        $.ajax({
        url:loginApi, 
        data:JSON.stringify(jsonObj),
        type:'POST',
        dataType:'json',
        processData: false,
        contentType: 'application/json',
        success:function(result){
           //alert(result.user_token);
           loadParameter(api,dataObj,result.user_token,9,companyId);
        }});
    }
    
    loadCompany();
    
    $("#dev_params").hide();
    var upload_data_set_interval_id=-99;
    function updateTime()
    {
        var url="<?php echo $base_url;  ?>"+"/AQMS SIMULATOR/get_param_conc_for_aqi_new.php?get_date_time=1";
        $.ajax({url: url,
            dataType:'html',
            success: function(result){
                $("#date_id").html(result);
            }
        });
    }
    
    setInterval(updateTime,1000);
    
    function getUploadInterval(aqmi)
    {
        // alert(aqmi);
        var aqmis=$("#aqmi").val();
        
        var operation = $("#operation").val();
        
       
           // alert("device:"+aqmis);
            // var url="http://localhost/AQMS SIMULATOR/get_param_conc_for_aqi_new.php?get_upload_interval=1&aqmi="+aqmi;
            var url="<?php echo $base_url;  ?>"+"/AQMS SIMULATOR/get_param_conc_for_aqi_new.php?get_upload_interval=1&aqmi="+aqmis; //changes by abhishek 9-28-22
            $.ajax({
                 url: url,
                dataType:'html',
                success: function(result){
                    var ui=result.split(":")[1];
                    // alert("up:"+ui+"nnn");
                    $("#upload_interval").val(ui);
                    var uploadInterval1=parseFloat(ui);
                    //alert(uploadInterval1);
                
                    if(uploadInterval1>0)
                    {
                        $("#dev_id_label").attr("style","color:green;font-weight:bold;");
                        //$("#msg_id").html("");
                        upload_data_set_interval_id=setInterval(uploadData,uploadInterval1*1000,aqmis);
                    }
                    else
                    {
                        if(upload_data_set_interval_id>0)
                        {
                            clearInterval(upload_data_set_interval_id);
                        }
                        $("#dev_id_label").attr("style","color:red;font-weight:bold;");
                    }
                }
            });  
       
        
       
    }
    //getUploadInterval();
    $("#param_div").hide();
    $("#aq_class_color_code").hide();

    $("#aqmi").on("change",function()
    {
        var building=$("#building").val();
        var floor=$("#floor").val();
        var lab=$("#lab").val();
        var aqmi=$("#aqmi").val();
        
        var aqmiLabel = $("#aqmi option:selected").html();
        
        $("#msg_id").html("");
        if((building!="")&& (floor!="") && (lab!="") && (aqmi!=""))
        {
            $("#param_div").show();
            $("#dev_params").show();
            $("#dev_id_label").html("DEVICE :"+aqmiLabel);
            //getUploadInterval(aqmi);
        }
        else
        {
             $("#param_div").hide();
             $("#dev_id_label").html("");
        }
    });

    $("#company").on("change",function()
    {
        var company=$("#company").val();
        //alert(company);
        var dataObj={};
        loadlocation(dataObj);
    });

    $(".faulty_sensor_1").on("click",function(){
        alert("jjjj"); 
      /* var par=$(this).attr("data-par");
       var par_id=$(this).attr("id");
        if($(this).prop("checked")==true)
        {
           // alert(par);
            if(par!="")
            {
                var url="http://localhost/AQMS SIMULATOR/get_param_conc_for_aqi_new.php?sm_invalid_par="+par;
    
                $.ajax({
                    url: url, 
                    dataType:'html',
                    success: function(result){
                        //alert(result);
                        var par_cls=par_id.split("_")[0];
                        $("#"+par_cls+"_v").val(result);
                        $("#"+par_cls+"_v").attr("style","color:red;");
                    }
                });
            }
        }
        else
        {
            getSensorData(par);
        }*/
    });
   

    $("#location").on("change",function()
    {
        var company=$("#company").val();
        var location=$("#location").val();
        //alert(company+" "+location);
        var dataObj={location_id:location};
        loadBranch(dataObj);
    });

    $("#branch").on("change",function()
    {
        var company=$("#company").val();
        var location=$("#location").val();
        var branch=$("#branch").val();
        //alert(company+" "+location);
        var dataObj={location_id:location,branch_id:branch};
        loadFacility(dataObj);
    });


    $("#facility").on("change",function()
    {
        var company=$("#company").val();
        var location=$("#location").val();
        var branch=$("#branch").val();
        var facility=$("#facility").val();
        //alert(company+" "+location);
        var dataObj={location_id:location,branch_id:branch,facility_id:facility};
        loadBuilding(dataObj);
    });


    $("#building").on("change",function()
    {
        var company=$("#company").val();
        var location=$("#location").val();
        var branch=$("#branch").val();
         var facility=$("#facility").val();
         var building=$("#building").val();
        //alert(company+" "+location);
        var dataObj={location_id:location,branch_id:branch,facility_id:facility,building_id:building};
        loadFloor(dataObj);
    });


    $("#floor").on("change",function()
    {
        var company=$("#company").val();
        var location=$("#location").val();
        var branch=$("#branch").val();
        var facility=$("#facility").val();
         var building=$("#building").val();
         var floor=$("#floor").val();
        //alert(company+" "+location);
        var dataObj={location_id:location,branch_id:branch,facility_id:facility,building_id:building,floor_id:floor};
        loadLab(dataObj);
    });


    $("#lab").on("change",function()
    {
        var company=$("#company").val();
        var location=$("#location").val();
        var branch=$("#branch").val();
        var facility=$("#facility").val();
        var building=$("#building").val();
        var floor=$("#floor").val();
        var lab=$("#lab").val();
        //alert(company+" "+location);
        var dataObj={location_id:location,branch_id:branch,facility_id:facility,building_id:building,floor_id:floor,lab_id:lab};
        loadDevice(dataObj);
    });

    $("#aqmi").on("change",function()
    {
        var company=$("#company").val();
        var location=$("#location").val();
        var branch=$("#branch").val();
        var facility=$("#facility").val();
        var building=$("#building").val();
        var floor=$("#floor").val();
        var lab=$("#lab").val();
        var deviceId=$("#aqmi").val();
        var dataObj={location_id:location,branch_id:branch,facility_id:facility,building_id:building,floor_id:floor,lab_id:lab,device_id:deviceId};
        loadDeviceParameters(dataObj);
    });

    $("#setData_btn").on("click",function()
    {
        $("#msg_id").html("");
        uploadInterval=$("#uploadInt").val();
        //var parameters=["PM10","PM2.5","SO2","NH3","CO","O3","NO2","Pb"];
        
        var parameters=parameterList.split(",");
        var customer = $("#company").val();
        var location = $("#location").val();
        var branch = $("#branch").val();
        var facility = $("#facility").val();
        var building=$("#building").val();
        var floor=$("#floor").val();
        var lab=$("#lab").val();
        var aqmi=$("#aqmi").val();
        var dev_mode=$("#dev_mode").val();
        var access_code= $("#access_code").val();
        
        if((aqmi=="")||(dev_mode=="")||(access_code==""))
        {
            alert("Please fill all the fields..!");
            return;
        }
        //alert(lab);
        for(var k=0;k<parameters.length;k++)
        {
            var parm=parameters[k];
            var val=$("#"+k+"_v").val();
            var id=$("#par_id_"+k).val();
            var act=0;
            //alert($("#"+k+"_ch").prop("checked"));
             
            if($("#"+k+"_ch").prop("checked"))
            {
                act=1;
            }
            var url="<?php echo $base_url;  ?>"+"/AQMS SIMULATOR/set_par_values.php?P1="+building+"&P2="+floor+"&P3="+lab+"&P4="+parm+"&P5="+val+"&P6="+id+"&P7="+act+"&P8="+aqmi+"&P9="+dev_mode+"&P10="+access_code+"&P11="+customer+"&P12="+location+"&P13="+branch+"&P14="+facility;
            //alert(url);
            $.ajax({
                url: url,
                success: function(result){
                    $("#msg_id").html(result);
                    $("#msg_id").attr("style","color:green;font-size:20px;font-weight:bold;");
                    //alert(result);
                }
            });
        }
    });


    $("#upload_int_set").on("click",function()
    {
        $("#msg_id").html("");
        uploadInterval=$("#upload_interval").val();
        //alert(uploadInterval);
        var aqmi=$("#aqmi").val();
        //alert(aqmi);
        
        
        var url="<?php echo $base_url;  ?>"+"/RDLAQMSSIMULATOR/stop_device.php";
        $.ajax({
            method:"POST",
            url: url, 
            data:{
               deviceName:aqmi,
               CHECK_SIMULATOR:"SIMULATOR1"
            },
            success: function(result){
                if(result == 0){
                    alert("Press stop and Click on SET again")
                }else{
                    $.ajax({
                        method:"POST",
                        url: url, 
                        data:{
                           deviceName:aqmi,
                           UPDATE_SIMULATOR:"SIMULATOR1"
                        },
                        success: function(result){
                            if(result == 1){
                                // alert("device started running");
                                var url="<?php echo $base_url;  ?>"+"/AQMS SIMULATOR/set_config_values.php?uploadInterval="+uploadInterval+"&aqmi="+aqmi;
                                 $.ajax({
                                    url: url, 
                                    success: function(result){
                                        getUploadInterval(uploadInterval);
                                         $("#msg_id").html(result);
                                         $("#msg_id").attr("style","color:green;font-size:20px;font-weight:bold;");
                                    }
                                 });
                            }else{
                                alert("SOMETHING WENT WRONG");
                            }
                        }
                    });
                }
            }
        });
         
        
    });
    
    $("#STOP_DEVICE").on("click",function()
    {
        var aqmi=$("#aqmi").val();
        var url="<?php echo $base_url;  ?>"+"/RDLAQMSSIMULATOR/stop_device.php";
         $.ajax({
            method:"POST",
            url: url, 
            data:{
               deviceName:aqmi,
               SIMULATOR_NAME:"SIMULATOR1"
            },
            success: function(result){
                alert(result);
                location.reload();
            }
         });
    });
    
    


    function getSensorData(par_name)
    {
        // var params= ["PM10","PM2.5","SO2","NH3","CO","O3","NO2","Pb"];
        var val=$("#pref_aqi").val();
        if(val!="")
        {
            var url="<?php echo $base_url;  ?>"+"/AQMS SIMULATOR/get_param_conc_for_aqi_new.php?sm_aqi_new="+val;
            $.ajax({
                url: url, 
                dataType:'json',
                success: function(result){
                    //alert(result);
                    $("#aq_class_id").html(result["AQI_CLASS"]);
                    //alert(result['AQI_COLOR_CODE']);
                    $("#aq_class_color_code").attr("style","background-color:"+result['AQI_COLOR_CODE']+";");
                    $("#aq_class_color_code").show();
                    var params=parameterList.split(",");
                    //alert(parameterList);
                    for(var k=0;k<params.length;k++)
                    {
                        var par=params[k];
                        if(par_name=='all')
                        {
                            //alert("par:"+par+" val:"+result[par]);
                            $("#"+k+"_v").val(result[par]);
                            $("#"+k+"_v").attr("style","color:black;");
                        }
                        else if(par_name==par)
                        {  
                          $("#"+k+"_v").val(result[par]);
                          $("#"+k+"_v").attr("style","color:black;");
                        }
                    }
                }
            });
        }
    }

    $("#pref_aqi").on("keyup mouseup",function()
    {
        $("#aq_class_color_code").hide();
        getSensorData('all');
    });


    $("#data_view").on("click",function()
    {
        var aqmi=$("#aqmi").val();
        window.open("dataloggerretrieve.php?device="+aqmi,"_blank");
    });


    $("#data_delete").on("click",function()
    {
        $("#msg_id").html("");
        var building=$("#building").val();
        var floor=$("#floor").val();
        var lab=$("#lab").val();
        var aqmi=$("#aqmi").val();
        var url="<?php echo $base_url;  ?>"+"/AQMS SIMULATOR/get_param_conc_for_aqi_new.php?clear_data_aqmi="+aqmi+"&building="+building+"&floor="+floor+"&lab="+lab;
         $.ajax({
            url: url, 
            dataType:'html',
            success: function(result){
                //alert(result);
                //$("#aq_class_id").html(result["AQI_CLASS"]);
                //alert(result['AQI_COLOR_CODE']);
                $("#msg_id").html(result);
                $("#msg_id").attr("style","color:green;font-size:20px;font-weight:bold;");
            }
         });
    });
});

</script>

<div class="container">
    <div class="row my-4">
        <div class="col-sm-12 col-md-12 col-lg-12 text-center">
            <div class="card my-4">
                <div class="card-header">AQMS DATA SIMULATOR <label id="date_id" style="color:rgba(18,172,174,64);font-weight:bold;font-size:18px;float:right"></label></div>
                <div class="card-body">
                    <div class="row my-4">
                        <div class="col-sm-1 col-md-1 col-lg-1 text-center">
                            <label>CUSTOMER</label>
                        </div>
                        <div class="col-sm-2 col-md-2 col-lg-2 form-group text-center">
                            <select class="form-control" name="company" id="company">
                               <!-- <option value="">SELECT COMPANY</option>
                                <option value="B1">BUILDING 1</option>
                                <option value="B2">BUILDING 2</option>
                                <option value="B3">BUILDING 3</option>
                                <option value="B4">BUILDING 4</option>-->
                            </select>
                        </div>
                        
                        <div class="col-sm-1 col-md-1 col-lg-1 text-center">
                            <label>LOCATION</label>
                        </div>
                        <div class="col-sm-2 col-md-2 col-lg-2 text-center">
                            <select class="form-control" name="location" id="location">
                                <option value="">SELECT LOCATION</option>
                                <option value="F1">FLOOR 1</option>
                                <option value="F2">FLOOR 2</option>
                                <option value="F3">FLOOR 3</option>
                                <option value="F4">FLOOR 4</option>
                            </select>
                        </div>
                            <div class="col-sm-1 col-md-1 col-lg-1 text-center">
                            <label>BRANCH</label>
                        </div>
                        <div class="col-sm-2 col-md-2 col-lg-2 text-center">
                            <select class="form-control" name="branch" id="branch">
                                 <option value="">SELECT BRANCH</option>
                                <option value="L1">LAB 1</option>
                                <option value="L2">LAB 2</option>
                                <option value="L3">LAB 3</option>
                                <option value="L4">LAB 4</option>
                            </select>
                        </div>
                        <div class="col-sm-1 col-md-1 col-lg-1 text-center">
                            <label>FACILITY</label>
                        </div>
                          <div class="col-sm-2 col-md-2 col-lg-2 text-center">
                            <select class="form-control" name="facility" id="facility">
                                 <option value="">SELECT FACILITY</option>
                                <!--<option value="AQMI_001">AQMI 1</option>-->
                                <!--<option value="AQMI_002">AQMI 2</option>-->
                                <!--<option value="AQMI_003">AQMI 3</option>-->
                                <!--<option value="AQMI_004">AQMI 4</option>-->
                                <!--<option value="AQMO_001">AQMO 1</option>-->
                                <!--<option value="AQMO_002">AQMO 2</option>-->
                                <!--<option value="AQMO_003">AQMO 3</option>-->
                                <!--<option value="AQMO_004">AQMO 4</option>-->
                            </select>
                        </div>
                    </div>
                    
                    <div class="row my-4">
                        <div class="col-sm-1 col-md-1 col-lg-1 text-center">
                            <label>BUILDING</label>
                        </div>
                        <div class="col-sm-2 col-md-2 col-lg-2 form-group text-center">
                            <select class="form-control" name="building" id="building">
                                <option value="">SELECT BUILDING</option>
                                <option value="B1">BUILDING 1</option>
                                <option value="B2">BUILDING 2</option>
                                <option value="B3">BUILDING 3</option>
                                <option value="B4">BUILDING 4</option>
                            </select>
                        </div>
                        <div class="col-sm-1 col-md-1 col-lg-1 text-center">
                            <label>FLOOR</label>
                        </div>
                        <div class="col-sm-2 col-md-2 col-lg-2 text-center">
                            <select class="form-control" name="floor" id="floor">
                                <option value="">SELECT FLOOR</option>
                                <option value="F1">FLOOR 1</option>
                                <option value="F2">FLOOR 2</option>
                                <option value="F3">FLOOR 3</option>
                                <option value="F4">FLOOR 4</option>
                            </select>
                        </div>
                            <div class="col-sm-1 col-md-1 col-lg-1 text-center">
                            <label>LAB</label>
                        </div>
                        <div class="col-sm-2 col-md-2 col-lg-2 text-center">
                            <select class="form-control" name="lab" id="lab">
                                 <option value="">SELECT LAB</option>
                                <option value="L1">LAB 1</option>
                                <option value="L2">LAB 2</option>
                                <option value="L3">LAB 3</option>
                                <option value="L4">LAB 4</option>
                            </select>
                        </div>
                        <div class="col-sm-1 col-md-1 col-lg-1 text-center">
                            <label>AQMx</label>
                        </div>
                          <div class="col-sm-2 col-md-2 col-lg-2 text-center">
                            <select class="form-control" name="aqmi" id="aqmi">
                                 <option value="">SELECT AQMI</option>
                                <!--<option value="AQMI_001">AQMI 1</option>-->
                                <!--<option value="AQMI_002">AQMI 2</option>-->
                                <!--<option value="AQMI_003">AQMI 3</option>-->
                                <!--<option value="AQMI_004">AQMI 4</option>-->
                                <!--<option value="AQMO_001">AQMO 1</option>-->
                                <!--<option value="AQMO_002">AQMO 2</option>-->
                                <!--<option value="AQMO_003">AQMO 3</option>-->
                                <!--<option value="AQMO_004">AQMO 4</option>-->
                            </select>
                        </div>
                    </div>
                    <div class="row my-4" id="dev_params">
                        <div class="col-sm-2 col-md-2 col-lg-2 text-center">
                                <label>ACCESS CODE</label>
                        </div>
                        <div class="col-sm-2 col-md-2 col-lg-2 text-center">
                            <input type="text" id="access_code" class="form-control"></input>
                        </div>
                        <div class="col-sm-2 col-md-2 col-lg-2 text-center">
                                <label>DEVICE MODE</label>
                        </div>
                        <div class="col-sm-3 col-md-3 col-lg-3 text-center">
                           <select class="form-control" name="dev_mode" id="dev_mode">
                                    <option value="">SELECT MODE</option>
                                    <option value="1">FIRMWARE UPGRADATION</option> 
                                    <option value="2">ENABLE</option>               
                                    <option value="3">DISABLE</option>
                                    <option value="4">BUMP TEST</option>
                                    
                                   
                            </select>
                        </div>
                         <!--   <div class="col-sm-1 col-md-1 col-lg-1 text-center">
                                    <label>ACCESS CODE</label>
                            </div>
                            <div class="col-sm-2 col-md-2 col-lg-2 text-center">
                                <input type="text" id="accesscode" class="form-control"></input>
                            </div>-->
                    </div>
                    <div class="row my-4">
                        <div class="col-sm-12 col-md-12 col-lg-12">
                            <div class="card" id="param_div">
                                <div class="card-header">AQMx PARAMETERS  <b></b><span style="float:right;"><label style="color:green;font-weight:bold;" id="dev_id_label"></label></b><a><i id="data_view" style="color:rgba(18,174,172,64);margin-left:15px;font-size:20px;" class="fa fa-eye" aria-hidden="true"></i></a><a><i id="data_delete" style="float:right;color:rgba(18,174,172,64);margin-left:45px;font-size:20px;" class="fa fa-trash" aria-hidden="true"></i></a></span></div>
                                    <div class="card-body" id="param_sec_id">
                                        <div class="row my-4">
                                            <div class="col-sm-8 col-md-8 col-lg-8">
                                                <div class="row my-4">
                                                    <div class="col-sm-7 col-md-7 col-lg-7">
                                                        
                                                        
                                                    </div>
                                                    <div class="col-sm-2 col-md-2 col-lg-2">
                                                        <label style="font-size:15px;">Enable/Disable</label>
                                                    </div>
                                                     <div class="col-sm-2 col-md-2 col-lg-2 text-center">
                                                        <label style="font-size:15px;">Faulty</label>
                                                     </div>
                                                </div>
                                                <div id="param_input_ui_id">
                                                      
                                                </div>
                                                <?php
                                                
                                                /*for($i=0;$i<count($aqmi_parameters);$i++)
                                                {
                                                    $gp=$aqmi_parameters[$i];
                                                    ?>
                                                        <div class="row my-4">
                                                            <div class="col-sm-1 col-md-1 col-lg-1">
                                                                <label><?php  echo $gp;?></label>
                                                         </div>
                                                         <div class="col-sm-1 col-md-1 col-lg-1">
                                                             <label>ID</label>
                                                             </div>
                                                              <div class="col-sm-2 col-md-2 col-lg-2">
                                                             <input type="text" class="form-control"  value="<?php  echo clean($gp);?>" name="<?php  echo $gp;?>" id="<?php  echo $i."_id";?>"></input>
                                                        </div>
                                                         <div class="col-sm-1 col-md-1 col-lg-1">
                                                             <label>VALUE</label>
                                                        </div>
                                                        <div class="col-sm-2 col-md-2 col-lg-2">
                                                             <input type="number" class="form-control <?php echo clean($gp);?>"  step="0.001" name="<?php  echo $gp;?>" id="<?php  echo $i."_v";?>"></input>
                                                        </div>
                                                        <div class="col-sm-2 col-md-2 col-lg-2">
                                                             <input type="checkbox" class="form-control"  name="params[]" id="<?php  echo $i."_ch";?>"></input>
                                                        </div>
                                                        <div class="col-sm-2 col-md-2 col-lg-2">
                                                             <input type="checkbox" data-par="<?php echo $gp;?>" class="form-control faulty_sensor"  name="params2[]" id="<?php  echo $i."_ch_fl";?>"></input>
                                                        </div>
                                                        </div>
                                                    <?php
                                                }*/
                                                ?>
                                            </div>
                        
                                            <div class="col-sm-4 col-md-4 col-lg-4 text-left">
                                                <div class="row">
                                                    <div class="col-sm-6 col-md-6 col-lg-6 text-left">
                                                         <label>PREFERED AQI:</label>
                                                    </div>
                                                    <div class="col-sm-6 col-md-6 col-lg-6 text-left">
                                                        <input type="number" class="form-control" step="0.001" name="pref_aqi" id="pref_aqi"></input>
                                                    </div>
                                                </div>
                                                <div class="row my-4">
                                                    <div class="col-sm-12 col-md-12 col-lg-12 text-center">
                                                        <div class="card">
                                                            <div class="card-body" id="aq_class_color_code" style="background-color:green;">
                                                                 <label id="aq_class_id" style="font-weight: bold;"></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row my-4">
                                                </div>
                                                <div class="row my-4">
                                                </div>
                                                <div class="row my-4">
                                                </div>
                                                <div class="row my-4">
                                                    <div class="col-sm-12 col-md-12 col-lg-12 text-center">
                                                         <label id="msg_id"></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class"row my-12">
                                            <div class="col-sm-4 col-md-4 col-lg-4 text-center">
                                               <button type="button" class="btn btn-primary" id="setData_btn">SET DATA</button>
                                            </div>
                                            
                                            
                                        </div>
                                        <div class"row my-4">
                                            
                                        </div>
                                        <div class="row my-4">
                                            <div class="col-sm-3 col-md-3 col-lg-3">
                                                <label>UPLOAD INTERVAL [sec]</label>
                                            </div>
                                            <div class="col-sm-3 col-md-3 col-lg-3">
                                             <input type="number" class="form-control"  name="upload_int" id="upload_interval"></input>
                                            </div>
                                            <div class="col-sm-1 col-md-1 col-lg-1">
                                                <button type="button" class="btn btn-primary" id="upload_int_set">SET</button>
                                            </div>
                                            <div class="col-sm-4 col-md-4 col-lg-4">
                                               <button type="button" class="btn btn-primary" id="STOP_DEVICE">STOP</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body" id="param_sec_id_err">
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>


