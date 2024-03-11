<?php

namespace plugin\admin\app\controller\enumList;

# library
use support\Request;
use plugin\admin\app\controller\Base;
# database & logic
use app\model\database\SettingOperatorModel;
use app\model\database\SettingCoinModel;
use app\model\database\SettingWalletModel;
use app\model\database\SettingBlockchainNetworkModel;
use app\model\database\SettingPetModel;
use app\model\database\SettingPaymentModel;
use app\model\database\SettingAttributeModel;
use app\model\database\SettingGachaModel;
use app\model\database\SettingLangModel;
use app\model\database\SettingItemModel;
use app\model\database\SettingRewardModel;
use app\model\database\PermissionTemplateModel;
use app\model\logic\HelperLogic;

class Listing extends Base
{
    # [validation-rule]
    protected $rule = [
        "type" => "require|max:80"
    ];

    # [inputs-pattern]
    protected $patternInputs = ["type"];

    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->get(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->get(), $this->patternInputs);

        # [proceed]
        if (!count($this->error)) {
            $res = "";

            // filter by type
            switch ($cleanVars["type"]) {
                case "yes_no":
                    $res = [
                        "1" => "yes",
                        "0" => "no",
                    ];
                    break;

                case "active_status":
                    $res = [
                        "1" => "active",
                        "0" => "inactive",
                    ];
                    break;

                case "account_status":
                    $res = [
                        "active" => "active",
                        "inactivated" => "inactivated",
                        "freezed" => "freezed",
                        "suspended" => "suspended"
                    ];
                    break;

                case "permission_action":
                    $res = [
                        "POST" => "POST",
                        "GET" => "GET",
                        "PUT" => "PUT",
                        "DELETE" => "DELETE",
                        "PATCH" => "PATCH",
                    ];
                    break;

                case "pet_quality":
                    $res = [
                        "normal" => "normal",
                        "premium" => "premium",
                    ];
                    break;

                case "pet_rank":
                    $res = [
                        "N" => "N",
                        "R" => "R",
                        "SR" => "SR",
                        "SSR" => "SSR",
                        "SSSR" => "SSSR",
                    ];
                    break;

                case "pet_star":
                    $res = [
                        "3" => "3",
                        "2" => "2",
                        "1" => "1",
                        "0" => "0",
                    ];
                    break;

                case "gacha_table":
                    $res = [
                        "setting_pet" => "setting_pet",
                        "setting_item" => "setting_item",
                        "setting_wallet" => "setting_wallet",
                    ];
                    break;

                case "market_table":
                    $res = [
                        "user_pet" => "user_pet",
                        "user_inventory" => "user_inventory",
                    ];
                    break;

                case "payment_formula":
                    $res = [
                        "equal" => "equal",
                        "min" => "min",
                        "max" => "max",
                    ];
                    break;

                case "mission_action":
                    $res = [
                        "internal" => "internal",
                        "external" => "external",
                        "bot" => "bot",
                    ];
                    break;

                case "mission_type":
                    $res = [
                        "daily" => "daily",
                        "weekly" => "weekly",
                        "permanent" => "permanent",
                        "limited" => "limited",
                        "onboarding" => "onboarding"
                    ];
                    break;

                case "user_point":
                    $res = [
                        "claim" => "claim",
                        "referral" => "referral",
                        "purchase_nft" => "purchase_nft",
                        "refund_nft" => "refund_nft"
                    ];
                    break;

                case "admin_role":
                    $res = [];
                    $settings = PermissionTemplateModel::select("id", "template_code")->get();

                    foreach ($settings as $setting) {
                        $res[$setting["id"]] = $setting["template_code"];
                    }
                    break;

                case "user_deposit_status":
                    $res = [];
                    $settings = SettingOperatorModel::select("id", "code")
                        ->where("category", "status")
                        ->whereIn("code", ["success", "failed"])
                        ->get();

                    foreach ($settings as $setting) {
                        $res[$setting["id"]] = $setting["code"];
                    }
                    break;

                case "user_withdraw_status":
                    $res = [];
                    $settings = SettingOperatorModel::select("id", "code")
                        ->where("category", "status")
                        ->whereIn("code", ["pending", "accepted", "processing", "rejected", "success", "failed"])
                        ->get();

                    foreach ($settings as $setting) {
                        $res[$setting["id"]] = $setting["code"];
                    }
                    break;

                case "user_nft_status":
                    $res = [];
                    $settings = SettingOperatorModel::select("id", "code")
                        ->where("category", "status")
                        ->whereIn("code", ["processing", "success", "failed"])
                        ->get();

                    foreach ($settings as $setting) {
                        $res[$setting["id"]] = $setting["code"];
                    }
                    break;

                case "operator_type":
                    $res = [];
                    $settings = SettingOperatorModel::select("id", "code")
                        ->where("category", "type")
                        ->get();

                    foreach ($settings as $setting) {
                        $res[$setting["id"]] = $setting["code"];
                    }
                    break;

                case "operator_reward":
                    $res = [];
                    $settings = SettingOperatorModel::select("id", "code")
                        ->where("category", "reward")
                        ->get();

                    foreach ($settings as $setting) {
                        $res[$setting["id"]] = $setting["code"];
                    }
                    break;

                case "operator_battle":
                    $res = [];
                    $settings = SettingOperatorModel::select("id", "code")
                        ->where("category", "battle")
                        ->get();

                    foreach ($settings as $setting) {
                        $res[$setting["id"]] = $setting["code"];
                    }
                    break;

                case "operator_pet":
                    $res = [];
                    $settings = SettingOperatorModel::select("id", "code")
                        ->where("category", "pet")
                        ->get();

                    foreach ($settings as $setting) {
                        $res[$setting["id"]] = $setting["code"];
                    }
                    break;

                case "operator_announcement":
                    $res = [];
                    $settings = SettingOperatorModel::select("id", "code")
                        ->where("category", "announcement")
                        ->get();

                    foreach ($settings as $setting) {
                        $res[$setting["id"]] = $setting["code"];
                    }
                    break;

                case "operator_deposit":
                    $res = [];
                    $settings = SettingOperatorModel::select("id", "code")
                        ->whereIn("code", ["admin_top_up", "admin_deduct", "top_up", "deduct"])
                        ->get();

                    foreach ($settings as $setting) {
                        $res[$setting["id"]] = $setting["code"];
                    }
                    break;

                case "transaction_type":
                    $res = [];
                    $settings = SettingOperatorModel::select("id", "code")
                        ->whereIn("category", ["type", "reward"])
                        ->get();

                    foreach ($settings as $setting) {
                        $res[$setting["id"]] = $setting["code"];
                    }
                    break;

                case "attribute":
                    $res = [];
                    $settings = SettingAttributeModel::select("id", "code")->get();

                    foreach ($settings as $setting) {
                        $res[$setting["id"]] = $setting["code"];
                    }
                    break;

                case "gacha":
                    $res = [];
                    $settings = SettingGachaModel::select("id", "name")->get();

                    foreach ($settings as $setting) {
                        $res[$setting["id"]] = $setting["name"];
                    }
                    break;

                case "network":
                    $res = [];
                    $settings = SettingBlockchainNetworkModel::select("id", "code")->get();

                    foreach ($settings as $setting) {
                        $res[$setting["id"]] = $setting["code"];
                    }
                    break;

                case "coin":
                    $res = [];
                    $settings = SettingCoinModel::select("id", "code")->get();

                    foreach ($settings as $setting) {
                        $res[$setting["id"]] = $setting["code"];
                    }
                    break;

                case "lang":
                    $res = [];
                    $settings = SettingLangModel::select("id", "code")->get();

                    foreach ($settings as $setting) {
                        $res[$setting["id"]] = $setting["code"];
                    }
                    break;

                case "wallet":
                    $res = [];
                    $settings = SettingWalletModel::select("id", "code")->get();

                    foreach ($settings as $setting) {
                        $res[$setting["id"]] = $setting["code"];
                    }
                    break;                

                case "payment":
                    $res = [];
                    $settings = SettingPaymentModel::select("id", "code")->get();

                    foreach ($settings as $setting) {
                        $res[$setting["id"]] = $setting["code"];
                    }
                    break;
                 
                case "pet":
                    $res = [];
                    $settings = SettingPetModel::select("id", "name")->get();

                    foreach ($settings as $setting) {
                        $res[$setting["id"]] = $setting["name"];
                    }
                    break;

                case "item":
                    $res = [];
                    $settings = SettingItemModel::select("id", "name")->get();

                    foreach ($settings as $setting) {
                        $res[$setting["id"]] = $setting["name"];
                    }
                    break;

                case "reward":
                    $res = [];
                    $settings = SettingRewardModel::select("id", "code")->get();

                    foreach ($settings as $setting) {
                        $res[$setting["id"]] = $setting["code"];
                    }
                    break;

                default:
                    // handle default case if needed
                    break;
            }

            # [result]
            if ($res) {
                $this->response = [
                    "success" => true,
                    "data" => $res,
                ];
            }
        }

        # [standard output]
        return $this->output();
    }
}
