<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Coin extends Model
{
    use HasFactory;

    const ONE_HOUR = '1h';
    const SIX_HOUR = '6h';

    protected $intervals = [
        '1m',
        '3m',
        '5m',
        '15m',
        '30m',
        self::ONE_HOUR,
        '2h',
        '4h',
        self::SIX_HOUR,
        '8h',
        '12h',
        '1d',
        '3d',
        '1w',
        '1M',
    ];

    protected $table = 'coins';

    protected $fillable = [
        'name', 'code', 'is_enabled'
    ];

    public function pairs()
    {
        return $this->hasMany(CoinPair::class)->enabled();
    }
}
