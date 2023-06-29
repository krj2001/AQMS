<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AqmiJsonData extends Model
{
    use HasFactory;
    protected $table="aqmi_json_data";
    protected $fillable = [
        'date_time',
        'j_data'      
    ];
}
