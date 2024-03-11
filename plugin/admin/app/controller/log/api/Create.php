<?php

namespace plugin\admin\app\controller\log\api;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\LogApiModel;
use app\model\logic\HelperLogic;

class Create extends Base
{
    # [validation-rule]
    protected $rule = [
        "name" => "require",
        "group" => "require",
        "ip" => "",
        "ref_table" => "",
        "ref_id" => "number|max:11",
        "response" => "max:500",
        "by_pass" => "",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "name",
        "group",
        "ip",
        "ref_table",
        "ref_id",
        "response",
        "by_pass",
        "remark",
    ];

    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->post(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->post(), $this->patternInputs);

        # [proceed]
        if (!count($this->error)) {
            $res = "";

            # [process]
            if (count($cleanVars) > 0) {
                $cleanVars["sn"] = HelperLogic::generateUniqueSN("log_api");
                $res = LogApiModel::create($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "create", "log_api", $res["id"]);
                $this->response = [
                    "success" => true,
                ];
            }
        }

        # [standard output]
        return $this->output();
    }
}
