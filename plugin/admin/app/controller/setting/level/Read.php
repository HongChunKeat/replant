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
        "item_required",
        "item_required_quantity",
        "pet_required",
        "pet_required_quantity",
        "stamina",
        "pet_slots",
        "inventory_pages",
        "remark",
    ];

    public function index(Request $request, int $targetId = 0)
    {
        # [process]
        $res = SettingLevelModel::where("id", $targetId)->first();

        # [result]
        if ($res) {
            if(isset($res["item_required"])){
                [$res["item_required"], $res["item_required_quantity"]] = HelperLogic::splitJsonParams($res["item_required"]);
            }

            if(isset($res["pet_required"])){
                [$res["pet_required"], $res["pet_required_quantity"]] = HelperLogic::splitJsonParams($res["pet_required"]);
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
