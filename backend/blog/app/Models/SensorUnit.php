<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SensorUnit extends Model
{
    use HasFactory;
    protected $table = "sensor_units";
    protected $fillable = [
        'companyCode',
        'sensorCategoryId',
        'sensorCategoryName',
        'sensorName',
        'manufacturer',
        'partId',
        'sensorOutput',
        'sensorType',
        'units',
        
        
        /**columns used in sensor table begin */

        'minRatedReading',
        'minRatedReadingChecked',
        'minRatedReadingScale',
        'maxRatedReading',
        'maxRatedReadingChecked',
        'maxRatedReadingScale',       

        /**columns used in sensor table end */

        'slaveId',
        'registerId',
        'length',
        'registerType',
        'conversionType',
        'ipAddress',
        'subnetMask',  
        


        /**columns used in sensor table begin */


        //EXCEPT ALERT MESSAGE IN SENSOR BEGIN

        'criticalMinValue',
        'criticalMaxValue',
       

        'warningMinValue',
        'warningMaxValue',
        

        'outofrangeMinValue',
        'outofrangeMaxValue',
       

        //EXCEPT ALERT MESSAGE IN SENSOR END 

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
        
        /**columns used in sensor table end */
        
        'bumpTestRequired'
        
        
        
        
    ];
}
