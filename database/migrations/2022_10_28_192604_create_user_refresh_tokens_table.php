<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserRefreshTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_refresh_tokens', function (Blueprint $table) {
            $table->id();
            $table->char('refresh_token', 128)->unique('user_refresh_tokens_refresh_token');
            $table->bigInteger('user_id', false, true);
            $table->dateTime('issued_at');
            $table->dateTime('expire_at');
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
        Schema::dropIfExists('user_refresh_tokens');
    }
}
