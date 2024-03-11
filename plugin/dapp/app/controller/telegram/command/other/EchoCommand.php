<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\ChatAction;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;

class EchoCommand extends UserCommand
{
    protected $name = "echo";

    protected $description = "Echo command";

    protected $usage = "/echo <text>";

    public function execute(): ServerResponse
    {
        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();
        $text = $message->getText(true);

        // Send chat action "typing..."
        Request::sendChatAction([
            "chat_id" => $chat_id,
            "action"  => ChatAction::TYPING,
        ]);

        Request::deleteMessage([
            "chat_id"    => $chat_id,
            "message_id" => $message->getMessageId(),
        ]);

        if ($text === "") {
            return $this->replyToChat("Command usage: " . $this->getUsage());
        }

        return $this->replyToChat($text);
    }
}