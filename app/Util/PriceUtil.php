<?php
namespace App\Util;

class PriceUtil
{
    /**
     * @param string $symbol
     * @return string
     */
    public static function sanitizeSymbol(string $symbol): string
    {
        return strtoupper(trim($symbol));
    }
}