<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public const MAX_LIMIT = 200;
    public const DEFAULT_LIMIT = 50;

    protected function getLimit($default = self::DEFAULT_LIMIT)
    {
        $limit = (int)request()->input('limit', 0);
        if (!$limit || $limit < 0) {
            $limit = $default;
        } elseif ($limit > self::MAX_LIMIT) {
            $limit = self::MAX_LIMIT;
        }

        return $limit;
    }

    protected function getOffset()
    {
        $offset = (int)request()->input('offset', 0);
        if ($offset < 0) {
            $offset = 0;
        }
        return $offset;
    }

    protected function getChangePeriod($default = '24h')
    {
        $period = request()->input('period', $default);
        if (preg_match('/^(1h|24h|7d)$/i', $period)) {
            return $period;
        }

        return $default;
    }
}
