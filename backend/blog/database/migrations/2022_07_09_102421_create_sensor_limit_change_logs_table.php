<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSensorLimitChangeLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sensor_limit_change_logs', function (Blueprint $table) {
            $table->id();
            $table->string('companyCode')->nullable();
            $table->string('device_id')->nullable();            
            $table->string('sensor_id')->nullable();
            $table->text('criticalMinValue')->nullable();
            $table->text('criticalMaxValue')->nullable();
            $table->text('warningMinValue')->nullable();
            $table->text('warningMaxValue')->nullable();
            $table->text('outofrangeMinValue')->nullable();
            $table->text('outofrangeMaxValue')->nullable();
            $table->text('email')->nullable();
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
        Schema::dropIfExists('sensor_limit_change_logs');
    }
}
