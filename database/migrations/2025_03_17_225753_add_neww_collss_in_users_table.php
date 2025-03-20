<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewwCollssInUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->String('station_name')->after("email")->nullable();
            $table->String('station_image')->after("station_name")->nullable();
            $table->boolean('has_station')->after("station_image")->default(0);
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['station_name', 'station_image', 'has_station']);
        });
    }
}
