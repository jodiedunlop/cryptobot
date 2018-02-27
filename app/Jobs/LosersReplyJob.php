<?php
namespace App\Jobs;

use App\Models\VO\LosersRequest;
use App\Replies\LosersReply;
use BotMan\BotMan\BotMan;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class LosersReplyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $bot;

    protected $request;

    /**
     * Create a new job instance.
     *
     * @param BotMan $bot
     * @param LosersRequest $request
     */
    public function __construct(BotMan $bot, LosersRequest $request)
    {
        $this->bot = $bot;
        $this->request = $request;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        Log::info('Sending LosersReply');
        (new LosersReply($this->bot))->send($this->request);
    }
}
