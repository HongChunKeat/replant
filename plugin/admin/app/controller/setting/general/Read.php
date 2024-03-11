<?php

namespace plugin\admin\app\controller\setting\general;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\SettingGeneralModel;
use app\model\logic\HelperLogic;

class Read extends Base
{
    # [outputs-pattern]
    protected $patternOutputs = [
        "id", 
        "category", 
        "code", 
        "value", 
        "is_show", 
        "remark"
    ];

    public function index(Request $request, int $targetId = 0)
    {
        $res = SettingGeneralModel::where("id", $targetId)->first();

        # [result]
        if ($res) {
            $res["is_show"] = $res["is_show"] ? "yes" : "no";

            $this->response = [
                "success" => true,
                "data" => HelperLogic::formatOutput($res, $this->patternOutputs),
            ];
        }

        # [standard output]
        return $this->output();
    }
}
