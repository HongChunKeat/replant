<?php

namespace plugin\admin\app\controller\user\mission;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\SettingMissionModel;
use app\model\database\UserMissionModel;
use app\model\database\SettingOperatorModel;
use app\model\database\AccountUserModel;
use app\model\logic\HelperLogic;

class Update extends Base
{
    # [validation-rule]
    protected $rule = [
        "uid" => "number|max:11",
        "mission" => "number|max:11",
        "status" => "number|max:11",
        "progress" => "number|egt:0|max:11",
        "expired_at" => "date",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "uid",
        "mission",
        "status",
        "progress",
        "expired_at",
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
                if (!empty($cleanVars["mission"])) {
                    $cleanVars["mission_id"] = $cleanVars["mission"];
                }

                # [unset key]
                unset($cleanVars["mission"]);

                $res = UserMissionModel::where("id", $targetId)->update($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "update", "user_mission", $targetId);
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
        if (!empty($params["uid"])) {
            if (!AccountUserModel::where("id", $params["uid"])->first()) {
                $this->error[] = "uid:invalid";
            }
        }

        if (!empty($params["mission"])) {
            if (!SettingMissionModel::where("id", $params["mission"])->first()) {
                $this->error[] = "mission:invalid";
            }
        }

        if (!empty($params["status"])) {
            if (!SettingOperatorModel::where("id", $params["status"])->where("category", "status")->first()) {
                $this->error[] = "status:invalid";
            }
        }
    }
}
