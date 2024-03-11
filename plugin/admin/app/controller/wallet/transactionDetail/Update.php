<?php

namespace plugin\admin\app\controller\wallet\transactionDetail;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\SettingWalletModel;
use app\model\database\WalletTransactionDetailModel;
use app\model\database\WalletTransactionModel;
use app\model\logic\HelperLogic;

class Update extends Base
{
    # [validation-rule]
    protected $rule = [
        "wallet_transaction_id" => "number|max:11",
        "wallet" => "number|max:11",
        "amount" => "float|max:20|negative",
        "before_amount" => "float|max:20",
        "after_amount" => "float|max:20",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "wallet_transaction_id",
        "wallet",
        "amount",
        "before_amount",
        "after_amount",
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
                if (!empty($cleanVars["wallet"])) {
                    $cleanVars["wallet_id"] = $cleanVars["wallet"];
                }

                # [unset key]
                unset($cleanVars["wallet"]);

                $res = WalletTransactionDetailModel::where("id", $targetId)->update($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "update", "wallet_transaction_detail", $targetId);
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
        if (!empty($params["wallet_transaction_id"])) {
            // check wallet_transaction_id exists
            if (!WalletTransactionModel::where(["id" => $params["wallet_transaction_id"]])->first()) {
                $this->error[] = "wallet_transaction_id:invalid";
            }
        }

        if (!empty($params["wallet"])) {
            // check wallet exists
            if (!SettingWalletModel::where(["id" => $params["wallet"]])->first()) {
                $this->error[] = "wallet:invalid";
            }
        }
    }
}
