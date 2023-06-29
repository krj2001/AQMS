<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SensorLimitChangeLog extends Model
{
    use HasFactory;
    protected $table = "sensor_limit_change_logs";
    protected $fillable = [
        'companyCode',
        'device_id',
        'sensor_id',
        'criticalMinValue',
        'criticalMaxValue',
        'warningMinValue',
        'warningMaxValue',
        'outofrangeMinValue',
        'outofrangeMaxValue',
        'email'
    ];
    
}
