<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTinggMobileMoneyProvidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tingg_mobile_money_providers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('mobile_money_provider_id')->index();
            $table->string('code');
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
        Schema::dropIfExists('tingg_mobile_money_providers');
    }
}
