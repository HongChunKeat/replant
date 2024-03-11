<?php

namespace plugin\admin\app\controller\setting\mission;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\SettingItemModel;
use app\model\database\SettingPetModel;
use app\model\database\SettingMissionModel;
use app\model\database\SettingWalletModel;
use app\model\logic\HelperLogic;

class Update extends Base
{
    # [validation-rule]
    protected $rule = [
        "name" => "max:200",
        "description" => "max:500",
        "level" => "number|egt:0|max:11",
        "item_reward" => "",
        "item_reward_quantity" => "",
        "pet_reward" => "",
        "pet_reward_quantity" => "",
        "currency_reward_wallet" => "",
        "currency_reward_value" => "",
        "requirement" => "",
        "action" => "in:internal,external,bot",
        "type" => "in:daily,weekly,permanent,limited,onboarding",
        "stamina" => "number|egt:0|max:11",
        "is_show" => "in:1,0",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "name",
        "description",
        "level",
        "item_reward",
        "item_reward_quantity",
        "pet_reward",
        "pet_reward_quantity",
        "currency_reward_wallet",
        "currency_reward_value",
        "requirement",
        "action",
        "type",
        "stamina",
        "is_show",
        "remark",
    ];

    public function index(Request $request, int $targetId = 0)
    {
        if ($request->post("item_reward") || $request->post("item_reward_quantity")) {
            $this->rule["item_reward"] .= "|require";
            $this->rule["item_reward_quantity"] .= "|require";
        }

        if ($request->post("pet_reward") || $request->post("pet_reward_quantity")) {
            $this->rule["pet_reward"] .= "|require";
            $this->rule["pet_reward_quantity"] .= "|require";
        }

        if ($request->post("currency_reward_wallet") || $request->post("currency_reward_value")) {
            $this->rule["currency_reward_wallet"] .= "|require";
            $this->rule["currency_reward_value"] .= "|require";
        }

        if (($request->post("action") == "internal" || $request->post("action") == "bot")
            && $request->post("requirement")
        ) {
            $this->rule["requirement"] .= "|number";
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
                if(empty($cleanVars["requirement"])){
                    $cleanVars["requirement"] = null;
                }

                if (!empty($cleanVars["item_reward"]) && !empty($cleanVars["item_reward_quantity"])) {
                    $cleanVars["item_reward"] = json_encode(
                        HelperLogic::combineParamsToArray($cleanVars["item_reward"], $cleanVars["item_reward_quantity"])
                    );
                } else {
                    $cleanVars["item_reward"] = null;
                }

                if (!empty($cleanVars["pet_reward"]) && !empty($cleanVars["pet_reward_quantity"])) {
                    $cleanVars["pet_reward"] = json_encode(
                        HelperLogic::combineParamsToArray($cleanVars["pet_reward"], $cleanVars["pet_reward_quantity"])
                    );
                } else {
                    $cleanVars["pet_reward"] = null;
                }

                if (!empty($cleanVars["currency_reward_wallet"]) && !empty($cleanVars["currency_reward_value"])) {
                    $cleanVars["currency_reward"] = json_encode(
                        HelperLogic::combineParamsToArray($cleanVars["currency_reward_wallet"], $cleanVars["currency_reward_value"])
                    );
                } else {
                    $cleanVars["currency_reward"] = null;
                }

                # [unset key]
                unset($cleanVars["item_reward_quantity"]);
                unset($cleanVars["pet_reward_quantity"]);
                unset($cleanVars["currency_reward_wallet"]);
                unset($cleanVars["currency_reward_value"]);

                $res = SettingMissionModel::where("id", $targetId)->update($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "update", "setting_mission", $targetId);
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
        if (!empty($params["name"])) {
            if (SettingMissionModel::where("name", $params["name"])
                ->whereNot("id", $params["id"])
                ->first()
            ) {
                $this->error[] = "name:exists";
            }
        }

        if (!empty($params["item_reward"]) && !empty($params["item_reward_quantity"])) {
            $itemQuantityBreak = HelperLogic::explodeParams($params["item_reward_quantity"]);

            $checkItem = SettingItemModel::whereIn("id", $params["item_reward"])->get();
            if (count($params["item_reward"]) != count($checkItem)) {
                $this->error[] = "item_reward:invalid";
            }

            foreach($itemQuantityBreak as $value) {
                if(!is_numeric($value)) {
                    $this->error[] = "item_reward_quantity:must_be_number";
                    break;
                }
            }

            if (count($params["item_reward"]) != count($itemQuantityBreak)) {
                $this->error[] = "item_reward_and_quantity:invalid";
            }
        }

        if (!empty($params["pet_reward"]) && !empty($params["pet_reward_quantity"])) {
            $petQuantityBreak = HelperLogic::explodeParams($params["pet_reward_quantity"]);

            $checkPet = SettingPetModel::whereIn("id", $params["pet_reward"])->get();
            if (count($params["pet_reward"]) != count($checkPet)) {
                $this->error[] = "pet_reward:invalid";
            }

            foreach($petQuantityBreak as $value) {
                if(!is_numeric($value)) {
                    $this->error[] = "pet_reward_quantity:must_be_number";
                    break;
                }
            }

            if (count($params["pet_reward"]) != count($petQuantityBreak)) {
                $this->error[] = "pet_reward_and_quantity:invalid";
            }
        }

        if (!empty($params["currency_reward_wallet"]) && !empty($params["currency_reward_value"])) {
            $rewardValueBreak = HelperLogic::explodeParams($params["currency_reward_value"]);

            $checkWallet = SettingWalletModel::whereIn("id", $params["currency_reward_wallet"])->get();
            if (count($checkWallet) != count($params["currency_reward_wallet"])) {
                $this->error[] = "currency_reward_wallet:invalid";
            }

            foreach($rewardValueBreak as $value) {
                if(!is_numeric($value)) {
                    $this->error[] = "currency_reward_value:must_be_number";
                    break;
                }
            }

            if (count($params["currency_reward_wallet"]) != count($rewardValueBreak)) {
                $this->error[] = "currency_reward_wallet_and_value:invalid";
            }
        }

        if (!empty($params["requirement"])) {
            $mission = SettingMissionModel::where("id", $params["id"])->first();

            if(($mission["action"] == "internal" || $mission["action"] == "bot")
                && !is_numeric($params["requirement"])
            ) {
                $this->error[] = "requirement:must_be_number";
            }
        }
    }
}