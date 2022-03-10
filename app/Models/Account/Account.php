<?php

namespace App\Models\Account;

use App\Models\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Account extends Model
{
    use HasFactory;
    use BinanceApiAccount;

    protected $table = 'accounts';
}
