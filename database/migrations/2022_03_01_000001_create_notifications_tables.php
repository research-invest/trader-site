<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications_subscribers', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('is_enabled')->default(1);
            $table->integer('telegram_id')->unique('notifications_subscribers_telegram_id');
            $table->string('telegram_first_name', 128)->nullable();
            $table->string('telegram_last_name', 128)->nullable();
            $table->string('telegram_username', 128)->nullable();
            $table->string('email', 128)->nullable();
            $table->timestamps();
        });

        Schema::create('notifications_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subscriber_id');
            $table->string('notification', 1000);
            $table->timestamps();
            $table->foreign('subscriber_id')->references('id')->on('notifications_subscribers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notifications_logs');
        Schema::dropIfExists('notifications_subscribers');
    }
}
