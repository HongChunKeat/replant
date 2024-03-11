<?php

namespace plugin\dapp\app\controller\pet;

# library
use Webman\RedisQueue\Redis as RedisQueue;
use plugin\dapp\app\controller\Base;
use support\Request;
use support\Redis;
# database & logic
use app\model\database\LogUserModel;
use app\model\database\AccountUserModel;
use app\model\database\UserPetModel;
use plugin\admin\app\model\logic\PetLogic;
use app\model\logic\SettingLogic;

class MiningReward extends Base
{
    public function index(Request $request)
    {
        // check maintenance
        $stop_pet = SettingLogic::get("general", ["category" => "maintenance", "code" => "stop_pet", "value" => 1]);
        if ($stop_pet) {
            $this->error[] = "under_maintenance";
            return $this->output();
        }

        # user id
        $cleanVars["uid"] = $request->visitor["id"];

        // get and set redis lock
        Redis::get("claim_mining_reward-lock:" . $cleanVars["uid"])
            ? $this->error[] = "claim_mining_reward:lock"
            : Redis::set("claim_mining_reward-lock:" . $cleanVars["uid"], 1);

        # [checking]
        $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error) && ($this->successTotalCount == $this->successPassedCount)) {
            # [process]
            if (count($cleanVars) > 0) {
                # [process with queue]
                RedisQueue::send("user_wallet", [
                    "type" => "petMiningReward",
                    "data" => [
                        "uid" => $cleanVars["uid"],
                    ]
                ]);

                LogUserModel::log($request, "pet_claim_mining_reward");

                # [result]
                $this->response = [
                    "success" => true,
                ];
            }
        }

        // remove redis lock
        Redis::del("claim_mining_reward-lock:" . $cleanVars["uid"]);

        # [standard output]
        return $this->output();
    }

    private function checking(array $params = [])
    {
        # [init success condition]
        $this->successTotalCount = 2;

        # [condition]
        if (isset($params["uid"])) {
            // check uid exist
            $user = AccountUserModel::where(["id" => $params["uid"], "status" => "active"])->first();
            if (!$user) {
                $this->error[] = "user:missing";
            } else {
                $this->successPassedCount++;
                // check pet
                $pets = UserPetModel::defaultWhere()->where(["uid" => $params["uid"], "is_active" => 1])->get();

                $amount = 0;
                foreach ($pets as $pet) {
                    $amount += PetLogic::countMining($pet["id"]);
                }

                if ($amount <= 0) {
                    $this->error[] = "reward:unable_to_claim";
                } else {
                    $this->successPassedCount++;
                }
            }
        }
    }
}