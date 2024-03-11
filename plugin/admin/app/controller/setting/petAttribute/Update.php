<?php

namespace plugin\admin\app\controller\setting\petAttribute;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\SettingAttributeModel;
use app\model\database\SettingPetAttributeModel;
use app\model\database\SettingPetModel;
use app\model\logic\HelperLogic;

class Update extends Base
{
    # [validation-rule]
    protected $rule = [
        "pet" => "number",
        "attribute" => "number",
        "value" => "",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "pet",
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
                if (!empty($cleanVars["pet"])) {
                    $cleanVars["pet_id"] = $cleanVars["pet"];
                }

                if (!empty($cleanVars["attribute"])) {
                    $cleanVars["attribute_id"] = $cleanVars["attribute"];
                }

                # [unset key]
                unset($cleanVars["pet"]);
                unset($cleanVars["attribute"]);

                $res = SettingPetAttributeModel::where("id", $targetId)->update($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "update", "setting_pet_attribute", $targetId);
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
        if (!empty($params["pet"])) {
            if (!SettingPetModel::where("id", $params["pet"])->first()) {
                $this->error[] = "pet:invalid";
            }
        }

        if (!empty($params["attribute"])) {
            if (!SettingAttributeModel::where("id", $params["attribute"])->first()) {
                $this->error[] = "attribute:invalid";
            }
        }

        if (!empty($params["pet"]) || !empty($params["attribute"])) {
            $check = SettingPetAttributeModel::where("id", $params["id"])->first();

            if (SettingPetAttributeModel::where([
                "pet_id" => empty($params["pet"])
                    ? $check["pet_id"]
                    : $params["pet"],
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