<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVisamgrApptypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('visamgr_apptypes', function (Blueprint $table) {
            $table->id();
            $table->string('APPTYPE_NAME');
            $table->longText('APPSUBCAT_NAME')->nullable();
            $table->boolean('STATUS');
            $table->string('USER');
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
        Schema::dropIfExists('visamgr_apptypes');
    }
}
