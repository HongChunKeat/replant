<?php

namespace app\queue\redis;

# system lib
use Webman\RedisQueue\Consumer;
# database & logic
use plugin\dapp\app\model\logic\UserWalletLogic;
use app\model\logic\SettingLogic;

class AdminWalletTransaction implements Consumer
{
    // queue name
    public $queue = "admin_wallet";

    // connection name refer config/plugin/webman/redis-queue/redis.php
    public $connection = "default";

    // process
    public function consume($queue)
    {
        switch ($queue["type"]) {
            case "editWallet":
                $this->editWallet($queue["data"]);
                break;
        }
    }

    private function editWallet($data)
    {
        $uid = $data["uid"];
        $walletId = $data["walletId"];
        $amount = $data["amount"];
        $type = $data["type"];

        $adminTopUp = SettingLogic::get("operator", ["code" => "admin_top_up"]);
        $adminDeduct = SettingLogic::get("operator", ["code" => "admin_deduct"]);

        if ($type == "add") {
            UserWalletLogic::add([
                "type" => $adminTopUp["id"],
                "uid" => $uid,
                "fromUid" => $uid,
                "toUid" => $uid,
                "distribution" => [$walletId => round($amount, 8)],
                "refTable" => "account_user",
                "refId" => $uid
            ]);
        } 
        else if ($type == "deduct") {
            UserWalletLogic::deduct([
                "type" => $adminDeduct["id"],
                "uid" => $uid,
                "fromUid" => $uid,
                "toUid" => $uid,
                "distribution" => [$walletId => round($amount, 8)],
                "refTable" => "account_user",
                "refId" => $uid
            ]);
        }
    }
}
