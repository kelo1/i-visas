<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePreScreeningsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pre_screenings', function (Blueprint $table) {
            $table->id();
            $table->integer('client_id');
            $table->string('client_first_name');
            $table->string('client_middle_name')->nullable();
            $table->string('client_last_name');
            $table->string('client_email')->unique();
            $table->string('client_phone');
            $table->boolean('prescreened')->default(0);
            $table->smallInteger('prescreened_status')->default(0);
            $table->string('country_of_residence')->nullable();
            $table->string('residency_question')->nullable();
            $table->integer('client_office')->default(1);
            $table->date('expiry')->nullable();
            $table->date('dob')->nullable();
            $table->string('english_proficiency')->nullable();
            $table->string('english_proficiency_level')->nullable();
            $table->string('other_languages')->nullable();
            $table->string('visa_refusal')->nullable();
            $table->string('visa_refusal_reason')->nullable();
            $table->string('how_we_can_help_you_question')->nullable();
            $table->string('type_of_permission')->nullable();
            $table->string('how_we_can_help_you')->nullable();
            $table->string('in_your_own_words')->nullable();
            $table->string('created_by')->nullable();
            $table->string('USER')->nullable();
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
        Schema::dropIfExists('pre_screenings');
    }
}
