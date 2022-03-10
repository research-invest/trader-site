<?php

namespace App\Console\Commands;

use App\Models\Account\Account;
use App\Models\Coin;
use App\Models\CoinPair;
use Illuminate\Console\Command;

/**
 * Class ExchangeInfo
 * @doc https://binance-docs.github.io/apidocs/spot/en/#cancel-all-open-orders-on-a-symbol-trade
 * @doc https://github.com/binance/binance-spot-api-docs/blob/master/rest-api.md
 * @package App\Console\Commands
 */
class ExchangeInfo extends Command
{
    const STATUS_TRADING = 'TRADING';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exchange-info:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'exchange-info';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        /**
         * @var $account Account
         */
        $account = Account::query()->enabled()->first();

        if (!$account) {
            $this->error('no account');
            return;
        }

        $exchangeInfo = $account->getAccountExchangeInfoApi();

//        ^ array:18 [
//        "symbol" => "ETHBTC"
//  "status" => "TRADING"
//  "baseAsset" => "ETH"
//  "baseAssetPrecision" => 8
//  "quoteAsset" => "BTC"
//  "quotePrecision" => 8
//  "quoteAssetPrecision" => 8
//  "baseCommissionPrecision" => 8
//  "quoteCommissionPrecision" => 8
//  "orderTypes" => array:5 [
//        0 => "LIMIT"
//    1 => "LIMIT_MAKER"
//    2 => "MARKET"
//    3 => "STOP_LOSS_LIMIT"
//    4 => "TAKE_PROFIT_LIMIT"
//  ]
//  "icebergAllowed" => true
//  "ocoAllowed" => true
//  "quoteOrderQtyMarketAllowed" => true
//  "allowTrailingStop" => false
//  "isSpotTradingAllowed" => true
//  "isMarginTradingAllowed" => true
//  "filters" => array:8 [
//        0 => array:4 [
//        "filterType" => "PRICE_FILTER"
//      "minPrice" => "0.00000100"
//      "maxPrice" => "922327.00000000"
//      "tickSize" => "0.00000100"
//    ]
//    1 => array:4 [
//        "filterType" => "PERCENT_PRICE"
//      "multiplierUp" => "5"
//      "multiplierDown" => "0.2"
//      "avgPriceMins" => 5
//    ]
//    2 => array:4 [
//        "filterType" => "LOT_SIZE"
//      "minQty" => "0.00010000"
//      "maxQty" => "100000.00000000"
//      "stepSize" => "0.00010000"
//    ]
//    3 => array:4 [
//        "filterType" => "MIN_NOTIONAL"
//      "minNotional" => "0.00010000"
//      "applyToMarket" => true
//      "avgPriceMins" => 5
//    ]
//    4 => array:2 [
//        "filterType" => "ICEBERG_PARTS"
//      "limit" => 10
//    ]
//    5 => array:4 [
//        "filterType" => "MARKET_LOT_SIZE"
//      "minQty" => "0.00000000"
//      "maxQty" => "983.77465851"
//      "stepSize" => "0.00000000"
//    ]
//    6 => array:2 [
//        "filterType" => "MAX_NUM_ORDERS"
//      "maxNumOrders" => 200
//    ]
//    7 => array:2 [
//        "filterType" => "MAX_NUM_ALGO_ORDERS"
//      "maxNumAlgoOrders" => 5
//    ]
//  ]
//  "permissions" => array:2 [
//        0 => "SPOT"
//    1 => "MARGIN"
//  ]
//]


        $enabledQuoteAssets = [
            'BTC', 'ETH', 'USDT', 'BNB', 'BUSD', 'RUB'
        ];


//        PRE_TRADING
//TRADING
//POST_TRADING
//END_OF_DAY
//HALT
//AUCTION_MATCH
//BREAK

        $coins = [];
        foreach ($exchangeInfo['symbols'] as $symbol => $data) {
            if (in_array($data['quoteAsset'], $enabledQuoteAssets)) {
                $isEnabledCoin = $data['status'] === self::STATUS_TRADING
                    && $data['isSpotTradingAllowed'] === true; // temp

                $coins[$data['baseAsset']][$data['quoteAsset']] = $isEnabledCoin;
            }
        }

        $this->output->progressStart(count($coins));

        foreach ($coins as $baseAsset => $quoteAssets) {

            /**
             * @var Coin $coin
             * @var CoinPair $pair
             */
            $coin = Coin::whereCode($baseAsset)->first();

            if (!$coin) {
                $coin = Coin::create([
                    'name' => $baseAsset,
                    'code' => $baseAsset,
                    'is_enabled' => Coin::IS_ENABLED_TRUE,
                ]);
            }

            $countDisabled = 0;
            foreach ($quoteAssets as $quoteAsset => $isEnabled) {
                $pair = CoinPair::query()->where('coin_id', $coin->id)
                    ->where('couple', $quoteAsset)->first();

                if (empty($pair)) {
                    $pair = new CoinPair();
                    $pair->couple = $quoteAsset;
                    $pair->coin_id = $coin->id;
                    $pair->is_enabled = $isEnabled ? CoinPair::IS_ENABLED_TRUE : CoinPair::IS_ENABLED_FALSE;
                    $pair->save();
                } else if ($pair->isEnabledTrue() !== $isEnabled) {
                    $pair->is_enabled = $isEnabled ? CoinPair::IS_ENABLED_TRUE : CoinPair::IS_ENABLED_FALSE;
                    $pair->save();
                }

                $countDisabled = !$isEnabled ? (++$countDisabled) : $countDisabled;
            }

            if ($countDisabled === count($quoteAssets)) {
                $coin->is_enabled = Coin::IS_ENABLED_FALSE;
                $coin->save();
            }

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();

        //time
    }
}
