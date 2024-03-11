<?php

namespace plugin\admin\app\controller\user\gacha;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\UserGachaModel;
use app\model\database\AccountUserModel;
use app\model\database\SettingGachaModel;
use app\model\database\SettingPetModel;
use app\model\database\SettingItemModel;
use app\model\database\SettingWalletModel;
use app\model\logic\HelperLogic;

class Update extends Base
{
    # [validation-rule]
    protected $rule = [
        "uid" => "number|max:11",
        "gacha" => "number|max:11",
        "pet" => "number|max:11",
        "item" => "number|max:11",
        "wallet" => "number|max:11",
        "token_reward" => "float|max:11",
        "ref_table" => "",
        "ref_id" => "number|max:11",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "uid",
        "gacha",
        "pet",
        "item",
        "wallet",
        "token_reward",
        "ref_table",
        "ref_id",
        "remark",
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
                if (!empty($cleanVars["gacha"])) {
                    $cleanVars["gacha_id"] = $cleanVars["gacha"];
                }
    
                if (!empty($cleanVars["pet"])) {
                    $cleanVars["pet_id"] = $cleanVars["pet"];
                }
    
                if (!empty($cleanVars["item"])) {
                    $cleanVars["item_id"] = $cleanVars["item"];
                }
                
                if (!empty($cleanVars["wallet"])) {
                    $cleanVars["wallet_id"] = $cleanVars["wallet"];
                }

                # [unset key]
                unset($cleanVars["gacha"]);
                unset($cleanVars["pet"]);
                unset($cleanVars["item"]);
                unset($cleanVars["wallet"]);

                $res = UserGachaModel::where("id", $targetId)->update($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "update", "user_gacha", $targetId);
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
        // check uid
        if (!empty($params["uid"])) {
            if (!AccountUserModel::where("id", $params["uid"])->first()) {
                $this->error[] = "uid:invalid";
            }
        }

        // check gacha
        if (!empty($params["gacha"])) {
            if (!SettingGachaModel::where("id", $params["gacha"])->first()) {
                $this->error[] = "gacha:invalid";
            }
        }

        // check pet
        if (!empty($params["pet"])) {
            if (!SettingPetModel::where("id", $params["pet"])->first()) {
                $this->error[] = "pet:invalid";
            }
        }

        // check item
        if (!empty($params["item"])) {
            if (!SettingItemModel::where("id", $params["item"])->first()) {
                $this->error[] = "item:invalid";
            }
        }

        // check wallet
        if (!empty($params["wallet"])) {
            if (!SettingWalletModel::where("id", $params["wallet"])->first()) {
                $this->error[] = "wallet:invalid";
            }
        }
    }
}