<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeviceConfigSetupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('device_config_setups', function (Blueprint $table) {
            $table->id();
            $table->string('companyCode');
            $table->bigInteger('device_id')->unsigned();  
            $table->foreign('device_id')->references('id')->on('devices')->onDelete('cascade');  
            $table->text('deviceName');
            $table->text('accessType')->nullable();
            $table->text('accessPointName')->nullable();
            $table->text('ssId')->nullable();
            $table->text('accessPointPassword')->nullable();

            //secondary
            $table->text('accessPointNameSecondary')->nullable();
            $table->text('ssIdSecondary')->nullable();
            $table->text('accessPointPasswordSecondary')->nullable();

            $table->text('ftpAccountName')->nullable();
            $table->text('userName')->nullable();
            $table->text('ftpPassword')->nullable();
            $table->text('port')->nullable();
            $table->text('serverUrl')->nullable();
            $table->text('folderPath')->nullable();

            $table->text('serviceProvider')->nullable();
            $table->text('apn')->nullable();
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
        Schema::dropIfExists('device_config_setups');
    }
}
