<?php

namespace plugin\dapp\app\controller\inventory;

# library
use plugin\dapp\app\controller\Base;
use support\Request;
use support\Redis;
# database & logic
use app\model\database\LogUserModel;
use app\model\database\AccountUserModel;
use app\model\database\UserInventoryModel;
use app\model\logic\HelperLogic;
use app\model\logic\SettingLogic;
use plugin\admin\app\model\logic\ItemLogic;

class UseItem extends Base
{
    # [validation-rule]
    protected $rule = [
        "name" => "require",
        "target_sn" => ""
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "name",
        "target_sn",
    ];

    public function index(Request $request)
    {
        // check maintenance
        $stop_item = SettingLogic::get("general", ["category" => "maintenance", "code" => "stop_item", "value" => 1]);
        if ($stop_item) {
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
        Redis::get("use_item-lock:" . $cleanVars["uid"])
            ? $this->error[] = "use_item:lock"
            : Redis::set("use_item-lock:" . $cleanVars["uid"], 1);

        # [checking]
        [$itemId] = $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error) && ($this->successTotalCount == $this->successPassedCount) && $itemId) {
            # [process]
            if (count($cleanVars) > 0) {
                ItemLogic::useItem($cleanVars["uid"], $itemId, $cleanVars["target_sn"] ?? "");

                LogUserModel::log($request, "item_use");

                # [result]
                $this->response = [
                    "success" => true,
                ];
            }
        }

        // remove redis lock
        Redis::del("use_item-lock:" . $cleanVars["uid"]);

        # [standard output]
        return $this->output();
    }

    private function checking(array $params = [])
    {
        # [init success condition]
        $this->successTotalCount = 4;

        # [condition]
        if (isset($params["uid"]) && isset($params["name"])) {
            // check uid exist
            $user = AccountUserModel::where(["id" => $params["uid"], "status" => "active"])->first();
            if (!$user) {
                $this->error[] = "user:missing";
            } else {
                $this->successPassedCount++;
                // check item
                $item = SettingLogic::get("item", ["name" => $params["name"]]);

                if (!$item) {
                    $this->error[] = "item:not_found";
                } else {
                    $this->successPassedCount++;
                    $inventory = UserInventoryModel::defaultWhere()->where(["uid" => $params["uid"], "item_id" => $item["id"]])
                        ->orderBy("id")
                        ->first();

                    if (!$inventory) {
                        $this->error[] = "item:not_found_in_inventory";
                    } else {
                        $this->successPassedCount++;
                        // character food and potion no need target
                        if (in_array($item["category"], ["character food", "potion"])) {
                            self::itemCheckAndUsage($params["uid"], $item["id"]);
                        }
                        // pet related stuff need target
                        else if (in_array($item["category"], ["pet food", "tools"])) {
                            if (empty($params["target_sn"])) {
                                $this->error[] = "target_sn:missing";
                            } else {
                                self::itemCheckAndUsage($params["uid"], $item["id"], $params["target_sn"]);
                            }
                        }
                        // level and other item at here
                        else {
                            $this->error[] = "item:invalid_action";
                        }
                    }
                }
            }
        }

        return [$item["id"] ?? 0];
    }

    private function itemCheckAndUsage($uid = "", $itemId = "", $targetSn = "")
    {
        $itemCheck = ItemLogic::itemCheck($uid, $itemId, $targetSn);
        if (!$itemCheck["success"]) {
            $this->error[] = $itemCheck["data"][0];
        } else {
            $this->successPassedCount++;
        }
    }
}