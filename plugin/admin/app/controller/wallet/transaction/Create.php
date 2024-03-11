<?php

namespace plugin\admin\app\controller\wallet\transaction;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\AccountUserModel;
use app\model\database\WalletTransactionModel;
use app\model\database\SettingOperatorModel;
use app\model\database\SettingWalletModel;
use app\model\logic\HelperLogic;

class Create extends Base
{
    # [validation-rule]
    protected $rule = [
        "transaction_type" => "require|number|max:11",
        "uid" => "require|number|max:11",
        "from_uid" => "require|number|max:11",
        "to_uid" => "require|number|max:11",
        "amount" => "require|float|max:20",
        "distribution_wallet" => "require",
        "distribution_value" => "require",
        "ref_table" => "require",
        "ref_id" => "require|number|max:11",
        "remark" => "",
        "used_at" => "number|max:14",
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
                if (isset($cleanVars["distribution_wallet"]) && isset($cleanVars["distribution_value"])) {
                    $cleanVars["distribution"] = json_encode(
                        HelperLogic::combineParamsToArray($cleanVars["distribution_wallet"], $cleanVars["distribution_value"])
                    );
                }

                # [unset key]
                unset($cleanVars["distribution_wallet"]);
                unset($cleanVars["distribution_value"]);

                $cleanVars["sn"] = HelperLogic::generateUniqueSN("wallet_transaction");
                $res = WalletTransactionModel::create($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "create", "wallet_transaction", $res["id"]);
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
        if (isset($params["uid"])) {
            if (!AccountUserModel::where(["id" => $params["uid"]])->first()) {
                $this->error[] = "uid:invalid";
            }
        }

        if (isset($params["from_uid"])) {
            if (!AccountUserModel::where(["id" => $params["from_uid"]])->first()) {
                $this->error[] = "from_uid:invalid";
            }
        }

        if (isset($params["to_uid"])) {
            if (!AccountUserModel::where(["id" => $params["to_uid"]])->first()) {
                $this->error[] = "to_uid:invalid";
            }
        }

        if (isset($params["transaction_type"])) {
            if (!SettingOperatorModel::where(["id" => $params["transaction_type"]])->whereIn("category", ["type", "reward"])->first()) {
                $this->error[] = "transaction_type:invalid";
            }
        }

        if (isset($params["distribution_wallet"]) && isset($params["distribution_value"])) {
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
