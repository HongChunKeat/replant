<?php

namespace plugin\admin\app\controller\wallet\transactionDetail;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\WalletTransactionDetailModel;
use app\model\database\SettingWalletModel;
use app\model\database\WalletTransactionModel;
use app\model\logic\HelperLogic;

class Create extends Base
{
    # [validation-rule]
    protected $rule = [
        "wallet_transaction_id" => "require|number|max:11",
        "wallet" => "require|number|max:11",
        "amount" => "require|float|max:20|negative",
        "before_amount" => "require|float|max:20",
        "after_amount" => "require|float|max:20",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "wallet_transaction_id",
        "wallet",
        "amount",
        "before_amount",
        "after_amount",
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
                if (isset($cleanVars["wallet"])) {
                    $cleanVars["wallet_id"] = $cleanVars["wallet"];
                }

                # [unset key]
                unset($cleanVars["wallet"]);

                $res = WalletTransactionDetailModel::create($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "create", "wallet_transaction_detail", $res["id"]);
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
        if (isset($params["wallet_transaction_id"])) {
            // check wallet_transaction_id exists
            if (!WalletTransactionModel::where(["id" => $params["wallet_transaction_id"]])->first()) {
                $this->error[] = "wallet_transaction_id:invalid";
            }
        }

        if (isset($params["wallet"])) {
            // check wallet exists
            if (!SettingWalletModel::where(["id" => $params["wallet"]])->first()) {
                $this->error[] = "wallet:invalid";
            }
        }
    }
}
