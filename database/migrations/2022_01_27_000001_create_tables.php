<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('is_enabled')->default(1);
            $table->integer('telegram_id')->unique('accounts_telegram_id');
            $table->integer('maker_commission')->nullable(false)->default(0);
            $table->integer('taker_commission')->nullable(false)->default(0);
            $table->integer('buyer_commission')->nullable(false)->default(0);
            $table->integer('seller_commission')->nullable(false)->default(0);
            $table->string('telegram_first_name', 128)->nullable();
            $table->string('telegram_last_name', 128)->nullable();
            $table->string('telegram_username', 128)->nullable();
            $table->string('binance_api_key', 128)->nullable();
            $table->string('binance_secret_key', 128)->nullable();
            $table->string('email', 128)->nullable();
            $table->timestamps();

            $table->unique(['binance_api_key', 'binance_secret_key'], 'accounts_binance_api_key_binance_secret_key');
        });

        Schema::create('coins', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 30)->unique('coins_code');
            $table->string('icon', 100)->nullable();
            $table->string('interval', 10)->nullable(false)->default('1h');
            $table->unsignedSmallInteger('is_enabled')->default(1);
            $table->timestamps();
        });

        Schema::create('coins_pairs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('coin_id');
            $table->string('couple', 30);
            $table->unsignedSmallInteger('is_enabled')->default(1);
            $table->timestamps();

            $table->foreign('coin_id')->references('id')->on('coins');

            $table->unique(['coin_id', 'couple'], 'coins_pairs_coin_id_couple');
        });

        Schema::create('klines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('coin_pair_id');
            $table->timestamp('open_time');
            $table->timestamp('close_time');
            $table->double('open', 16, 2);
            $table->double('high', 16, 2);
            $table->double('low', 16, 2);
            $table->double('close', 16, 2);
            $table->double('volume', 16, 2);
            $table->double('quote_asset_volume', 16, 2);
            $table->unsignedBigInteger('trade_num');
            $table->double('taker_buy_base_asset_volume', 16, 2);
            $table->double('taker_buy_quote_asset_volume', 16, 2);
            $table->double('ratio_open_close', 16, 2);
            $table->double('ratio_high_low', 16, 2);

            $table->foreign('coin_pair_id')->references('id')->on('coins_pairs');

            $table->unique(['coin_pair_id', 'open_time'], 'klines_coin_id_open_time');
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('coin_pair_id');
            $table->unsignedBigInteger('order_id')->unique('orders_order_id');
            $table->integer('order_list_id');
            $table->string('client_order_id', 100);
            $table->unsignedSmallInteger('status');
            $table->unsignedSmallInteger('type');
            $table->unsignedSmallInteger('side');
            $table->double('price', 16, 2);
            $table->double('orig_qty', 16, 2);
            $table->double('executed_qty', 16, 2);
            $table->double('cummulative_quote_qty', 16, 2);
            $table->double('stop_price', 16, 2);
            $table->double('iceberg_qty', 16, 2);
            $table->double('orig_quote_order_qty', 16, 2);
            $table->timestamp('time');
            $table->timestamp('update_time');

            $table->unsignedBigInteger('account_id')->nullable(false)->default(1);

            $table->foreign('account_id')->references('id')->on('accounts');

            $table->foreign('coin_pair_id')->references('id')->on('coins_pairs');
        });

        Schema::create('balances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('coin_id');
            $table->unsignedBigInteger('account_id');
            $table->double('free', 16, 2);
            $table->double('locked', 16, 2);
            $table->timestamps();

            $table->foreign('coin_id')->references('id')->on('coins');
            $table->foreign('account_id')->references('id')->on('accounts');

            $table->unique(['coin_id', 'account_id'], 'balances_coin_id_account_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('klines');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('balances');
        Schema::dropIfExists('coins_pairs');
        Schema::dropIfExists('coins');
        Schema::dropIfExists('accounts');
    }
}
