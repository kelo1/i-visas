<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id('MESSAGE_ID')->autoIncrement();
            $table->uuid('CONVERSATION_ID');
            $table->string('MESSAGE_TYPE')->nullable();
            $table->string('SENDER')->nullable();
            $table->text('MESSAGE');
            $table->text('MESSAGE_SUBJECT');
            $table->text('MESSAGE_TAG')->nullable();
            $table->integer('USER_ID')->nullable();
            $table->string('CLIENT_DELETE')->nullable();
            $table->string('USER_DELETE')->nullable();
            $table->string('RECEIPIENT')->nullable();
            $table->string('MESSAGE_STATUS')->default('unread');
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
        Schema::dropIfExists('messages');
    }
}
