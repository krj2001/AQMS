<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeviceModelLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('device_model_logs', function (Blueprint $table) {
            $table->id();
            $table->string("companyName")->nullable();
            $table->string("deviceId")->nullable();
            $table->string("deviceName")->nullable();
            $table->string("deviceModel")->nullable();
            $table->string("currentDateTime")->nullable();
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
        Schema::dropIfExists('device_model_logs');
    }
}
