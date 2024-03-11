<?php

namespace plugin\dapp\app\controller\setting;

# library
use plugin\dapp\app\controller\Base;
use support\Request;
# database & logic
use app\model\logic\HelperLogic;
use app\model\logic\SettingLogic;

class Withdraw extends Base
{
    # [validation-rule]
    protected $rule = [
        "coin" => "require|max:20",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "coin",
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "coin",
        "token_address",
        "network",
        "address",
    ];

    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->get(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->get(), $this->patternInputs);

        # [proceed]
        if (!count($this->error)) {
            $res = "";

            if (isset($cleanVars["coin"])) {
                $coin = SettingLogic::get("coin", ["code" => $cleanVars["coin"]]);
                $cleanVars["coin_id"] = $coin["id"] ?? 0;

                $res = SettingLogic::getWithdraw(["coin_id" => $cleanVars["coin_id"], "is_active" => 1]);
            }

            # [result]
            if ($res) {
                $network = SettingLogic::get("blockchain_network", ["id" => $res["network"]]);
                $res["network"] = $network ? $network["code"] : "";

                $coin = SettingLogic::get("coin", ["id" => $res["coin_id"]]);
                $res["coin"] = $coin ? $coin["code"] : "";

                $this->response = [
                    "success" => true,
                    "data" => HelperLogic::formatOutput($res, $this->patternOutputs),
                ];
            }
        }

        # [standard output]
        return $this->output();
    }
}