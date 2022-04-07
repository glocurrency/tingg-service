<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTinggTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tingg_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('transaction_id')->unique()->index();
            $table->uuid('processing_item_id')->index();

            $table->string('state_code');
            $table->longText('state_code_reason')->nullable();

            $table->string('error_code')->nullable();
            $table->longText('error_code_description')->nullable();

            $table->string('reference')->unique();
            $table->string('remote_reference')->nullable();
            $table->char('country_code', 3);
            $table->char('currency_code', 3);
            $table->double('amount');
            $table->string('service_code');
            $table->string('product_code')->nullable();
            $table->string('sender_name');
            $table->string('sender_phone_number');
            $table->string('recipient_phone_number');

            $table->timestamps(6);
            $table->softDeletes('deleted_at', 6);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tingg_transactions');
    }
}
