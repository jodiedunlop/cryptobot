<?php

namespace App\Replies\Game;

use App\Models\Game;
use App\Replies\AbstractReply;
use App\Services\GameService;
use BotMan\BotMan\BotMan;
use BotMan\Drivers\Slack\SlackDriver;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class GameStatusReply extends AbstractReply implements ShouldQueue
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

        $recipientId = $this->getRecipientId();
        $leaderboard = $gameService->getLeaderboard($this->game);
        $first = $leaderboard->shift();
        $last = $leaderboard->pop();

        $attachments = [];

        // Add player in first place
        if ($first !== null) {
            $attachments[] = [
                'fallback' => "{$first['player_name']} is first with total {$first['total']}",
                'color' => '#38C172',
                'title' => ":muscle: Currently 1st Place - {$first['player_name']} - \$" . number_format($first['total'],
                        2),
                //            'text' => 'Total net worth: *$'.number_format($first['total'], 2).'*',
            ];
        }

        // Provide the position of up to 20 of the other players
        $otherPlayers = $leaderboard->take(20);
        if ($otherPlayers->count()) {
            $playerPosition = 2;
            $attachments[] = [
                'fallback' => 'Other players',
                'color' => '#64D5CA',
                'text' => implode(', ', $otherPlayers->map(function($entry) use (&$playerPosition) {
                    return sprintf('#%d %s', $playerPosition++, $entry['player_name']);
                })->toArray()),
            ];
        }

        // Show the last place for the funz
        if ($last !== null) {
            $attachments[] = [
                'fallback' => "{$last['player_name']} is last with total {$last['total']}",
                'color' => '#EF5753',
                'title' => ":rip: Last Place - {$last['player_name']} - \$" . number_format($last['total'],
                        2),
                //            'text' => 'Total net worth: *$'.number_format($last['total'], 2).'*',
            ];
        }

        $attachments[] = [
            'fields' => [
                [
                    "value" => ':hourglass: Finishes '.$this->game->finishes_at->timezone('Australia/Brisbane')->format('H:ia, D jS M'),
                    "short" => true,
                ],
            ],
        ];

        Log::debug("Game update to $recipientId", $attachments);
        try {
            $this->bot->say('Game update', [$recipientId], SlackDriver::class, [
                'attachments' => json_encode($attachments),
            ]);
        } catch (\Exception $e) {
            Log::error('Error while sending game status update:'.$e->message());
        }
    }

    protected function getRecipientId()
    {
        $recipientId = null;

        // See if there is a message we can get the recipient ID from
        if (($message = $this->bot->getMessage()) !== null) {
            $platformId = $this->bot->getMessage()->getRecipient();
            if (!empty($platformId)) {
                $recipientId = $platformId;
                Log::debug('Got recipient from message: '.$recipientId);
            }
        }

        // Default to using the ID assigned to the game
        return $recipientId ?? $this->game->platform_id;
    }
}
