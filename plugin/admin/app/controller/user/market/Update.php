<?php

namespace plugin\admin\app\controller\user\market;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\AccountUserModel;
use app\model\database\SettingWalletModel;
use app\model\database\UserInventoryModel;
use app\model\database\UserMarketModel;
use app\model\database\UserPetModel;
use app\model\logic\HelperLogic;

class Update extends Base
{
    # [validation-rule]
    protected $rule = [
        "seller_uid" => "number|max:11",
        "buyer_uid" => "number|max:11",
        "amount" => "float|max:11",
        "fee" => "float|max:11",
        "amount_wallet" => "number|max:11",
        "fee_wallet" => "number|max:11",
        "ref_table" => "",
        "ref_id" => "number|max:11",
        "removed_at" => "date",
        "sold_at" => "date",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "seller_uid",
        "buyer_uid",
        "amount",
        "fee",
        "amount_wallet",
        "fee_wallet",
        "ref_table",
        "ref_id",
        "removed_at",
        "sold_at",
        "remark",
    ];

    public function index(Request $request, int $targetId = 0)
    {
        if ($request->post("ref_table") || $request->post("ref_id")) {
            $this->rule["ref_table"] .= "|require";
            $this->rule["ref_id"] .= "|require";
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
                if (!empty($cleanVars["amount_wallet"])) {
                    $cleanVars["amount_wallet_id"] = $cleanVars["amount_wallet"];
                }

                if (!empty($cleanVars["fee_wallet"])) {
                    $cleanVars["fee_wallet_id"] = $cleanVars["fee_wallet"];
                }
                # [unset key]
                unset($cleanVars["amount_wallet"]);
                unset($cleanVars["fee_wallet"]);

                $res = UserMarketModel::where("id", $targetId)->update($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "update", "user_market", $targetId);
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
        // check seller_uid
        if (!empty($params["seller_uid"])) {
            if (!AccountUserModel::where("id", $params["seller_uid"])->first()) {
                $this->error[] = "seller_uid:invalid";
            }
        }

        // check buyer_uid
        if (!empty($params["buyer_uid"])) {
            if (!AccountUserModel::where("id", $params["buyer_uid"])->first()) {
                $this->error[] = "buyer_uid:invalid";
            }
        }

        // check amount_wallet
        if (!empty($params["amount_wallet"])) {
            if (!SettingWalletModel::where("id", $params["amount_wallet"])->first()) {
                $this->error[] = "amount_wallet:invalid";
            }
        }

        // check fee_wallet
        if (!empty($params["fee_wallet"])) {
            if (!SettingWalletModel::where("id", $params["fee_wallet"])->first()) {
                $this->error[] = "fee_wallet:invalid";
            }
        }

        if (!empty($params["ref_table"]) && !empty($params["ref_id"])) {
            if ($params["ref_table"] == "user_pet") {
                if (!UserPetModel::where("id", $params["ref_id"])->first()) {
                    $this->error[] = "ref_id:invalid";
                }
            } else if ($params["ref_table"] == "user_inventory") {
                if (!UserInventoryModel::where("id", $params["ref_id"])->first()) {
                    $this->error[] = "ref_id:invalid";
                }
            } else {
                $this->error[] = "ref_table:invalid";
            }
        }
    }
}