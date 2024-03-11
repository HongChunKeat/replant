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

class PetDelete extends Base
{
    # [validation-rule]
    protected $rule = [
        "pets" => "require|max:500",
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
        Redis::get("pet_delete-lock:" . $cleanVars["uid"])
            ? $this->error[] = "pet_delete:lock"
            : Redis::set("pet_delete-lock:" . $cleanVars["uid"], 1);

        # [checking]
        $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error) && ($this->successTotalCount == $this->successPassedCount)) {
            # [process]
            if (count($cleanVars) > 0) {
                # [process with queue]
                RedisQueue::send("user_wallet", [
                    "type" => "petDelete",
                    "data" => [
                        "uid" => $cleanVars["uid"],
                        "pets" => $cleanVars["pets"],
                    ]
                ]);

                LogUserModel::log($request, "pet_delete");

                # [result]
                $this->response = [
                    "success" => true,
                ];
            }
        }

        // remove redis lock
        Redis::del("pet_delete-lock:" . $cleanVars["uid"]);

        # [standard output]
        return $this->output();
    }

    private function checking(array $params = [])
    {
        # [init success condition]
        $this->successTotalCount = 2;

        # [condition]
        if (isset($params["uid"]) && isset($params["pets"])) {
            // check uid exist
            $user = AccountUserModel::where(["id" => $params["uid"], "status" => "active"])->first();
            if (!$user) {
                $this->error[] = "user:missing";
            } else {
                $this->successPassedCount++;
                // check pet
                $count = UserPetModel::defaultWhere()->where("uid", $params["uid"])->whereIn("sn", $params["pets"])->count();

                if (count($params["pets"]) != $count) {
                    $this->error[] = "pets:invalid";
                } else {
                    $this->successPassedCount++;
                }
            }
        }
    }
}