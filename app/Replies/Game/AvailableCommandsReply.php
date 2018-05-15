<?php

namespace App\Replies\Game;

use App\Replies\AbstractReply;
use BotMan\BotMan\BotMan;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class AvailableCommandsReply extends AbstractReply implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var BotMan */
    protected $bot;

    public function __construct(BotMan $bot)
    {
        $this->bot = $bot;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->bot->reply('Available commands:', [
            'attachments' => json_encode([
                [
                    'text' =>
                        "`play` - join the current game\n" .
                        "`buy <amount> <coin>` - to buy coins with your available funds\n" .
                        "`sell <amount> <coin>` - to sell your coins and increase your funds\n" .
                        "`portfolio` - view your current coin portfolio\n" .
                        "`leaderboard` - view the leaderboard\n" .
                        "`funds` - view your available funds\n" .
                        "`new game` - start a new game".
                        "`help` - view this command list",
                    'color' => '#3AA3E3', // 'good', 'warning', 'bad'
                ]
            ])
        ]);
    }
}
