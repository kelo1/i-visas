<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTempVisamgrLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('temp_visamgr_locations', function (Blueprint $table) {
            $table->id('LOCATION_ID')->autoIncrement();
            $table->integer('APPLICATION_ID');
            $table->string('LOCATION_NAME')->nullable();
            $table->string('LOCATION_CODE')->nullable();
            $table->string('ADDRESS1')->nullable();
            $table->string('ADDRESS2')->nullable();
            $table->string('TOWN')->nullable();
            $table->string('COUNTY')->nullable();
            $table->string('COUNTRY')->nullable();
            $table->string('POSTCODE')->nullable();
            $table->string('TELEPHONE')->nullable();
            $table->string('FAX')->nullable();
            $table->string('EMAIL')->nullable();
            $table->string('COUNTRY_PREFIX')->nullable();
            $table->string('REASON_ENTERING_COUNTRY')->nullable();
            $table->date('DATE_ENTERED_COUNTRY')->nullable();
            $table->date('DATE_LEFT_COUNTRY')->nullable();
            $table->string('VAT_RATE')->nullable();
            $table->string('STATUS')->nullable();
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
        Schema::dropIfExists('temp_visamgr_locations');
    }
}
