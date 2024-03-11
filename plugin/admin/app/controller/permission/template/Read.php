<?php

namespace plugin\admin\app\controller\permission\template;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\PermissionTemplateModel;
use app\model\logic\HelperLogic;

class Read extends Base
{
    # [outputs-pattern]
    protected $patternOutputs = [
        "id", 
        "template_code", 
        "rule", 
        "remark"
    ];

    public function index(Request $request, int $targetId = 0)
    {
        # [process]
        $res = PermissionTemplateModel::where("id", $targetId)->first();

        # [result]
        if ($res) {
            $res["rule"] = json_decode($res["rule"]);

            $this->response = [
                "success" => true,
                "data" => HelperLogic::formatOutput($res, $this->patternOutputs),
            ];
        }

        # [standard output]
        return $this->output();
    }
}
