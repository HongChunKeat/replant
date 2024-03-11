<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Request;
use app\model\logic\SettingLogic;

class PlayCommand extends UserCommand
{
    protected $name = "play";

    protected $description = "Open Tendopia";

    protected $usage = "/play";

    public function execute(): ServerResponse
    {
        $chat_id = $this->getMessage()->getChat()->getId();
        $website = SettingLogic::get("general", ["code" => "dapp_website"]);

        $inline_keyboard = new InlineKeyboard([
            ["text" => "Visit Us", "callback_game" => "tendopia"], ["text" => "Play Tendopia", "url" => $website["value"]],
        ]);
        
        return Request::sendGame([
            "chat_id"    => $chat_id,
            "game_short_name" => "tendopia",
            "reply_markup" => $inline_keyboard,
        ]);
    }
}
