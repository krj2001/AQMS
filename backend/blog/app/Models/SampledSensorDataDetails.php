<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SampledSensorDataDetails extends Model
{
    use HasFactory;
    protected $table = "sampled_sensor_data_details";
    protected $fillable = [
        'sensor_id',
        'parameterName',
        'last_val',
        'max_val',
        'min_val',
        'avg_val',
        'sample_date_time',
        'time_stamp',
        'param_unit'
    ];
}
