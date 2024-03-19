<?php

namespace plugin\dapp\app\controller\seed;

# library
use plugin\dapp\app\controller\Base;
use support\Request;
use support\Redis;
# database & logic
use app\model\database\LogUserModel;
use app\model\database\AccountUserModel;
use app\model\database\UserSeedModel;
use app\model\database\UserTreeModel;
use app\model\logic\SettingLogic;

class Assign extends Base
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
        Redis::get("seed_assign-lock:" . $cleanVars["uid"])
            ? $this->error[] = "seed_assign:lock"
            : Redis::set("seed_assign-lock:" . $cleanVars["uid"], 1);

        # [checking]
        [$seed] = $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error) && ($this->successTotalCount == $this->successPassedCount)) {
            # [process]
            if (count($cleanVars) > 0) {
                UserSeedModel::where("id", $seed["id"])->update([
                    "claimed_at" => date("Y-m-d H:i:s"),
                    "is_active" => 1
                ]);

                LogUserModel::log($request, "seed_assign");
                $this->response = [
                    "success" => true,
                ];
            }
        }

        // remove redis lock
        Redis::del("seed_assign-lock:" . $cleanVars["uid"]);

        # [standard output]
        return $this->output();
    }

    private function checking(array $params = [])
    {
        # [init success condition]
        $this->successTotalCount = 6;

        # [condition]
        if (isset($params["uid"])) {
            $user = AccountUserModel::where(["id" => $params["uid"], "status" => "active"])->first();
            if (!$user) {
                $this->error[] = "user:missing";
            } else {
                $this->successPassedCount++;

                // check seed nft setting
                $seedNft = SettingLogic::get("nft", ["name" => "seed"]);
                if (!$seedNft) {
                    $this->error[] = "setting:nft_not_found";
                } else {
                    $this->successPassedCount++;
                    $seedNetwork = SettingLogic::get("blockchain_network", ["id" => $seedNft["network"]]);
                    if (!$seedNetwork) {
                        $this->error[] = "setting:network_not_found";
                    } else {
                        $this->successPassedCount++;
                    }
                }

                // check seed
                $seed = UserSeedModel::where(["uid" => $params["uid"], "claimable" => 1])->first();
                if (!$seed) {
                    $this->error[] = "seed:not_found";
                } else {
                    $this->successPassedCount++;
                    if ($seed["is_active"] == 1) {
                        $this->error[] = "seed:already_assigned";
                    } else {
                        $this->successPassedCount++;
                    }

                    $tree = UserTreeModel::where(["uid" => $params["uid"], "is_active" => 1])->first();
                    if ($tree) {
                        $this->error[] = "seed:unassign_tree_first";
                    } else {
                        $this->successPassedCount++;
                    }
                }
            }
        }

        return [$seed ?? 0];
    }
}
