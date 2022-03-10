<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as EModel;

class Model extends EModel
{
    const IS_ENABLED_TRUE = 1;
    const IS_ENABLED_FALSE = 0;

    public function scopeEnabled(Builder $query)
    {
        $query->where('is_enabled', '=', self::IS_ENABLED_TRUE);

        return $query;
    }

    public function isEnabledTrue(): bool
    {
        return (int)$this->is_enabled === self::IS_ENABLED_TRUE;
    }
}
