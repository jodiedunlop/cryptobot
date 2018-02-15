<?php
namespace App\Replies;

use App\Models\VO\PriceRequest;
use App\Services\CoinDataService;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Messages\Attachments\Image;
use BotMan\BotMan\Messages\Outgoing\OutgoingMessage;
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
            $text .= 'at ' . $price->getDate()->toDateTimeString() . " was ";
        } else {
            $text .= "is ";
        }
        $text .= "*\${$price->getPrice('USD')}USD*";

        $fields = [];
        foreach ($price->getPrices() as $key => $value) {
            if ($coin->symbol() === $key) {
                continue;
            }
            $fields[] = [
                'title' => $key,
                'value' => (float)$value,
                'short' => true,
            ];
        }

        Log::info('Sending reply: '.$text);
        $this->bot->reply($text, [
            'attachments' => json_encode([
                [
                    'title' => $coin->name,
                    'fields' => $fields,
                    'color' => '#3AA3E3', // 'good', 'warning', 'bad'
                    'thumb_url' => $coin->thumbUrl(),
                    'ts' => $price->getTimestamp(),
                ],
            ])
        ]);
    }
}