<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AqiChartConfigValues extends Model
{
    use HasFactory;
    protected $table="aqi_chart_config_values";
    protected $fillable = [
            'aqiTemplateName',
            'aqiGoodMinScale',
            'aqiGoodMaxScale',
            'aqiSatisfactoryMinScale',
            'aqiSatisfactoryMaxScale',
            'aqiModerateMinScale',
            'aqiModerateMaxScale',
            'aqiPoorMinScale',
            'aqiPoorMaxScale',
            'aqiVeryPoorMinScale',
            'aqiVeryPoorMaxScale',
            'aqiSevereMinScale',
            'aqiSevereMaxScale'
    ];

}
