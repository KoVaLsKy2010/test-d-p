<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('status')->default('created');
            $table->bigInteger('amount');
            $table->integer('user_id');
            $table->bigInteger('payment_id');
            $table->integer('merchant_id');
            $table->timestamps();
            /*
             * В зависимости от того, где и как будут выбираться платежы, можно повесить
             * составные и обычные индексы. Платеж тесно связан с пользователем, так что по нему точно вешаю индекс.
             */
            $table->index(['user_id']);
            $table->unique(['payment_id', 'merchant_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
