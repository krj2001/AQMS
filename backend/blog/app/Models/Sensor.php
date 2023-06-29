<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sensor extends Model
{
    use HasFactory;
    protected $table = "sensors";
    
    
    protected $fillable = [
        'companyCode',
        'location_id',
        'branch_id',   
        'facility_id', 
        'building_id',
        'floor_id',
        'lab_id',
                            
      
        
        'categoryId',//1
        'deviceCategory',//AQMI
        
        'sensorCategoryId',//1
        'sensorCategoryName',//Temperature
        
        'deviceId',//34
        'deviceName',//AQMI-007

        'sensorName',//2
        'sensorNameUnit',//h20
        
        'sensorOutput',
        'sensorType',
        'sensorTag',
        
        'registerId',
        'registerType',
        'slaveId',
        
        'subnetMask',
        'units',
        'ipAddress',
        'length',

        'maxRatedReading',
        'maxRatedReadingChecked',
        'maxRatedReadingScale',
        'minRatedReading',
        'minRatedReadingChecked',
        'minRatedReadingScale',
        
        'pollingIntervalType',
        
        'criticalMinValue',
        'criticalMaxValue',
        'criticalAlertType',
        'criticalLowAlert',
        'criticalHighAlert',
        
        
        'warningMinValue',
        'warningMaxValue',
        'warningAlertType',
        'warningLowAlert',
        'warningHighAlert',
        
        
        'outofrangeMinValue',
        'outofrangeMaxValue',
        'outofrangeAlertType',
        'outofrangeLowAlert',
        'outofrangeHighAlert',
        
        'digitalAlertType',
        'digitalLowAlert',
        'digitalHighAlert',
        
        'isStel',
        'stelDuration',
        'stelType',
        'stelLimit',
        'stelAlert',
        
        'twaDuration',
        'twaType',
        'twaLimit',
        'twaAlert',

        'alarm',
        'unLatchDuration',

        'isAQI',
        'parmGoodMinScale',
        'parmGoodMaxScale',
        
        'parmSatisfactoryMinScale',
        'parmSatisfactoryMaxScale',

        'parmModerateMinScale',
        'parmModerateMaxScale',

        'parmPoorMinScale',
        'parmPoorMaxScale',

        'parmVeryPoorMinScale',
        'parmVeryPoorMaxScale',

        'parmSevereMinScale',
        'parmSevereMaxScale',  

        'relayOutput',
        'sensorFault',
        
        'sensorStatus',
        'notificationStatus',
        
        'hooterRelayStatus',
        'audioDecibelLevel',
        'criticalRefMinValue',
        'criticalRefMaxValue',
        'warningRefMinValue',
        'warningRefMaxValue',
        'outofrangeRefMinValue',
        'outofrangeRefMaxValue',

    ];
    
  








   
    
}
