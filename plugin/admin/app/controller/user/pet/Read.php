<?php

namespace plugin\admin\app\controller\user\pet;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\AccountUserModel;
use app\model\database\SettingPetModel;
use app\model\database\UserPetModel;
use app\model\logic\HelperLogic;
use plugin\admin\app\model\logic\PetLogic;

class Read extends Base
{
    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "sn",
        "mining_cutoff_at",
        "health_pause_at",
        "health_end_at",
        "removed_at",
        "marketed_at",
        "created_at",
        "updated_at",
        "uid",
        "user",
        "quality",
        "pet",
        "rank",
        "star",
        "mining_rate",
        "health",
        "status",
        "mined_amount",
        "is_active",
        "remark",
    ];

    public function index(Request $request, int $targetId = 0)
    {
        # [process]
        $res = UserPetModel::where("id", $targetId)->first();

        # [result]
        if ($res) {
            $res["is_active"] = $res["is_active"] ? "active" : "inactive";

            $user = AccountUserModel::where("id", $res["uid"])->first();
            $res["user"] = $user ? $user["user_id"] : "";

            $pet = SettingPetModel::where("id", $res["pet_id"])->first();
            $res["pet"] = $pet ? $pet["name"] : "";

            $health = PetLogic::countHealth($res["id"]);
            $res["health"] = $health < 0 ? 0 : $health;
            $res["status"] = PetLogic::checkHealth($health);
            $res["mined_amount"] = PetLogic::countMining($res["id"]);

            $this->response = [
                "success" => true,
                "data" => HelperLogic::formatOutput($res, $this->patternOutputs),
            ];
        }

        # [standard output]
        return $this->output();
    }
}
