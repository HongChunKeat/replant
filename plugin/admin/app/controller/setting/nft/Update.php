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
        "is_active" => "in:0,1",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "name",
        "token_address",
        "network",
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

        if (!empty($params["token_address"])) {
            if (SettingNftModel::where("token_address", $params["token_address"])
                ->whereNot("id", $params["id"])
                ->first()
            ) {
                $this->error[] = "token_address:exists";
            }
        }

        if (!empty($params["network"])) {
            if (!SettingBlockchainNetworkModel::where("id", $params["network"])->first()) {
                $this->error[] = "network:invalid";
            }
        }
    }
}
