<?php

namespace plugin\admin\app\controller\setting\petAttribute;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\SettingAttributeModel;
use app\model\database\SettingPetAttributeModel;
use app\model\database\SettingPetModel;
use app\model\logic\HelperLogic;

class Read extends Base
{
    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "pet",
        "attribute",
        "value",
        "remark"
    ];


    public function index(Request $request, int $targetId = 0)
    {
        $res = SettingPetAttributeModel::where("id", $targetId)->first();

        # [result]
        if ($res) {
            // type
            $pet_id = SettingPetModel::where("id", $res["pet_id"])->first();
            $res["pet"] = $pet_id ? $pet_id["name"] : "";

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
