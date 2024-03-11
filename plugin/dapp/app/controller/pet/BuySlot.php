<?php

namespace plugin\dapp\app\controller\pet;

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

class BuySlot extends Base
{
    public function index(Request $request)
    {
        // check maintenance
        $stop_pet = SettingLogic::get("general", ["category" => "maintenance", "code" => "stop_pet", "value" => 1]);
        if ($stop_pet) {
            $this->error[] = "under_maintenance";
            return $this->output();
        }

        # user id
        $cleanVars["uid"] = $request->visitor["id"];

        // get and set redis lock
        Redis::get("pet_slot-lock:" . $cleanVars["uid"])
            ? $this->error[] = "pet_slot:lock"
            : Redis::set("pet_slot-lock:" . $cleanVars["uid"], 1);

        # [checking]
        $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error) && ($this->successTotalCount == $this->successPassedCount)) {
            # [process]
            if (count($cleanVars) > 0) {
                # [process with queue]
                RedisQueue::send("user_wallet", [
                    "type" => "petSlotBuy",
                    "data" => [
                        "uid" => $cleanVars["uid"],
                    ]
                ]);

                LogUserModel::log($request, "pet_slot_buy");

                # [result]
                $this->response = [
                    "success" => true,
                ];
            }
        }

        // remove redis lock
        Redis::del("pet_slot-lock:" . $cleanVars["uid"]);

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
                    $attribute = HelperLogic::buildAttributeGeneral(["code" => "pet_slot_price"]);

                    if (!count($attribute)) {
                        $this->error[] = "setting:missing";
                    } else {
                        $this->successPassedCount++;
                        // slot is max 5, price is from 2-5 only so only got 4
                        if ($level["pet_slots"] >= (count($attribute) + 1)) {
                            $this->error[] = "slot:maxed";
                        } else {
                            $this->successPassedCount++;
                            // need minus 1
                            $selected = $attribute[$level["pet_slots"] - 1];

                            $userBalance = UserWalletLogic::getBalance($params["uid"], $selected["key"]);
                            if ($selected["value"] > $userBalance) {
                                $walletName = SettingLogic::get("wallet", ["id" => $selected["key"]]);
                                $this->error[] = $walletName["code"] . ":insufficient_balance";
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