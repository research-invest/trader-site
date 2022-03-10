<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    public $timestamps = false;

    public function coinPair()
    {
        return $this->hasOne(CoinPair::class);
    }

    const ORDER_STATUS_ACTIVE = 'ACTIVE';
    const ORDER_STATUS_CANCELED = 'CANCELED';
    const ORDER_STATUS_FILLED = 'FILLED';

    const ORDER_STATUSES = [
        self::ORDER_STATUS_ACTIVE => 1,
        self::ORDER_STATUS_CANCELED => 2,
        self::ORDER_STATUS_FILLED => 3,
    ];

    public static function getOrderStatusByName($status): int
    {
        return self::ORDER_STATUSES[strtoupper($status)] ?? 0;
    }

    public static function getOrderStatusByInt($status): string
    {
        $statuses = array_flip(self::ORDER_STATUSES);
         return $statuses[(int)$status] ?? '';
    }

    const ORDER_TYPE_LIMIT = 'LIMIT';
    const ORDER_TYPE_MARKET = 'MARKET';
    const ORDER_TYPE_STOP_LOSS = 'STOP_LOSS';
    const ORDER_TYPE_STOP_LOSS_LIMIT = 'STOP_LOSS_LIMIT';
    const ORDER_TYPE_TAKE_PROFIT = 'TAKE_PROFIT';
    const ORDER_TYPE_TAKE_PROFIT_LIMIT = 'TAKE_PROFIT_LIMIT';
    const ORDER_TYPE_TAKE_LIMIT_MAKER = 'LIMIT_MAKER';

    const ORDER_TYPES = [
        self::ORDER_TYPE_LIMIT => 1,
        self::ORDER_TYPE_MARKET => 2,
        self::ORDER_TYPE_STOP_LOSS => 3,
        self::ORDER_TYPE_STOP_LOSS_LIMIT => 4,
        self::ORDER_TYPE_TAKE_PROFIT => 5,
        self::ORDER_TYPE_TAKE_PROFIT_LIMIT => 6,
        self::ORDER_TYPE_TAKE_LIMIT_MAKER => 7,
    ];

    public static function getOrderTypeByName($type): int
    {
        return self::ORDER_TYPES[strtoupper($type)] ?? 0;
    }

    public static function getOrderTypeByInt($type)
    {
        $types = array_flip(self::ORDER_TYPES);
        return $types[(int)$type] ?? '';
    }

    const ORDER_SIDE_BUY = 'BUY';
    const ORDER_SIDE_SELL = 'SELL';

    const ORDER_SIDES = [
        self::ORDER_SIDE_BUY => 1,
        self::ORDER_SIDE_SELL => 2,
    ];

    public static function getOrderSideByName($side): int
    {
        return self::ORDER_SIDES[strtoupper($side)] ?? 0;
    }

    public static function getOrderSideByInt($side)
    {
        $sides= array_flip(self::ORDER_SIDES);
        return $sides[(int)$side] ?? '';
    }
}
