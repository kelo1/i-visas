<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVisamgrEmploymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('visamgr_employments', function (Blueprint $table) {
            $table->id('RECID')->autoIncrement();
            $table->integer('APPLICATION_ID');
            $table->string('EMPLOYER_NAME')->nullable();
            $table->string('EMPLOYER_PHONE')->nullable();
            $table->string('EMPLOYER_EMAIL')->nullable();
            $table->string('EMPLOYMENT_STATUS')->nullable();
            $table->string('EMPLOYMENT_DATE')->nullable();
            $table->string('EMPLOYER_ADDRESS1')->nullable();
            $table->string('EMPLOYER_ADDRESS2')->nullable();
            $table->string('EMPLOYER_LOCATION')->nullable();
            $table->string('EMPLOYER_LOCATION_CODE')->nullable();
            $table->string('EMPLOYER_TOWN')->nullable();
            $table->string('EMPLOYER_COUNTRY')->nullable();
            $table->string('EMPLOYER_POSTCODE')->nullable();
            $table->string('EMPLOYER_COUNTRYPREFIX')->nullable();
            $table->string('EMPLOYER_COUNTY')->nullable();
            $table->string('EMPLOYER_FAX')->nullable();
            $table->string('EMPLOYER_VATRATE')->nullable();
            $table->string('JOB_TITLE')->nullable();
            $table->date('JOB_START_DATE')->nullable();
            $table->date('JOB_END_DATE')->nullable();
            $table->double('SALARY')->default(0);
            $table->string('SOC_CODE')->nullable();
            $table->string('SOC_BAND')->nullable();
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
        Schema::dropIfExists('visamgr_employments');
    }
}
