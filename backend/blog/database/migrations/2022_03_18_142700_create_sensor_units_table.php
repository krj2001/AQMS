<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSensorUnitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sensor_units', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('sensorCategoryId')->unsigned();  
            $table->foreign('sensorCategoryId')->references('id')->on('sensor_categories')->onDelete('cascade');
            $table->string('sensorName')->unique();
            $table->string('manufacture')->nullable();
            $table->string('partId')->nullable();
            $table->string('sensorOutput')->nullable();
            $table->string('sensorType')->nullable();
            $table->string('units')->nullable();
            $table->string('minRatedReading')->nullable();
            $table->string('minRatedReadingChecked')->default('0')->nullable();
            $table->string('minRatedReadingScale')->default('0')->nullable();
            $table->string('maxRatedReading')->nullable();
            $table->string('maxRatedReadingChecked')->default('0')->nullable();
            $table->string('maxRatedReadingScale')->nullable();
            $table->string('slaveId')->nullable();
            $table->string('registerId')->nullable();
            $table->string('length')->nullable();
            $table->string('registerType')->nullable(); 
            $table->string('conversionType')->nullable();
            $table->string('ipAddress')->nullable();
            $table->string('subnetMask')->nullable(); 
            
            $table->string('criticalMinValue')->nullable();
            $table->string('criticalMaxValue')->nullable();

            $table->string('warningMinValue')->nullable();
            $table->string('warningMaxValue')->nullable();

            $table->string('outofrangeMinValue')->nullable();
            $table->string('outofrangeMaxValue')->nullable();
           

            $table->string('isStel')->nullable(); 
            $table->string('stelDuration')->nullable(); 
            $table->string('twaStartTime')->nullable();
            $table->string('stelType')->nullable(); 
            $table->string('stelLimit')->nullable(); 
            $table->string('stelAlert')->nullable(); 
            
            $table->string('twaDuration')->nullable(); 
            $table->string('twaType')->nullable(); 
            $table->string('twaLimit')->nullable(); 
            $table->string('twaAlert')->nullable(); 

            $table->string('alarm')->nullable(); 
            $table->string('unLatchDuration')->nullable();
                        
            $table->text('isAQI')->nullable(); 

            $table->text('parmGoodMinScale')->nullable(); 
            $table->text('parmGoodMaxScale')->nullable();            
            $table->text('parmSatisfactoryMinScale')->nullable();
            $table->text('parmSatisfactoryMaxScale')->nullable();
            $table->text('parmModerateMinScale')->nullable();
            $table->text('parmModerateMaxScale')->nullable();
            $table->text('parmPoorMinScale')->nullable();
            $table->text('parmPoorMaxScale')->nullable();
            $table->text('parmVeryPoorMinScale')->nullable();
            $table->text('parmVeryPoorMaxScale')->nullable();
            $table->text('parmSevereMinScale')->nullable();
            $table->text('parmSevereMaxScale')->nullable();    
            
             $table->text('relayOutput')->nullable();

            $table->string('bumpTestRequired')->nullable();
            
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
        Schema::dropIfExists('sensor_units');
    }
}
