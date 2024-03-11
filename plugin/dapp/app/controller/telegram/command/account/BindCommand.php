<?php

/**
 * User "/bind" command
 *
 * bind user telegram
 */

namespace Longman\TelegramBot\Commands\UserCommands;

use support\Redis;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use app\model\database\AccountUserModel;
use plugin\admin\app\model\logic\MissionLogic;

class BindCommand extends UserCommand
{
    protected $name = "bind";

    protected $description = "Bind Telegram with code provided";

    protected $usage = "/bind <text>";

    protected $private_only = true;

    public function execute(): ServerResponse
    {
        $res = false;
        $msg = "Failed to bind";

        $message = $this->getMessage();
        $from = $message->getFrom();
        $telegramId = $from->getId();
        $firstName = $from->getFirstName();
        $lastName = $from->getLastName();
        $username = $from->getUsername();
        $code = $message->getText(true);

        if ($code === "") {
            return $this->replyToChat("Command usage: " . $this->getUsage());
        } else {
            $userId = Redis::get("telegram_bind:" . $code);

            if($userId) {
                $check = AccountUserModel::where(["telegram" => $telegramId])->first();
                if($check) {
                    $msg = "Account already exists";
                } else {
                    // name
                    if(isset($firstName) || isset($lastName)){
                        $firstName = isset($firstName)
                            ? $firstName
                            : "";
                        $lastName = isset($lastName)
                            ? $lastName
                            : "";
                        
                        $name = $firstName . " " . $lastName;
                    } else if(isset($username)) {
                        $name = $username;
                    } else {
                        $name = $telegramId;
                    }

                    $res = AccountUserModel::where("user_id", $userId)->update([
                        "telegram" => $telegramId,
                        "telegram_name" => $name
                    ]);
                }
            }

            if($res) {
                $msg = "Successfully bind";

                Redis::del("telegram_bind:" . $code);

                // do mission
                $user = AccountUserModel::where("user_id", $userId)->first();
                if($user) {
                    MissionLogic::missionProgress($user["id"], ["name" => "link telegram"]);
                }
            }
        }        

        return $this->replyToChat($msg);;
    }
}