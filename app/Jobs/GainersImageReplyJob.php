<?php
namespace App\Jobs;

use App\Models\VO\GainersRequest;
use App\Replies\GainersImageReply;
use BotMan\BotMan\BotMan;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class GainersImageReplyJob implements ShouldQueue
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
     * @throws \Spatie\Browsershot\Exceptions\CouldNotTakeBrowsershot
     */
    public function handle(): void
    {
        Log::info('Sending GainersImageReply');
        (new GainersImageReply($this->bot))->send($this->request);
    }
}
