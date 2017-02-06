<?php

namespace App;

use Closure;
use DB;

class Transact
{
    public static function ion (Closure $next)
    {
        $result = false;

        try {
            DB::beginTransaction();

            $result = $next();

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }

        return $result;
    }
}
