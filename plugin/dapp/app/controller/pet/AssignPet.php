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
use app\model\database\UserLevelModel;
use app\model\database\UserPetModel;
use app\model\logic\HelperLogic;
use app\model\logic\SettingLogic;
use plugin\admin\app\model\logic\MissionLogic;
use plugin\admin\app\model\logic\PetLogic;

class AssignPet extends Base
{
    # [validation-rule]
    protected $rule = [
        "pets" => "max:100",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "pets",
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
        Redis::get("assign_pet-lock:" . $cleanVars["uid"])
            ? $this->error[] = "assign_pet:lock"
            : Redis::set("assign_pet-lock:" . $cleanVars["uid"], 1);

        # [checking]
        $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error) && ($this->successTotalCount == $this->successPassedCount)) {
            # [process]
            if (count($cleanVars) > 0) {
                // $cleanVars["pets"] = isset($cleanVars["pets"]) ? HelperLogic::explodeParams($cleanVars["pets"]) : [];
                // auto claim mining reward
                RedisQueue::send("user_wallet", [
                    "type" => "petAssign",
                    "data" => [
                        "uid" => $cleanVars["uid"],
                        "pets" => $cleanVars["pets"] ?? []
                    ]
                ]);

                // do mission
                MissionLogic::missionProgress($cleanVars["uid"], ["name" => "assign your pet"]);

                LogUserModel::log($request, "pet_assign");

                # [result]
                $this->response = [
                    "success" => true,
                ];
            }
        }

        // remove redis lock
        Redis::del("assign_pet-lock:" . $cleanVars["uid"]);

        # [standard output]
        return $this->output();
    }

    private function checking(array $params = [])
    {
        # [init success condition]
        $this->successTotalCount = 1;

        # [condition]
        if (isset($params["uid"])) {
            // check uid exist
            $user = AccountUserModel::where(["id" => $params["uid"], "status" => "active"])->first();
            if (!$user) {
                $this->error[] = "user:missing";
            } else {
                $this->successPassedCount++;
                if (isset($params["pets"])) {
                    // $params["pets"] = HelperLogic::explodeParams($params["pets"]);
                    // check pet
                    $level = UserLevelModel::where(["uid" => $params["uid"], "is_current" => 1])->first();
                    if (!$level) {
                        $this->error[] = "user:level_missing";
                    } else {
                        if (count($params["pets"]) > $level["pet_slots"]) {
                            $this->error[] = "pets:out_of_slots";
                        }
                    }

                    // can only assign healthy or unhealthy
                    $count = 0;
                    $assignPets = UserPetModel::defaultWhere()->where("uid", $params["uid"])->whereIn("sn", $params["pets"])->get();

                    foreach ($assignPets as $pet) {
                        $health = PetLogic::countHealth($pet["id"]);
                        $status = PetLogic::checkHealth($health);

                        if (in_array($status, ["healthy", "unhealthy"])) {
                            $count++;
                        }
                    }

                    if (count($params["pets"]) != $count) {
                        $this->error[] = "pets:invalid";
                    }
                }
            }
        }
    }
}