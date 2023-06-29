<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;
    protected $table = "devices";
    protected $fillable = [
        'companyCode', 
        'location_id',
        'branch_id',
        'facility_id',
        'building_id',
        'floor_id',
        'floorCords',
        'lab_id',
        
        'deviceName', 
        'category_id',
        'firmwareVersion',
        'macAddress',
        'deviceImage',
        'deviceTag',
        'nonPollingPriority',
        'pollingPriority',
        
        'deviceMode',
        'binFileName',
        'firmwareStatus',
        'configurationStatus',        
        
        'xAxisTimeInterval',
        
        'disconnectedStatus'
    ];
}
