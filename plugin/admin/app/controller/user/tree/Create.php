<?php

namespace plugin\admin\app\controller\user\tree;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\AccountUserModel;
use app\model\database\UserTreeModel;
use app\model\database\SettingLevelModel;
use app\model\logic\HelperLogic;

class Create extends Base
{
    # [validation-rule]
    protected $rule = [
        "uid" => "require|number|max:11",
        "level" => "require|number|max:11",
        "health" => "require|number|egt:0|max:11",
        "mined_amount" => "require|float|egt:0|max:11",
        "is_active" => "require|in:0,1",
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
                $cleanVars["sn"] = HelperLogic::generateUniqueSN("user_tree");
                $res = UserTreeModel::create($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "create", "user_tree", $res["id"]);
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
        if (isset($params["uid"])) {
            if (!AccountUserModel::where("id", $params["uid"])->first()) {
                $this->error[] = "uid:invalid";
            }
        }

        // check level
        if (isset($params["level"])) {
            if (!SettingLevelModel::where("id", $params["level"])->first()) {
                $this->error[] = "level:invalid";
            }
        }
    }
}
