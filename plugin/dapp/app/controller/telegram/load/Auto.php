<?php

namespace plugin\dapp\app\controller\telegram\load;

# library
use PDO;
use support\Log;
use support\Db;
use support\Request;
use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Exception\TelegramLogException;
use plugin\dapp\app\controller\Base;
use app\model\logic\SettingLogic;

class Auto extends Base
{
    public function index(Request $request)
    {
        try {
            $botName = SettingLogic::get("general", ["code" => "bot_name"]);

            // Create Telegram API object
            $telegram = new Telegram(ENV("TELEGRAM_API_SECRET"), $botName["value"]);
            $telegram->addCommandsPaths([__DIR__ . "/../command"]);
            // $telegram->enableLimiter(["enabled" => true]);

            $dbConnected = false;
            $counter = 0;
            do {
                try {
                    $pdo = Db::connection()->getPdo();
                    $pdo->query("SELECT 1");
                    $dbConnected = true;
                } catch (\Exception $e) {
                    Db::connection()->reconnect();
                }
                $counter++;
            } while (!$dbConnected && $counter < 5);

            if ($dbConnected) {
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $telegram->enableExternalMySql($pdo, "tg_");

                $telegram->setCustomInput($request->rawBody()); // set for webman
                $telegram->handle(); // if hook
            }

            return json("ok");
        } catch (TelegramException $e) {
            // Uncomment this to output any errors (ONLY FOR DEVELOPMENT!)
            Log::error($e);
        } catch (TelegramLogException $e) {
            // Uncomment this to output log initialisation errors (ONLY FOR DEVELOPMENT!)
            Log::error($e);
        }
    }
}