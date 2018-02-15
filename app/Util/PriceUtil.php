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

    public static function formatDecimal($amount): string
    {
        $amount = rtrim(number_format($amount, 8), '0');
        $parts = explode('.', $amount);
        echo "$amount\n";
        if (\strlen($parts[1]) < 2) {
            $amount = $parts[0].'.'.sprintf('%02d', $parts[1]);
        }

        return $amount;
    }
}