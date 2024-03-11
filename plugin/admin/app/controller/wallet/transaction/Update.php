<?php

namespace plugin\admin\app\controller\wallet\transaction;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\AccountUserModel;
use app\model\database\SettingOperatorModel;
use app\model\database\WalletTransactionModel;
use app\model\database\SettingWalletModel;
use app\model\logic\HelperLogic;

class Update extends Base
{
    # [validation-rule]
    protected $rule = [
        "transaction_type" => "number|max:11",
        "uid" => "number|max:11",
        "from_uid" => "number|max:11",
        "to_uid" => "number|max:11",
        "amount" => "float|max:20",
        "distribution_wallet" => "",
        "distribution_value" => "",
        "ref_table" => "",
        "ref_id" => "number|max:11",
        "remark" => "",
        "used_at" => "number|length:8",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "transaction_type",
        "uid",
        "from_uid",
        "to_uid",
        "amount",
        "distribution_wallet",
        "distribution_value",
        "ref_table",
        "ref_id",
        "remark",
        "used_at",
    ];

    public function index(Request $request, int $targetId = 0)
    {
        if ($request->post("distribution_wallet") || $request->post("distribution_value")) {
            $this->rule["distribution_wallet"] .= "|require";
            $this->rule["distribution_value"] .= "|require";
        }

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
                if (!empty($cleanVars["distribution_wallet"]) && !empty($cleanVars["distribution_value"])) {
                    $cleanVars["distribution"] = json_encode(
                        HelperLogic::combineParamsToArray($cleanVars["distribution_wallet"], $cleanVars["distribution_value"])
                    );
                }

                # [unset key]
                unset($cleanVars["distribution_wallet"]);
                unset($cleanVars["distribution_value"]);

                $res = WalletTransactionModel::where("id", $targetId)->update($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "update", "wallet_transaction", $targetId);
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
        if (!empty($params["uid"])) {
            if (!AccountUserModel::where(["id" => $params["uid"]])->first()) {
                $this->error[] = "uid:invalid";
            }
        }

        if (!empty($params["from_uid"])) {
            if (!AccountUserModel::where(["id" => $params["from_uid"]])->first()) {
                $this->error[] = "from_uid:invalid";
            }
        }

        if (!empty($params["to_uid"])) {
            if (!AccountUserModel::where(["id" => $params["to_uid"]])->first()) {
                $this->error[] = "to_uid:invalid";
            }
        }

        if (!empty($params["transaction_type"])) {
            if (!SettingOperatorModel::where(["id" => $params["transaction_type"]])->whereIn("category", ["type", "reward"])->first()) {
                $this->error[] = "transaction_type:invalid";
            }
        }

        if (!empty($params["distribution_wallet"]) && !empty($params["distribution_value"])) {
            $distributionValueBreak = HelperLogic::explodeParams($params["distribution_value"]);

            $checkWallet = SettingWalletModel::whereIn("id", $params["distribution_wallet"])->get();
            if (count($checkWallet) != count($params["distribution_wallet"])) {
                $this->error[] = "distribution_wallet:invalid";
            }

            if (count($params["distribution_wallet"]) != count($distributionValueBreak)) {
                $this->error[] = "distribution_wallet_and_value:invalid";
            }
        }
    }
}
