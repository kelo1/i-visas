<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVisamgrTrackingTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('visamgr_tracking_types', function (Blueprint $table) {
            $table->id('RECID')->autoIncrement();
            $table->string('TRACK_CODE')->nullable();
            $table->string('TRACK_URL')->nullable();
            $table->string('STATUS')->nullable();
            $table->string('ADD_USER')->nullable();
            $table->date('ADD_DATE')->nullable();
            $table->string('MOD_USER')->nullable();
            $table->date('MOD_DATE')->nullable();
            $table->string('USER')->nullable();
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
        Schema::dropIfExists('visamgr_tracking_types');
    }
}
