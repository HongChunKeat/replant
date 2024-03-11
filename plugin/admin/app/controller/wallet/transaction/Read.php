<?php

namespace plugin\admin\app\controller\wallet\transaction;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\WalletTransactionModel;
use app\model\database\AccountUserModel;
use app\model\database\SettingOperatorModel;
use app\model\logic\HelperLogic;

class Read extends Base
{
    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "sn",
        "created_at",
        "updated_at",
        "used_at",
        "uid",
        "user",
        "from_uid",
        "from_user",
        "to_uid",
        "to_user",
        "transaction_type",
        "amount",
        "distribution_wallet",
        "distribution_value",
        "ref_table",
        "ref_id",
        "remark",
    ];

    public function index(Request $request, int $targetId = 0)
    {
        # [process]
        $res = WalletTransactionModel::where("id", $targetId)->first();

        # [result]
        if ($res) {
            // address
            $uid = AccountUserModel::where("id", $res["uid"])->first();
            $res["user"] = $uid ? $uid["user_id"] : "";

            $from_uid = AccountUserModel::where("id", $res["from_uid"])->first();
            $res["from_user"] = $from_uid ? $from_uid["user_id"] : "";

            $to_uid = AccountUserModel::where("id", $res["to_uid"])->first();
            $res["to_user"] = $to_uid ? $to_uid["user_id"] : "";

            $transaction_type = SettingOperatorModel::where("id", $res["transaction_type"])->first();
            $res["transaction_type"] = $transaction_type ? $transaction_type["code"] : "";

            [$res["distribution_wallet"], $res["distribution_value"]] = HelperLogic::splitJsonParams($res["distribution"]);

            $this->response = [
                "success" => true,
                "data" => HelperLogic::formatOutput($res, $this->patternOutputs),
            ];
        }

        # [standard output]
        return $this->output();
    }
}
