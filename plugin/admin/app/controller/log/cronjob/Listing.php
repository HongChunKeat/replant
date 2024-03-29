<?php

namespace plugin\admin\app\controller\log\cronjob;

# library
use support\Request;
use plugin\admin\app\controller\Base;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\LogCronjobModel;
use app\model\logic\HelperLogic;

class Listing extends Base
{
    # [validation-rule]
    protected $rule = [
        "id" => "number|max:11",
        "used_at" => "number|length:8",
        "cronjob_code" => "",
        "info" => "",
        "created_at_start" => "date",
        "created_at_end" => "date",
        "updated_at_start" => "date",
        "updated_at_end" => "date",
        "completed_at_start" => "date",
        "completed_at_end" => "date",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "id",
        "used_at",
        "cronjob_code",
        "info"
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "created_at",
        "updated_at",
        "completed_at",
        "used_at",
        "cronjob_code",
        "info",
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
            # [search date range]
            $cleanVars = array_merge(
                $cleanVars, 
                HelperLogic::buildDateSearch($request, ["created_at", "updated_at", "completed_at"])
            );

            # [listing query]
            $res = LogCronjobModel::listing(
                $cleanVars,
                ["*"],
                ["id", "desc"]
            );

            # [result]
            if ($res) {
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
