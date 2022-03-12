<?php

namespace App\Helpers;

class MathHelper
{
    public static function getPercentageChange($oldNumber, $newNumber): float|int
    {
        if ($oldNumber === 0 && $newNumber === 0) {
            return 0;
        }

        $decreaseValue = $newNumber - $oldNumber;

        if ($oldNumber > $newNumber) {
            $result = $newNumber > 0 ? ($decreaseValue / $newNumber) * 100 : -100;
        } else {
            $result = $oldNumber > 0 ? ($decreaseValue / $oldNumber) * 100 : 100;
        }

        return $result;
    }

}
