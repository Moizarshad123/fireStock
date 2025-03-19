<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMembersTable extends Migration
{
    public function up()
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->Integer("user_id")->nullable();
            $table->Integer("station_id")->nullable();
            $table->String("name")->nullable();
            $table->String("email")->nullable();
            $table->String("phone")->nullable();
            $table->String("image")->nullable();
            $table->String("status")->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('members');
    }
}
