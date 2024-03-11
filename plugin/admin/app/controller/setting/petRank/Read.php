<?php

namespace plugin\admin\app\controller\setting\petRank;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\SettingPetRankModel;
use app\model\logic\HelperLogic;

class Read extends Base
{
    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "quality",
        "rank",
        "star",
        "item_required",
        "item_required_quantity",
        "mining_rate",
        "remark",
    ];

    public function index(Request $request, int $targetId = 0)
    {
        # [process]
        $res = SettingPetRankModel::where("id", $targetId)->first();

        # [result]
        if ($res) {
            if(isset($res["item_required"])){
                [$res["item_required"], $res["item_required_quantity"]] = HelperLogic::splitJsonParams($res["item_required"]);
            }

            $this->response = [
                "success" => true,
                "data" => HelperLogic::formatOutput($res, $this->patternOutputs),
            ];
        }

        # [standard output]
        return $this->output();
    }
}
