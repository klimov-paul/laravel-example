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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->decimal('price');
            $table->decimal('max_book_price');
            $table->unsignedSmallInteger('max_rent_count')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('subscription_plan_has_category', function (Blueprint $table) {
            $table->unsignedBigInteger('subscription_plan_id');
            $table->unsignedBigInteger('category_id');

            $table->primary(['subscription_plan_id', 'category_id'], 'subscription_plan_has_category_primary');

            $table->foreign('subscription_plan_id')
                ->references('id')
                ->on('subscription_plans')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('subscription_plan_id');
            $table->timestamp('begin_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->unsignedSmallInteger('status');
            $table->boolean('is_recurrent');
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('subscription_plan_id')
                ->references('id')
                ->on('subscription_plans')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('subscription_plan_has_category');
        Schema::dropIfExists('subscription_plans');
    }
};
