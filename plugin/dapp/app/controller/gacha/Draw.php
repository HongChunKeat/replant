<?php

namespace plugin\dapp\app\controller\gacha;

# library
use plugin\dapp\app\controller\Base;
use support\Request;
use support\Redis;
# database & logic
use app\model\database\LogUserModel;
use app\model\database\AccountUserModel;
use app\model\database\UserInventoryModel;
use app\model\database\UserLevelModel;
use app\model\logic\HelperLogic;
use app\model\logic\SettingLogic;
use plugin\dapp\app\model\logic\UserWalletLogic;
use plugin\admin\app\model\logic\ItemLogic;
use plugin\admin\app\model\logic\MissionLogic;

class Draw extends Base
{
    # [validation-rule]
    protected $rule = [
        "gacha" => "require",
        "multi" => "require|in:1,0"
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "gacha",
        "multi"
    ];

    public function index(Request $request)
    {
        // check maintenance
        $stop_gacha = SettingLogic::get("general", ["category" => "maintenance", "code" => "stop_gacha", "value" => 1]);
        if ($stop_gacha) {
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
        Redis::get("draw_gacha-lock:" . $cleanVars["uid"])
            ? $this->error[] = "draw_gacha:lock"
            : Redis::set("draw_gacha-lock:" . $cleanVars["uid"], 1);

        # [checking]
        [$gacha, $wallet, $total] = $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error) && ($this->successTotalCount == $this->successPassedCount) && $gacha && $wallet && $total) {
            # [process]
            if (count($cleanVars) > 0) {
                $res = ItemLogic::gacha($cleanVars["uid"], $gacha["id"], $cleanVars["multi"]);

                if ($res["success"]) {
                    $gachaOperator = SettingLogic::get("operator", ["code" => "gacha"]);

                    // deduct wallet
                    UserWalletLogic::deduct([
                        "type" => $gachaOperator["id"],
                        "uid" => $cleanVars["uid"],
                        "fromUid" => $cleanVars["uid"],
                        "toUid" => $cleanVars["uid"],
                        "distribution" => [$wallet => round($total, 8)],
                        "refTable" => "setting_gacha",
                        "refId" => $gacha["id"],
                    ]);

                    // do mission
                    if ($cleanVars["multi"] == 0) {
                        MissionLogic::missionProgress($cleanVars["uid"], ["name" => "purchase chestbox draw 1 for 5 times"]);
                    }

                    LogUserModel::log($request, "draw_gacha");
                    # [result]
                    $this->response = [
                        "success" => true,
                        "data" => $res["data"]
                    ];
                } else {
                    $this->error[] = "gacha:internal_error";
                }
            }
        }

        // remove redis lock
        Redis::del("draw_gacha-lock:" . $cleanVars["uid"]);

        # [standard output]
        return $this->output();
    }

    private function checking(array $params = [])
    {
        # [init success condition]
        $this->successTotalCount = 7;

        # [condition]
        if (isset($params["uid"]) && isset($params["gacha"]) && isset($params["multi"])) {
            // check uid exist
            $user = AccountUserModel::where(["id" => $params["uid"], "status" => "active"])->first();
            if (!$user) {
                $this->error[] = "user:missing";
            } else {
                $this->successPassedCount++;
                $userLevel = UserLevelModel::where(["uid" => $params["uid"], "is_current" => 1])->first();

                if (!$userLevel) {
                    $this->error[] = "user:level_missing";
                } else {
                    $this->successPassedCount++;
                    $quantity = $params["multi"] == 1
                        ? 10
                        : 1;

                    // check inventory is over limit or not
                    $maxStorage = $userLevel["inventory_pages"] * 25;

                    $currentStorage = UserInventoryModel::defaultWhere()->where("uid", $params["uid"])->count();

                    if ($currentStorage + $quantity > $maxStorage) {
                        $this->error[] = "inventory:full";
                    } else {
                        $this->successPassedCount++;
                    }
                }

                $gacha = SettingLogic::get("gacha", ["name" => $params["gacha"], "is_show" => 1]);
                if (!$gacha) {
                    $this->error[] = "gacha:invalid";
                } else {
                    $this->successPassedCount++;
                    if (!empty($gacha["start_at"]) || !empty($gacha["end_at"])) {
                        if (
                            (isset($gacha["start_at"]) && time() < strtotime($gacha["start_at"])) ||
                            (isset($gacha["end_at"]) && time() > strtotime($gacha["end_at"]))
                        ) {
                            $this->error[] = "gacha:not_available";
                        }
                    }

                    $gachaItem = SettingLogic::get("gacha_item", ["gacha_id" => $gacha["id"]], true);
                    if (!count($gachaItem)) {
                        $this->error[] = "gacha:no_items";
                    } else {
                        $this->successPassedCount++;
                    }

                    $payment = SettingLogic::get("payment", ["id" => $gacha["payment_id"]]);
                    if (!$payment) {
                        $this->error[] = "payment:missing";
                    } else {
                        $this->successPassedCount++;
                        // get the first wallet only
                        $wallet = array_keys(json_decode($payment["formula"], 1));

                        $total = $params["multi"] == 1
                            ? $gacha["ten_sales_price"]
                            : $gacha["single_sales_price"];

                        $payment = UserWalletLogic::paymentCheck($params["uid"], $gacha["payment_id"], [$wallet[0] => $total]);

                        if (!$payment["success"]) {
                            $this->error[] = $payment["data"][0];
                        } else {
                            $this->successPassedCount++;
                        }
                    }
                }
            }
        }

        return [$gacha ?? 0, $wallet[0] ?? 0, $total ?? 0];
    }
}