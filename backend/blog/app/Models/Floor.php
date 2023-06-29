<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Floor extends Model
{
    use HasFactory;
    protected $table="floors";
    protected $fillable = [
        'companyCode',
        'location_id',
        'branch_id',
        'fecility_id',
        'building_id',
        'floorStage',
        'floorName',
        'floorMap',
        'floorCords',
    ];
}
