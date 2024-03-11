<?php

namespace plugin\admin\app\controller\setting\attribute;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\SettingAttributeModel;
use app\model\logic\HelperLogic;

class Update extends Base
{
    # [validation-rule]
    protected $rule = [
        "code" => "",
        "category" => "",
        "filter" => "",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "code",
        "category",
        "filter",
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
                $res = SettingAttributeModel::where("id", $targetId)->update($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "update", "setting_attribute", $targetId);
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
        if (!empty($params["code"])) {
            if (SettingAttributeModel::where("code", $params["code"])
                    ->whereNot("id", $params["id"])
                    ->first()
            ) {
                $this->error[] = "code:exists";
            }
        }
    }
}
