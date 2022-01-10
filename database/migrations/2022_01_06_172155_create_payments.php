<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('credit_cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('external_id')->nullable();
            $table->string('owner_email')->nullable();
            $table->string('brand')->nullable();
            $table->string('last_four')->nullable();
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
            $table->unsignedBigInteger('credit_card_id');
            $table->unsignedSmallInteger('type');
            $table->unsignedSmallInteger('status');
            $table->unsignedDecimal('amount');
            $table->json('details');
            $table->timestamps();

            $table->index('type');
            $table->index('status');

            $table->foreign('credit_card_id')
                ->references('id')
                ->on('credit_cards')
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
    }
}
