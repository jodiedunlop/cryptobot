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
        if (\strlen($parts[1]) < 2) {
            $amount = $parts[0].'.'.sprintf('%02d', $parts[1]);
        }

        return $amount;
    }

    public static function formatPercentage($amount): string
    {
        $amount = rtrim(number_format($amount, 2), '0');
        $parts = explode('.', $amount);
        if (empty($parts[1])) {
            $amount = $parts[0];
        } elseif (\strlen($parts[1]) < 2) {
            $amount = $parts[0].'.'.sprintf('%02d', $parts[1]);
        }

        return $amount.'%';
    }

    public static function formatLargeAmount($amount): string
    {
        $output = '';
        if ($amount > 1000000000) {
            // Billions
            $output = sprintf('%sbn', number_format($amount / 1000000000, 2));
        } elseif ($amount / 1000000){
            // Millions
            $output = sprintf('%sm', number_format($amount / 1000000, 2));
        } elseif ($amount / 1000) {
            // Thousands
            $output = sprintf('%sk', number_format($amount / 1000000, 2));
        } else {
            $output = sprintf('%s', number_format($amount, 2));
        }

        return $output;
    }
}