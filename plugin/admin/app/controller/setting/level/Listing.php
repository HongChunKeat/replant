<?php

namespace plugin\admin\app\controller\setting\level;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\SettingLevelModel;
use app\model\logic\HelperLogic;

class Listing extends Base
{
    # [validation-rule]
    protected $rule = [
        "id" => "number|max:11",
        "level" => "number|max:11",
        "cost" => "float|egt:0|max:11",
        "mining_rate" => "float|egt:0|max:11",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "id",
        "level",
        "cost",
        "mining_rate",
        "remark",
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "level",
        "cost",
        "mining_rate",
        "remark",
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
            $res = SettingLevelModel::listing(
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
