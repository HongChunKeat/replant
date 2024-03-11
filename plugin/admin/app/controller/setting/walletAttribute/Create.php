<?php

namespace plugin\admin\app\controller\setting\walletAttribute;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\SettingWalletAttributeModel;
use app\model\database\SettingWalletModel;
use app\model\logic\HelperLogic;

class Create extends Base
{
    # [validation-rule]
    protected $rule = [
        "from_wallet" => "require|number|max:11",
        "to_wallet" => "require|number|max:11",
        "fee_wallet" => "number|max:11",
        "to_self" => "require|in:0,1",
        "to_other" => "require|in:0,1",
        "to_self_fee" => "require|float|egt:0|max:20",
        "to_other_fee" => "require|float|egt:0|max:20",
        "to_self_rate" => "require|float|max:20",
        "to_other_rate" => "require|float|max:20",
        "is_show" => "require|in:0,1",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "from_wallet",
        "to_wallet",
        "fee_wallet",
        "to_self",
        "to_other",
        "to_self_fee",
        "to_other_fee",
        "to_self_rate",
        "to_other_rate",
        "is_show",
        "remark",
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
                if (isset($cleanVars["from_wallet"])) {
                    $cleanVars["from_wallet_id"] = $cleanVars["from_wallet"];
                }

                if (isset($cleanVars["to_wallet"])) {
                    $cleanVars["to_wallet_id"] = $cleanVars["to_wallet"];
                }

                if (isset($cleanVars["fee_wallet"])) {
                    $cleanVars["fee_wallet_id"] = $cleanVars["fee_wallet"];
                }

                # [unset key]
                unset($cleanVars["from_wallet"]);
                unset($cleanVars["to_wallet"]);
                unset($cleanVars["fee_wallet"]);

                $res = SettingWalletAttributeModel::create($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "create", "setting_wallet_attribute", $res["id"]);
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
        //check pair
        if (isset($params["from_wallet"]) && isset($params["to_wallet"])) {
            if (SettingWalletAttributeModel::where(["from_wallet_id" => $params["from_wallet"], "to_wallet_id" => $params["to_wallet"]])->first()) {
                $this->error[] = "pair:exists";
            }
        }

        //check wallet id
        if (isset($params["from_wallet"])) {
            if (!SettingWalletModel::where("id", $params["from_wallet"])->first()) {
                $this->error[] = "from_wallet:invalid";
            }
        }

        if (isset($params["to_wallet"])) {
            if (!SettingWalletModel::where("id", $params["to_wallet"])->first()) {
                $this->error[] = "to_wallet:invalid";
            }
        }

        if (isset($params["fee_wallet"])) {
            if (!SettingWalletModel::where("id", $params["fee_wallet"])->first()) {
                $this->error[] = "fee_wallet:invalid";
            }
        }
    }
}
