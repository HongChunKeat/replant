<?php

namespace plugin\dapp\app\controller\onboarding;

# library
use plugin\dapp\app\controller\Base;
use support\Request;
use support\Redis;
# database & logic
use app\model\database\LogUserModel;
use app\model\database\AccountUserModel;
use app\model\database\UserMissionModel;
use app\model\database\UserPointModel;
use app\model\logic\SettingLogic;

class ClaimPoint extends Base
{
    public function index(Request $request)
    {
        # user id
        $cleanVars["uid"] = $request->visitor["id"];

        // get and set redis lock
        Redis::get("claim_point-lock:" . $cleanVars["uid"])
            ? $this->error[] = "claim_point:lock"
            : Redis::set("claim_point-lock:" . $cleanVars["uid"], 1);

        # [checking]
        [$userMissionId, $point] = $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error) && ($this->successTotalCount == $this->successPassedCount) && $userMissionId) {
            # [process]
            if (count($cleanVars) > 0) {
                $claimed = SettingLogic::get("operator", ["code" => "claimed"]);
                UserMissionModel::where("id", $userMissionId)->update([
                    "status" => $claimed["id"]
                ]);

                // give 1000 point
                UserPointModel::create([
                    "uid" => $cleanVars["uid"],
                    "from_uid" => $cleanVars["uid"],
                    "point" => $point["value"],
                    "source" => "claim"
                ]);

                LogUserModel::log($request, "onboarding_claim_point");

                # [result]
                $this->response = [
                    "success" => true,
                ];
            }
        }

        // remove redis lock
        Redis::del("claim_point-lock:" . $cleanVars["uid"]);

        # [standard output]
        return $this->output();
    }

    private function checking(array $params = [])
    {
        # [init success condition]
        $this->successTotalCount = 4;

        # [condition]
        if (isset($params["uid"])) {
            // check uid exist
            $user = AccountUserModel::where(["id" => $params["uid"], "status" => "active"])->first();
            if (!$user) {
                $this->error[] = "user:missing";
            } else {
                $this->successPassedCount++;
                $point = SettingLogic::get("general", ["code" => "point", "category" => "onboarding"]);
                if (!$point) {
                    $this->error[] = "setting:missing";
                } else {
                    $this->successPassedCount++;
                    // check claimed
                    $checkClaimed = UserPointModel::where(["uid" => $params["uid"], "from_uid" => $params["uid"], "source" => "claim"])->first();

                    if ($checkClaimed) {
                        $this->error[] = "point:already_claimed";
                    } else {
                        $this->successPassedCount++;
                        $completed = SettingLogic::get("operator", ["code" => "completed"]);

                        // onboarding mission id = 1
                        $userMission = UserMissionModel::where(["uid" => $params["uid"], "mission_id" => 1, "status" => $completed["id"]])->first();

                        if (!$userMission || !$user["telegram"] || !$user["web3_address"]) {
                            $this->error[] = "point:unable_to_claim";
                        } else {
                            $this->successPassedCount++;
                        }
                    }
                }
            }
        }

        return [$userMission["id"] ?? 0, $point ?? 0];
    }
}