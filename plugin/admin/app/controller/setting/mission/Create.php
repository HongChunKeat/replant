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

class Create extends Base
{
    # [validation-rule]
    protected $rule = [
        "name" => "require|max:200",
        "description" => "max:500",
        "level" => "require|number|egt:0|max:11",
        "item_reward" => "",
        "item_reward_quantity" => "",
        "pet_reward" => "",
        "pet_reward_quantity" => "",
        "currency_reward_wallet" => "",
        "currency_reward_value" => "",
        "requirement" => "",
        "action" => "require|in:internal,external,bot",
        "type" => "require|in:daily,weekly,permanent,limited,onboarding",
        "stamina" => "require|number|egt:0|max:11",
        "is_show" => "require|in:1,0",
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

    public function index(Request $request)
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
        $cleanVars = HelperLogic::cleanParams($request->post(), $this->patternInputs);

        # [checking]
        $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error)) {
            $res = "";

            # [process]
            if (count($cleanVars) > 0) {
                if (isset($cleanVars["item_reward"]) && isset($cleanVars["item_reward_quantity"])) {
                    $cleanVars["item_reward"] = json_encode(
                        HelperLogic::combineParamsToArray($cleanVars["item_reward"], $cleanVars["item_reward_quantity"])
                    );
                }

                if (isset($cleanVars["pet_reward"]) && isset($cleanVars["pet_reward_quantity"])) {
                    $cleanVars["pet_reward"] = json_encode(
                        HelperLogic::combineParamsToArray($cleanVars["pet_reward"], $cleanVars["pet_reward_quantity"])
                    );
                }

                if (isset($cleanVars["currency_reward_wallet"]) && isset($cleanVars["currency_reward_value"])) {
                    $cleanVars["currency_reward"] = json_encode(
                        HelperLogic::combineParamsToArray($cleanVars["currency_reward_wallet"], $cleanVars["currency_reward_value"])
                    );
                }

                # [unset key]
                unset($cleanVars["item_reward_quantity"]);
                unset($cleanVars["pet_reward_quantity"]);
                unset($cleanVars["currency_reward_wallet"]);
                unset($cleanVars["currency_reward_value"]);

                $res = SettingMissionModel::create($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "create", "setting_mission", $res["id"]);
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
        if (isset($params["name"])) {
            if (SettingMissionModel::where("name", $params["name"])->first()) {
                $this->error[] = "name:exists";
            }
        }

        if (isset($params["item_reward"]) && isset($params["item_reward_quantity"])) {
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

        if (isset($params["pet_reward"]) && isset($params["pet_reward_quantity"])) {
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

        if (isset($params["currency_reward_wallet"]) && isset($params["currency_reward_value"])) {
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
    }
}