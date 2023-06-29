<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSampledSensorDataDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sampled_sensor_data_details', function (Blueprint $table) {
            $table->id();
            $table->string('device_id')->nullable();
            $table->string('sensor_id')->nullable();
            $table->string('parameterName')->nullable();
            $table->string('last_val')->nullable();
            $table->string('max_val')->nullable();
            $table->string('min_val')->nullable();
            $table->string('avg_val')->nullable();
            $table->string('sample_date_time')->nullable();
            $table->text('alertType')->nullable();
            $table->text('alertStandardMessage')->nullable();
            $table->timestamp('time_stamp')->useCurrent()->useCurrentOnUpdate();
            $table->string('param_unit')->nullable();
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
        Schema::dropIfExists('sampled_sensor_data_details');
    }
}
