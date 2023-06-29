<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSensorCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sensor_categories', function (Blueprint $table) {
            $table->id();
            $table->text('companyCode')->nullable();            
            $table->text('sensorName')->nullable();            
            $table->string('sensorDescriptions')->nullable();         
            $table->text('measureUnitList')->nullable();    
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
        Schema::dropIfExists('sensor_categories');
    }
}
