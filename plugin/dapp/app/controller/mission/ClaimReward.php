<?php

namespace plugin\dapp\app\controller\mission;

# library
use plugin\dapp\app\controller\Base;
use support\Request;
use support\Redis;
# database & logic
use app\model\database\LogUserModel;
use app\model\database\AccountUserModel;
use app\model\database\UserInventoryModel;
use app\model\database\UserLevelModel;
use app\model\database\UserMissionModel;
use plugin\admin\app\model\logic\MissionLogic;
use app\model\logic\HelperLogic;
use app\model\logic\SettingLogic;

class ClaimReward extends Base
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
        $stop_mission = SettingLogic::get("general", ["category" => "maintenance", "code" => "stop_mission", "value" => 1]);
        if ($stop_mission) {
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
        Redis::get("claim_mission_reward-lock:" . $cleanVars["uid"])
            ? $this->error[] = "claim_mission_reward:lock"
            : Redis::set("claim_mission_reward-lock:" . $cleanVars["uid"], 1);

        # [checking]
        [$userMissionId] = $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error) && ($this->successTotalCount == $this->successPassedCount) && $userMissionId) {
            # [process]
            if (count($cleanVars) > 0) {
                MissionLogic::claimReward($cleanVars["uid"], $userMissionId);

                LogUserModel::log($request, "claim_mission_reward");

                # [result]
                $this->response = [
                    "success" => true,
                ];
            }
        }

        // remove redis lock
        Redis::del("claim_mission_reward-lock:" . $cleanVars["uid"]);

        # [standard output]
        return $this->output();
    }

    private function checking(array $params = [])
    {
        # [init success condition]
        $this->successTotalCount = 3;

        # [condition]
        if (isset($params["uid"]) && isset($params["sn"])) {
            // check uid exist
            $user = AccountUserModel::where(["id" => $params["uid"], "status" => "active"])->first();
            if (!$user) {
                $this->error[] = "user:missing";
            } else {
                $this->successPassedCount++;
                $completed = SettingLogic::get("operator", ["code" => "completed"]);

                $userMission = UserMissionModel::where(["uid" => $params["uid"], "sn" => $params["sn"], "status" => $completed["id"]])->first();
                $userLevel = UserLevelModel::where(["uid" => $params["uid"], "is_current" => 1])->first();

                if (!$userMission || !$userLevel) {
                    $this->error[] = "reward:unable_to_claim";
                } else {
                    $this->successPassedCount++;
                    $mission = SettingLogic::get("mission", ["id" => $userMission["mission_id"]]);

                    if (!$mission) {
                        $this->error[] = "mission:invalid";
                    } else {
                        $this->successPassedCount++;
                        // check inventory is over limit or not
                        if (isset($mission["item_reward"])) {
                            $maxStorage = $userLevel["inventory_pages"] * 25;

                            $items = json_decode($mission["item_reward"]);
                            $itemsTotal = 0;
                            foreach ($items as $key => $value) {
                                $itemsTotal += $value;
                            }

                            $currentStorage = UserInventoryModel::defaultWhere()->where("uid", $params["uid"])->count();

                            if ($currentStorage + $itemsTotal > $maxStorage) {
                                $this->error[] = "inventory:full";
                            }
                        }
                    }
                }
            }
        }

        return [$userMission["id"] ?? 0];
    }
}