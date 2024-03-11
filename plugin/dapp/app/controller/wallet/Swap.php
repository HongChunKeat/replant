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
use plugin\dapp\app\model\logic\UserWalletLogic;
use app\model\logic\SettingLogic;
use app\model\logic\HelperLogic;

class Swap extends Base
{
    # [validation-rule]
    protected $rule = [
        "from_wallet" => "require|max:20",
        "to_wallet" => "require|max:20",
        "amount" => "require|float|max:20",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "from_wallet",
        "to_wallet",
        "amount"
    ];

    public function index(Request $request)
    {
        // check maintenance
        $stop_swap = SettingLogic::get("general", ["category" => "maintenance", "code" => "stop_swap", "value" => 1]);
        if ($stop_swap) {
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
        Redis::get("swap-lock:" . $cleanVars["uid"])
            ? $this->error[] = "swap:lock"
            : Redis::set("swap-lock:" . $cleanVars["uid"], 1);

        if (isset($cleanVars["from_wallet"])) {
            $fromWallet = SettingLogic::get("wallet", ["code" => $cleanVars["from_wallet"]]);
            $cleanVars["from_wallet_id"] = $fromWallet["id"] ?? 0;
        }

        if (isset($cleanVars["to_wallet"])) {
            $toWallet = SettingLogic::get("wallet", ["code" => $cleanVars["to_wallet"]]);
            $cleanVars["to_wallet_id"] = $toWallet["id"] ?? 0;
        }

        # [unset key]
        unset($cleanVars["from_wallet"]);
        unset($cleanVars["to_wallet"]);

        # [checking]
        $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error) && ($this->successTotalCount == $this->successPassedCount)) {
            # [process]
            if (count($cleanVars) > 0) {
                # [process with queue]
                RedisQueue::send("user_wallet", [
                    "type" => "swap",
                    "data" => [
                        "uid" => $cleanVars["uid"],
                        "fromWallet" => $cleanVars["from_wallet_id"],
                        "toWallet" => $cleanVars["to_wallet_id"],
                        "fromAmount" => $cleanVars["amount"],
                    ]
                ]);

                LogUserModel::log($request, "swap");

                # [result]
                $this->response = [
                    "success" => true,
                ];
            }
        }

        // remove redis lock
        Redis::del("swap-lock:" . $cleanVars["uid"]);

        # [standard output]
        return $this->output();
    }

    private function checking(array $params = [])
    {
        # [init success condition]
        $this->successTotalCount = 3;

        # [condition]
        if (isset($params["uid"]) && isset($params["from_wallet_id"]) && isset($params["to_wallet_id"]) && isset($params["amount"])) {
            // check uid exist
            $user = AccountUserModel::where(["id" => $params["uid"], "status" => "active"])->first();
            if (!$user) {
                $this->error[] = "user:missing";
            } else {
                $this->successPassedCount++;
                // get setting
                $setting = SettingLogic::get("wallet_attribute", [
                    "from_wallet_id" => $params["from_wallet_id"],
                    "to_wallet_id" => $params["to_wallet_id"],
                    "to_self" => 1,
                    "is_show" => 1
                ]);

                if (!$setting) {
                    $this->error[] = "wallet:invalid_pair";
                } else {
                    $this->successPassedCount++;
                    // check balance for from amount
                    $fromBalance = UserWalletLogic::getBalance($params["uid"], $params["from_wallet_id"]);
                    if ($params["amount"] > $fromBalance) {
                        $this->error[] = "amount:insufficient_balance";
                    } else {
                        $this->successPassedCount++;
                    }

                    if ($setting["to_self_fee"] > 0) {
                        // check balance for fee
                        $fees = $params["amount"] * ($setting["to_self_fee"] / 100);

                        $feeBalance = UserWalletLogic::getBalance($params["uid"], $setting["fee_wallet_id"]);
                        if ($fees > $feeBalance) {
                            $this->error[] = "fee:insufficient_balance";
                        }
                    }
                }
            }
        }
    }
}