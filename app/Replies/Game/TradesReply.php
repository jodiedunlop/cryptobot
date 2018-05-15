<?php

namespace App\Replies\Game;

use App\Models\Player;
use App\Models\PlayerTrade;
use App\Models\Support\TradeType;
use App\Replies\AbstractReply;
use BotMan\BotMan\BotMan;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class TradesReply extends AbstractReply implements ShouldQueue
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
        $attachments = [];
        $trades = $this->player
            ->trades()
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->get();

        /** @var PlayerTrade $trade */
        foreach ($trades as $trade) {
            $attachments[] = [
                'fallback' => $trade->description,
                'color' => $trade->type_id === TradeType::BUY ? '#F6993F' : '#36a64f',
                'title' => $trade->description,
//                'fields' => [
//                    [
//                        'title' => 'Amount',
//                        'value' => $coinGroup->total,
//                        'short' => true,
//                    ],
//                    [
//                        'title' => 'USD Value',
//                        'value' => $totalValueUsdFormatted,
//                        'short' => true,
//                    ],
//                ],
                'footer' => $trade->created_at->diffForHumans(),
            ];
        }


        $this->bot->reply("Last {$trades->count()} trades", [
            'attachments' => json_encode($attachments),
        ]);
    }
}
