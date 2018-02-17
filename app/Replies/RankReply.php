<?php

namespace App\Replies;

use App\Models\Coin;
use App\Models\VO\PriceRequest;
use App\Services\CoinDataService;
use App\Util\PriceUtil;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Messages\Attachments\Image;
use BotMan\BotMan\Messages\Outgoing\OutgoingMessage;
use function GuzzleHttp\Promise\promise_for;
use Illuminate\Support\Facades\Log;

class RankReply extends AbstractReply
{
    /** @var BotMan */
    protected $bot;

    /** @var PriceRequest */
    protected $priceRequest;

    public function __construct(BotMan $bot)
    {
        $this->bot = $bot;
    }

    public function send(): void
    {
        $text = '';

        /** @var Coin[] $coinList */
        $coinList = Coin::orderBy('rank', 'ASC')
            ->where('rank', '!=', 0)
            ->limit(20);

        foreach ($coinList->get() as $coin) {
            $text .=
                sprintf('*%d.* %s - `$%s` `%sBTC` ▲%s  Supply:`%s` | Cap:`$%s`',
                    $coin->rank,
                    $coin->full_name,
                    PriceUtil::formatDecimal($coin->priceFor('usd')),
                    PriceUtil::formatDecimal($coin->priceFor('btc')),
                    PriceUtil::formatPercentage($coin->percent_change_24h),
                    PriceUtil::formatLargeAmount($coin->total_supply),
                    PriceUtil::formatLargeAmount($coin->market_cap_usd)
                ) . "\n";
        }
        Log::info('Sending reply: ' . $text);
        $this->bot->reply($text);
    }
}
