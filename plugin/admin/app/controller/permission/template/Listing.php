<?php

namespace plugin\admin\app\controller\permission\template;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\PermissionTemplateModel;
use app\model\logic\HelperLogic;

class Listing extends Base
{
    # [validation-rule]
    protected $rule = [
        "id" => "number|max:11",
        "template_code" => "",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "id",
        "template_code",
        "remark"
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "template_code",
        "rule",
        "remark"
    ];

    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->get(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->get(), $this->patternInputs);

        # [proceed]
        if (!count($this->error)) {

            # [listing query]
            $res = PermissionTemplateModel::listing(
                $cleanVars,
                ["*"],
                ["id", "desc"]
            );

            # [result]
            if ($res) {
                foreach ($res as $row){
                    $row["rule"] = json_decode($row["rule"]);
                }

                $this->response = [
                    "success" => true,
                    "data" => HelperLogic::formatOutput($res, $this->patternOutputs, 1),
                ];
            }
        }

        # [standard output]
        return $this->output();
    }
}
