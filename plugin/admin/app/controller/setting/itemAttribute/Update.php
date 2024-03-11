<?php

namespace plugin\admin\app\controller\setting\itemAttribute;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\SettingAttributeModel;
use app\model\database\SettingItemAttributeModel;
use app\model\database\SettingItemModel;
use app\model\logic\HelperLogic;

class Update extends Base
{
    # [validation-rule]
    protected $rule = [
        "item" => "number",
        "attribute" => "number",
        "value" => "",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "item",
        "attribute",
        "value",
        "remark"
    ];

    public function index(Request $request, int $targetId = 0)
    {
        # [validation]
        $this->validation($request->post(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->post(), $this->patternInputs, 1);

        # [checking]
        $this->checking(["id" => $targetId] + $cleanVars);

        # [proceed]
        if (!count($this->error)) {
            $res = "";

            # [process]
            if (count($cleanVars) > 0) {
                if (!empty($cleanVars["item"])) {
                    $cleanVars["item_id"] = $cleanVars["item"];
                }

                if (!empty($cleanVars["attribute"])) {
                    $cleanVars["attribute_id"] = $cleanVars["attribute"];
                }

                # [unset key]
                unset($cleanVars["item"]);
                unset($cleanVars["attribute"]);

                $res = SettingItemAttributeModel::where("id", $targetId)->update($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "update", "setting_item_attribute", $targetId);
                $this->response = [
                    "success" => true,
                ];
            }
        }

        # [standard output]
        return $this->output();
    }

    private function checking(array $params = [])
    {
        # [condition]
        if (!empty($params["item"])) {
            if (!SettingItemModel::where("id", $params["item"])->first()) {
                $this->error[] = "item:invalid";
            }
        }

        if (!empty($params["attribute"])) {
            if (!SettingAttributeModel::where("id", $params["attribute"])->first()) {
                $this->error[] = "attribute:invalid";
            }
        }

        if (!empty($params["item"]) || !empty($params["attribute"])) {
            $check = SettingItemAttributeModel::where("id", $params["id"])->first();

            if (SettingItemAttributeModel::where([
                "item_id" => empty($params["item"])
                    ? $check["item_id"]
                    : $params["item"],
                "attribute_id" => empty($params["attribute"])
                    ? $check["attribute_id"]
                    : $params["attribute"],
                ])
                ->whereNot("id", $params["id"])
                ->first()
            ) {
                $this->error[] = "entry:exists";
            }
        }
    }
}
