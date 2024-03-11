<?php

namespace plugin\admin\app\controller\setting\wallet;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\SettingWalletModel;
use app\model\logic\HelperLogic;

class Create extends Base
{
    # [validation-rule]
    protected $rule = [
        "image" => "require|max:100",
        "code" => "require",
        "is_deposit" => "require|in:1,0",
        "is_withdraw" => "require|in:1,0",
        "is_transfer" => "require|in:1,0",
        "is_swap" => "require|in:1,0",
        "is_show" => "require|in:1,0",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "image",
        "code",
        "is_deposit",
        "is_withdraw",
        "is_transfer",
        "is_swap",
        "is_show",
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
                $res = SettingWalletModel::create($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "create", "setting_wallet", $res["id"]);
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
            if (SettingWalletModel::where("code", $params["code"])->first()) {
                $this->error[] = "code:exists";
            }
        }
    }
}
