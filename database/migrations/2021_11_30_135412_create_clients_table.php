<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('email');
            $table->integer('OTP')->nullable();
            $table->uuid('email_token')->nullable();
            $table->boolean('sms_verified')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone');
            $table->string('country');
            $table->string('agent_reference')->nullable();//agent_reference
            $table->string('agent_reference_hist')->nullable();
            $table->integer('client_office')->default(1);
            $table->integer('DEFAULT_USER')->default(1);
            $table->string('USER')->nullable();
            $table->string('group')->nullable();
            $table->string('created_by')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *  client_office
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clients');
    }
}
