<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBlockedToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('blocked')->default(0)->after('email');
            $table->integer('login_fail_attempt')->default(0)->after('email');
            $table->boolean('isverified')->default(0)->after('email');
            $table->integer('otpno')->default(0)->after('email');
            $table->timestamp('otpgenerated_at')->nullable()->after('otpno');
            $table->integer('sec_level_auth')->default(0)->after('email');
            $table->string('user_role')->after('email')->nullable();
            $table->timestamp('last_login_ativity')->nullable();
            $table->string('mobileno')->nullable()->after('email');
            $table->string('employeeId')->after('mobileno')->nullable();
            $table->string('companycode')->after('employeeId')->nullable();
            $table->string('companyLogo')->nullable();           
            $table->boolean('changePassword')->default('1')->after('user_role');    
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
