<?php
/**
 * php artisan coins-set-interval:run
 */
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CoinsSetInterval extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coins-set-interval:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re interval coin';

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
            $sqlReInterval = <<<SQL
WITH coin_asset_volume AS (
    SELECT t.*,
    row_number() OVER (ORDER BY t.quote_asset_volume DESC) AS rank
    FROM (
        SELECT
            DISTINCT ON (k.coin_pair_id) k.coin_pair_id,
                cp.coin_id,
                k.quote_asset_volume
        FROM klines AS k
        INNER JOIN coins_pairs AS cp ON cp.id = k.coin_pair_id
        INNER JOIN coins AS c ON c.id = cp.coin_id
        WHERE k.open_time >= NOW() - interval '24 HOUR' AND c.is_enabled = 1
        ORDER BY k.coin_pair_id, k.quote_asset_volume DESC
    ) AS t
)

UPDATE coins
    SET interval = CASE
                 WHEN volume.rank <= 10 THEN '1m'
                 WHEN volume.rank > 10 AND volume.rank <= 20 THEN '3m'
                 WHEN volume.rank > 20 AND volume.rank <= 30 THEN '5m'
                 WHEN volume.rank > 30 AND volume.rank <= 40 THEN '15m'
                 WHEN volume.rank > 40 AND volume.rank <= 50 THEN '30m'
                 WHEN volume.rank > 50 AND volume.rank <= 80 THEN '1h'
                 WHEN volume.rank > 80 AND volume.rank <= 100 THEN '2h'
                ELSE '12h'
              END
FROM coin_asset_volume AS volume
WHERE volume.coin_id = coins.id;
SQL;

            $countUpdated = DB::affectingStatement($sqlReInterval);

            $this->line(sprintf('Updated coins: %s', $countUpdated));
        } catch (\Exception $e) {
            \Log::error('Error reinterval coins - ' . $e->getMessage());
            $this->error('Error reinterval coins: ' . $e->getMessage());
            return;
        }

        $timeExecute += microtime(true);
        $timeWorking = sprintf('%f', $timeExecute);

        $this->line('ReInterval time: ' . $timeWorking);
    }
}
