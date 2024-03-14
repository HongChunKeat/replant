<?php

namespace plugin\admin\app\controller\user\tree;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\AccountUserModel;
use app\model\database\SettingLevelModel;
use app\model\database\UserTreeModel;
use app\model\logic\HelperLogic;

class Update extends Base
{
    # [validation-rule]
    protected $rule = [
        "uid" => "number|max:11",
        "level" => "number|max:11",
        "health" => "number|egt:0|max:11",
        "mined_amount" => "float|egt:0|max:11",
        "is_active" => "in:0,1",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "uid",
        "level",
        "health",
        "mined_amount",
        "is_active",
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
                $res = UserTreeModel::where("id", $targetId)->update($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "update", "user_tree", $targetId);
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
        // check uid
        if (!empty($params["uid"])) {
            if (!AccountUserModel::where("id", $params["uid"])->first()) {
                $this->error[] = "uid:invalid";
            }
        }

        // check level
        if (!empty($params["level"])) {
            if (!SettingLevelModel::where("id", $params["level"])->first()) {
                $this->error[] = "level:invalid";
            }
        }
    }
}
