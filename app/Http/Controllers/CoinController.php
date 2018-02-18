<?php

namespace App\Http\Controllers;

use App\Models\Coin;
use Illuminate\Http\Request;
use App\Util\PriceUtil;
use Illuminate\Support\Facades\Log;
use Spatie\Browsershot\Browsershot;

class CoinController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $coins = Coin::where('rank', '!=', 0)
            ->orderBy('rank', 'ASC')
            ->limit($this->getLimit())
            ->offset($this->getOffset())
            ->get();

        return view('coins.index', [
            'coins' => $coins,
        ]);
    }

    public function gainers()
    {
        $changePeriod = $this->getChangePeriod();
        $coins = Coin::gainers($changePeriod)
            ->limit($this->getLimit(10))
            ->offset($this->getOffset())
            ->get();

        return view('coins.gainers', compact(
            'coins',
            'changePeriod'
        ));
    }


}
