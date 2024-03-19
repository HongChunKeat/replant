<?php

namespace plugin\dapp\app\controller\seed;

# library
use Webman\RedisQueue\Redis as RedisQueue;
use plugin\dapp\app\controller\Base;
use support\Request;
use support\Redis;
# database & logic
use app\model\database\LogUserModel;
use app\model\database\AccountUserModel;
use app\model\database\SettingGeneralModel;
use app\model\database\UserSeedModel;
use app\model\logic\SettingLogic;

class ClaimPoint extends Base
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
        Redis::get("seed_claim_point-lock:" . $cleanVars["uid"])
            ? $this->error[] = "seed_claim_point:lock"
            : Redis::set("seed_claim_point-lock:" . $cleanVars["uid"], 1);

        # [checking]
        $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error) && ($this->successTotalCount == $this->successPassedCount)) {
            # [process]
            if (count($cleanVars) > 0) {
                # [process with queue]
                RedisQueue::send("user_wallet", [
                    "type" => "claimSeedPoint",
                    "data" => [
                        "uid" => $cleanVars["uid"],
                    ]
                ]);

                LogUserModel::log($request, "seed_claim_point");
                $this->response = [
                    "success" => true,
                ];
            }
        }

        // remove redis lock
        Redis::del("seed_claim_point-lock:" . $cleanVars["uid"]);

        # [standard output]
        return $this->output();
    }

    private function checking(array $params = [])
    {
        # [init success condition]
        $this->successTotalCount = 7;

        # [condition]
        if (isset($params["uid"])) {
            $user = AccountUserModel::where(["id" => $params["uid"], "status" => "active"])->first();
            if (!$user) {
                $this->error[] = "user:missing";
            } else {
                $this->successPassedCount++;

                //check setting
                $setting = SettingGeneralModel::whereIn("code", [
                    "gen1_nft_multiplier",
                    "gen2_nft_multiplier",
                    "reward_wallet",
                    "reward_amount",
                    "reward_distribution"
                ])->where("is_show", 1)->count();

                if ($setting != 5) {
                    $this->error[] = "setting:missing";
                } else {
                    $this->successPassedCount++;

                    // check seed nft setting
                    $seedNft = SettingLogic::get("nft", ["name" => "plant"]);
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
                        // only claim point need check claimed at is empty or not, assign will insert claimed at
                        if ($seed["is_active"] != 1 || empty($seed["claimed_at"])) {
                            $this->error[] = "seed:invalid_action";
                        } else {
                            $this->successPassedCount++;

                            // check if already 24 hour or 86400 seconds
                            $dff = time() - strtotime($seed["claimed_at"]);
                            if ($dff < 86400) {
                                $this->error[] = "seed:no_reward_to_be_claim";
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
