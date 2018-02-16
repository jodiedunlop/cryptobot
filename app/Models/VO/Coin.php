<?php
/**
 * Created by PhpStorm.
 * User: jd
 * Date: 13/2/18
 * Time: 9:28 AM
 */

namespace App\Models\VO;


use App\Util\PriceUtil;

class Coin
{
    protected $props = [];

    public function __construct(string $symbol, array $props)
    {
        $this->props = array_merge($props, [
            'symbol' => PriceUtil::sanitizeSymbol($symbol),
        ]);
    }

    public function symbol(): string
    {
        return $this->props['symbol'];
    }

    public function model(): \App\Models\Coin
    {

    }

    public function thumbUrl(): string
    {
        return $this->props['thumb_url'] ??
            'https://raw.githubusercontent.com/cjdowner/cryptocurrency-icons/master/128/color/'.
            strtolower($this->symbol()).
            '.png';
    }

    public function get($propKey, $default = null)
    {
        return $this->props[$propKey] ?? $default;
    }

    public function set($propKey, $val)
    {
        $this->props[$propKey] = $val;
        return $this;
    }

    public function has($propKey): bool
    {
        return isset($this->props[$propKey]);
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    public function __isset($name)
    {
        return $this->has($name);
    }


}
