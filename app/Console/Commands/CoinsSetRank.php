<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CoinsSetRank extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coins-set-rank:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate rank coin';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $timeExecute = -microtime(true);

        try {
            $sqlReCalculate = <<<SQL
WITH coin_asset_volume AS (
    SELECT
        DISTINCT ON (k.coin_pair_id) k.coin_pair_id,
            cp.coin_id,
            k.quote_asset_volume,
            row_number() OVER (ORDER BY k.quote_asset_volume DESC) AS rank
    FROM klines AS k
    INNER JOIN coins_pairs AS cp ON cp.id = k.coin_pair_id
    ORDER BY k.coin_pair_id, k.quote_asset_volume DESC
)

UPDATE coins SET rank = volume.rank
FROM coin_asset_volume AS volume
WHERE volume.coin_id = coins.id;
SQL;
            $countUpdated = DB::affectingStatement($sqlReCalculate);

            $this->line(sprintf('Updated coins: %s', $countUpdated));
        } catch (\Exception $e) {
            \Log::error('Error recalculate coins rank - ' . $e->getMessage());
            $this->error('Error recalculate coins rank: ' . $e->getMessage());
            return;
        }

        $timeExecute += microtime(true);
        $timeWorking = sprintf('%f', $timeExecute);

        $this->line('Recalculate rank time: ' . $timeWorking);
    }
}
