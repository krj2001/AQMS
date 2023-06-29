<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLabDepartmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lab_departments', function (Blueprint $table) {
            $table->id();
            $table->string('companyCode');
            $table->bigInteger('location_id')->unsigned();  
            $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');   
            $table->bigInteger('branch_id')->unsigned();  
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');   
            $table->bigInteger('facility_id')->unsigned();  
            $table->foreign('facility_id')->references('id')->on('facilities')->onDelete('cascade');
            $table->bigInteger('building_id')->unsigned();  
            $table->foreign('building_id')->references('id')->on('buildings')->onDelete('cascade');
            $table->bigInteger('floor_id')->unsigned();  
            $table->foreign('floor_id')->references('id')->on('floors')->onDelete('cascade');
            $table->text('labDepName');
            $table->text('labDepMap');
            $table->text('labCords');  
            $table->string('labHooterStatus')->default('0');          
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
        Schema::dropIfExists('lab_departments');
    }
}
