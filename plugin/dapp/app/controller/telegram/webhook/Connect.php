<?php

namespace plugin\dapp\app\controller\telegram\webhook;

# library
use support\Log;
use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Exception\TelegramLogException;
use plugin\dapp\app\controller\Base;
use app\model\logic\SettingLogic;

class Connect extends Base
{
    public function index()
    {
        try {
            $botName = SettingLogic::get("general", ["code" => "bot_name"]);
            $botDomain = SettingLogic::get("general", ["code" => "bot_domain"]);

            // Create Telegram API object
            $telegram = new Telegram(ENV("TELEGRAM_API_SECRET"), $botName["value"]);

            $result = $telegram->setWebhook($botDomain["value"]);

            return json($result->getDescription());
        } catch (TelegramException $e) {
            // Uncomment this to output any errors (ONLY FOR DEVELOPMENT!)
            Log::error($e);
        } catch (TelegramLogException $e) {
            // Uncomment this to output log initialisation errors (ONLY FOR DEVELOPMENT!)
            Log::error($e);
        }
    }
}