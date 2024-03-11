<?php

namespace plugin\admin\app\controller\setting\blockchainNetwork;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\SettingBlockchainNetworkModel;
use app\model\logic\HelperLogic;

class Listing extends Base
{
    # [validation-rule]
    protected $rule = [
        "id" => "number|max:11",
        "code" => "max:48",
        "type" => "",
        "chain_id" => "number|max:11",
        "rpc_url" => "max:255",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "id",
        "code",
        "type",
        "chain_id",
        "rpc_url"
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "code",
        "type",
        "chain_id",
        "rpc_url"
    ];

    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->get(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->get(), $this->patternInputs);

        # [proceed]
        if (!count($this->error)) {

            # [listing query]
            $res = SettingBlockchainNetworkModel::listing(
                $cleanVars,
                ["*"],
                ["id", "desc"]
            );

            # [result]
            if ($res) {
                $this->response = [
                    "success" => true,
                    "data" => HelperLogic::formatOutput($res, $this->patternOutputs, 1),
                ];
            }
        }

        # [standard output]
        return $this->output();
    }
}
