<?php

namespace plugin\dapp\app\controller\petInfo;

# library
use plugin\dapp\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\UserPetModel;
use app\model\logic\HelperLogic;
use app\model\logic\SettingLogic;
use plugin\admin\app\model\logic\PetLogic;

class Pet extends Base
{
    # [validation-rule]
    protected $rule = [
        "sn" => "require",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "sn",
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "image",
        "name",
        "quality",
        "rank",
        "star",
        "mining_rate",
        "health",
        "status",
        "mined_amount",
        "is_active",
    ];

    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->get(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->get(), $this->patternInputs);

        # user id
        $cleanVars["uid"] = $request->visitor["id"];

        # [proceed]
        if (!count($this->error)) {
            $res = "";

            $res = UserPetModel::defaultWhere()->where(["sn" => $cleanVars["sn"], "uid" => $cleanVars["uid"]])->first();

            # [result]
            if($res) {
                $pet = SettingLogic::get("pet", ["id" => $res["pet_id"]]);
                $res["name"] = $pet["name"];
                $res["image"] = $pet["image"];

                $health = PetLogic::countHealth($res["id"]);
                $res["health"] = $health < 0 ? 0 : $health;
                $res["status"] = PetLogic::checkHealth($health);
                $res["mined_amount"] = PetLogic::countMining($res["id"]);

                $this->response = [
                    "success" => true,
                    "data" => HelperLogic::formatOutput($res, $this->patternOutputs),
                ];
            }
        }

        # [standard output]
        return $this->output();
    }
}