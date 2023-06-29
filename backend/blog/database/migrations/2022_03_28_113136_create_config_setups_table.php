<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfigSetupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('config_setups', function (Blueprint $table) {
            $table->id();
            $table->string('companyCode');
            $table->text('accessType')->nullable();
            $table->text('accessPointName')->nullable();
            $table->text('ssId')->nullable();
            $table->text('accessPointPassword')->nullable();

            $table->text('accessPointNameSecondary')->nullable();
            $table->text('ssIdSecondary')->nullable();
            $table->text('accessPointPasswordSecondary')->nullable();

            $table->text('ftpAccountName')->nullable();
            $table->text('userName')->nullable();
            $table->text('ftpAccountPassword')->nullable();
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
        Schema::dropIfExists('config_setups');
    }
}
