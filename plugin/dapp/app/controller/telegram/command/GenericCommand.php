<?php

/**
 * Generic command
 *
 * Gets executed for generic commands, when no other appropriate one is found.
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;

class GenericCommand extends SystemCommand
{
    protected $name = "generic";

    protected $description = "Handles generic commands or is executed by default when a command is not found";

    public function execute(): ServerResponse
    {
        $message = $this->getMessage();
        if($message != null) {
            $command = $message->getCommand();
            return $this->replyToChat("Command /{$command} not found.. :(");
        }

        return Request::emptyResponse();
    }
}