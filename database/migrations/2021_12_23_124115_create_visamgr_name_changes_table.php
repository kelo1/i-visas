<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVisamgrNameChangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('visamgr_name_changes', function (Blueprint $table) {
            $table->id('RECID')->autoIncrement();
            $table->integer('APPLICATION_ID');
            $table->string('NAME_CHANGE_ANSWER')->nullable();
            $table->date('NAME_CHANGE_FROM_DATE')->nullable();
            $table->date('NAME_CHANGE_TO_DATE')->nullable();
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
        Schema::dropIfExists('visamgr_name_changes');
    }
}
