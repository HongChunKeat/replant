<?php

namespace plugin\admin\app\controller\setting\operator;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\SettingOperatorModel;
use app\model\logic\HelperLogic;

class Create extends Base
{
    # [validation-rule]
    protected $rule = [
        "category" => "require",
        "code" => "require",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "category", 
        "code",
        "remark"
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
                $res = SettingOperatorModel::create($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "create", "setting_operator", $res["id"]);
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
        if (isset($params["category"]) && isset($params["code"])) {
            if (SettingOperatorModel::where(["category" => $params["category"], "code" => $params["code"]])->first()) {
                $this->error[] = "entry:exists";
            }
        }
    }
}
