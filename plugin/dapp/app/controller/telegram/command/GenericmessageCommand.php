<?php

/**
 * Generic message command
 *
 * Gets executed when any type of message is sent.
 *
 * In this group-related context, we can handle new and left group members.
 * In this conversation-related context, we must ensure that active conversations get executed correctly.
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use app\model\database\AccountUserModel;
use plugin\admin\app\model\logic\MissionLogic;
use support\Log;

class GenericmessageCommand extends SystemCommand
{
    protected $name = "genericmessage";

    protected $description = "Handle generic message";

    public function execute(): ServerResponse
    {
        $message = $this->getMessage();
        $user = $message->getFrom();
        $userId = $user->getId();
        $text = $message->getText(true) != null
            ? trim($message->getText(true))
            : "";

        // Handle new chat members
        if ($message->getNewChatMembers()) {
            return $this->telegram->executeCommand("newchatmembers");
        }

        // Handle left chat members
        if ($message->getLeftChatMember()) {
            return $this->telegram->executeCommand("leftchatmember");
        }

        // The chat photo was changed
        if ($new_chat_photo = $message->getNewChatPhoto()) {
            // Whatever...
        }

        // The chat title was changed
        if ($new_chat_title = $message->getNewChatTitle()) {
            // Whatever...
        }

        // A message has been pinned
        if ($pinned_message = $message->getPinnedMessage()) {
            // Whatever...
        }

        // If a conversation is busy, execute the conversation command after handling the message.
        $conversation = new Conversation(
            $message->getFrom()->getId(),
            $message->getChat()->getId()
        );

        // Fetch conversation command if it exists and execute it.
        if ($conversation->exists() && $command = $conversation->getCommand()) {
            return $this->telegram->executeCommand($command);
        }

        // if not private then trigger
        if ($message->getChat()->getType() != "private") {
            // do mission
            $user = AccountUserModel::where("telegram", $userId)->first();
            if ($user) {
                // type : any message
                MissionLogic::missionProgress($user["id"], ["name" => "chat in tendopia group I"]);
                MissionLogic::missionProgress($user["id"], ["name" => "chat in tendopia group II"]);
                MissionLogic::missionProgress($user["id"], ["name" => "chat in tendopia group III"]);
                MissionLogic::missionProgress($user["id"], ["name" => "socialize in tendopia group"]);

                // type : contains word tendopia
                if (str_contains(strtolower($text), "tendopia")) {
                    MissionLogic::missionProgress($user["id"], ["name" => "chat in tendopia group with keyword tendopia I"]);
                    MissionLogic::missionProgress($user["id"], ["name" => "chat in tendopia group with keyword tendopia II"]);
                    MissionLogic::missionProgress($user["id"], ["name" => "chat in tendopia group with keyword tendopia III"]);
                }
            }
        }

        return Request::emptyResponse();
    }
}