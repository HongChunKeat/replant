<?php

namespace plugin\admin\app\controller\setting\level;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\SettingItemModel;
use app\model\database\SettingPetModel;
use app\model\database\SettingLevelModel;
use app\model\logic\HelperLogic;

class Create extends Base
{
    # [validation-rule]
    protected $rule = [
        "level" => "require|number|max:11",
        "item_required" => "",
        "item_required_quantity" => "",
        "pet_required" => "",
        "pet_required_quantity" => "",
        "stamina" => "require|number|egt:0|max:11",
        "pet_slots" => "require|number|max:11",
        "inventory_pages" => "require|number|max:11",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
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

    public function index(Request $request)
    {
        if ($request->post("item_required") || $request->post("item_required_quantity")) {
            $this->rule["item_required"] .= "|require";
            $this->rule["item_required_quantity"] .= "|require";
        }

        if ($request->post("pet_required") || $request->post("pet_required_quantity")) {
            $this->rule["pet_required"] .= "|require";
            $this->rule["pet_required_quantity"] .= "|require";
        }

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
                if (isset($cleanVars["item_required"]) && isset($cleanVars["item_required_quantity"])) {
                    $cleanVars["item_required"] = json_encode(
                        HelperLogic::combineParamsToArray($cleanVars["item_required"], $cleanVars["item_required_quantity"])
                    );
                }

                if (isset($cleanVars["pet_required"]) && isset($cleanVars["pet_required_quantity"])) {
                    $cleanVars["pet_required"] = json_encode(
                        HelperLogic::combineParamsToArray($cleanVars["pet_required"], $cleanVars["pet_required_quantity"])
                    );
                }

                # [unset key]
                unset($cleanVars["item_required_quantity"]);
                unset($cleanVars["pet_required_quantity"]);

                $res = SettingLevelModel::create($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "create", "setting_level", $res["id"]);
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
        if (isset($params["level"])) {
            if (SettingLevelModel::where("level", $params["level"])->first()) {
                $this->error[] = "level:exists";
            }
        }

        if (isset($params["item_required"]) && isset($params["item_required_quantity"])) {
            $itemQuantityBreak = HelperLogic::explodeParams($params["item_required_quantity"]);

            $checkItem = SettingItemModel::whereIn("id", $params["item_required"])->get();
            if (count($params["item_required"]) != count($checkItem)) {
                $this->error[] = "item_required:invalid";
            }

            foreach($itemQuantityBreak as $value) {
                if(!is_numeric($value)) {
                    $this->error[] = "item_required_quantity:must_be_number";
                    break;
                }
            }

            if (count($params["item_required"]) != count($itemQuantityBreak)) {
                $this->error[] = "item_required_and_quantity:invalid";
            }
        }

        if (isset($params["pet_required"]) && isset($params["pet_required_quantity"])) {
            $petQuantityBreak = HelperLogic::explodeParams($params["pet_required_quantity"]);

            $checkPet = SettingPetModel::whereIn("id", $params["pet_required"])->get();
            if (count($params["pet_required"]) != count($checkPet)) {
                $this->error[] = "pet_required:invalid";
            }

            foreach($petQuantityBreak as $value) {
                if(!is_numeric($value)) {
                    $this->error[] = "pet_required_quantity:must_be_number";
                    break;
                }
            }

            if (count($params["pet_required"]) != count($petQuantityBreak)) {
                $this->error[] = "pet_required_and_quantity:invalid";
            }
        }
    }
}