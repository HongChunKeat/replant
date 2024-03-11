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

class Create extends Base
{
    # [validation-rule]
    protected $rule = [
        "pet" => "require|number",
        "attribute" => "require|number",
        "value" => "require",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "pet",
        "attribute",
        "value",
        "remark"
    ];

    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->post(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->post(), $this->patternInputs);

        # [checking]
        $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error)) {
            $res = "";

            # [process]
            if (count($cleanVars) > 0) {
                if (isset($cleanVars["pet"])) {
                    $cleanVars["pet_id"] = $cleanVars["pet"];
                }

                if (isset($cleanVars["attribute"])) {
                    $cleanVars["attribute_id"] = $cleanVars["attribute"];
                }

                # [unset key]
                unset($cleanVars["pet"]);
                unset($cleanVars["attribute"]);

                $res = SettingPetAttributeModel::create($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "create", "setting_pet_attribute", $res["id"]);
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
        if (isset($params["pet"])) {
            if (!SettingPetModel::where("id", $params["pet"])->first()) {
                $this->error[] = "pet:invalid";
            }
        }

        if (isset($params["attribute"])) {
            if (!SettingAttributeModel::where("id", $params["attribute"])->first()) {
                $this->error[] = "attribute:invalid";
            }
        }

        if (isset($params["pet"]) && isset($params["attribute"])) {
            if (SettingPetAttributeModel::where(["pet_id" => $params["pet"], "attribute_id" => $params["attribute"]])->first()) {
                $this->error[] = "entry:exists";
            }
        }
    }
}
