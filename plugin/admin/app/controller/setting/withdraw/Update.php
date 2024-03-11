<?php

namespace plugin\admin\app\controller\setting\withdraw;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\SettingBlockchainNetworkModel;
use app\model\database\SettingCoinModel;
use app\model\database\SettingWithdrawModel;
use app\model\logic\HelperLogic;

class Update extends Base
{
    # [validation-rule]
    protected $rule = [
        "coin" => "number|max:11",
        "token_address" => "length:42|alphaNum",
        "network" => "number|max:11",
        "address" => "length:42|alphaNum",
        "private_key" => "max:255",
        "is_active" => "in:0,1",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "coin",
        "token_address",
        "network",
        "address",
        "private_key",
        "is_active",
        "remark"
    ];

    public function index(Request $request, int $targetId = 0)
    {
        # [validation]
        $this->validation($request->post(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->post(), $this->patternInputs, 1);

        # [checking]
        $this->checking(["id" => $targetId] + $cleanVars);

        # [proceed]
        if (!count($this->error)) {
            $res = "";

            # [process]
            if (count($cleanVars) > 0) {
                if (!empty($cleanVars["coin"])) {
                    $cleanVars["coin_id"] = $cleanVars["coin"];
                }

                if (!empty($cleanVars["private_key"])) {
                    $cleanVars["private_key"] = HelperLogic::encrypt($cleanVars["private_key"]);
                }

                # [unset key]
                unset($cleanVars["coin"]);

                # [update query]
                $res = SettingWithdrawModel::where("id", $targetId)->update($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "update", "setting_withdraw", $targetId);
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
        if (!empty($params["coin"]) || !empty($params["address"]) ||  !empty($params["token_address"])) {
            $check = SettingWithdrawModel::where("id", $params["id"])->first();

            if (SettingWithdrawModel::where([
                "coin_id" => empty($params["coin"])
                    ? $check["coin_id"]
                    : $params["coin"], 
                "address" => empty($params["address"])
                    ? $check["address"]
                    : $params["address"], 
                "token_address" => empty($params["token_address"])
                    ? $check["token_address"]
                    : $params["token_address"],
            ])
            ->whereNot("id", $params["id"])
            ->first()
            ) {
                $this->error[] = "entry:exists";
            }
        }

        if (!empty($params["network"])) {
            if (!SettingBlockchainNetworkModel::where("id", $params["network"])->first()) {
                $this->error[] = "network:invalid";
            }
        }

        if (!empty($params["coin"])) {
            if (!SettingCoinModel::where("id", $params["coin"])->first()) {
                $this->error[] = "coin:invalid";
            }
        }
    }
}
