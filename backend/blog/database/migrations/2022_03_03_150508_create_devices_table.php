<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('devices', function (Blueprint $table) {
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
            $table->string('floorCords')->nullable();
            $table->bigInteger('lab_id')->unsigned();  
            $table->foreign('lab_id')->references('id')->on('lab_departments')->onDelete('cascade');
            $table->text('deviceName');
            $table->text('category_id');
            $table->text('deviceCategory');
            $table->text('firmwareVersion');
            $table->text('macAddress');
            $table->text('deviceImage');            
            $table->text('deviceTag')->nullable();
            $table->text('nonPollingPriority')->nullable();
            $table->text('pollingPriority')->nullable();
            $table->text('dataPushUrl')->nullable();
            $table->text('firmwarePushUrl')->nullable();
            $table->text('binFileName')->nullable();
            $table->text('deviceMode')->nullable();           
            $table->boolean('firmwareStatus')->default('0');     
            $table->boolean('configurationStatus')->default('0');                
            $table->text('xAxisTimeInterval')->nullable();     
            $table->string('disconnectedStatus')->default('0'); 
            $table->text('lastAnalogMemoryAddressIndex')->nullable();  
            $table->text('lastDigitalMemoryAddressIndex')->nullable();  
            $table->text('lastModbusMemoryAddressIndex')->nullable();     
            $table->text('configurationProcessStatus')->nullable();
            $table->text('hardwareModelVersion')->nullable();
            $table->text('initialConnect')->default('0');      
            $table->text('modeChangedDateTime')->nullable();       
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
        Schema::dropIfExists('devices');
    }
}
