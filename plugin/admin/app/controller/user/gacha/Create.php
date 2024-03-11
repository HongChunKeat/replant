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

class Create extends Base
{
    # [validation-rule]
    protected $rule = [
        "uid" => "require|number|max:11",
        "gacha" => "require|number|max:11",
        "pet" => "number|max:11",
        "item" => "number|max:11",
        "wallet" => "number|max:11",
        "token_reward" => "float|max:11",
        "ref_table" => "require",
        "ref_id" => "require|number|max:11",
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
                if (isset($cleanVars["gacha"])) {
                    $cleanVars["gacha_id"] = $cleanVars["gacha"];
                }
    
                if (isset($cleanVars["pet"])) {
                    $cleanVars["pet_id"] = $cleanVars["pet"];
                }
    
                if (isset($cleanVars["item"])) {
                    $cleanVars["item_id"] = $cleanVars["item"];
                }

                if (isset($cleanVars["wallet"])) {
                    $cleanVars["wallet_id"] = $cleanVars["wallet"];
                }

                # [unset key]
                unset($cleanVars["gacha"]);
                unset($cleanVars["pet"]);
                unset($cleanVars["item"]);
                unset($cleanVars["wallet"]);

                $res = UserGachaModel::create($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "create", "user_gacha", $res["id"]);
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
        if (isset($params["uid"])) {
            if (!AccountUserModel::where("id", $params["uid"])->first()) {
                $this->error[] = "uid:invalid";
            }
        }

        // check gacha
        if (isset($params["gacha"])) {
            if (!SettingGachaModel::where("id", $params["gacha"])->first()) {
                $this->error[] = "gacha:invalid";
            }
        }

        // check pet
        if (isset($params["pet"])) {
            if (!SettingPetModel::where("id", $params["pet"])->first()) {
                $this->error[] = "pet:invalid";
            }
        }

        // check item
        if (isset($params["item"])) {
            if (!SettingItemModel::where("id", $params["item"])->first()) {
                $this->error[] = "item:invalid";
            }
        }

        // check wallet
        if (isset($params["wallet"])) {
            if (!SettingWalletModel::where("id", $params["wallet"])->first()) {
                $this->error[] = "wallet:invalid";
            }
        }
    }
}