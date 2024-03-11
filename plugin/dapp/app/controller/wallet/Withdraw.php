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

class Withdraw extends Base
{
    # [validation-rule]
    protected $rule = [
        "id" => "require|number|max:11",
        "amount" => "require|float|max:20",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "id",
        "amount"
    ];

    public function index(Request $request)
    {
        // check maintenance
        $stop_withdraw = SettingLogic::get("general", ["category" => "maintenance", "code" => "stop_withdraw", "value" => 1]);
        if ($stop_withdraw) {
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
        Redis::get("withdraw-lock:" . $cleanVars["uid"])
            ? $this->error[] = "withdraw:lock"
            : Redis::set("withdraw-lock:" . $cleanVars["uid"], 1);

        # [checking]
        [$tokenAddress, $fromAddress, $toAddress, $network, $coinId] = $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error) && ($this->successTotalCount == $this->successPassedCount)) {
            # [process]
            if (count($cleanVars) > 0) {
                # [process with queue]
                RedisQueue::send("user_wallet", [
                    "type" => "withdraw",
                    "data" => [
                        "uid" => $cleanVars["uid"],
                        "amount" => $cleanVars["amount"],
                        "coinId" => $coinId,
                        "fromAddress" => $fromAddress,
                        "toAddress" => $toAddress,
                        "network" => $network,
                        "tokenAddress" => $tokenAddress,
                    ]
                ]);

                LogUserModel::log($request, "withdraw");

                # [result]
                $this->response = [
                    "success" => true,
                ];
            }
        }

        // remove redis lock
        Redis::del("withdraw-lock:" . $cleanVars["uid"]);

        # [standard output]
        return $this->output();
    }

    private function checking(array $params = [])
    {
        # [init success condition]
        $this->successTotalCount = 8;

        # [condition]
        if (isset($params["uid"]) && isset($params["id"]) && isset($params["amount"])) {
            $user = AccountUserModel::select("id", "web3_address")->where(["id" => $params["uid"], "status" => "active"])->first();
            // check uid exists
            if (!$user) {
                $this->error[] = "user:missing";
            } else {
                $this->successPassedCount++;
                // must have web 3 address
                if (empty($user["web3_address"])) {
                    $this->error[] = "user:no_web3_address";
                } else {
                    $this->successPassedCount++;
                }
            }

            // check min max
            $withdrawMin = SettingLogic::get("general", ["category" => "withdraw", "code" => "withdraw_min"]);
            if ($withdrawMin && $withdrawMin["value"] > 0) {
                if ($params["amount"] < $withdrawMin["value"]) {
                    $this->error[] = "withdraw:below_minimum_amount";
                }
            }

            $withdrawMax = SettingLogic::get("general", ["category" => "withdraw", "code" => "withdraw_max"]);
            if ($withdrawMax && $withdrawMax["value"] > 0) {
                if ($params["amount"] > $withdrawMax["value"]) {
                    $this->error[] = "withdraw:exceed_maximum_amount";
                }
            }

            $settingWithdraw = SettingLogic::get("withdraw", ["id" => $params["id"], "is_active" => 1]);
            if (!$settingWithdraw) {
                $this->error[] = "withdraw:invalid";
            } else {
                $this->successPassedCount++;
                // Check network condition
                $network = SettingLogic::get("blockchain_network", ["id" => $settingWithdraw["network"]]);
                if (!$network) {
                    $this->error[] = "network:invalid";
                } else {
                    $this->successPassedCount++;
                }

                // Check setting coin condition
                $settingCoin = SettingLogic::get("coin", ["id" => $settingWithdraw["coin_id"]]);
                if (!$settingCoin) {
                    $this->error[] = "coin:invalid";
                } else {
                    $this->successPassedCount++;
                    $settingWallet = SettingLogic::get("wallet", ["id" => $settingCoin["wallet_id"]]);

                    if (!$settingWallet) {
                        $this->error[] = "wallet:invalid";
                    } else {
                        $this->successPassedCount++;
                        // Get amount fee
                        $settingFee = SettingLogic::get("general", ["category" => "withdraw", "code" => "withdraw_fee"]);
                        $settingFeeWallet = SettingLogic::get("general", ["category" => "withdraw", "code" => "withdraw_fee_wallet"]);

                        if (!$settingFee || !$settingFeeWallet) {
                            $this->error[] = "setting:missing";
                        } else {
                            $this->successPassedCount++;
                            $feeRate = $settingFee["value"] / 100 ?? 0;
                            $fees = $params["amount"] * $feeRate;

                            if ($settingWallet["id"] == $settingFeeWallet["value"]) {
                                // check amount + fee
                                // $totalAmount = $params["amount"] + $fees;

                                $userBalance = UserWalletLogic::getBalance($params["uid"], $settingWallet["id"]);
                                if ($params["amount"] > $userBalance) {
                                    $this->error[] = "amount:insufficient_balance";
                                } else {
                                    $this->successPassedCount++;
                                }
                            } else {
                                // check amount
                                $userBalance = UserWalletLogic::getBalance($params["uid"], $settingWallet["id"]);
                                if ($params["amount"] > $userBalance) {
                                    $this->error[] = "amount:insufficient_balance";
                                } else {
                                    $this->successPassedCount++;
                                }

                                // check fee
                                $feeBalance = UserWalletLogic::getBalance($params["uid"], $settingFeeWallet["value"]);
                                if ($fees > $feeBalance) {
                                    $this->error[] = "fee:insufficient_balance";
                                }
                            }
                        }
                    }
                }
            }
        }

        // Returning the value of "uid" from $params array if it exists, otherwise default to 0
        return [
            $settingWithdraw["token_address"] ?? "",
            $settingWithdraw["address"] ?? "",
            $user["web3_address"] ?? 0,
            $network["id"] ?? 0,
            $settingWithdraw["coin_id"] ?? "",
        ];
    }
}