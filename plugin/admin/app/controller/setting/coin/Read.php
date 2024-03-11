<?php

namespace plugin\admin\app\controller\setting\coin;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\SettingCoinModel;
use app\model\database\SettingWalletModel;
use app\model\logic\HelperLogic;

class Read extends Base
{
    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "code",
        "wallet",
        "is_show",
        "remark"
    ];

    public function index(Request $request, int $targetId = 0)
    {
        # [process]
        $res = SettingCoinModel::where("id", $targetId)->first();

        # [result]
        if ($res) {
            $res["is_show"] = $res["is_show"] ? "yes" : "no";

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
