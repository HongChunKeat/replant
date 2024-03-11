<?php

namespace plugin\dapp\app\controller\walletInfo;

# library
use plugin\dapp\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\SettingOperatorModel;
use app\model\database\WalletTransactionDetailModel;
use app\model\database\UserDepositModel;
use app\model\database\UserWithdrawModel;
use app\model\logic\HelperLogic;
use app\model\logic\SettingLogic;

class Transaction extends Base
{
    # [validation-rule]
    protected $rule = [
        "size" => "require|number",
        "page" => "require|number",
        "wallet" => "max:20",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "wallet",
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "date",
        "sn",
        "type",
        "status",
        "amount",
        "wallet",
    ];

    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->get(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->get(), $this->patternInputs);

        # user id
        $cleanVars["uid"] = $request->visitor["id"];

        # [proceed]
        if (!count($this->error)) {
            if (isset($cleanVars["wallet"])) {
                $wallet = SettingLogic::get("wallet", ["code" => $cleanVars["wallet"]]);
                $cleanVars["wallet_transaction_detail.wallet_id"] = $wallet["id"] ?? 0;
            }

            # [unset key]
            unset($cleanVars["wallet"]);

            # [paging query]
            $res = WalletTransactionDetailModel::paging(
                $cleanVars,
                $request->get("page"),
                $request->get("size"),
                [
                    "wallet_transaction_detail.*",
                    "wallet_transaction.uid",
                    "wallet_transaction.transaction_type",
                    "wallet_transaction.created_at",
                    "wallet_transaction.ref_id",
                    "wallet_transaction.sn",
                ],
                ["wallet_transaction_detail.id", "desc"],
                [["wallet_transaction", "wallet_transaction_detail.wallet_transaction_id", "=", "wallet_transaction.id"]]
            );

            if ($res) {
                $success = SettingLogic::get("operator", ["category" => "status", "code" => "success"]);
                $topUp = SettingLogic::get("operator", ["category" => "type", "code" => "top_up"]);
                $withdrawList = SettingOperatorModel::where("category", "type")
                    ->whereIn("code", ["withdraw", "withdraw_fee", "withdraw_refund", "withdraw_refund_fee"])
                    ->get()
                    ->toArray();

                # [add and edit column using for loop]
                foreach ($res["items"] as $row) {
                    $row["status"] = $success["code"];
                    $row["date"] = $row["created_at"];

                    $transaction_type = SettingLogic::get("operator", ["id" => $row["transaction_type"]]);
                    $row["type"] = $transaction_type ? $transaction_type["code"] : "";

                    $wallet = SettingLogic::get("wallet", ["id" => $row["wallet_id"]]);
                    $row["wallet"] = $wallet["code"] ?? "";

                    // based on type fetch its status
                    switch ($row["transaction_type"]) {
                        case $topUp["id"]:
                            $table = UserDepositModel::where("id", $row["ref_id"])->first();
                            $setting = SettingLogic::get("operator", ["id" => $table["status"]]);
                            $row["status"] = $setting["code"];
                            break;
                        case in_array($row["transaction_type"], array_column($withdrawList, "id")):
                            $table = UserWithdrawModel::where("id", $row["ref_id"])->first();
                            $setting = SettingLogic::get("operator", ["id" => $table["status"]]);
                            $row["status"] = $setting["code"];
                            break;
                        default:
                            break;
                    }
                }

                # [result]
                $this->response = [
                    "success" => true,
                    "data" => [
                        "data" => HelperLogic::formatOutput($res["items"], $this->patternOutputs, 1),
                        "count" => $res["count"],
                        "last_page" => ceil($res["count"] / $request->get("size")),
                    ],
                ];
            }
        }

        # [standard output]
        return $this->output();
    }
}