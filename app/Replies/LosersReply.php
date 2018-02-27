<?php

namespace App\Replies;

use App\Models\Coin;
use App\Models\VO\LosersRequest;
use App\Models\VO\PriceRequest;
use App\Util\PriceUtil;
use BotMan\BotMan\BotMan;
use Illuminate\Support\Facades\Log;

class LosersReply extends AbstractReply
{
    /** @var BotMan */
    protected $bot;

    /** @var PriceRequest */
    protected $priceRequest;

    public function __construct(BotMan $bot)
    {
        $this->bot = $bot;
    }

    public function send(LosersRequest $request): void
    {
        $changeField = null;
        switch ($request->getPeriod()) {
            case LosersRequest::PERIOD_1H:
                $changeField = 'percent_change_1h';
                break;
            case LosersRequest::PERIOD_24H:
                $changeField = 'percent_change_24h';
                break;
            case LosersRequest::PERIOD_7D:
                $changeField = 'percent_change_7d';
                break;
            default:
                $changeField = 'percent_change_24h';
                break;
        }

        /** @var Coin[] $coinList */
        $coinList = Coin::orderBy($changeField, 'ASC')
            ->where('rank', '>', $request->getMin())
            ->where('rank', '<', $request->getMax())
            ->limit($request->getLimit());

        $text = 'Losers: '.$request->getPeriodDescription().":\n";
        foreach ($coinList->get() as $coin) {
            $text .=
                sprintf('*#%d* %s - `$%s` `%sBTC` %s%s  Supply:`%s` | Cap:`$%s`',
                    $coin->rank,
                    $coin->full_name,
                    PriceUtil::formatDecimal($coin->priceFor('usd')),
                    PriceUtil::formatDecimal($coin->priceFor('btc')),
                    $coin->$changeField < 0 ? '▼' : '▲',
                    PriceUtil::formatPercentage($coin->$changeField),
                    PriceUtil::formatLargeAmount($coin->total_supply),
                    PriceUtil::formatLargeAmount($coin->market_cap_usd)
                ) . "\n";
        }
        Log::info('Sending reply: ' . $text);
        $this->bot->reply($text);
    }
}
