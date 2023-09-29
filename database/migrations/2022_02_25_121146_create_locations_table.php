<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('COUNTRY')->nullable();
            $table->integer('BRANCH_ID')->nullable();
            $table->integer('USER_ID')->nullable();
            $table->integer('DEFAULT_USER')->nullable();
           // $table->string('DEFAULT_USER')->default('Samarah');
            // $table->string('ADDRESS1')->nullable();
            // $table->string('ADDRESS2')->nullable();
            // $table->string('TOWN')->nullable();
            // $table->string('COUNTY')->nullable();
            // $table->string('POSTCODE')->nullable();
            // $table->string('TELEPHONE')->nullable();
            // $table->string('FAX')->nullable();
            // $table->string('EMAIL')->nullable();
            // $table->string('COUNTRY_PREFIX')->nullable();
            // $table->double('VAT_RATE')->nullable();
            $table->string('USER')->nullable();
            // $table->string('ADD_USER')->nullable();
            // $table->date('ADD_DATE')->nullable();
            // $table->string('MOD_USER')->nullable();
            // $table->date('MOD_DATE')->nullable();
            $table->timestamps();
        });
    }
// $table->string('DEFAULT_USER')->nullable();
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('locations');
    }
}
