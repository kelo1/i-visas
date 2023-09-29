<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVisamgrNamesOfPeopleAtAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('visamgr_names_of_people_at_addresses', function (Blueprint $table) {
            $table->id();
            $table->integer('APPLICATION_ID');
            $table->string('NAME_OF_PERSON_LIVING_AT_ADDRESS')->nullable();
            $table->string('RELATIONSHIP')->nullable();
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
        Schema::dropIfExists('visamgr_names_of_people_at_addresses');
    }
}
