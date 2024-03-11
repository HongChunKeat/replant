<?php

namespace plugin\admin\app\controller\setting\deposit;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\SettingOperatorModel;
use app\model\database\SettingBlockchainNetworkModel;
use app\model\database\SettingCoinModel;
use app\model\database\SettingDepositModel;
use app\model\database\UserDepositModel;
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
        "is_active",
        "total_deposit",
        "created_at",
        "updated_at",
        "latest_block",
        "remark",
    ];

    public function index(Request $request, int $targetId = 0)
    {
        # [process]
        $res = SettingDepositModel::where("id", $targetId)->first();

        # [result]
        if ($res) {
            $success = SettingOperatorModel::where("code", "success")->first();

            $res["is_active"] = $res["is_active"] ? "active" : "inactive";

            $network = SettingBlockchainNetworkModel::where("id", $res["network"])->first();
            $res["network"] = $network ? $network["code"] : "";

            $coin_id = SettingCoinModel::where("id", $res["coin_id"])->first();
            $res["coin"] = $coin_id ? $coin_id["code"] : "";

            $res["total_deposit"] = UserDepositModel::where(["status" => $success["id"], "to_address" => $res["address"]])->sum("amount");

            $this->response = [
                "success" => true,
                "data" => HelperLogic::formatOutput($res, $this->patternOutputs),
            ];
        }

        # [standard output]
        return $this->output();
    }
}
