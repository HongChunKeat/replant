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

class Create extends Base
{
    # [validation-rule]
    protected $rule = [
        "uid" => "require|number|max:11",
        "mission" => "require|number|max:11",
        "status" => "require|number|max:11",
        "progress" => "require|number|egt:0|max:11",
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
                if (isset($cleanVars["mission"])) {
                    $cleanVars["mission_id"] = $cleanVars["mission"];
                }

                # [unset key]
                unset($cleanVars["mission"]);

                $cleanVars["sn"] = HelperLogic::generateUniqueSN("user_mission");
                $res = UserMissionModel::create($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "create", "user_mission", $res["id"]);
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
        if (isset($params["uid"])) {
            if (!AccountUserModel::where("id", $params["uid"])->first()) {
                $this->error[] = "uid:invalid";
            }
        }

        if (isset($params["mission"])) {
            if (!SettingMissionModel::where("id", $params["mission"])->first()) {
                $this->error[] = "mission:invalid";
            }
        }

        if (isset($params["status"])) {
            if (!SettingOperatorModel::where("id", $params["status"])->where("category", "status")->first()) {
                $this->error[] = "status:invalid";
            }
        }
    }
}
