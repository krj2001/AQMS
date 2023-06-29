<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class labDepartment extends Model
{
    use HasFactory;
    protected $table="lab_departments";
    protected $fillable = [
        'companyCode',
        'location_id',
        'branch_id',
        'fecility_id',
        'building_id',
        'floor_id',
        'labDepName',
        'labDepMap',
        'labCords',
        'labHooterStatus'
    ];
}
