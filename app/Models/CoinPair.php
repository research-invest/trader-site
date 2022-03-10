<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class CoinPair extends Model
{
    use HasFactory;

    protected $table = 'coins_pairs';

    const STABLE_COIN_USDT = 'USDT';
    const STABLE_COIN_BUSD = 'BUSD';
}
