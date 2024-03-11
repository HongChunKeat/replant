<?php

namespace plugin\admin\app\controller\permission\template;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\PermissionTemplateModel;
use app\model\logic\HelperLogic;

class Update extends Base
{
    # [validation-rule]
    protected $rule = [
        "template_code" => "",
        "rule" => "max:1000",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = ["template_code", "rule", "remark"];

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
                // encode input
                if (!empty($cleanVars["rule"])) {
                    $cleanVars["rule"] = json_encode($cleanVars["rule"]);
                }

                $res = PermissionTemplateModel::where("id", $targetId)->update($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "update", "permission_template", $targetId);
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
        if (!empty($params["template_code"])) {
            // Check template_code exists
            if (PermissionTemplateModel::where("template_code", $params["template_code"])
                    ->whereNot("id", $params["id"])
                    ->first()
            ) {
                $this->error[] = "template_code:exists";
            }
        }
    }
}
