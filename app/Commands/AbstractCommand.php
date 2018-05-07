<?php
namespace App\Commands;

use BotMan\BotMan\BotMan;

abstract class AbstractCommand
{
    protected $bot;

    public function __construct()
    {
    }

    protected function replyFail()
    {
        $this->bot->reply("Mate.");
    }

    protected function failOnException(\Closure $closure)
    {
        try {
            $closure();
        } catch (\Exception $e) {
            $this->replyFail();
        }
    }
}
