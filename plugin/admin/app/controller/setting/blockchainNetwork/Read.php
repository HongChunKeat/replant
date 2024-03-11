<?php

namespace plugin\admin\app\controller\setting\blockchainNetwork;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\SettingBlockchainNetworkModel;
use app\model\logic\HelperLogic;

class Read extends Base
{
    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "code",
        "type",
        "chain_id",
        "rpc_url"
    ];

    public function index(Request $request, int $targetId = 0)
    {
        # [process]
        $res = SettingBlockchainNetworkModel::where("id", $targetId)->first();

        # [result]
        if ($res) {
            $this->response = [
                "success" => true,
                "data" => HelperLogic::formatOutput($res, $this->patternOutputs),
            ];
        }

        # [standard output]
        return $this->output();
    }
}
