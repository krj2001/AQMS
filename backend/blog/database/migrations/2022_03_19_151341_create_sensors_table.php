<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSensorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sensors', function (Blueprint $table) {
            $table->id();
            $table->string('companyCode');
            $table->bigInteger('location_id')->unsigned();  
            $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');   
            $table->bigInteger('branch_id')->unsigned();  
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');   
            $table->bigInteger('facility_id')->unsigned();  
            $table->foreign('facility_id')->references('id')->on('facilities')->onDelete('cascade');
            $table->bigInteger('building_id')->unsigned();  
            $table->foreign('building_id')->references('id')->on('buildings')->onDelete('cascade'); 

            $table->bigInteger('floor_id')->unsigned();  
            $table->foreign('floor_id')->references('id')->on('floors')->onDelete('cascade');
            
            $table->bigInteger('lab_id')->unsigned();  
            $table->foreign('lab_id')->references('id')->on('lab_departments')->onDelete('cascade');

            
            $table->text('category_id');
            $table->text('deviceCategory');
            
            $table->bigInteger('device_id')->unsigned();  
            $table->foreign('device_id')->references('id')->on('devices')->onDelete('cascade');  
            $table->text('deviceName');

            $table->bigInteger('sensor_id')->unsigned();  
            $table->foreign('sensor_id')->references('id')->on('sensor_categories')->onDelete('cascade');  
            //SENSOR  details
            $table->string('sensorName')->unique();         
            $table->text('sensorOutput')->nullable();
            $table->text('sensorVolt')->nullable();
            $table->text('registerAddress')->nullable();
            $table->text('registerLength')->nullable();

            //MIN   
            $table->text('minReadingRage')->nullable(); 
            $table->text('minScale')->nullable();
            $table->text('minUnits')->nullable();

            //MAX
            $table->text('maxReadingRage')->nullable();
            $table->text('maxScale')->nullable();
            $table->text('maxUnits')->nullable();

            //Threshold
            $table->text('minThreshold')->nullable();
            $table->text('maxThreshold')->nullable();
            
            //Alert
            $table->text('pollingIntervalType')->nullable();
            $table->text('criticalMinValue')->nullable();
            $table->text('criticalMaxValue')->nullable();
            $table->text('criticalAlertType')->nullable();
            $table->text('criticalLowAlert')->nullable();
            $table->text('criticalHighAlert')->nullable();
            $table->text('warningMinValue')->nullable();
            $table->text('warningMaxValue')->nullable();
            $table->text('warningAlertType')->nullable();
            $table->text('warningLowAlert')->nullable();
            $table->text('warningHighAlert')->nullable();
            $table->text('outofrangeMinValue')->nullable();
            $table->text('outofrangeMaxValue')->nullable();
            $table->text('outofrangeAlertType')->nullable();
            $table->text('outofrangeLowAlert')->nullable();
            $table->text('outofrangeHighAlert')->nullable();

            $table->text('digitalAlertType')->nullable();
            $table->text('digitalLowAlert')->nullable();
            $table->text('digitalHighAlert')->nullable();
            
            $table->string('isStel')->nullable(); 
            $table->string('stelDuration')->nullable(); 
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
            $table->text('sensorFault')->nullable();

            $table->boolean('sensorStatus')->default('1');    
            $table->boolean('notificationStatus')->default('1');
            
            $table->boolean('hooterRelayStatus')->default('0'); 
            
            $table->text('audioDecibelLevel')->nullable();
            
            $table->text('criticalRefMinValue')->nullable();
            $table->text('criticalRefMaxValue')->nullable();
            $table->text('warningRefMinValue')->nullable();
            $table->text('warningRefMaxValue')->nullable();
            $table->text('outofrangeRefMinValue')->nullable();
            $table->text('outofrangeRefMaxValue')->nullable();
            
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
        Schema::dropIfExists('sensors');
    }
}


           