<?php

namespace App\Replies;

use App\Models\VO\PriceRequest;
use App\Services\CoinDataService;
use App\Util\PriceUtil;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Messages\Attachments\Image;
use BotMan\BotMan\Messages\Outgoing\OutgoingMessage;
use function GuzzleHttp\Promise\promise_for;
use Illuminate\Support\Facades\Log;

class PriceReply extends AbstractReply
{
    /** @var BotMan */
    protected $bot;

    /** @var PriceRequest */
    protected $priceRequest;

    public function __construct(BotMan $bot)
    {
        $this->bot = $bot;
    }

    public function send(PriceRequest $priceRequest): void
    {
        try {
            $service = new CoinDataService();
            $price = $service->price($priceRequest);
        } catch (\Exception $e) {
            $this->bot->reply('Mate. ' . $e->getMessage());
            return;
        }
        $coin = $price->getCoin();
        Log::info('Price is', $price->getPrices());

        // Build a reply message
        $text = "The price of {$coin->full_name} ";
        if ($priceRequest->hasDate()) {
            $text .= 'at ' . $price->getDate()->toDateTimeString() . ' was ';
        } else {
            $text .= 'is ';
        }
        $text .= '*$' . PriceUtil::formatDecimal($price->getPrice('USD')) . '* USD';

        $fields = [];
        foreach ($price->getPrices() as $key => $value) {
            if ($coin->symbol() === $key) {
                continue;
            }
            $fields[] = [
                'title' => $key,
                'value' => !empty($value) ? PriceUtil::formatDecimal($value) : '0.00',
                'short' => true,
            ];
        }

        Log::info('Sending reply: ' . $text);
        $this->bot->reply($text, [
            'attachments' => json_encode([
                [
                    'title' => $coin->name,
                    'fields' => $fields,
                    'color' => '#3AA3E3', // 'good', 'warning', 'bad'
                    'thumb_url' => $coin->thumbUrl(),
                    'ts' => $price->getTimestamp(),
                ],
                [
                    'title' => 'Current price change',
                    'fields' => [
                        [
                            'title' => '1 Hour',
                            'value' => $this->formatPercentage($coin->percent_change_1h),
                            'short' => true,
                        ],
                        [
                            'title' => '24 Hours',
                            'value' => $this->formatPercentage($coin->percent_change_24h),
                            'short' => true,
                        ],
                        [
                            'title' => '7 Days',
                            'value' => $this->formatPercentage($coin->percent_change_7d),
                            'short' => true,
                        ],
                    ]
                ]
            ])
        ]);

    }

    protected function formatPercentage($val): string
    {
        return sprintf('%s %s',
            $val < 0 ? ':small_red_triangle_down:' : ':arrow_up_small:',
            PriceUtil::formatPercentage($val)
        );
    }
}
