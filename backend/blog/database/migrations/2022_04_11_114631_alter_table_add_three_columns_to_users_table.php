<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableAddThreeColumnsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {       
            
            $table->bigInteger('location_id')->unsigned();  
            $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade')->nullable();  
            $table->bigInteger('branch_id')->unsigned();  
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade')->nullable(); 
            $table->bigInteger('facility_id')->unsigned();  
            $table->foreign('facility_id')->references('id')->on('facilities')->onDelete('cascade')->nullable();
            $table->bigInteger('building_id')->unsigned();  
            $table->foreign('building_id')->references('id')->on('buildings')->onDelete('cascade')->nullable();
            $table->bigInteger('floor_id')->unsigned();  
            $table->foreign('floor_id')->references('id')->on('floors')->onDelete('cascade');            
            $table->bigInteger('lab_id')->unsigned();  
            $table->foreign('lab_id')->references('id')->on('lab_departments')->onDelete('cascade');
            $table->boolean('empNotification')->default('0');
                 
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
}
