<?php

namespace plugin\admin\app\controller\setting\petRank;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\SettingPetRankModel;
use app\model\logic\HelperLogic;

class Listing extends Base
{
    # [validation-rule]
    protected $rule = [
        "id" => "number|max:11",
        "quality" => "in:normal,premium",
        "rank" => "",
        "star" => "number|egt:0|max:11",
        "item_required" => "",
        "mining_rate" => "float|egt:0|max:11",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "id",
        "quality",
        "rank",
        "star",
        "item_required",
        "mining_rate",
        "remark",
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "quality",
        "rank",
        "star",
        "item_required",
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
            $res = SettingPetRankModel::listing(
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
