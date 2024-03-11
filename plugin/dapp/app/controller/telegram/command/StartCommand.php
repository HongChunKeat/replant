<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;

class StartCommand extends SystemCommand
{
    protected $name = "start";

    protected $description = "start command";

    protected $usage = "/start";

    public function execute(): ServerResponse
    {
        return $this->replyToChat("Hi There!");
    }
}