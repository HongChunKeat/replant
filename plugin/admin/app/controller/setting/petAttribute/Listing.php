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

class Listing extends Base
{
    # [validation-rule]
    protected $rule = [
        "id" => "number|max:11",
        "pet" => "number|max:11",
        "attribute" => "number|max:11",
        "value" => "",
        "remark" => ""
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "id",
        "pet",
        "attribute",
        "value",
        "remark"
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "pet",
        "attribute",
        "value",
        "remark"
    ];

    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->get(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->get(), $this->patternInputs);

        # [proceed]
        if (!count($this->error)) {

            // type
            if (isset($cleanVars["pet"])) {
                $pet = SettingPetModel::where("id", $cleanVars["pet"])->first();
                $cleanVars["pet_id"] = $pet["id"] ?? 0;
            }

            if (isset($cleanVars["attribute"])) {
                $attribute = SettingAttributeModel::where("id", $cleanVars["attribute"])->first();
                $cleanVars["attribute_id"] = $attribute["id"] ?? 0;
            }

            # [unset key]
            unset($cleanVars["pet"]);
            unset($cleanVars["attribute"]);

            # [listing query]
            $res = SettingPetAttributeModel::listing(
                $cleanVars,
                ["*"],
                ["id", "desc"]
            );

            # [result]
            if ($res) {
                # [add and edit column using for loop]
                foreach ($res as $row) {
                    // type
                    $pet_id = SettingPetModel::where("id", $row["pet_id"])->first();
                    $row["pet"] = $pet_id ? $pet_id["name"] : "";

                    $attribute_id = SettingAttributeModel::where("id", $row["attribute_id"])->first();
                    $row["attribute"] = $attribute_id ? $attribute_id["code"] : "";
                }

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
