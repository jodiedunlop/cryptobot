<?php
namespace App\Jobs;

use App\Models\VO\GainersRequest;
use App\Replies\GainersReply;
use BotMan\BotMan\BotMan;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class GainersReplyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $bot;

    protected $request;

    /**
     * Create a new job instance.
     *
     * @param BotMan $bot
     * @param GainersRequest $request
     */
    public function __construct(BotMan $bot, GainersRequest $request)
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
        Log::info('Sending GainersReply');
        (new GainersReply($this->bot))->send($this->request);
    }
}
