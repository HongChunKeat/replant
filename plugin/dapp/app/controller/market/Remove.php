<?php

namespace plugin\dapp\app\controller\market;

# library
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

class Remove extends Base
{
    # [validation-rule]
    protected $rule = [
        "sn" => "require",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "sn",
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
        Redis::get("market_item_remove-lock:" . $cleanVars["uid"])
            ? $this->error[] = "market_item_remove:lock"
            : Redis::set("market_item_remove-lock:" . $cleanVars["uid"], 1);

        # [checking]
        [$item] = $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error) && ($this->successTotalCount == $this->successPassedCount) && $item) {
            $res = "";

            # [process]
            if (count($cleanVars) > 0) {
                $marketItem = UserMarketModel::defaultWhere()->where("id", $item["id"])->first();
                if ($marketItem) {
                    UserMarketModel::defaultWhere()->where("id", $marketItem["id"])->update(["removed_at" => date("Y-m-d H:i:s")]);

                    if ($marketItem["ref_table"] == "user_pet") {
                        $res = UserPetModel::where("id", $marketItem["ref_id"])->update(["marketed_at" => null]);
                    } else if ($marketItem["ref_table"] == "user_inventory") {
                        $res = UserInventoryModel::where("id", $marketItem["ref_id"])->update(["marketed_at" => null]);
                    }
                }
            }

            # [result]
            if ($res) {
                LogUserModel::log($request, "market_item_remove");
                $this->response = [
                    "success" => true,
                ];
            }
        }

        // remove redis lock
        Redis::del("market_item_remove-lock:" . $cleanVars["uid"]);

        # [standard output]
        return $this->output();
    }

    private function checking(array $params = [])
    {
        # [init success condition]
        $this->successTotalCount = 2;

        # [condition]
        if (isset($params["uid"]) && isset($params["sn"])) {
            // check uid exist
            $user = AccountUserModel::where(["id" => $params["uid"], "status" => "active"])->first();
            if (!$user) {
                $this->error[] = "user:missing";
            } else {
                $this->successPassedCount++;
                // check exist in market list
                $item = UserMarketModel::defaultWhere()->where(["seller_uid" => $params["uid"], "sn" => $params["sn"]])->first();
                if (!$item) {
                    $this->error[] = "market_item:not_found";
                } else {
                    $this->successPassedCount++;
                }
            }
        }

        return [$item ?? 0];
    }
}