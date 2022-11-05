<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserSubscriptionsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id', false, true);
            $table->string('plan_code', 50);
            $table->dateTimeTz('start_at');
            $table->dateTimeTz('end_at');
            $table->dateTimeTz('cancel_date')->nullable();
            $table->double('amount', null, null, true)->nullable();
            $table->string('currency', 3)->nullable();
            $table->bigInteger('payment_method_id', false, true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('user_subscriptions');
    }

}
