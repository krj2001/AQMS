<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\EmpUserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\FacilitiesController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\FloorController;
use App\Http\Controllers\LabDepartmentController;
use App\Http\Controllers\DataController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\SensorCategoryController;
use App\Http\Controllers\SensorController;
use App\Http\Controllers\SensorUnitController;
use App\Http\Controllers\ConfigSetupController;
use App\Http\Controllers\DeviceConfigSetupController;
use App\Http\Controllers\AqiChartConfigValuesController;
use App\Http\Controllers\BumpTestResultController;
use App\Http\Controllers\CalibrationTestResultController;
use App\Http\Controllers\SampledSensorDataDetailsController;
use App\Http\Controllers\AlertCronController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\DeviceConfigController;
use App\Http\Controllers\AqmiJsonDataController;
use App\Http\Controllers\ApplicationVersionController;
use App\Http\Controllers\GasCylinderController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\AidealabCompanyController;
use App\Http\Controllers\DeviceDataController;
use App\Http\Controllers\ReportControllerTEST;
use App\Http\Controllers\CalibrationReportController;
use App\Http\Controllers\LocationLogController;
use App\Http\Controllers\EventLogController;
use App\Http\Controllers\HooterController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => ['web']], function () {
    
});

#php artisan make:model Facilities -c -m -r


Route::post('register', [AuthController::class,'register']);
Route::post('login', [AuthController::class, 'login']);

Route::get('block',function(){
    $response=[
         "message" => "Unable to access the page, Token Expired"
    ];
    return response($response, 401);
})->name('block');

Route::middleware(['auth:sanctum'])->group(function () {       

    //Authentication routes
    Route::post('sendOtp', [Authcontroller::class, 'sendOtp']);
    Route::post('requestToken', [AuthController::class, 'requestToken']);
    Route::post('resetUserPassword', [AuthController::class, 'resetUserPassword']);
    Route::post('blockedUserPasswordAutogenerate', [AuthController::class, 'blockedUserPasswordAutogenerate']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('updateNotification', [AuthController::class, 'updateNotification']); //23-03-2023

    
    //Company employee users
    Route::post('empuser/add', [EmpUserController::class, 'store']);
    Route::get('empuser', [EmpUserController::class, 'index']);
    Route::get('empuser/{id}/show', [EmpUserController::class, 'show']);//work in progress
    Route::post('empuser/{id}/update', [EmpUserController::class, 'update']);//work in progress
    Route::post('empuser/{id}/delete', [EmpUserController::class, 'destroy']);
    
    //Roles
    Route::post('role/add', [RoleController::class, 'store']);
    Route::get('role', [RoleController::class, 'index']);
    Route::get('role/{id}/show', [RoleController::class, 'show']);
    Route::post('role/{id}/update', [RoleController::class, 'update']);
    Route::delete('role/{id}/delete', [RoleController::class, 'destroy']);

    //Customers
    Route::post('customer/add', [CustomerController::class, 'store']);
    Route::post('customer/{id}/update', [CustomerController::class, 'update']);
    Route::post('customer/{id}/delete', [CustomerController::class, 'destroy']);
    //Route::get('customers', [CustomerController::class, 'customerCustomData']); 

    //locations   
    Route::post('location/add', [LocationController::class, 'store']);
    //Route::get('location', [LocationController::class, 'index']);
    Route::post('location/{id}/update', [LocationController::class, 'update']);
    Route::delete('location/{id}/delete', [LocationController::class, 'destroy']);    

    
    
    //branches
    Route::post('branch/add', [BranchController::class, 'store']);
    //Route::get('branch', [BranchController::class, 'index']);
    Route::post('branch/{id}/update', [BranchController::class, 'update']);
    Route::delete('branch/{id}/delete', [BranchController::class, 'destroy']);

    //facility
    Route::post('facility/add', [FacilitiesController::class, 'store']);
    //Route::get('facility', [FacilitiesController::class, 'index']);
    Route::post('facility/{id}/update', [FacilitiesController::class, 'update']);
    Route::delete('facility/{id}/delete', [FacilitiesController::class, 'destroy']);

    //buildings
    Route::post('building/add', [BuildingController::class, 'store']);
    //Route::get('building', [BuildingController::class, 'index']);
    Route::post('building/{id}/update', [BuildingController::class, 'update']);
    Route::delete('building/{id}/delete', [BuildingController::class, 'destroy']);

    //floors
    Route::post('floor/add', [FloorController::class, 'store']);
    //Route::get('floor', [FloorController::class, 'index']);
    Route::post('floor/{id}/update', [FloorController::class, 'update']);
    Route::delete('floor/{id}/delete', [FloorController::class, 'destroy']);

    //department
    Route::post('labDepartment/add', [LabDepartmentController::class, 'store']);
    //Route::get('labDepartment', [LabDepartmentController::class, 'index']);
    Route::post('labDepartment/{id}/update', [LabDepartmentController::class, 'update']);
    Route::delete('labDepartment/{id}/delete', [LabDepartmentController::class, 'destroy']);
    
    
    //Route::post('search', [DataController::class, 'search']); //navigation api

    //vendor
    Route::post('vendor/add',[VendorController::class,'store']);
    Route::post('vendor/{id}/update',[VendorController::class,'update']);
    Route::delete('vendor/{id}/delete',[VendorController::class,'destroy']);
    Route::get('vendor', [VendorController::class, 'vendorCustomData']);
    
    //devicecategory
    Route::post('category/add',[CategoriesController::class,'store']);
    Route::post('category/{id}/update',[CategoriesController::class,'update']);
    Route::delete('category/{id}/delete',[CategoriesController::class,'destroy']);
    //Route::get('category', [CategoriesController::class, 'index']);
    
    //device
    Route::post('device/add',[DeviceController::class,'store']);
    Route::post('device/{id}/update',[DeviceController::class,'update1']);
    Route::delete('device/{id}/delete',[DeviceController::class,'destroy']);
    Route::post('deviceMode/{id}/update',[DeviceController::class,'updateDeviceMode']);
    //Route::get('device', [DeviceController::class, 'index']);
    
    //sensorCategory
    Route::post('sensorCategory/add',[SensorCategoryController::class,'store']);
    Route::post('sensorCategory/{id}/update',[SensorCategoryController::class,'update']);
    Route::delete('sensorCategory/{id}/delete',[SensorCategoryController::class,'destroy']);
    Route::get('sensorCategoryUnitsDisplay/{id}', [SensorCategoryController::class, 'sensorCategoryUnitsDisplay']);  
    
    //sensor
   // Route::POST('sensor', [SensorController::class, 'index']); 
    Route::post('sensor/add',[SensorController::class,'store']);
    Route::post('sensor/{id}/update',[SensorController::class,'update']);
    Route::delete('sensor/{id}/delete',[SensorController::class,'destroy']);
    Route::get('deviceDeployedSensors/{id}', [SensorController::class, 'deviceDeployedSensors']); 
    // Route::get('bumptestDeviceDeployedSensors/{id}', [SensorController::class, 'bumptestDeviceDeployedSensors']); 
    Route::post('sensorProperties/{id}/update', [SensorController::class, 'sensorPropertiesUpdate']);
    
    //sensorUnit
    Route::get('sensorUnit/{id}', [SensorUnitController::class, 'index']);
    //Route::get('sensorUnit', [SensorUnitController::class, 'getData']);
    Route::post('sensorUnit/add',[SensorUnitController::class,'store']);
    Route::post('sensorUnit/{id}/update',[SensorUnitController::class,'update']);
    Route::post('stel/{id}/update',[SensorUnitController::class,'StelTwd']);
    Route::delete('sensorUnit/{id}/delete',[SensorUnitController::class,'destroy']);
    
    //Config setup
    Route::get('configSetup', [ConfigSetupController::class, 'index']); 
    Route::post('configSetup/add',[ConfigSetupController::class,'store']);
    Route::post('configSetup/{id}/update',[ConfigSetupController::class,'update']);
    Route::delete('configSetup/{id}/delete',[ConfigSetupController::class,'destroy']);
    

    //configSetup
    Route::post('DeviceConfigSetup/add',[DeviceConfigSetupController::class,'DeviceConfigAddOrUpdate']);
    Route::get('DeviceConfigSetup/{id}/getDeviceConfigData',[DeviceConfigSetupController::class,'getDeviceConfigData']);
    
    Route::post('stel/{id}/update',[SensorUnitController::class,'StelTwd']);
    
    Route::post('AqiChart/add', [AqiChartConfigValuesController::class, 'store']);
    Route::get('AqiChart', [AqiChartConfigValuesController::class, 'index']);
    
    // Route::post('bumpTestResult/add',[BumpTestResultController::class,'store']);
    Route::post('bumpTestResult', [BumpTestResultController::class, 'index']); 
    
    // Route::post('calibrationTestResult/add',[CalibrationTestResultController::class,'store']);
    Route::post('calibrationTestResult', [CalibrationTestResultController::class, 'index']); 
    
    Route::post('aqmiValues', [SampledSensorDataDetailsController::class, 'index']);
    
    Route::post('userListDetails', [AuthController::class, 'userListDetails']);
    
    Route::post('sendMessage', [AuthController::class, 'sendMessage']);
    
    Route::post('userLog', [AuthController::class, 'UserLogDetails']);
    
    // Route::post('alertData', [AlertCronController::class, 'showAlertNew']);

    // Route::post('alertDataUpdate', [AlertCronController::class, 'update']);
 
    Route::get('gasCylinder', [GasCylinderController::class, 'index']);

    
    
});
Route::post('alertData', [AlertCronController::class, 'showAlertNewV2']);
Route::post('alertDataNew', [AlertCronController::class, 'showAlertNewV2']);
Route::post('bumpTestResult/add',[BumpTestResultController::class,'store']);
Route::post('DeviceDebugConfigData',[DeviceConfigSetupController::class,'deviceDebugModeData']);

Route::post('calibrationTestResult/add',[CalibrationTestResultController::class,'store']);
Route::post('configurationStatus', [DeviceConfigController::class, 'configurationStatus']);

Route::post('configurationProcessStatus', [DeviceConfigController::class, 'configurationProcessStatus']);

Route::post('alertDataUpdate', [AlertCronController::class, 'update']);

Route::post('alertDataUpdateNew', [AlertCronController::class, 'updateNew']);

Route::post('AqmiPushData', [AqmiJsonDataController::class, 'store']); 

Route::get('bumptestDeviceDeployedSensors/{id}', [SensorController::class, 'bumptestDeviceDeployedSensors']); 

Route::post('labHooterRelay', [LabDepartmentController::class, 'updateLabHooter']);



Route::post('getDetailedAlerts', [AlertController::class, 'getAlertDetailedData']);

//CONFIGURING THE DEVICE
Route::post('configDevice', [DeviceConfigController::class, 'configDevice']);


//USED FOR QUERY WRITING
Route::post('query', [BumpTestResultController::class, 'sql']); 

//CURRENTLY FOR TESTING VALUE IT IS KEPT OUTSIDE AUTHENTICATION, AFTER COMPLETION NEED TO SHIFT TO AUTHENTICATION IN THE TOP   
Route::post('aqmiSensorValues', [SampledSensorDataDetailsController::class, 'show']);
Route::post('aqmiDeviceSensorValues', [SampledSensorDataDetailsController::class, 'deviceSensorShow']);
Route::post('lastSampledValues', [SampledSensorDataDetailsController::class, 'lastSampledData']);
Route::post('sensorTagIdData', [SampledSensorDataDetailsController::class, 'getLastSampledDataOfSensorTagIdBarLine']);
Route::post('sensorTagIdDataNew', [SampledSensorDataDetailsController::class, 'getLastSampledDataOfSensorTagIdBarLineNew']);
//Route::post('sensorTagIdGraphData', [SampledSensorDataDetailsController::class, 'getLastSampledDataOfSensorTagIdBarLine']); commented used for sensorTagIdData route
Route::post('lastUpdatedData', [SampledSensorDataDetailsController::class, 'liveDataDeviceIdTest']); 
Route::post('lastUpdatedDataNEW', [SampledSensorDataDetailsController::class, 'liveDataDeviceIdTest']);

Route::post('/getAlerts', [AlertController::class, 'getAlertDataNew']);
Route::post('/getAlertNew', [AlertController::class, 'getAlertDataNew']);
Route::post('deviceAlert', [AlertController::class, 'deviceAlert']);

Route::post('/updateCustomerSettings', [CustomerController::class, 'updateCustomerSettings']);


Route::get('AqiChart/add', [AqiChartConfigValuesController::class, 'store']);
Route::get('AqiChart', [AqiChartConfigValuesController::class, 'index']);
Route::get('aqmi', [AqmiJsonDataController::class, 'index']);


//NO USED APIS
Route::get('sensorTag', [SensorController::class, 'getSensorTagData']); 
Route::post('/uploadFile', [CustomerController::class, 'uploadImageFile']); 
Route::post('searchDevice', [DataController::class, 'searchDevice']);




    /** Prajwal Reports api begin startDate 16-06-2022 */
    
    //CURRENTLY FOR TESTING VALUE IT IS KEPT OUTSIDE AUTHENTICATION, AFTER COMPLETION NEED TO SHIFT TO AUTHENTICATION IN THE TOP
    
    Route::post('appVersion/add', [ApplicationVersionController::class, 'store']);
    Route::post('appVersion/{id}/update',[ApplicationVersionController::class,'update']);
    Route::delete('appVersion/{id}/delete', [ApplicationVersionController::class, 'destroy']);


    
    Route::post('SiteDeviceReport', [ReportController::class, 'SiteDeviceReport']);

    
    // Route::get('fetchComputeAqmoAqi', [ReportController::class, 'fetchComputeAqmoAqi']);
    Route::post('testLabHooterRealay', [LabDepartmentController::class, 'testLabHooterRealay']);
    
    
    // gas cylinder
    Route::post('gasCylinder/add',[GasCylinderController::class,'store']);
    Route::post('gasCylinder/{id}/update',[GasCylinderController::class,'update']);
    // Route::get('gasCylinder', [GasCylinderController::class, 'index']);
    Route::delete('gasCylinder/{id}/delete', [GasCylinderController::class, 'destroy']);
    
    

    Route::post('emailTemplate/update',[EmailTemplateController::class,'emailTemplate']);
    Route::get('emailTemplate', [EmailTemplateController::class, 'emailTemplateFetch']);
    Route::post('aidealabCompany/update',[AidealabCompanyController::class,'update']);
    
    Route::post('searchDeviceData', [DeviceDataController::class, 'searchDeviceData']); 

// reports api
      Route::post('DeviceAqiReport', [ReportController::class, 'DeviceAqiReport']);  //query incurrect //company code query
      Route::post('sensorStatusReport', [ReportController::class, 'SensorStatusReport']);
      Route::post('alarmReport', [ReportController::class, 'alarmReport']); 
      Route::post('SensorLog', [ReportController::class, 'SensorLog']); 
      Route::post('serverUsage', [ReportController::class, 'serverUsageReport']);
      Route::get('appVersion', [ApplicationVersionController::class, 'index']);
      Route::post('FirmwareVersionReport', [ReportController::class, 'FirmwareVersionReport']);
      Route::post('reportBumpTest', [ReportController::class, 'reportBumpTest']); 
      Route::post('hardwareVersionLogsReports', [ReportController::class, 'DeviceModelLogsReports']);


// downloads api
      Route::get('aqiReportExport', [ReportController::class, 'ExportAqiReport']); 
      Route::get('sensorStatusReportExport', [ReportController::class, 'sensorStatusReportExport']);
      Route::get('exportAlarm', [ReportController::class, 'exportAlarm']);
      Route::get('exportSensorLogCsv', [ReportController::class, 'exportSensorLog']);
      Route::get('serverUtiliExport', [ReportController::class, 'ServerUtilizationExport']); 
      Route::get('appVersionExport', [ApplicationVersionController::class, 'ApplicationVersionExport']);
      Route::get('firmwareExport', [ReportController::class, 'FirmwareVersionExport']);
      Route::get('exportBumpTestCsv', [ReportController::class, 'exportBumpTest']);
      Route::get('exportHardwareVersionLogs', [ReportController::class, 'ExportDeviceModelLog']);


// email send option
      Route::get('alarmReportMailExcelFile', [ReportController::class, 'alarmReportExcelFile']);
      Route::get('emailSensorStatusReport', [ReportController::class, 'EmailsensorStatusReport']);
      Route::get('aqiReportMailExcelFile', [ReportController::class, 'AqiReportMailExcelFile']);
      Route::get('emailDeviceLog', [ReportController::class, 'emailDeviceLog']);
      Route::get('emailServerUtilization', [ReportController::class, 'EmailServerUtilization']); 
      Route::get('emailApplicationVersion', [ApplicationVersionController::class, 'emailApplicationVersion']);
      Route::get('emailFirmwareVersion', [ReportController::class, 'EmailFirmwareVersion']);
      Route::get('emailBumpTest', [ReportController::class, 'emailBumpTest']);
      Route::get('emailHardwareVersionLog', [ReportController::class, 'EmailDeviceModelLog']);

      
     




      
      


         
//  unused api
//    Route::get('exportAqiStatusReport', [ReportController::class, 'exportAqiStatusReport'])->middleware(CheckReportHeaders::class);
    Route::get('export', [RoleController::class, 'export']);




//commented so that it can be accessed by SANTHOSH SIR
Route::get('customers', [CustomerController::class, 'customerCustomData']); 
Route::get('location', [LocationController::class, 'index']);
Route::get('branch', [BranchController::class, 'index']);
Route::get('facility', [FacilitiesController::class, 'index']);
Route::get('building', [BuildingController::class, 'index']);
Route::get('floor', [FloorController::class, 'index']);
Route::get('labDepartment', [LabDepartmentController::class, 'index']);
Route::post('search', [DataController::class, 'search']);
Route::get('category', [CategoriesController::class, 'index']);
Route::get('device', [DeviceController::class, 'index']);
Route::get('sensorCategory', [SensorCategoryController::class, 'index']);
Route::POST('sensor', [SensorController::class, 'index']); 
Route::get('sensorUnit', [SensorUnitController::class, 'getData']);
Route::post('getLocationDetails', [CalibrationTestResultController::class, 'testUsers']);


// api's done by vaishak
Route::post('getAqiReport', [SampledSensorDataDetailsController::class, 'getDeviceAqiGraph']);
Route::post('test', [SampledSensorDataDetailsController::class, 'test']);

Route::post('getUrl', [DeviceController::class, 'getUrlSpit']);
Route::post('testApi', [DataController::class, 'testApi']);

Route::post('testReport', [ReportControllerTEST::class, 'AqiReportMailExcelFile']);
Route::post('sendEmail',[BumpTestResultController::class,'sendEmail']);

Route::post('calibrationReport',[CalibrationReportController::class,'report']);
Route::get('calibrationReport/download',[CalibrationReportController::class,'export']);
Route::post('calibrationReport/email',[CalibrationReportController::class,'email']);
Route::post('getUrl',[CalibrationReportController::class,'getUrl']);

Route::post('getSensorUnit',[SensorController::class,'getSensorUnit']);
Route::get('getSensorRefValues/{id}',[SensorController::class,'getSensorRefValues']);
Route::post('updateDeviceMode',[BumpTestResultController::class,'updateDeviceMode']);
    
// Location details logs
Route::post('getFirst',[LocationLogController::class,'get']);

// Event Logs
Route::post('addLog',[EventLogController::class,'addLog']);
Route::post('showEventLog',[EventLogController::class,'showEventLog']);
Route::get('eventLogExport',[EventLogController::class,'eventLogExport']);
Route::post('eventLogMail',[EventLogController::class,'eventLogMail']);
Route::post('insertFirmwareLog',[EventLogController::class,'insertFirmwareLog']);
Route::post('locationDetails',[EventLogController::class,'locationDetails']);

Route::post('navigateAlarm',[SensorController::class,'navigateAlarm']);

Route::post('hooter/add',[HooterController::class,'store']);
Route::post('hooterStatus',[HooterController::class,'index']);




