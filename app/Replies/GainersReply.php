<?php

namespace App\Replies;

use App\Models\Coin;
use App\Models\VO\GainersRequest;
use App\Models\VO\PriceRequest;
use App\Services\CoinDataService;
use App\Util\PriceUtil;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Messages\Attachments\Image;
use BotMan\BotMan\Messages\Outgoing\OutgoingMessage;
use function GuzzleHttp\Promise\promise_for;
use Illuminate\Support\Facades\Log;

class GainersReply extends AbstractReply
{
    /** @var BotMan */
    protected $bot;

    /** @var PriceRequest */
    protected $priceRequest;

    public function __construct(BotMan $bot)
    {
        $this->bot = $bot;
    }

    public function send(GainersRequest $request): void
    {
        $changeField = null;
        switch ($request->getPeriod()) {
            case GainersRequest::PERIOD_1H:
                $changeField = 'percent_change_1h';
                break;
            case GainersRequest::PERIOD_24H:
                $changeField = 'percent_change_24h';
                break;
            case GainersRequest::PERIOD_7D:
                $changeField = 'percent_change_7d';
                break;
            default:
                $changeField = 'percent_change_24h';
                break;
        }

        /** @var Coin[] $coinList */
        $coinList = Coin::orderBy($changeField, 'DESC')
            ->where('rank', '>', $request->getMinRank())
            ->where('rank', '<', $request->getMaxRank())
            ->limit($request->getLimit());

        $text = 'Gainers over the '.$request->getPeriodDescription().":\n";
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
