<?php

/**
 * New chat members command
 *
 * Gets executed when a new member joins the chat.
 *
 * NOTE: This command must be called from GenericmessageCommand.php!
 * It is only in a separate command file for easier code maintenance.
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;

class NewchatmembersCommand extends SystemCommand
{
    protected $name = "newchatmembers";

    protected $description = "New Chat Members";

    public function execute(): ServerResponse
    {
        $message = $this->getMessage();
        $members = $message->getNewChatMembers();

        if ($message->botAddedInChat()) {
            return $this->replyToChat("Hi there, you BOT!");
        }

        $member_names = [];
        foreach ($members as $member) {
            $member_names[] = $member->tryMention();
        }

        return $this->replyToChat("Hi " . implode(", ", $member_names) . "!");
    }
}