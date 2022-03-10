<?php

namespace App\Console\Commands\SpotAccountTrade;

use App\Models\Account\Account;
use Illuminate\Console\Command;

/**
 * Class BalanceCommand
 * @doc https://binance-docs.github.io/apidocs/spot/en/#cancel-all-open-orders-on-a-symbol-trade
 * @doc https://github.com/binance/binance-spot-api-docs/blob/master/rest-api.md
 * @package App\Console\Commands
 */
class BalanceCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spot-account-trade-get-balance:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check an orders status, account info';

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
        $accounts = Account::query()->enabled()->get();

        $this->output->progressStart(count($accounts));

        /**
         * @var Account $account
         */
        foreach ($accounts as $account) {
            $account->setBalances();
            $this->output->progressAdvance();
        }

        $this->output->progressFinish();

        //time
    }
}
