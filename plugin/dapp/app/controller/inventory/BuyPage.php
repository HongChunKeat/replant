<?php

namespace plugin\dapp\app\controller\inventory;

# library
use Webman\RedisQueue\Redis as RedisQueue;
use plugin\dapp\app\controller\Base;
use support\Request;
use support\Redis;
# database & logic
use app\model\database\LogUserModel;
use app\model\database\AccountUserModel;
use app\model\database\UserLevelModel;
use plugin\dapp\app\model\logic\UserWalletLogic;
use app\model\logic\HelperLogic;
use app\model\logic\SettingLogic;

class BuyPage extends Base
{
    public function index(Request $request)
    {
        // check maintenance
        $stop_item = SettingLogic::get("general", ["category" => "maintenance", "code" => "stop_item", "value" => 1]);
        if ($stop_item) {
            $this->error[] = "under_maintenance";
            return $this->output();
        }

        # user id
        $cleanVars["uid"] = $request->visitor["id"];

        // get and set redis lock
        Redis::get("inventory_page-lock:" . $cleanVars["uid"])
            ? $this->error[] = "inventory_page:lock"
            : Redis::set("inventory_page-lock:" . $cleanVars["uid"], 1);

        # [checking]
        $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error) && ($this->successTotalCount == $this->successPassedCount)) {
            # [process]
            if (count($cleanVars) > 0) {
                # [process with queue]
                RedisQueue::send("user_wallet", [
                    "type" => "inventoryPageBuy",
                    "data" => [
                        "uid" => $cleanVars["uid"],
                    ]
                ]);

                LogUserModel::log($request, "inventory_page_buy");

                # [result]
                $this->response = [
                    "success" => true,
                ];
            }
        }

        // remove redis lock
        Redis::del("inventory_page-lock:" . $cleanVars["uid"]);

        # [standard output]
        return $this->output();
    }

    private function checking(array $params = [])
    {
        # [init success condition]
        $this->successTotalCount = 5;

        # [condition]
        if (isset($params["uid"])) {
            // check uid exist
            $user = AccountUserModel::where(["id" => $params["uid"], "status" => "active"])->first();
            if (!$user) {
                $this->error[] = "user:missing";
            } else {
                $this->successPassedCount++;
                $level = UserLevelModel::where(["uid" => $params["uid"], "is_current" => 1])->first();
                if (!$level) {
                    $this->error[] = "user:level_missing";
                } else {
                    $this->successPassedCount++;
                    $attribute = HelperLogic::buildAttributeGeneral(["code" => "inventory_page_price"]);

                    if (!count($attribute)) {
                        $this->error[] = "setting:missing";
                    } else {
                        $this->successPassedCount++;
                        // page is max 5, price is from 2-5 only so only got 4
                        if ($level["inventory_pages"] >= (count($attribute) + 1)) {
                            $this->error[] = "page:maxed";
                        } else {
                            $this->successPassedCount++;
                            // need minus 1
                            $selected = $attribute[$level["inventory_pages"] - 1];

                            $userBalance = UserWalletLogic::getBalance($params["uid"], $selected["key"]);
                            if ($selected["value"] > $userBalance) {
                                $walletName = SettingLogic::get("wallet", ["id" => $selected["key"]]);
                                $this->error[] = $walletName["code"].":insufficient_balance";
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