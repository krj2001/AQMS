<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAqiChartConfigValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aqi_chart_config_values', function (Blueprint $table) {
            $table->id();
            $table->text('aqiTemplateName')->nullable();             
            $table->text('aqiGoodMinScale')->nullable();
            $table->text('aqiGoodMaxScale')->nullable();
            $table->text('aqiSatisfactoryMinScale')->nullable();
            $table->text('aqiSatisfactoryMaxScale')->nullable();
            $table->text('aqiModerateMinScale')->nullable();
            $table->text('aqiModerateMaxScale')->nullable();
            $table->text('aqiPoorMinScale')->nullable();
            $table->text('aqiPoorMaxScale')->nullable();
            $table->text('aqiVeryPoorMinScale')->nullable();
            $table->text('aqiVeryPoorMaxScale')->nullable();
            $table->text('aqiSevereMinScale')->nullable();
            $table->text('aqiSevereMaxScale')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('aqi_chart_config_values');
    }
}
