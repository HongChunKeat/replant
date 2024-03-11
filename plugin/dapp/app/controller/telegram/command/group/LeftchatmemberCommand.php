<?php

/**
 * Left chat member command
 *
 * Gets executed when a member leaves the chat.
 *
 * NOTE: This command must be called from GenericmessageCommand.php!
 * It is only in a separate command file for easier code maintenance.
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;

class LeftchatmemberCommand extends SystemCommand
{
    protected $name = "leftchatmember";

    protected $description = "Left Chat Member";

    public function execute(): ServerResponse
    {
        $message = $this->getMessage();
        $member  = $message->getLeftChatMember();

        return $this->replyToChat("Sorry to see you go " . $member->getFirstName() . " :(");
    }
}