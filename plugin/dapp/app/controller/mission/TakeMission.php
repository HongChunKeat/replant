<?php

namespace plugin\dapp\app\controller\mission;

# library
use plugin\dapp\app\controller\Base;
use support\Request;
use support\Redis;
# database & logic
use app\model\database\LogUserModel;
use app\model\database\AccountUserModel;
use app\model\database\SettingMissionModel;
use app\model\database\UserLevelModel;
use app\model\database\UserMissionModel;
use app\model\database\UserStaminaModel;
use plugin\admin\app\model\logic\MissionLogic;
use app\model\logic\HelperLogic;
use app\model\logic\SettingLogic;

class TakeMission extends Base
{
    # [validation-rule]
    protected $rule = [
        "name" => "require|max:200",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "name",
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
        Redis::get("take_mission-lock:" . $cleanVars["uid"])
            ? $this->error[] = "take_mission:lock"
            : Redis::set("take_mission-lock:" . $cleanVars["uid"], 1);

        # [checking]
        [$mission, $expiredAt, $userStamina] = $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error) && ($this->successTotalCount == $this->successPassedCount) && $mission) {
            $res = "";

            # [process]
            if (count($cleanVars) > 0) {
                $pending = SettingLogic::get("operator", ["code" => "pending"]);

                // deduct stamina and add usage, usage wont increase if stamina required = 0 
                $staminaRequired = round($mission["stamina"] * $userStamina["usage"]);

                UserStaminaModel::where("uid", $cleanVars["uid"])->update([
                    "current_stamina" => $userStamina["current_stamina"] - $staminaRequired,
                    "usage" => ($staminaRequired > 0)
                        ? $userStamina["usage"] + 0.1
                        : $userStamina["usage"]
                ]);

                // create user mission
                $res = UserMissionModel::create([
                    "sn" => HelperLogic::generateUniqueSN("user_mission"),
                    "uid" => $cleanVars["uid"],
                    "mission_id" => $mission["id"],
                    "status" => $pending["id"],
                    "expired_at" => $expiredAt
                ]);
            }

            # [result]
            if ($res) {
                // do mission
                MissionLogic::missionProgress($cleanVars["uid"], ["name" => "take your first mission"]);

                if ($mission["action"] == "external") {
                    // do mission - for external direct end
                    MissionLogic::missionProgress($cleanVars["uid"], ["id" => $mission["id"]]);
                }

                LogUserModel::log($request, "take_mission");
                # [result]
                $this->response = [
                    "success" => true,
                ];
            }
        }

        // remove redis lock
        Redis::del("take_mission-lock:" . $cleanVars["uid"]);

        # [standard output]
        return $this->output();
    }

    private function checking(array $params = [])
    {
        # [init success condition]
        $this->successTotalCount = 6;

        # [condition]
        if (isset($params["uid"]) && isset($params["name"])) {
            // check uid exist
            $user = AccountUserModel::where(["id" => $params["uid"], "status" => "active"])->first();
            if (!$user) {
                $this->error[] = "user:missing";
            } else {
                $this->successPassedCount++;
                // get mission
                $mission = SettingLogic::get("mission", ["name" => $params["name"]]);

                // get stamina and level
                $userStamina = UserStaminaModel::where("uid", $params["uid"])->first();
                $userLevel = UserLevelModel::where(["uid" => $params["uid"], "is_current" => 1])->first();

                if (!$mission || !$userStamina || !$userLevel) {
                    $this->error[] = "mission:invalid";
                } else {
                    $this->successPassedCount++;
                    // check mission
                    $checkMission = false;

                    // check if exist on today, take once per day
                    if ($mission["type"] == "daily") {
                        $checkMission = UserMissionModel::where(["uid" => $params["uid"], "mission_id" => $mission["id"]])
                            ->whereBetween("created_at", [date("Y-m-d 00:00:00"), date("Y-m-d 23:59:59")])
                            ->first();

                        $expiredAt = date("Y-m-d 23:59:59");
                    }
                    // check if exist in this week, take once per week
                    else if ($mission["type"] == "weekly") {
                        $checkMission = UserMissionModel::where(["uid" => $params["uid"], "mission_id" => $mission["id"]])
                            ->whereBetween("created_at", [
                                date("Y-m-d 00:00:00", strtotime("this saturday -6 day")),
                                date("Y-m-d 23:59:59", strtotime("this saturday"))
                            ])
                            ->first();

                        $expiredAt = date("Y-m-d 23:59:59", strtotime("this saturday"));
                    }
                    // check if ever exist, can only take once
                    else {
                        $checkMission = UserMissionModel::where(["uid" => $params["uid"], "mission_id" => $mission["id"]])->first();
                    }

                    if ($checkMission) {
                        $this->error[] = "mission:already_taken";
                    } else {
                        $this->successPassedCount++;
                    }

                    // check level
                    if ($mission["level"] > $userLevel["level"]) {
                        $this->error[] = "level:invalid";
                    } else {
                        $this->successPassedCount++;
                    }

                    $keyword = "chat in tendopia group";
                    $pending = SettingLogic::get("operator", ["code" => "pending"]);
                    $getChatMission = SettingMissionModel::where("name", "like", "%" . $keyword . "%")->pluck("id");
                    $checkChatMission = UserMissionModel::where(["uid" => $params["uid"], "status" => $pending["id"]])
                        ->whereBetween("created_at", [date("Y-m-d 00:00:00"), date("Y-m-d 23:59:59")])
                        ->whereIn("mission_id", $getChatMission)
                        ->first();
                    // restrict one social mission at a time for daily type
                    if (str_contains($mission["name"], $keyword) && $checkChatMission) {
                        $this->error[] = "mission:cannot_take_multiple_social_mission_at_once";
                    } else {
                        $this->successPassedCount++;
                    }

                    // check stamina
                    $staminaRequired = round($mission["stamina"] * $userStamina["usage"]);
                    if ($userStamina["current_stamina"] < $staminaRequired) {
                        $this->error[] = "stamina:insufficient_stamina";
                    } else {
                        $this->successPassedCount++;
                    }
                }
            }
        }

        return [$mission ?? 0, $expiredAt ?? null, $userStamina ?? 0];
    }
}