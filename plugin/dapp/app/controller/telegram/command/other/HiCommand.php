<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;

class HiCommand extends UserCommand
{
    protected $name = "hi";

    protected $description = "Hi command";

    protected $usage = "/hi";

    public function execute(): ServerResponse
    {
        $message = $this->getMessage();
        $from = $message->getFrom();

        return $this->replyToChat("Hi ! " . $from->getFirstName() . " " . $from->getLastName());
    }
}