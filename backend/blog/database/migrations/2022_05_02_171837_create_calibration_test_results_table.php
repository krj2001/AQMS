<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCalibrationTestResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('calibration_test_results', function (Blueprint $table) {
            $table->id();
            $table->text('sensorTag')->nullable();             
            $table->text('name')->nullable();   
            $table->text('model')->nullable();   
            $table->text('testResult')->nullable();   
            $table->text('calibrationDate')->nullable();
            $table->text('nextDueDate')->nullable();      
            $table->text('calibratedDate')->nullable();
            $table->text('lastDueDate')->nullable();
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
        Schema::dropIfExists('calibration_test_results');
    }
}
