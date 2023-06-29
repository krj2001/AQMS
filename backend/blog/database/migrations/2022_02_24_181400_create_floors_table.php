<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFloorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('floors', function (Blueprint $table) {
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
            $table->text('floorStage');
            $table->text('floorName');
            $table->text('floorMap');
            $table->text('floorCords');
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
        Schema::dropIfExists('floors');
    }
}
