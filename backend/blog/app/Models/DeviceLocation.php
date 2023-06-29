<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceLocation extends Model
{
    use HasFactory;
    protected  $table = "device_locations";
    protected $fillable = [
        'companyCode',
        'location_id',
        'branch_id',
        'facility_id',
        'building_id',
        'floor_id',
        'lab_id',
        'category_id',
        'device_id',
        'deviceName',
        'description',
        'assetTag',
        'macAddress',
        'deviceIcon',
        'floorCords'
    ];
}
