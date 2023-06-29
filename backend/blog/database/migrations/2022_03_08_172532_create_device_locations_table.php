<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeviceLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('device_locations', function (Blueprint $table) {
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
            $table->bigInteger('category_id')->unsigned();  
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->text('categoryName');
            $table->bigInteger('device_id')->unsigned();  
            $table->foreign('device_id')->references('id')->on('devices')->onDelete('cascade');
            $table->text('deviceName');
            $table->text('description');
            $table->text('assetTag')->nullable();
            $table->text('macAddress');
            $table->text('deviceIcon');            
            $table->text('floorCords')->nullable();  
            
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
        Schema::dropIfExists('device_locations');
    }
}
