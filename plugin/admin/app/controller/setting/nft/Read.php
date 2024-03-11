<?php

namespace plugin\admin\app\controller\setting\nft;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\SettingBlockchainNetworkModel;
use app\model\database\SettingNftModel;
use app\model\logic\HelperLogic;

class Read extends Base
{
    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "token_address",
        "network",
        "address",
        "private_key",
        "is_active",
        "created_at",
        "updated_at",
        "remark",
    ];

    public function index(Request $request, int $targetId = 0)
    {
        # [process]
        $res = SettingNftModel::where("id", $targetId)->first();

        # [result]
        if ($res) {
            $res["is_active"] = $res["is_active"] ? "active" : "inactive";

            $network = SettingBlockchainNetworkModel::where("id", $res["network"])->first();
            $res["network"] = $network ? $network["code"] : "";

            $res["private_key"] = isset($res["private_key"]) ? "available" : "none";

            $this->response = [
                "success" => true,
                "data" => HelperLogic::formatOutput($res, $this->patternOutputs),
            ];
        }

        # [standard output]
        return $this->output();
    }
}
