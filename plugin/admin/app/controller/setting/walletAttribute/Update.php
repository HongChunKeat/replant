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

class Update extends Base
{
    # [validation-rule]
    protected $rule = [
        "from_wallet" => "number|max:11",
        "to_wallet" => "number|max:11",
        "fee_wallet" => "number|max:11",
        "to_self" => "in:0,1",
        "to_other" => "in:0,1",
        "to_self_fee" => "float|egt:0|max:20",
        "to_other_fee" => "float|egt:0|max:20",
        "to_self_rate" => "float|max:20",
        "to_other_rate" => "float|max:20",
        "is_show" => "in:0,1",
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
                if (!empty($cleanVars["from_wallet"])) {
                    $cleanVars["from_wallet_id"] = $cleanVars["from_wallet"];
                }

                if (!empty($cleanVars["to_wallet"])) {
                    $cleanVars["to_wallet_id"] = $cleanVars["to_wallet"];
                }

                if (!empty($cleanVars["fee_wallet"])) {
                    $cleanVars["fee_wallet_id"] = $cleanVars["fee_wallet"];
                }

                # [unset key]
                unset($cleanVars["from_wallet"]);
                unset($cleanVars["to_wallet"]);
                unset($cleanVars["fee_wallet"]);

                $res = SettingWalletAttributeModel::where("id", $targetId)->update($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "update", "setting_wallet_attribute", $targetId);
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
        if (!empty($params["from_wallet"]) || !empty($params["to_wallet"])) {
            $check = SettingWalletAttributeModel::where("id", $params["id"])->first();

            if (SettingWalletAttributeModel::where([
                "from_wallet_id" => empty($params["from_wallet"])
                    ? $check["from_wallet_id"]
                    : $params["from_wallet"],
                "to_wallet_id" => empty($params["to_wallet"])
                    ? $check["to_wallet_id"]
                    : $params["to_wallet"],
                ])
                ->whereNot("id", $params["id"])
                ->first()
            ) {
                $this->error[] = "entry:exists";
            }
        }

        //check wallet id
        if (!empty($params["from_wallet"])) {
            if (!SettingWalletModel::where("id", $params["from_wallet"])->first()) {
                $this->error[] = "from_wallet:invalid";
            }
        }

        if (!empty($params["to_wallet"])) {
            if (!SettingWalletModel::where("id", $params["to_wallet"])->first()) {
                $this->error[] = "to_wallet:invalid";
            }
        }

        if (!empty($params["fee_wallet"])) {
            if (!SettingWalletModel::where("id", $params["fee_wallet"])->first()) {
                $this->error[] = "fee_wallet:invalid";
            }
        }
    }
}