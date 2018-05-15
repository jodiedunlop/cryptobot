<?php

namespace App\Replies\Game;

use App\Models\Coin;
use App\Models\Player;
use App\Replies\AbstractReply;
use App\Services\GameService;
use BotMan\BotMan\BotMan;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PortfolioReply extends AbstractReply implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var BotMan */
    protected $bot;

    /** @var Player */
    protected $player;

    public function __construct(BotMan $bot, Player $player)
    {
        $this->bot = $bot;
        $this->player = $player;
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

        $coinGroups = DB::table('player_trades')
            ->select('coin_id', DB::raw('SUM(amount) as total'))
            ->where('player_id', $this->player->id)
            ->groupBy('coin_id')
            ->having('total', '>', 0)
            ->orderBy('total', 'DESC')
            ->get();

        $attachments = [];
        $portfolioValueUsd = 0;
        foreach ($coinGroups as $coinGroup) {
            Log::debug('Coin group', (array)$coinGroup);
            Log::debug($coinGroup->coin_id);
            if (($coin = Coin::find
                ($coinGroup->coin_id)) === null) {
                continue;
            }
            Log::debug($coin->symbol());
            $coinPriceUsd = $gameService->getPriceOfCoin($coin);
            $totalValueUsd = $coinGroup->total * $coinPriceUsd;
            $portfolioValueUsd += $totalValueUsd;
            $totalValueUsdFormatted = '$'.number_format($totalValueUsd, 2);
            $attachments[] = [
                'fallback' => "{$coinGroup->total} {$coin->symbol()} = $totalValueUsdFormatted",
                'color' => '#36a64f',
                'title' => $coin->symbol(),
//                'title_link' => '',
                'fields' => [
                    [
                        'title' => 'Amount',
                        'value' => $coinGroup->total,
                        'short' => true,
                    ],
                    [
                        'title' => 'USD Value',
                        'value' => $totalValueUsdFormatted,
                        'short' => true,
                    ],
                ],
            ];
        }

        $totalFundsUsd = $this->player->getTransactionBalance();
        $attachments[] = [
            'fallback' => "Portfolio value = $portfolioValueUsd",
            'color' => '#36a64f',
            'fields' => [
                [
                    'title' => 'Portfolio Value',
                    'value' => '$'.number_format($portfolioValueUsd, 2),
                    'short' => true,
                ],
                [
                    'title' => 'Available USD',
                    'value' => '$'.number_format($totalFundsUsd, 2),
                    'short' => true,
                ],
            ],
        ];

        $totalNetWorthUsd = $totalFundsUsd + $portfolioValueUsd;
        $totalNetworthUsdFormatted = '$'.number_format($totalNetWorthUsd, 2);
        $this->bot->reply(
            "Current net worth is *{$totalNetworthUsdFormatted}* USD:", [
            'attachments' => json_encode($attachments),
        ]);
    }
}
