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

class Listing extends Base
{
    # [validation-rule]
    protected $rule = [
        "id" => "number|max:11",
        "item" => "number|max:11",
        "attribute" => "number|max:11",
        "value" => "",
        "remark" => ""
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "id",
        "item",
        "attribute",
        "value",
        "remark"
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "item",
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
            if (isset($cleanVars["item"])) {
                $item = SettingItemModel::where("id", $cleanVars["item"])->first();
                $cleanVars["item_id"] = $item["id"] ?? 0;
            }

            if (isset($cleanVars["attribute"])) {
                $attribute = SettingAttributeModel::where("id", $cleanVars["attribute"])->first();
                $cleanVars["attribute_id"] = $attribute["id"] ?? 0;
            }

            # [unset key]
            unset($cleanVars["item"]);
            unset($cleanVars["attribute"]);

            # [listing query]
            $res = SettingItemAttributeModel::listing(
                $cleanVars,
                ["*"],
                ["id", "desc"]
            );

            # [result]
            if ($res) {
                # [add and edit column using for loop]
                foreach ($res as $row) {
                    // type
                    $item_id = SettingItemModel::where("id", $row["item_id"])->first();
                    $row["item"] = $item_id ? $item_id["code"] : "";

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
