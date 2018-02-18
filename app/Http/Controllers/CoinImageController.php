<?php

namespace App\Http\Controllers;

use App\Models\Coin;
use Illuminate\Http\Request;
use Spatie\Browsershot\Browsershot;

class CoinImageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $limit = $this->getLimit();
        $offset = $this->getOffset();

        return response(
            Browsershot::url(url("/coins?offset=$offset&limit=$limit"))
//                ->noSandbox()
                ->windowSize(800, 600)
                ->waitUntilNetworkIdle()
                ->fullPage()
                ->deviceScaleFactor(2)
                ->screenshot(),
            200,
            [
                'Content-Type' => 'image/png',
            ]
        );
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function gainers()
    {
        $limit = $this->getLimit(10);
        $offset = $this->getOffset();
        $period = $this->getChangePeriod();

        return response(
            Browsershot::url(url("/coins/gainers?period=$period&offset=$offset&limit=$limit"))
//                ->noSandbox()
                ->windowSize(800, 600)
                ->waitUntilNetworkIdle()
                ->fullPage()
                ->deviceScaleFactor(2)
                ->screenshot(),
            200,
            [
                'Content-Type' => 'image/png',
            ]
        );
    }
}
