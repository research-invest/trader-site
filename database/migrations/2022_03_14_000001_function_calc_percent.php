<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FunctionCalcPercent extends Migration
{
    public function up()
    {
        $sql = <<<SQL
CREATE OR REPLACE FUNCTION CALC_PERCENT(old double precision, new double precision)
    RETURNS double precision
    LANGUAGE plpgsql
AS
$$
DECLARE
    decreaseValue double precision;
    result        double precision;
BEGIN
    decreaseValue = new - old;

    if old > new then
        result = CASE WHEN new > 0 THEN (decreaseValue / new) * 100 ELSE -100 END;
    else
        result = CASE WHEN old > 0 then (decreaseValue / old) * 100 ELSE 0 END;
    end if;

    RETURN ROUND(CAST(result AS NUMERIC), 3);
END;
$$;
SQL;

        DB::unprepared($sql);
    }

    public function down()
    {
        DB::unprepared('DROP FUNCTION IF EXISTS CALC_PERCENT(old double precision, new double precision);');
    }
}
