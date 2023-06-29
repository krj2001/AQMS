<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Building extends Model
{
    use HasFactory;
    protected $table="buildings";
    protected $fillable = [
        'companyCode',
        'location_id',
        'branch_id',
        'fecility_id',
        'buildingName',
        'coordinates',
        'buildingDescription',
        'buildingImg',
        'buildingTag',
    ];
}
