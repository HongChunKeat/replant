<?php

namespace plugin\admin\app\controller\user\deposit;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\AccountUserModel;
use app\model\database\SettingBlockchainNetworkModel;
use app\model\database\SettingCoinModel;
use app\model\database\SettingOperatorModel;
use app\model\database\UserDepositModel;
use app\model\logic\HelperLogic;

class Read extends Base
{
    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "sn",
        "uid",
        "user",
        "amount",
        "status",
        "coin",
        "txid",
        "log_index",
        "from_address",
        "to_address",
        "network",
        "token_address",
        "remark",
        "created_at",
        "updated_at",
        "completed_at",
    ];

    public function index(Request $request, int $targetId = 0)
    {
        # [process]
        $res = UserDepositModel::where("id", $targetId)->first();

        # [result]
        if ($res) {
            $user = AccountUserModel::where("id", $res["uid"])->first();
            $res["user"] = $user ? $user["user_id"] : "";

            $network = SettingBlockchainNetworkModel::where("id", $res["network"])->first();
            $res["network"] = $network ? $network["code"] : "";

            $coin = SettingCoinModel::where("id", $res["coin_id"])->first();
            $res["coin"] = $coin ? $coin["code"] : "";

            $status = SettingOperatorModel::where("id", $res["status"])->first();
            $res["status"] = $status ? $status["code"] : "";

            $this->response = [
                "success" => true,
                "data" => HelperLogic::formatOutput($res, $this->patternOutputs),
            ];
        }

        # [standard output]
        return $this->output();
    }
}
