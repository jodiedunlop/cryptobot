<?php
namespace App\Jobs;

use App\Models\VO\PriceRequest;
use App\Replies\PriceReply;
use BotMan\BotMan\BotMan;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class PriceReplyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $bot;

    protected $priceRequest;

    /**
     * Create a new job instance.
     *
     * @param BotMan $bot
     * @param PriceRequest $priceRequest
     */
    public function __construct(BotMan $bot, PriceRequest $priceRequest)
    {
        $this->bot = $bot;
        $this->priceRequest = $priceRequest;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        Log::info('Sending PriceReply in PriceReplyJob');
        (new PriceReply($this->bot))->send($this->priceRequest);
    }
}
