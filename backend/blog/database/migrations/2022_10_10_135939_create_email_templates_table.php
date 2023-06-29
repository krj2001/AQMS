<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('companyCode');
            $table->string('calibrartionSubject');
            $table->string('calibrartionBody')->nullable();           
            $table->string('bumpTestSubject')->nullable();
            $table->string('bumpTestBody')->nullable();
            $table->string('stelSubject')->nullable();
            $table->string('stelBody')->nullable();
            $table->string('twaSubject')->nullable();
            $table->string('twaBody')->nullable();
            $table->string('warningSubject')->nullable();
            $table->string('warningBody')->nullable();
            $table->string('criticalSubject')->nullable();
            $table->string('criticalBody')->nullable();
            $table->string('outOfRangeSubject')->nullable();
            $table->string('outOfRangeBody')->nullable();
            $table->string('periodicitySubject')->nullable();
            $table->string('periodicityBody')->nullable();
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
        Schema::dropIfExists('email_templates');
    }
}
