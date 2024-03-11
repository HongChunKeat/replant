<?php

namespace plugin\admin\app\controller\setting\nft;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\SettingBlockchainNetworkModel;
use app\model\database\SettingNftModel;
use app\model\logic\HelperLogic;

class Create extends Base
{
    # [validation-rule]
    protected $rule = [
        "token_address" => "require|length:42|alphaNum",
        "network" => "require|number|max:11",
        "address" => "require|length:42|alphaNum",
        "private_key" => "require|max:255",
        "is_active" => "require|in:0,1",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "token_address",
        "network",
        "address",
        "private_key",
        "is_active",
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
                if (isset($cleanVars["private_key"])) {
                    $cleanVars["private_key"] = HelperLogic::encrypt($cleanVars["private_key"]);
                }

                # [create query]
                $res = SettingNftModel::create($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "create", "setting_nft", $res["id"]);
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
        if (isset($params["address"]) && isset($params["token_address"])) {
            if (SettingNftModel::where([
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
    }
}