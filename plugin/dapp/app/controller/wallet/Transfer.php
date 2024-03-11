<?php

namespace plugin\dapp\app\controller\wallet;

# library
use Webman\RedisQueue\Redis as RedisQueue;
use plugin\dapp\app\controller\Base;
use support\Redis;
use support\Request;
# database & logic
use app\model\database\LogUserModel;
use app\model\database\AccountUserModel;
use app\model\logic\HelperLogic;
use app\model\logic\SettingLogic;
use plugin\dapp\app\model\logic\UserProfileLogic;
use plugin\dapp\app\model\logic\UserWalletLogic;

class Transfer extends Base
{
    # [validation-rule]
    protected $rule = [
        "recipient" => "require|max:80",
        "wallet" => "require|max:20",
        "amount" => "require|float|max:20",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "recipient",
        "wallet",
        "amount"
    ];

    public function index(Request $request)
    {
        // check maintenance
        $stop_transfer = SettingLogic::get("general", ["category" => "maintenance", "code" => "stop_transfer", "value" => 1]);
        if ($stop_transfer) {
            $this->error[] = "under_maintenance";
            return $this->output();
        }

        # [validation]
        $this->validation($request->post(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->post(), $this->patternInputs);

        # user id
        $cleanVars["uid"] = $request->visitor["id"];

        // get and set redis lock
        Redis::get("transfer-lock:" . $cleanVars["uid"])
            ? $this->error[] = "transfer:lock"
            : Redis::set("transfer-lock:" . $cleanVars["uid"], 1);

        if (isset($cleanVars["wallet"])) {
            $wallet = SettingLogic::get("wallet", ["code" => $cleanVars["wallet"]]);
            $cleanVars["wallet_id"] = $wallet["id"] ?? 0;
        }

        # [unset key]
        unset($cleanVars["wallet"]);

        # [checking]
        [$toUid] = $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error) && ($this->successTotalCount == $this->successPassedCount)) {
            # [process]
            if (count($cleanVars) > 0) {
                # [process with queue]
                RedisQueue::send("user_wallet", [
                    "type" => "transfer",
                    "data" => [
                        "fromUid" => $cleanVars["uid"],
                        "toUid" => $toUid,
                        "walletId" => $cleanVars["wallet_id"],
                        "amount" => $cleanVars["amount"],
                    ]
                ]);

                LogUserModel::log($request, "transfer");

                # [result]
                $this->response = [
                    "success" => true,
                ];
            }
        }

        // remove redis lock
        Redis::del("transfer-lock:" . $cleanVars["uid"]);

        # [standard output]
        return $this->output();
    }

    private function checking(array $params = [])
    {
        # [init success condition]
        $this->successTotalCount = 5;

        # [condition]
        if (isset($params["uid"]) && isset($params["recipient"]) && isset($params["wallet_id"]) && isset($params["amount"])) {
            // check uid exist
            $user = AccountUserModel::where(["id" => $params["uid"], "status" => "active"])->first();
            if (!$user) {
                $this->error[] = "user:missing";
            } else {
                $this->successPassedCount++;
                // 4 in 1 search
                $toUser = UserProfileLogic::multiSearch($params["recipient"]);
                if (!$toUser) {
                    $this->error[] = "recipient:invalid";
                } else {
                    $this->successPassedCount++;
                    // cannot send to self
                    if ($params["uid"] == $toUser["id"]) {
                        $this->error[] = "recipient:cannot_send_to_self";
                    } else {
                        $this->successPassedCount++;
                    }

                    // check in network
                    // $inMapToAbove = UserProfileLogic::inMap($params["uid"], $toUser["id"]);
                    // $inMapToBelow = UserProfileLogic::inMap($toUser["id"], $params["uid"]);
                    // if (!$inMapToAbove && !$inMapToBelow) {
                    //     $this->error[] = "recipient:not_in_network";
                    // }
                }

                // get setting
                $setting = SettingLogic::get("wallet_attribute", [
                    "from_wallet_id" => $params["wallet_id"],
                    "to_wallet_id" => $params["wallet_id"],
                    "to_other" => 1,
                    "is_show" => 1
                ]);

                if (!$setting) {
                    $this->error[] = "wallet:invalid_pair";
                } else {
                    $this->successPassedCount++;
                    // check balance for amount
                    $userBalance = UserWalletLogic::getBalance($params["uid"], $params["wallet_id"]);
                    if ($params["amount"] > $userBalance) {
                        $this->error[] = "amount:insufficient_balance";
                    } else {
                        $this->successPassedCount++;
                    }

                    if ($setting["to_other_fee"] > 0) {
                        // check balance for fee
                        $fees = $params["amount"] * ($setting["to_other_fee"] / 100);

                        $feeBalance = UserWalletLogic::getBalance($params["uid"], $setting["fee_wallet_id"]);
                        if ($fees > $feeBalance) {
                            $this->error[] = "fee:insufficient_balance";
                        }
                    }
                }
            }
        }

        return [$toUser["id"] ?? 0];
    }
}