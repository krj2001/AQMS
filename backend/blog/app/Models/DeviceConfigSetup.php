<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceConfigSetup extends Model
{
    use HasFactory;
    protected $table = "device_config_setups";
    protected $fillable = [
        'companyCode',
        'device_Id',
        'deviceName',
        'accessType',
        'accessPointName',
        'ssId',
        'accessPointPassword',
        
        'accessPointNameSecondary',
        'ssIdSecondary',
        'accessPointPasswordSecondary',

        'ftpAccountName',
        'userName',
        'ftpPassword',
        'port',
        'serverUrl',
        'folderPath',

        'serviceProvider',
        'apn',
    ];

}
