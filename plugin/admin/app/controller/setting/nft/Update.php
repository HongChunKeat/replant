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

class Update extends Base
{
    # [validation-rule]
    protected $rule = [
        "name" => "",
        "token_address" => "length:42|alphaNum",
        "network" => "number|max:11",
        "address" => "length:42|alphaNum",
        "private_key" => "max:255",
        "is_active" => "in:0,1",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "name",
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
                if (!empty($cleanVars["private_key"])) {
                    $cleanVars["private_key"] = HelperLogic::encrypt($cleanVars["private_key"]);
                }

                # [update query]
                $res = SettingNftModel::where("id", $targetId)->update($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "update", "setting_nft", $targetId);
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
        if (!empty($params["name"])) {
            if (SettingNftModel::where("name", $params["name"])
                ->whereNot("id", $params["id"])
                ->first()
            ) {
                $this->error[] = "name:exists";
            }
        }

        if (!empty($params["address"]) ||  !empty($params["token_address"])) {
            $check = SettingNftModel::where("id", $params["id"])->first();

            if (SettingNftModel::where([
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
    }
}
