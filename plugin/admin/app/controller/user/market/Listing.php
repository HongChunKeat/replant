<?php

namespace plugin\admin\app\controller\user\market;

# library
use support\Request;
use plugin\admin\app\controller\Base;
# database & logic
use plugin\dapp\app\model\logic\UserProfileLogic;
use app\model\database\AccountUserModel;
use app\model\database\SettingWalletModel;
use app\model\database\UserMarketModel;
use app\model\logic\HelperLogic;

class Listing extends Base
{
    # [validation-rule]
    protected $rule = [
        "id" => "number|max:11",
        "sn" => "",
        "seller_uid" => "number|max:11",
        "seller" => "",
        "buyer_uid" => "number|max:11",
        "buyer" => "",
        "amount" => "float|max:11",
        "fee" => "float|max:11",
        "amount_wallet" => "number|max:11",
        "fee_wallet" => "number|max:11",
        "ref_table" => "",
        "ref_id" => "number|max:11",
        "remark" => "",
        "removed_at_start" => "date",
        "removed_at_end" => "date",
        "sold_at_start" => "date",
        "sold_at_end" => "date",
        "created_at_start" => "date",
        "created_at_end" => "date",
        "updated_at_start" => "date",
        "updated_at_end" => "date",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "id",
        "sn",
        "seller_uid",
        "seller",
        "buyer_uid",
        "buyer",
        "amount",
        "fee",
        "amount_wallet",
        "fee_wallet",
        "ref_table",
        "ref_id",
        "remark",
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "removed_at",
        "sold_at",
        "created_at",
        "updated_at",
        "seller_uid",
        "seller",
        "buyer_uid",
        "buyer",
        "amount",
        "fee",
        "amount_wallet",
        "fee_wallet",
        "ref_table",
        "ref_id",
        "remark",
    ];

    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->get(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->get(), $this->patternInputs);

        # [proceed]
        if (!count($this->error)) {

            # [search join table columns]
            if (isset($cleanVars["seller"])) {
                // 4 in 1 search
                $seller = UserProfileLogic::multiSearch($cleanVars["seller"]);
                $cleanVars["seller_uid"] = $seller["id"] ?? 0;
            }

            if (isset($cleanVars["buyer"])) {
                // 4 in 1 search
                $buyer = UserProfileLogic::multiSearch($cleanVars["buyer"]);
                $cleanVars["buyer_uid"] = $buyer["id"] ?? 0;
            }

            if (isset($cleanVars["amount_wallet"])) {
                $cleanVars["amount_wallet_id"] = $cleanVars["amount_wallet"];
            }

            if (isset($cleanVars["fee_wallet"])) {
                $cleanVars["fee_wallet_id"] = $cleanVars["fee_wallet"];
            }

            # [unset key]
            unset($cleanVars["seller"]);
            unset($cleanVars["buyer"]);
            unset($cleanVars["amount_wallet"]);
            unset($cleanVars["fee_wallet"]);

            # [search date range]
            $cleanVars = array_merge(
                $cleanVars, 
                HelperLogic::buildDateSearch($request, ["created_at", "updated_at", "removed_at", "sold_at"])
            );

            # [listing query]
            $res = UserMarketModel::listing(
                $cleanVars,
                ["*"],
                ["id", "desc"]
            );

            # [result]
            if ($res) {
                # [add and edit column using for loop]
                foreach ($res as $row) {
                    $seller = AccountUserModel::where("id", $row["seller_uid"])->first();
                    $row["seller"] = $seller ? $seller["user_id"] : "";

                    $buyer = AccountUserModel::where("id", $row["buyer_uid"])->first();
                    $row["buyer"] = $buyer ? $buyer["user_id"] : "";

                    if (isset($row["amount_wallet_id"])) {
                        $amount_wallet = SettingWalletModel::where("id", $row["amount_wallet_id"])->first();
                        $row["amount_wallet"] = $amount_wallet ? $amount_wallet["code"] : "";
                    }

                    if (isset($row["fee_wallet_id"])) {
                        $fee_wallet = SettingWalletModel::where("id", $row["fee_wallet_id"])->first();
                        $row["fee_wallet"] = $fee_wallet ? $fee_wallet["code"] : "";
                    }
                }

                $this->response = [
                    "success" => true,
                    "data" => HelperLogic::formatOutput($res, $this->patternOutputs, 1),
                ];
            }
        }

        # [standard output]
        return $this->output();
    }
}
