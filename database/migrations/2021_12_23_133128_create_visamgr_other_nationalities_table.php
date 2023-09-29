<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVisamgrOtherNationalitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('visamgr_other_nationalities', function (Blueprint $table) {
            $table->id('RECID')->autoIncrement();
            $table->integer('APPLICATION_ID');
            $table->string('OTHER_NATIONALITY')->nullable();
            $table->date('OTHER_NATIONALITY_FROM_DATE')->nullable();
            $table->date('OTHER_NATIONALITY_TO_DATE')->nullable();
            $table->longText('ATTRIBUTES')->nullable();
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
        Schema::dropIfExists('visamgr_other_nationalities');
    }
}
