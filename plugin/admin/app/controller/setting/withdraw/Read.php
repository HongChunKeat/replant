<?php

namespace plugin\admin\app\controller\setting\withdraw;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\SettingOperatorModel;
use app\model\database\SettingBlockchainNetworkModel;
use app\model\database\SettingCoinModel;
use app\model\database\SettingWithdrawModel;
use app\model\database\UserWithdrawModel;
use app\model\logic\HelperLogic;

class Read extends Base
{
    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "coin_id",
        "coin",
        "token_address",
        "network",
        "address",
        "private_key",
        "is_active",
        "total_withdraw",
        "created_at",
        "updated_at",
        "remark",
    ];

    public function index(Request $request, int $targetId = 0)
    {
        # [process]
        $res = SettingWithdrawModel::where("id", $targetId)->first();

        # [result]
        if ($res) {
            $success = SettingOperatorModel::where("code", "success")->first();

            $res["is_active"] = $res["is_active"] ? "active" : "inactive";

            $network = SettingBlockchainNetworkModel::where("id", $res["network"])->first();
            $res["network"] = $network ? $network["code"] : "";

            $coin_id = SettingCoinModel::where("id", $res["coin_id"])->first();
            $res["coin"] = $coin_id ? $coin_id["code"] : "";

            $res["private_key"] = isset($res["private_key"]) ? "available" : "none";

            $res["total_withdraw"] = UserWithdrawModel::where(["status" => $success["id"], "from_address" => $res["address"]])->sum("amount");

            $this->response = [
                "success" => true,
                "data" => HelperLogic::formatOutput($res, $this->patternOutputs),
            ];
        }

        # [standard output]
        return $this->output();
    }
}
