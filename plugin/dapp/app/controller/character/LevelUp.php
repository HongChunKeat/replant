<?php

namespace plugin\dapp\app\controller\character;

# library
use plugin\dapp\app\controller\Base;
use support\Request;
use support\Redis;
# database & logic
use app\model\database\LogUserModel;
use app\model\database\AccountUserModel;
use app\model\database\UserInventoryModel;
use app\model\database\UserLevelModel;
use app\model\database\UserStaminaModel;
use app\model\logic\HelperLogic;
use app\model\logic\SettingLogic;
use plugin\admin\app\model\logic\ItemLogic;
use plugin\admin\app\model\logic\MissionLogic;

class LevelUp extends Base
{
    # [validation-rule]
    protected $rule = [
        "items" => "require|max:500",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "items",
    ];

    public function index(Request $request)
    {
        // check maintenance
        $stop_character = SettingLogic::get("general", ["category" => "maintenance", "code" => "stop_character", "value" => 1]);
        if ($stop_character) {
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
        Redis::get("character_level_up-lock:" . $cleanVars["uid"])
            ? $this->error[] = "character_level_up:lock"
            : Redis::set("character_level_up-lock:" . $cleanVars["uid"], 1);

        # [checking]
        [$userLevel] = $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error) && ($this->successTotalCount == $this->successPassedCount) && $userLevel) {
            $res = "";

            # [process]
            if (count($cleanVars) > 0) {
                // remove item used
                UserInventoryModel::defaultWhere()->where("uid", $cleanVars["uid"])->whereIn("sn", $cleanVars["items"])
                    ->update(["used_at" => date("Y-m-d H:i:s")]);

                // reset all to 0
                UserLevelModel::where("uid", $cleanVars["uid"])->update(["is_current" => 0]);

                // get next level stats
                $nextLevel = SettingLogic::get("level", ["level" => $userLevel["level"] + 1]);

                // set stamina and level to new one
                UserStaminaModel::where("uid", $cleanVars["uid"])->update(["max_stamina" => $nextLevel["stamina"]]);

                $res = UserLevelModel::create([
                    "uid" => $cleanVars["uid"],
                    "level" => $nextLevel["level"],
                    "pet_slots" => $userLevel["pet_slots"] > $nextLevel["pet_slots"]
                        ? $userLevel["pet_slots"]
                        : $nextLevel["pet_slots"],
                    "inventory_pages" => $userLevel["inventory_pages"] > $nextLevel["inventory_pages"]
                        ? $userLevel["inventory_pages"]
                        : $nextLevel["inventory_pages"],
                    "is_current" => 1
                ]);
            }

            # [result]
            if ($res) {
                // do mission
                MissionLogic::missionProgress($cleanVars["uid"], ["name" => "level up your character"]);

                LogUserModel::log($request, "character_level_up");
                $this->response = [
                    "success" => true,
                ];
            }
        }

        // remove redis lock
        Redis::del("character_level_up-lock:" . $cleanVars["uid"]);

        # [standard output]
        return $this->output();
    }

    private function checking(array $params = [])
    {
        # [init success condition]
        $this->successTotalCount = 3;

        # [condition]
        if (isset($params["uid"]) && isset($params["items"])) {
            $user = AccountUserModel::where(["id" => $params["uid"], "status" => "active"])->first();
            if (!$user) {
                $this->error[] = "user:missing";
            } else {
                $this->successPassedCount ++;
                // check level
                $userLevel = UserLevelModel::where(["uid" => $params["uid"], "is_current" => 1])->first();

                if (!$userLevel) {
                    $this->error[] = "user:level_missing";
                } else {
                    $this->successPassedCount ++;
                    $itemCheck = ItemLogic::checkLevelUp($params["uid"], $userLevel["level"] + 1, $params["items"]);

                    if (!$itemCheck["success"]) {
                        $this->error[] = $itemCheck["data"][0];
                    } else {
                        $this->successPassedCount ++;
                    }
                }
            }
        }

        return [$userLevel ?? 0];
    }
}