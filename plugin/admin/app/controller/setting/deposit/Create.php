<?php

namespace plugin\admin\app\controller\setting\deposit;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\SettingBlockchainNetworkModel;
use app\model\database\SettingCoinModel;
use app\model\database\SettingDepositModel;
use app\model\logic\HelperLogic;

class Create extends Base
{
    # [validation-rule]
    protected $rule = [
        "coin" => "require|number|max:11",
        "token_address" => "require|length:42|alphaNum",
        "network" => "require|number|max:11",
        "address" => "require|length:42|alphaNum",
        "is_active" => "require|in:0,1",
        "latest_block" => "number|max:20",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "coin",
        "token_address",
        "network",
        "address",
        "is_active",
        "latest_block",
        "remark"
    ];

    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->post(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->post(), $this->patternInputs);

        # [checking]
        $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error)) {
            $res = "";

            # [process]
            if (count($cleanVars) > 0) {
                if (isset($cleanVars["coin"])) {
                    $cleanVars["coin_id"] = $cleanVars["coin"];
                }

                # [unset key]
                unset($cleanVars["coin"]);

                # [create query]
                $res = SettingDepositModel::create($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "create", "setting_deposit", $res["id"]);
                $this->response = [
                    "success" => true,
                ];
            }
        }

        # [standard output]
        return $this->output();
    }

    private function checking(array $params = [])
    {
        # [condition]
        if (isset($params["coin"]) && isset($params["address"]) &&  isset($params["token_address"])) {
            if (SettingDepositModel::where([
                "coin_id" => $params["coin"], 
                "address" => $params["address"], 
                "token_address" => $params["token_address"]
            ])->first()
            ) {
                $this->error[] = "entry:exists";
            }
        }

        if (isset($params["network"])) {
            if (!SettingBlockchainNetworkModel::where("id", $params["network"])->first()) {
                $this->error[] = "network:invalid";
            }
        }

        if (isset($params["coin"])) {
            if (!SettingCoinModel::where("id", $params["coin"])->first()) {
                $this->error[] = "coin:invalid";
            }
        }
    }
}
