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

class Update extends Base
{
    # [validation-rule]
    protected $rule = [
        "uid" => "number|max:11",
        "pet" => "number|max:11",
        "quality" => "in:normal,premium",
        "rank" => "",
        "star" => "number|egt:0|max:11",
        "mining_rate" => "float|egt:0|max:11",
        "is_active" => "in:0,1",
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

    public function index(Request $request, int $targetId = 0)
    {
        # [validation]
        $this->validation($request->post(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->post(), $this->patternInputs, 1);

        # [checking]
        $this->checking(["id" => $targetId] + $cleanVars);

        # [proceed]
        if (!count($this->error)) {
            $res = "";

            # [process]
            if (count($cleanVars) > 0) {
                if (!empty($cleanVars["pet"])) {
                    $cleanVars["pet_id"] = $cleanVars["pet"];
                }

                # [unset key]
                unset($cleanVars["pet"]);

                $res = UserPetModel::where("id", $targetId)->update($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "update", "user_pet", $targetId);
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
        if (!empty($params["pet"])) {
            if (!SettingPetModel::where("id", $params["pet"])->first()) {
                $this->error[] = "pet:invalid";
            }
        }

        // check uid
        if (!empty($params["uid"])) {
            if (!AccountUserModel::where("id", $params["uid"])->first()) {
                $this->error[] = "uid:invalid";
            }
        }
    }
}
