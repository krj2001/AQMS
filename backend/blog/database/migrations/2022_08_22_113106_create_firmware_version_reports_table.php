<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFirmwareVersionReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('firmware_version_reports', function (Blueprint $table) {
            $table->id();
            $table->string('companyCode')->nullable();
            $table->string('deviceName')->nullable();
            $table->string('device_id')->nullable();            
            $table->string('firmwareVersion')->nullable();
            $table->string('hardwareVersion')->nullable();
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
        Schema::dropIfExists('firmware_version_reports');
    }
}
