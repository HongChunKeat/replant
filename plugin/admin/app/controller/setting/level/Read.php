<?php

namespace plugin\admin\app\controller\setting\level;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\SettingLevelModel;
use app\model\logic\HelperLogic;

class Read extends Base
{
    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "level",
        "cost",
        "mining_rate",
        "remark",
    ];

    public function index(Request $request, int $targetId = 0)
    {
        # [process]
        $res = SettingLevelModel::where("id", $targetId)->first();

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
