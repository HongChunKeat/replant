<?php

namespace plugin\admin\app\controller\user\pet;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\SettingPetModel;
use app\model\database\UserPetModel;
use app\model\database\AccountUserModel;
use app\model\logic\HelperLogic;

class Create extends Base
{
    # [validation-rule]
    protected $rule = [
        "uid" => "require|number|max:11",
        "pet" => "require|number|max:11",
        "quality" => "require|in:normal,premium",
        "rank" => "require",
        "star" => "require|number|egt:0|max:11",
        "mining_rate" => "require|float|egt:0|max:11",
        "is_active" => "require|in:0,1",
        "mining_cutoff_at" => "date",
        "health_pause_at" => "date",
        "health_end_at" => "date",
        "removed_at" => "date",
        "marketed_at" => "date",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "uid",
        "pet",
        "quality",
        "rank",
        "star",
        "mining_rate",
        "is_active",
        "mining_cutoff_at",
        "health_pause_at",
        "health_end_at",
        "removed_at",
        "marketed_at",
        "remark",
    ];

    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->post(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->post(), $this->patternInputs);

        # [checking]
        $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error)) {
            $res = "";

            # [process]
            if (count($cleanVars) > 0) {
                if (isset($cleanVars["pet"])) {
                    $cleanVars["pet_id"] = $cleanVars["pet"];
                }

                # [unset key]
                unset($cleanVars["pet"]);

                $cleanVars["sn"] = HelperLogic::generateUniqueSN("user_pet");
                $res = UserPetModel::create($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "create", "user_pet", $res["id"]);
                $this->response = [
                    "success" => true,
                ];
            }
        }

        # [standard output]
        return $this->output();
    }

    private function checking(array $params = [])
    {
        # [condition]
        // check pet
        if (isset($params["pet"])) {
            if (!SettingPetModel::where("id", $params["pet"])->first()) {
                $this->error[] = "pet:invalid";
            }
        }

        // check uid
        if (isset($params["uid"])) {
            if (!AccountUserModel::where("id", $params["uid"])->first()) {
                $this->error[] = "uid:invalid";
            }
        }
    }
}
