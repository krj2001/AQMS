<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GasCylinder extends Model
{
    use HasFactory;
    protected $table="gas_cylinders";
    protected $fillable = [
        'companyCode',
        'expiryDate',
        'gasCylinderName',        
    ];

}
