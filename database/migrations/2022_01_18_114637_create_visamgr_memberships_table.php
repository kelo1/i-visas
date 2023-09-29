<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVisamgrMembershipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('visamgr_memberships', function (Blueprint $table) {
            $table->id('RECID')->autoIncrement();
            $table->integer('APPLICATION_ID');
            $table->string('MEMBERSHIP_TYPE')->nullable();
            $table->string('MEMBERSHIP_NAME')->nullable();
            $table->date('MEMBERSHIP_ISSUED')->nullable();
            $table->date('MEMBERSHIP_EXPIRY')->nullable();
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
        Schema::dropIfExists('visamgr_memberships');
    }
}
