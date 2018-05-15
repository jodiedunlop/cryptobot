<?php

namespace App\Replies\Game;

use App\Models\Game;
use App\Models\Player;
use App\Replies\AbstractReply;
use App\Services\GameService;
use BotMan\BotMan\BotMan;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class LeaderboardReply extends AbstractReply implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var BotMan */
    protected $bot;

    /** @var Game */
    protected $game;

    public function __construct(BotMan $bot, Game $game)
    {
        $this->bot = $bot;
        $this->game = $game;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        /** @var GameService $gameService */
        $gameService = resolve(GameService::class);

        $attachments = [];
        $position = 0;
        $colours = ['#FFBBCA','#FA7EA8','#F66D9B','#EB5286','#6F213F','#451225'];
        foreach ($gameService->getLeaderboard($this->game) as $entry) {
            $position++;

            $colour = \count($colours) > 1 ? array_shift($colours) : $colours[0];

            $attachments[] = [
                'fallback' => "{$entry['player_name']} with total {$entry['total']}",
                'color' => $colour,
                'title' => "#${position} - {$entry['player_name']}",
                'text' => 'Total net worth: *$'.number_format($entry['total'], 2).'*',
                'footer' =>
                    'Funds Available: $'.number_format($entry['funds_available'], 2).
                    ' | Portfolio Value: $'.number_format($entry['portfolio_value'], 2)
            ];
        }

        Log::debug($attachments);

        $this->bot->reply('Current leaderboard', [
            'attachments' => json_encode($attachments),
        ]);
    }
}
