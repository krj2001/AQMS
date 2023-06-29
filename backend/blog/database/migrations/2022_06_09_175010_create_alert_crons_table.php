<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlertCronsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alert_crons', function (Blueprint $table) {
            $table->id();
            $table->text('a_date')->nullable();             
            $table->text('a_time')->nullable();   
            $table->text('companyCode')->nullable();   
            $table->text('deviceId')->nullable();   
            $table->text('sensorId')->nullable();   
            $table->text('sensorTag')->nullable();   
            $table->text('alertType')->nullable();   
            $table->text('value')->nullable();
            $table->text('msg')->nullable();   
            $table->text('severity')->nullable();
            $table->text('statusMessage')->nullable();
            $table->text('status')->nullable();
            $table->text('alarmType')->nullable();
            $table->text('alertStandardMessage')->nullable();
            $table->text('alertTriggeredDuration')->nullable();
            $table->string('alertCategory')->nullable();    
            $table->string('triggeredAlertFlag')->nullable();
             $table->string('timeDurations')->nullable();   
            $table->text('Reason')->nullable();
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
        Schema::dropIfExists('alert_crons');
    }
}
