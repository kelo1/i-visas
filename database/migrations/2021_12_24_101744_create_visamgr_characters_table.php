<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVisamgrCharactersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('visamgr_characters', function (Blueprint $table) {
            $table->id('CHARACTER_ID')->autoIncrement();
            $table->integer('APPLICATION_ID');
            $table->string('CRIMINAL_OFFENCE_ANSWER')->nullable();
            $table->string('PENDING_PERSECUTION')->nullable();
            $table->string('DETAILS_OF_PROSECUTIONS')->nullable();
            $table->string('TERRORIST_VIEW')->nullable();
            $table->string('DETAILS_OF_TERRORIST_CHARGES')->nullable();
            $table->string('GOVERNMENT_WORK')->nullable();
            $table->string('WORKED_FOR_SECURITY')->nullable();
            $table->string('DETAILS_OF_WORK')->nullable();
           // $table->longText('ATTRIBUTES')->nullable();
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
        Schema::dropIfExists('visamgr_characters');
    }
}
