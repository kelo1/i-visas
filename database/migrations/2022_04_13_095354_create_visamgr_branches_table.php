<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVisamgrBranchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('visamgr_branches', function (Blueprint $table) {
            $table->id();
            $table->string('COUNTRY')->nullable();
            $table->integer('DEFAULT_USER')->default(1);
            $table->string('LOCATION_CODE')->nullable();
            $table->string('LOCATION_NAME')->nullable();
            $table->string('ADDRESS1')->nullable();
            $table->string('ADDRESS2')->nullable();
            $table->string('TOWN')->nullable();
            $table->string('COUNTY')->nullable();
            $table->string('POSTCODE')->nullable();
            $table->string('TELEPHONE')->nullable();
            $table->string('FAX')->nullable();
            $table->string('EMAIL')->nullable();
            $table->string('COUNTRY_PREFIX')->nullable();
            $table->double('VAT_RATE')->nullable();
            $table->string('STATUS')->nullable();
            $table->string('USER')->nullable();
            $table->string('ADD_USER')->nullable();
            $table->date('ADD_DATE')->nullable();
            $table->string('MOD_USER')->nullable();
            $table->date('MOD_DATE')->nullable();
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
        Schema::dropIfExists('visamgr_branches');
    }
}
