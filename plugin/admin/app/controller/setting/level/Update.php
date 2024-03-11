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

class Update extends Base
{
    # [validation-rule]
    protected $rule = [
        "level" => "number|max:11",
        "item_required" => "",
        "item_required_quantity" => "",
        "pet_required" => "",
        "pet_required_quantity" => "",
        "stamina" => "number|egt:0|max:11",
        "pet_slots" => "number|max:11",
        "inventory_pages" => "number|max:11",
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

    public function index(Request $request, int $targetId = 0)
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
        $cleanVars = HelperLogic::cleanParams($request->post(), $this->patternInputs, 1);

        # [checking]
        $this->checking(["id" => $targetId] + $cleanVars);

        # [proceed]
        if (!count($this->error)) {
            $res = "";

            # [process]
            if (count($cleanVars) > 0) {
                if (!empty($cleanVars["item_required"]) && !empty($cleanVars["item_required_quantity"])) {
                    $cleanVars["item_required"] = json_encode(
                        HelperLogic::combineParamsToArray($cleanVars["item_required"], $cleanVars["item_required_quantity"])
                    );
                } else {
                    $cleanVars["item_required"] = null;
                }

                if (!empty($cleanVars["pet_required"]) && !empty($cleanVars["pet_required_quantity"])) {
                    $cleanVars["pet_required"] = json_encode(
                        HelperLogic::combineParamsToArray($cleanVars["pet_required"], $cleanVars["pet_required_quantity"])
                    );
                } else {
                    $cleanVars["pet_required"] = null;
                }

                # [unset key]
                unset($cleanVars["item_required_quantity"]);
                unset($cleanVars["pet_required_quantity"]);

                $res = SettingLevelModel::where("id", $targetId)->update($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "update", "setting_level", $targetId);
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
        if (!empty($params["level"])) {
            if (SettingLevelModel::where("level", $params["level"])
                ->whereNot("id", $params["id"])
                ->first()
            ) {
                $this->error[] = "level:exists";
            }
        }

        if (!empty($params["item_required"]) && !empty($params["item_required_quantity"])) {
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

        if (!empty($params["pet_required"]) && !empty($params["pet_required_quantity"])) {
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