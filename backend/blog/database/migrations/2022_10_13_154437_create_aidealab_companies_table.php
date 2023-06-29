<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAidealabCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aidealab_companies', function (Blueprint $table) {
            $table->id();
            $table->string("companyName")->nullable();
            $table->string("email")->nullable();
            $table->string("periodicBackupInterval")->nullable();
            $table->string("lastPeriodicBackupDate")->nullable();
            $table->string("dataRetentionPeriodInterval")->nullable();
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
        Schema::dropIfExists('aidealab_companies');
    }
}
