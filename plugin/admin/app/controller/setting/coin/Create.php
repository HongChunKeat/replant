<?php

namespace plugin\admin\app\controller\setting\coin;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\SettingCoinModel;
use app\model\database\SettingWalletModel;
use app\model\logic\HelperLogic;

class Create extends Base
{
    # [validation-rule]
    protected $rule = [
        "code" => "require",
        "wallet" => "require|number|max:11",
        "is_show" => "require|in:1,0",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = ["code", "wallet", "is_show", "remark"];

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
                if (isset($cleanVars["wallet"])) {
                    $cleanVars["wallet_id"] = $cleanVars["wallet"];
                }

                # [unset key]
                unset($cleanVars["wallet"]);

                $res = SettingCoinModel::create($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "create", "setting_coin", $res["id"]);
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
        if (isset($params["code"])) {
            if (SettingCoinModel::where("code", $params["code"])->first()) {
                $this->error[] = "code:exists";
            }
        }

        if (isset($params["wallet"])) {
            if (!SettingWalletModel::where("id", $params["wallet"])->first()) {
                $this->error[] = "wallet:invalid";
            }
        }
    }
}
