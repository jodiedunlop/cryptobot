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
use Spatie\Browsershot\Browsershot;

class GainersImageReply extends AbstractReply
{
    /** @var BotMan */
    protected $bot;

    /** @var PriceRequest */
    protected $priceRequest;

    public function __construct(BotMan $bot)
    {
        $this->bot = $bot;
    }

    /**
     * @param GainersRequest $request
     * @throws \Spatie\Browsershot\Exceptions\CouldNotTakeBrowsershot
     */
    public function send(GainersRequest $request): void
    {
        $period = $request->getPeriod();
        $imgName = sprintf('gainers_%s_%s.png', $period, date('Ymd'));
        $imgUrl = url('storage/'.$imgName);
        Browsershot::url(url("/coins/gainers?period=$period"))
//                ->noSandbox()
            ->windowSize(800, 600)
            ->waitUntilNetworkIdle()
            ->fullPage()
            ->deviceScaleFactor(2)
            ->save(storage_path('app/public/'.$imgName));

        $text = 'Gainers over the '.$request->getPeriodDescription();
        Log::info("Sending reply: $text (with image)");
        $image = Image::url($imgUrl)->title($text);
        $message = OutgoingMessage::create($text)
            ->withAttachment($image);


        $this->bot->reply($message);
    }
}
