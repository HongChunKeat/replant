<?php

namespace plugin\admin\app\controller\setting\operator;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\SettingOperatorModel;
use app\model\logic\HelperLogic;

class Read extends Base
{
    # [outputs-pattern]
    protected $patternOutputs = [
        "id", 
        "code",
        "category",
        "remark"
    ];

    public function index(Request $request, int $targetId = 0)
    {
        $res = SettingOperatorModel::where("id", $targetId)->first();

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
