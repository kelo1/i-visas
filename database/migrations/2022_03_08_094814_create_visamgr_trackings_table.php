<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVisamgrTrackingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('visamgr_trackings', function (Blueprint $table) {
            $table->id();
            $table->integer('APPLICATION_ID');
            $table->integer('CLIENT_ID')->nullable();
            $table->string('TRACKING_TYPE_ID')->unique();
            $table->string('DIRECTION');
            $table->date('TRACKING_DATE');
            $table->string('TRACKING_ID');
            $table->string('TRACKING_NOTE')->nullable();
            $table->string('USER')->nullable();
            $table->timestamps();
            $table->string('ADD_USER')->nullable();
            $table->string('ADD_DATE')->nullable();
            $table->string('MOD_USER')->nullable();
            $table->string('MOD_DATE')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('visamgr_trackings');
    }
}
