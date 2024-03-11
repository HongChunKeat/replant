<?php

namespace plugin\admin\app\controller\setting\operator;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\SettingOperatorModel;
use app\model\logic\HelperLogic;

class Update extends Base
{
    # [validation-rule]
    protected $rule = [
        "category" => "",
        "code" => "",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "category", 
        "code",
        "remark"
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
                $res = SettingOperatorModel::where("id", $targetId)->update($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "update", "setting_operator", $targetId);
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
        if (!empty($params["category"]) || !empty($params["code"])) {
            $check = SettingOperatorModel::where("id", $params["id"])->first();

            if (SettingOperatorModel::where([
                "category" => empty($params["category"])
                    ? $check["category"]
                    : $params["category"],
                "code" => empty($params["code"])
                    ? $check["code"]
                    : $params["code"],
                ])
                ->whereNot("id", $params["id"])
                ->first()
            ) {
                $this->error[] = "entry:exists";
            }
        }
    }
}
