<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoicings', function (Blueprint $table) {
            $table->id();
            $table->string('INVOICE_NUMBER')->unique();
            $table->integer('APPLICATION_ID');
            $table->integer('CLIENT_ID')->nullable();
            $table->longText('INVOICE_DETAILS')->nullable();
            // $table->integer('BILLING_ID')->nullable();
            // $table->integer('QUANTITY')->nullable();
            // $table->double('AMOUNT')->nullable();
            // $table->double('VAT')->nullable();
            $table->boolean('PAYMENT_STATUS')->nullable();
            $table->double('PAYMENT_AMOUNT')->nullable();
            $table->integer('USER')->nullable();
            $table->string('INVOICE')->nullable();
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
        Schema::dropIfExists('invoicings');
    }
}
