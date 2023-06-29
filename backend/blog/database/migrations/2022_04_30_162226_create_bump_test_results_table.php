<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBumpTestResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bump_test_results', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('device_id')->unsigned();  
            $table->foreign('device_id')->references('id')->on('devices')->onDelete('cascade');  
            $table->text('sensorTagName')->nullable();             
            $table->text('lastDueDate')->nullable();   
            $table->text('typeCheck')->nullable();   
            $table->text('percentageConcentrationGas')->nullable();   
            $table->text('durationPeriod')->nullable();   
            $table->text('displayedValue')->nullable();   
            $table->text('percentageDeviation')->nullable();   
            $table->text('calibrationDate')->nullable();
            $table->text('calibratedDate')->nullable();
            $table->text('nextDueDate')->nullable();   
            $table->text('result')->nullable();
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
        Schema::dropIfExists('bump_test_results');
    }
}
