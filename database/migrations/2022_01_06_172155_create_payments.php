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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('customer_id')->nullable();
            $table->string('paypal_email')->nullable();
            $table->string('card_brand')->nullable();
            $table->string('card_last_four')->nullable();
            $table->unsignedSmallInteger('status');

            $table->softDeletes();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('status');
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_method_id');
            $table->unsignedSmallInteger('type');
            $table->unsignedSmallInteger('status');
            $table->unsignedDecimal('amount');
            $table->json('details');
            $table->timestamps();

            $table->index('type');
            $table->index('status');

            $table->foreign('payment_method_id')
                ->references('id')
                ->on('payment_methods')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });

        Schema::create('payment_has_subscription', function (Blueprint $table) {
            $table->unsignedBigInteger('payment_id');
            $table->unsignedBigInteger('subscription_id');

            $table->foreign('payment_id')
                ->references('id')
                ->on('payments')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('subscription_id')
                ->references('id')
                ->on('subscriptions')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->primary(['payment_id', 'subscription_id'], 'payment_has_subscription_primary');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_has_subscription');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('payment_methods');
    }
};
