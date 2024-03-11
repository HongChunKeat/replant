<?php

namespace plugin\admin\app\controller\user\market;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\AccountUserModel;
use app\model\database\SettingWalletModel;
use app\model\database\UserMarketModel;
use app\model\logic\HelperLogic;

class Read extends Base
{
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

    public function index(Request $request, int $targetId = 0)
    {
        # [process]
        $res = UserMarketModel::where("id", $targetId)->first();

        # [result]
        if ($res) {
            $seller = AccountUserModel::where("id", $res["seller_uid"])->first();
            $res["seller"] = $seller ? $seller["user_id"] : "";

            $buyer = AccountUserModel::where("id", $res["buyer_uid"])->first();
            $res["buyer"] = $buyer ? $buyer["user_id"] : "";

            if (isset($res["amount_wallet_id"])) {
                $amount_wallet = SettingWalletModel::where("id", $res["amount_wallet_id"])->first();
                $res["amount_wallet"] = $amount_wallet ? $amount_wallet["code"] : "";
            }

            if (isset($res["fee_wallet_id"])) {
                $fee_wallet = SettingWalletModel::where("id", $res["fee_wallet_id"])->first();
                $res["fee_wallet"] = $fee_wallet ? $fee_wallet["code"] : "";
            }

            $this->response = [
                "success" => true,
                "data" => HelperLogic::formatOutput($res, $this->patternOutputs),
            ];
        }

        # [standard output]
        return $this->output();
    }
}