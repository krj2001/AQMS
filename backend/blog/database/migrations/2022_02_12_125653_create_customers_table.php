<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customerName');
            $table->string('email');
            $table->string('phoneNo');
            $table->string('address');
            $table->string('customerId');
            $table->string('customerLogo');
            $table->string('customerTheme');
            $table->string('alertLogInterval')->nullable();
            $table->string('deviceLogInterval')->nullable();
            $table->string('sensorLogInterval')->nullable();
            $table->string('periodicBackupInterval')->nullable();
            $table->string('dataRetentionPeriodInterval')->nullable();
            $table->string('expireDateReminder')->nullable();
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
        Schema::dropIfExists('customers');
    }
}
