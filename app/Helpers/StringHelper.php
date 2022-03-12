<?php

namespace App\Helpers;

class StringHelper
{

    public static function toInt($s): int
    {
        return (int)preg_replace('/[^\-\d]*(\-?\d*).*/', '$1', $s);
    }

    public static function toFloat($s): float
    {
        return (double)filter_var($s, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

    }

}
