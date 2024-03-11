<?php

namespace plugin\dapp\app\controller\market;

# library
use Webman\RedisQueue\Redis as RedisQueue;
use plugin\dapp\app\controller\Base;
use support\Request;
use support\Redis;
# database & logic
use app\model\database\LogUserModel;
use app\model\database\AccountUserModel;
use app\model\database\UserInventoryModel;
use app\model\database\UserMarketModel;
use app\model\database\UserPetModel;
use app\model\logic\HelperLogic;
use app\model\logic\SettingLogic;
use plugin\dapp\app\model\logic\UserWalletLogic;
use plugin\admin\app\model\logic\PetLogic;

class Sell extends Base
{
    # [validation-rule]
    protected $rule = [
        "source" => "require|in:pet,item",
        "sn" => "require",
        "payment" => "require|in:xtendo,gtendo",
        "price" => "require|float|max:11"
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "source",
        "sn",
        "payment",
        "price"
    ];

    public function index(Request $request)
    {
        // check maintenance
        $stop_market = SettingLogic::get("general", ["category" => "maintenance", "code" => "stop_market", "value" => 1]);
        if ($stop_market) {
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
        Redis::get("market_item_sell-lock:" . $cleanVars["uid"])
            ? $this->error[] = "market_item_sell:lock"
            : Redis::set("market_item_sell-lock:" . $cleanVars["uid"], 1);

        # [checking]
        $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error) && ($this->successTotalCount == $this->successPassedCount)) {
            # [process]
            if (count($cleanVars) > 0) {
                # [process with queue]
                RedisQueue::send("user_wallet", [
                    "type" => "marketItemSell",
                    "data" => [
                        "uid" => $cleanVars["uid"],
                        "source" => $cleanVars["source"],
                        "sn" => $cleanVars["sn"],
                        "payment" => $cleanVars["payment"],
                        "price" => $cleanVars["price"],
                    ]
                ]);

                LogUserModel::log($request, "market_item_sell");

                # [result]
                $this->response = [
                    "success" => true,
                ];
            }
        }

        // remove redis lock
        Redis::del("market_item_sell-lock:" . $cleanVars["uid"]);

        # [standard output]
        return $this->output();
    }

    private function checking(array $params = [])
    {
        # [init success condition]
        $this->successTotalCount = 6;

        # [condition]
        if (isset($params["uid"]) && isset($params["source"]) && isset($params["sn"]) && isset($params["payment"]) && isset($params["price"])) {
            // check uid exist
            $user = AccountUserModel::where(["id" => $params["uid"], "status" => "active"])->first();
            if (!$user) {
                $this->error[] = "user:missing";
            } else {
                $this->successPassedCount++;

                // check min max
                $salesMin = SettingLogic::get("general", ["category" => "market", "code" => "sales_min"]);
                if ($salesMin && $salesMin["value"] > 0) {
                    if ($params["price"] < $salesMin["value"]) {
                        $this->error[] = "price:below_minimum_amount";
                    }
                }

                $salesMax = SettingLogic::get("general", ["category" => "market", "code" => "sales_max"]);
                if ($salesMax && $salesMax["value"] > 0) {
                    if ($params["price"] > $salesMax["value"]) {
                        $this->error[] = "price:exceed_maximum_amount";
                    }
                }

                // check source and sn
                if ($params["source"] == "pet") {
                    $pet = UserPetModel::defaultWhere()->where(["uid" => $params["uid"], "sn" => $params["sn"]])->first();

                    if (!$pet) {
                        $this->error[] = "pet:not_found";
                    } else {
                        if ($pet["is_active"]) {
                            $this->error[] = "pet:need_stop_mining_first";
                        }

                        // hatching, healthy, unhealthy can sell
                        $health = PetLogic::countHealth($pet["id"]);
                        $status = PetLogic::checkHealth($health);
                        if (!in_array($status, ["hatching", "healthy", "unhealthy"])) {
                            $this->error[] = "pet:invalid_status";
                        }
                    }
                } else if ($params["source"] == "item") {
                    $item = UserInventoryModel::defaultWhere()->where(["uid" => $params["uid"], "sn" => $params["sn"]])->first();

                    if (!$item) {
                        $this->error[] = "item:not_found";
                    }
                } else {
                    $this->error[] = "source:invalid";
                }

                // check exist in market list
                if (isset($pet) || isset($item)) {
                    $check = UserMarketModel::defaultWhere()->where([
                        "seller_uid" => $params["uid"],
                        "ref_table" => ($params["source"] == "pet")
                            ? "user_pet"
                            : "user_inventory",
                        "ref_id" => ($params["source"] == "pet")
                            ? $pet["id"]
                            : $item["id"],
                    ])->first();

                    if ($check) {
                        $name = isset($pet) ? "pet" : "item";
                        $this->error[] = $name . ":already_on_market";
                    } else {
                        $this->successPassedCount++;
                    }
                }

                // check payment and balance
                $wallet = SettingLogic::get("wallet", ["code" => $params["payment"]]);
                if (!$wallet) {
                    $this->error[] = "payment:invalid";
                } else {
                    $this->successPassedCount++;
                    $settingFee = SettingLogic::get("general", ["category" => "market", "code" => "sales_fee"]);
                    $settingFeeWallet = SettingLogic::get("general", ["category" => "market", "code" => "sales_fee_wallet"]);

                    if (!$settingFee || !$settingFeeWallet) {
                        $this->error[] = "setting:missing";
                    } else {
                        $this->successPassedCount++;
                        $feeWallet = SettingLogic::get("wallet", ["id" => $settingFeeWallet["value"]]);
                        if (!$feeWallet) {
                            $this->error[] = "fee:invalid";
                        } else {
                            $this->successPassedCount++;
                            $fee = $params["price"] * ($settingFee["value"] / 100);
                            $balance = UserWalletLogic::getBalance($params["uid"], $feeWallet["id"]);
                            if ($fee > $balance) {
                                $this->error[] = $feeWallet["code"] . ":insufficient_balance";
                            } else {
                                $this->successPassedCount++;
                            }
                        }
                    }
                }
            }
        }
    }
}