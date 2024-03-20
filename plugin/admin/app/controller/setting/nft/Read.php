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
        "name",
        "token_address",
        "network",
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

            $this->response = [
                "success" => true,
                "data" => HelperLogic::formatOutput($res, $this->patternOutputs),
            ];
        }

        # [standard output]
        return $this->output();
    }
}
