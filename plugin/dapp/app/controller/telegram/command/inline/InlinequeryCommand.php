<?php

/**
 * Inline query command
 *
 * Command that handles inline queries and returns a list of results.
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\InlineQuery\InlineQueryResultGame;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\ServerResponse;
use app\model\logic\SettingLogic;

class InlinequeryCommand extends SystemCommand
{
    protected $name = "inlinequery";

    protected $description = "Handle inline query";

    public function execute(): ServerResponse
    {
        $inline_query = $this->getInlineQuery();
        $query = $inline_query->getQuery();
        $website = SettingLogic::get("general", ["code" => "dapp_website"]);

        $results = [];

        if ($query !== "") {
            $inline_keyboard = new InlineKeyboard([
                ["text" => "Visit Us", "callback_game" => "tendopia"], ["text" => "Play Tendopia", "url" => $website["value"]],
            ]);

            $results[] = new InlineQueryResultGame([
                "id" => "001",
                "game_short_name" => "tendopia",
                "reply_markup" => $inline_keyboard,
            ]);
        }

        return $inline_query->answer($results);
    }
}