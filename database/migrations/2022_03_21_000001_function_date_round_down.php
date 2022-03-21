<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FunctionDateRoundDown extends Migration
{
    public function up()
    {
        $sql = <<<SQL
CREATE OR REPLACE FUNCTION DATE_ROUND_DOWN(base_date timestamptz, round_interval INTERVAL)
    RETURNS timestamptz
    LANGUAGE plpgsql
AS
$$
DECLARE
    result timestamptz;
BEGIN
    result = (SELECT TO_TIMESTAMP(EXTRACT(epoch FROM date_trunc('hour', $1))::INTEGER +
                                  trunc((EXTRACT(epoch FROM $1)::INTEGER -
                                         EXTRACT(epoch FROM date_trunc('hour', $1))::INTEGER) /
                                        EXTRACT(epoch FROM $2)::INTEGER) * EXTRACT(epoch FROM $2)::INTEGER) as result);

    RETURN result;
END;
$$;
SQL;

        DB::unprepared($sql);
    }

    public function down()
    {
        DB::unprepared('DROP FUNCTION IF EXISTS DATE_ROUND_DOWN(base_date timestamptz, round_interval INTERVAL);');
    }
}
