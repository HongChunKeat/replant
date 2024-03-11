<?php

namespace plugin\admin\app\controller\user\withdraw;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\AccountUserModel;
use app\model\database\SettingBlockchainNetworkModel;
use app\model\database\SettingCoinModel;
use app\model\database\SettingOperatorModel;
use app\model\database\SettingWalletModel;
use app\model\database\UserWithdrawModel;
use app\model\logic\HelperLogic;

class Read extends Base
{
    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "sn",
        "created_at",
        "updated_at",
        "completed_at",
        "uid",
        "user",
        "amount",
        "fee",
        "distribution",
        "status",
        "amount_wallet",
        "fee_wallet",
        "coin",
        "txid",
        "log_index",
        "from_address",
        "to_address",
        "network",
        "token_address",
        "remark",
    ];

    public function index(Request $request, int $targetId = 0)
    {
        # [process]
        $res = UserWithdrawModel::where("id", $targetId)->first();

        # [result]
        if ($res) {
            $user = AccountUserModel::where("id", $res["uid"])->first();
            $res["user"] = $user ? $user["user_id"] : "";

            $status = SettingOperatorModel::where("id", $res["status"])->first();
            $res["status"] = $status ? $status["code"] : "";

            $amountWallet = SettingWalletModel::where("id", $res["amount_wallet_id"])->first();
            $res["amount_wallet"] = $amountWallet ? $amountWallet["code"] : "";

            $feeWallet = SettingWalletModel::where("id", $res["fee_wallet_id"])->first();
            $res["fee_wallet"] = $feeWallet ? $feeWallet["code"] : "";

            $coin = SettingCoinModel::where("id", $res["to_coin_id"])->first();
            $res["coin"] = $coin ? $coin["code"] : "";

            $network = SettingBlockchainNetworkModel::where("id", $res["network"])->first();
            $res["network"] = $network ? $network["code"] : "";

            $this->response = [
                "success" => true,
                "data" => HelperLogic::formatOutput($res, $this->patternOutputs),
            ];
        }

        # [standard output]
        return $this->output();
    }
}
