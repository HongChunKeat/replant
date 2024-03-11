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

class Update extends Base
{
    # [validation-rule]
    protected $rule = [
        "code" => "",
        "wallet" => "number|max:11",
        "is_show" => "in:1,0",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = ["code", "wallet", "is_show", "remark"];

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
                if (!empty($cleanVars["wallet"])) {
                    $cleanVars["wallet_id"] = $cleanVars["wallet"];
                }

                # [unset key]
                unset($cleanVars["wallet"]);

                $res = SettingCoinModel::where("id", $targetId)->update($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "update", "setting_coin", $targetId);
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
        if (!empty($params["code"])) {
            if (SettingCoinModel::where("code", $params["code"])
                    ->whereNot("id", $params["id"])
                    ->first()
            ) {
                $this->error[] = "code:exists";
            }
        }

        if (!empty($params["wallet"])) {
            if (!SettingWalletModel::where("id", $params["wallet"])->first()) {
                $this->error[] = "wallet:invalid";
            }
        }
    }
}
