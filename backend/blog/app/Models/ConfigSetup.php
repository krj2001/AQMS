<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigSetup extends Model
{
    use HasFactory;
    protected $table = "config_setups";
    protected $fillable = [
        'companyCode',
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
