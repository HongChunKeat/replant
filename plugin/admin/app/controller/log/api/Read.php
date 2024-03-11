<?php

namespace plugin\admin\app\controller\log\api;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogApiModel;
use app\model\logic\HelperLogic;

class Read extends Base
{
    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "sn",
        "created_at",
        "updated_at",
        "name",
        "group",
        "ip",
        "ref_table",
        "ref_id",
        "response",
        "by_pass",
        "remark"
    ];

    public function index(Request $request, int $targetId = 0)
    {
        # [process]
        $res = LogApiModel::where("id", $targetId)->first();

        # [result]
        if ($res) {
            $this->response = [
                "success" => true,
                "data" => HelperLogic::formatOutput($res, $this->patternOutputs),
            ];
        }

        # [standard output]
        return $this->output();
    }
}
