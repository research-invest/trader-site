<?php

namespace App\Models\Account;

use App\Models\Balance;
use App\Models\Coin;
use App\Models\CoinPair;
use App\Models\Order;

trait BinanceApiAccount
{

    /**
     * @var \Binance\API
     */
    private $api;

    private function getApi()
    {
        if ($this->api) {
            return $this->api;
        }

        throw_if(!$this->binance_api_key || !$this->binance_secret_key, 'RuntimeException', '!$this->binance_api_key || !$this->binance_secret_key');

        return $this->api = new \Binance\API($this->binance_api_key, $this->binance_secret_key);
    }

    public function getAccountDataApi()
    {
        $api = $this->getApi();

        try {
            return $api->account();
        } catch (\Exception $e) {
            \Log::error("Error get account api: " . $e->getMessage() . 'account id: ' . $this->id);
            return null;
        }
    }

    public function getAccountOrdersApi(string $symbol)
    {
        $api = $this->getApi();

        try {
            return $api->orders($symbol);
        } catch (\Exception $e) {
            \Log::error("Error get orders api: " . $e->getMessage() . 'symbol: ' . $symbol);
            return null;
        }
    }

    public function getAccountExchangeInfoApi()
    {
        $api = $this->getApi();

        try {
            return $api->exchangeInfo();
        } catch (\Exception $e) {
            \Log::error("Error get exchange info api: " . $e->getMessage());
            return null;
        }
    }

    public function setBalances()
    {
        $data = $this->getAccountDataApi();


        $this->maker_commission = $data['makerCommission'];
        $this->taker_commission = $data['takerCommission'];
        $this->buyer_commission = $data['buyerCommission'];
        $this->seller_commission = $data['sellerCommission'];
        $this->save();

        foreach ($data['balances'] ?? [] as $balance) {
            $code = strtoupper($balance['asset']);

            /**
             * @var Coin $coin
             */
            $coin = Coin::whereCode($code)->first();

            $isBalance = $balance['free'] > 0 || $balance['locked'] > 0;

            if (!$coin) {
                $coin = Coin::create([
                    'name' => $balance['asset'],
                    'code' => $code,
                    'is_enabled' => $isBalance ? Coin::IS_ENABLED_TRUE : Coin::IS_ENABLED_FALSE,
                ]);
            }

            if (!$isBalance) {
                continue;
            }

//            if (!$coin->isEnabledTrue()) {
//                $coin->is_enabled = Coin::IS_ENABLED_TRUE;
//                $coin->save();
//            }

            $balanceModel = Balance::query()->where('account_id', $this->id)
                ->where('coin_id', $coin->id)->first() ?: new Balance();

            $balanceModel->coin_id = $coin->id;
            $balanceModel->account_id = $this->id;
            $balanceModel->free = $balance['free'];
            $balanceModel->locked = $balance['locked'];

            $balanceModel->save();
        }

    }

    public function setOrders()
    {
        $coins = Coin::query()->enabled()->where('id', '=', 142)->get();

        $getTime = function ($time) {
            return ceil($time / 1000);
        };

        $getSymbol = function (Coin $coin, CoinPair $pair) {
            return strtoupper(trim($coin->code) . trim($pair->couple));
        };

        /**
         * @var Coin $coin
         */
        foreach ($coins as $coin) {
            foreach ($coin->pairs ?? [] as $pair) {

                $symbol = $getSymbol($coin, $pair);

                var_dump($symbol);
                try {
                    $orders = $this->getAccountOrdersApi($symbol);
                } catch (\Exception $exception) {
                    \Log::error("Error get orders: " . $exception->getMessage() . 'symbol: ' . $symbol);
                }

                if (empty($orders)) {
                    continue;
                }

                dd($orders);


                foreach ($orders as $order) {
                    $newOrder = new Order();

                    $newOrder->coin_pair_id = $pair->id;
                    $newOrder->order_id = (int)$order['orderId'];
                    $newOrder->order_list_id = $order['orderListId'];
                    $newOrder->client_order_id = $order['clientOrderId'];
                    $newOrder->status = Order::getOrderStatusByName($order['status']);
                    $newOrder->type = Order::getOrderTypeByName($order['type']);
                    $newOrder->side = Order::getOrderSideByName($order['side']);
                    $newOrder->price = (float)$order['price'];
                    $newOrder->orig_qty = (float)$order['origQty'];
                    $newOrder->executed_qty = (float)$order['executedQty'];
                    $newOrder->cummulative_quote_qty = (float)$order['cummulativeQuoteQty'];
                    $newOrder->stop_price = (float)$order['stopPrice'];
                    $newOrder->iceberg_qty = (float)$order['icebergQty'];
                    $newOrder->orig_quote_order_qty = (float)$order['origQuoteOrderQty'];
                    $newOrder->time = $getTime($order['time']);
                    $newOrder->update_time = $getTime($order['updateTime']);

                    try {
                        $newOrder->save();
                    } catch (\Exception $exception) {
//                        if (substr_count($exception->getMessage(), ' duplicate key value violates unique constraint "orders_order_id"')) {
//                            $this->error('Error order exist');
//                        }
                    }
                }
            }
        }
    }
}
