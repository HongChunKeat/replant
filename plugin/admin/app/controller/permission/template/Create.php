<?php

namespace plugin\admin\app\controller\permission\template;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\PermissionTemplateModel;
use app\model\logic\HelperLogic;

class Create extends Base
{
    # [validation-rule]
    protected $rule = [
        "template_code" => "require",
        "rule" => "require|max:1000",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = ["template_code", "rule", "remark"];

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
                // encode input
                if (isset($cleanVars["rule"])) {
                    $cleanVars["rule"] = json_encode($cleanVars["rule"]);
                }

                $res = PermissionTemplateModel::create($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "create", "permission_template", $res["id"]);
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
        if (isset($params["template_code"])) {
            // Check template_code exists
            if (PermissionTemplateModel::where("template_code", $params["template_code"])->first()) {
                $this->error[] = "template_code:exists";
            }
        }
    }
}
