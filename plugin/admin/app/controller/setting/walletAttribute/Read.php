<?php

namespace plugin\admin\app\controller\setting\walletAttribute;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\SettingWalletAttributeModel;
use app\model\database\SettingWalletModel;
use app\model\logic\HelperLogic;

class Read extends Base
{
    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "from_wallet_id",
        "from_wallet",
        "to_wallet_id",
        "to_wallet",
        "fee_wallet_id",
        "fee_wallet",
        "to_self",
        "to_other",
        "to_self_fee",
        "to_other_fee",
        "to_self_rate",
        "to_other_rate",
        "is_show",
        "remark",
    ];

    public function index(Request $request, int $targetId = 0)
    {
        $res = SettingWalletAttributeModel::where("id", $targetId)->first();

        # [result]
        if ($res) {
            $res["to_self"] = $res["to_self"] ? "yes" : "no";
            $res["to_other"] = $res["to_other"] ? "yes" : "no";
            $res["is_admin"] = $res["is_admin"] ? "yes" : "no";
            $res["is_show"] = $res["is_show"] ? "yes" : "no";

            // coin
            $from_wallet_id = SettingWalletModel::where("id", $res["from_wallet_id"])->first();
            $res["from_wallet"] = $from_wallet_id ? $from_wallet_id["code"] : "";

            $to_wallet_id = SettingWalletModel::where("id", $res["to_wallet_id"])->first();
            $res["to_wallet"] = $to_wallet_id ? $to_wallet_id["code"] : "";

            $fee_wallet_id = SettingWalletModel::where("id", $res["fee_wallet_id"])->first();
            $res["fee_wallet"] = $fee_wallet_id ? $fee_wallet_id["code"] : "";

            $this->response = [
                "success" => true,
                "data" => HelperLogic::formatOutput($res, $this->patternOutputs),
            ];
        }

        # [standard output]
        return $this->output();
    }
}
