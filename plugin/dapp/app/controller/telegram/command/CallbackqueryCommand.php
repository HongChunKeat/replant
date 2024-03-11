<?php

/**
 * Callback query command
 *
 * This command handles all callback queries sent via inline keyboard buttons.
 *
 * @see InlinekeyboardCommand.php
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use app\model\logic\SettingLogic;

class CallbackqueryCommand extends SystemCommand
{
    protected $name = "callbackquery";

    protected $description = "Handle the callback query";

    public function execute(): ServerResponse
    {
        // Callback query data can be fetched and handled accordingly.
        $callback_query = $this->getCallbackQuery();
        $callback_data  = $callback_query->getData();
        $callback_game = $callback_query->getGameShortName();
        $website = SettingLogic::get("general", ["code" => "official_website"]);

        // https://core.telegram.org/bots/api#answercallbackquery
        if (isset($callback_game) && $callback_game == "tendopia") {
            return $callback_query->answer([
                "url" => $website["value"],
            ]);
        }

        if (isset($callback_data) && $callback_data == "tendopia") {
            return $callback_query->answer([
                "text" => $callback_data,
                "show_alert" => 1,
            ]);
        }

        return Request::emptyResponse();
    }
}