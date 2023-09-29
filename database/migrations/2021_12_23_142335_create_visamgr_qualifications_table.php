<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVisamgrQualificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('visamgr_qualifications', function (Blueprint $table) {
            $table->id('RECID')->autoIncrement();
            $table->integer('APPLICATION_ID');
            $table->string('COUNTRY_OF_AWARD')->nullable();
            $table->string('STATE')->nullable();
            $table->longText('QUALIFICATION')->nullable();
            $table->string('AWARDING_INSTITUTE')->nullable();
            $table->integer('COURSE_LENGTH')->default(0);
            $table->string('COURSE_SUBJECT')->nullable();
            $table->date('YEAR_OF_AWARD')->nullable();
            $table->integer('GRADE')->default(0);
            $table->string('ADD_USER')->nullable();
            $table->date('ADD_DATE')->nullable();
            $table->string('MOD_USER')->nullable();
            $table->date('MOD_DATE')->nullable();
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
        Schema::dropIfExists('visamgr_qualifications');
    }
}
