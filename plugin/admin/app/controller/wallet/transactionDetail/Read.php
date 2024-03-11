<?php

namespace plugin\admin\app\controller\wallet\transactionDetail;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\WalletTransactionDetailModel;
use app\model\database\AccountUserModel;
use app\model\database\SettingWalletModel;
use app\model\logic\HelperLogic;

class Read extends Base
{
    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "user",
        "from_user",
        "to_user",
        "wallet_transaction_id",
        "wallet",
        "amount",
        "before_amount",
        "after_amount",
    ];

    public function index(Request $request, int $targetId = 0)
    {
        # [process]
        $res = WalletTransactionDetailModel::leftJoin(
            "wallet_transaction", 
            "wallet_transaction_detail.wallet_transaction_id", "=", 
            "wallet_transaction.id"
        )
        ->select("wallet_transaction_detail.*", "wallet_transaction.uid", "wallet_transaction.from_uid", "wallet_transaction.to_uid")
        ->where([
            "wallet_transaction_detail.id" => $targetId
        ])
        ->first();

        # [result]
        if ($res) {
            // address
            $uid = AccountUserModel::where("id", $res["uid"])->first();
            $res["user"] = $uid ? $uid["user_id"] : "";

            $from_uid = AccountUserModel::where("id", $res["from_uid"])->first();
            $res["from_user"] = $from_uid ? $from_uid["user_id"] : "";

            $to_uid = AccountUserModel::where("id", $res["to_uid"])->first();
            $res["to_user"] = $to_uid ? $to_uid["user_id"] : "";

            // coin
            $wallet_id = SettingWalletModel::where("id", $res["wallet_id"])->first();
            $res["wallet"] = $wallet_id ? $wallet_id["code"] : "";

            $this->response = [
                "success" => true,
                "data" => HelperLogic::formatOutput($res, $this->patternOutputs),
            ];
        }

        # [standard output]
        return $this->output();
    }
}
