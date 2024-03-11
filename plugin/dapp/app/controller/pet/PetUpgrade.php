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
use app\model\logic\HelperLogic;
use app\model\logic\SettingLogic;
use plugin\admin\app\model\logic\ItemLogic;

class PetUpgrade extends Base
{
    # [validation-rule]
    protected $rule = [
        "sn" => "require",
        "items" => "require|max:500",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "sn",
        "items",
    ];

    public function index(Request $request)
    {
        // check maintenance
        $stop_pet = SettingLogic::get("general", ["category" => "maintenance", "code" => "stop_pet", "value" => 1]);
        if ($stop_pet) {
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
        Redis::get("pet_upgrade-lock:" . $cleanVars["uid"])
            ? $this->error[] = "pet_upgrade:lock"
            : Redis::set("pet_upgrade-lock:" . $cleanVars["uid"], 1);

        # [checking]
        $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error) && ($this->successTotalCount == $this->successPassedCount)) {
            # [process]
            if (count($cleanVars) > 0) {
                // auto claim mining reward
                RedisQueue::send("user_wallet", [
                    "type" => "petUpgrade",
                    "data" => [
                        "uid" => $cleanVars["uid"],
                        "sn" => $cleanVars["sn"],
                        "items" => $cleanVars["items"]
                    ]
                ]);

                # [result]
                LogUserModel::log($request, "pet_upgrade");
                $this->response = [
                    "success" => true,
                ];
            }
        }

        // remove redis lock
        Redis::del("pet_upgrade-lock:" . $cleanVars["uid"]);

        # [standard output]
        return $this->output();
    }

    private function checking(array $params = [])
    {
        # [init success condition]
        $this->successTotalCount = 3;

        # [condition]
        if (isset($params["uid"]) && isset($params["sn"]) && isset($params["items"])) {
            $user = AccountUserModel::where(["id" => $params["uid"], "status" => "active"])->first();
            if (!$user) {
                $this->error[] = "user:missing";
            } else {
                $this->successPassedCount++;
                // check pet
                $userPet = UserPetModel::defaultWhere()->where(["uid" => $params["uid"], "sn" => $params["sn"]])->first();

                if (!$userPet) {
                    $this->error[] = "pet:not_found";
                } else {
                    $this->successPassedCount++;
                    $itemCheck = ItemLogic::checkPetUpgrade($params["uid"], $userPet, $params["items"]);

                    if (!$itemCheck["success"]) {
                        $this->error[] = $itemCheck["data"][0];
                    } else {
                        $this->successPassedCount++;
                    }
                }
            }
        }
    }
}