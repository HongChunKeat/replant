<?php

namespace plugin\dapp\app\controller\seed;

# library
use plugin\dapp\app\controller\Base;
use support\Request;
use support\Redis;
# database & logic
use app\model\database\LogUserModel;
use app\model\database\AccountUserModel;
use app\model\database\NftUsageModel;
use app\model\database\UserSeedModel;
use app\model\logic\SettingLogic;

class Unassign extends Base
{
    public function index(Request $request)
    {
        // check maintenance
        $stop_seed = SettingLogic::get("general", ["category" => "maintenance", "code" => "stop_seed", "value" => 1]);
        if ($stop_seed) {
            $this->error[] = "under_maintenance";
            return $this->output();
        }

        # user id
        $cleanVars["uid"] = $request->visitor["id"];

        // get and set redis lock
        Redis::get("seed_unassign-lock:" . $cleanVars["uid"])
            ? $this->error[] = "seed_unassign:lock"
            : Redis::set("seed_unassign-lock:" . $cleanVars["uid"], 1);

        # [checking]
        [$seed] = $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error) && ($this->successTotalCount == $this->successPassedCount)) {
            $res = "";

            # [process]
            if (count($cleanVars) > 0) {
                $res = UserSeedModel::where("id", $seed["id"])->update(["is_active" => 0]);

                // release user's nft lock
                NftUsageModel::where("uid", $cleanVars["uid"])->delete();
            }

            if ($res) {
                LogUserModel::log($request, "seed_unassign");
                $this->response = [
                    "success" => true,
                ];
            }
        }

        // remove redis lock
        Redis::del("seed_unassign-lock:" . $cleanVars["uid"]);

        # [standard output]
        return $this->output();
    }

    private function checking(array $params = [])
    {
        # [init success condition]
        $this->successTotalCount = 4;

        # [condition]
        if (isset($params["uid"])) {
            $user = AccountUserModel::where(["id" => $params["uid"], "status" => "active"])->first();
            if (!$user) {
                $this->error[] = "user:missing";
            } else {
                $this->successPassedCount++;

                // check seed
                $seed = UserSeedModel::where(["uid" => $params["uid"], "claimable" => 1])->first();
                if (!$seed) {
                    $this->error[] = "seed:not_found";
                } else {
                    $this->successPassedCount++;
                    if ($seed["is_active"] != 1) {
                        $this->error[] = "seed:already_unassigned";
                    } else {
                        $this->successPassedCount++;

                        // check if already 24 hour or 86400 seconds, if yes prompt them to claim first
                        $dff = time() - strtotime($seed["claimed_at"]);
                        if ($dff >= 86400) {
                            $this->error[] = "seed:please_claim_your_reward_first";
                        } else {
                            $this->successPassedCount++;
                        }
                    }
                }
            }
        }

        return [$seed ?? 0];
    }
}
