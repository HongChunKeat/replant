<?php

namespace plugin\admin\app\controller\setting\itemAttribute;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\SettingAttributeModel;
use app\model\database\SettingItemAttributeModel;
use app\model\database\SettingItemModel;
use app\model\logic\HelperLogic;

class Read extends Base
{
    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "item",
        "attribute",
        "value",
        "remark"
    ];

    public function index(Request $request, int $targetId = 0)
    {
        $res = SettingItemAttributeModel::where("id", $targetId)->first();

        # [result]
        if ($res) {
            // type
            $item_id = SettingItemModel::where("id", $res["item_id"])->first();
            $res["item"] = $item_id ? $item_id["code"] : "";

            $attribute_id = SettingAttributeModel::where("id", $res["attribute_id"])->first();
            $res["attribute"] = $attribute_id ? $attribute_id["code"] : "";

            $this->response = [
                "success" => true,
                "data" => HelperLogic::formatOutput($res, $this->patternOutputs),
            ];
        }

        # [standard output]
        return $this->output();
    }
}
