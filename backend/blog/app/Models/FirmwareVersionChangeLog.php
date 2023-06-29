<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FirmwareVersionChangeLog extends Model
{
    use HasFactory;
     protected $table="firmware_version_reports";
    protected $fillable = [
        'companyCode',
        'deviceName',
        'device_id',
        'firmwareVersion',
        'hardwareVersion',
    ];
    
}
