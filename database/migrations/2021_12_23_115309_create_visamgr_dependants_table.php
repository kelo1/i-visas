<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVisamgrDependantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('visamgr_dependants', function (Blueprint $table) {
            $table->id('RECID')->autoIncrement();
            $table->integer('APPLICATION_ID');
            $table->string('FULL_NAME')->nullable();
            $table->string('GENDER')->nullable();
            $table->date('DOB')->nullable();
            $table->string('RELATIONSHIP')->nullable();
            $table->string('NATIONALITY')->nullable();
            $table->string('PASSPORT_NO')->nullable();
            $table->date('PASSPORT_ISSUED')->nullable();
            $table->date('PASSPORT_EXPIRY')->nullable();
            $table->integer('VISA_TYPE_ID')->default(0);
            $table->date('VISA_ISSUED')->nullable();
            $table->date('VISA_EXPIRY')->nullable();
            $table->string('ADD_USER')->nullable();
            $table->date('ADD_DATE')->nullable();
            $table->string('MOD_USER')->nullable();
            $table->date('MOD_DATE')->nullable();
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
        Schema::dropIfExists('visamgr_dependants');
    }
}
