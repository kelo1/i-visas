<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVisamgrGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('visamgr_groups', function (Blueprint $table) {
            $table->id('GROUP_ID')->autoIncrement();
            $table->string('GROUP_NAME')->nullable();
            $table->string('LOCATION_ID')->nullable();
            $table->integer('CLIENT_ID')->nullable();
            $table->string('LOCATION')->nullable();
			$table->string('USER')->nullable();
            $table->string('ADD_USER')->nullable();
            $table->string('ADD_DATE')->nullable();
            $table->string('MOD_USER')->nullable();
            $table->string('MOD_DATE')->nullable();
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
        Schema::dropIfExists('visamgr_groups');
    }
}
