<?php

namespace plugin\admin\app\controller\wallet\transactionDetail;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use plugin\dapp\app\model\logic\UserProfileLogic;
use app\model\database\WalletTransactionDetailModel;
use app\model\database\AccountUserModel;
use app\model\database\SettingWalletModel;
use app\model\logic\HelperLogic;

class Paging extends Base
{
    # [validation-rule]
    protected $rule = [
        "size" => "require|number",
        "page" => "require|number",
        "id" => "number|max:11",
        "user" => "max:80",
        "from_user" => "max:80",
        "to_user" => "max:80",
        "wallet_transaction_id" => "number|max:11",
        "wallet" => "number|max:11",
        "amount" => "float|max:20|negative",
        "amount_min" => "float|max:20|negative",
        "amount_max" => "float|max:20|negative",
        "before_amount" => "float|max:20",
        "before_amount_min" => "float|max:20",
        "before_amount_max" => "float|max:20",
        "after_amount" => "float|max:20",
        "after_amount_min" => "float|max:20",
        "after_amount_max" => "float|max:20",
        "created_at_start" => "date",
        "created_at_end" => "date",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "id",
        "user",
        "from_user",
        "to_user",
        "wallet_transaction_id",
        "wallet",
        "amount",
        "before_amount",
        "after_amount",
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "created_at",
        "user",
        "from_user",
        "to_user",
        "wallet_transaction_id",
        "wallet",
        "amount",
        "before_amount",
        "after_amount",
    ];

    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->get(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->get(), $this->patternInputs);

        # [proceed]
        if (!count($this->error)) {

            # [search join table columns]
            if (isset($cleanVars["user"])) {
                // 4 in 1 search
                $user = UserProfileLogic::multiSearch($cleanVars["user"]);
                $cleanVars["uid"] = $user["id"] ?? 0;
            }

            if (isset($cleanVars["from_user"])) {
                // 4 in 1 search
                $from_user = UserProfileLogic::multiSearch($cleanVars["from_user"]);
                $cleanVars["from_uid"] = $from_user["id"] ?? 0;
            }

            if (isset($cleanVars["to_user"])) {
                // 4 in 1 search
                $to_user = UserProfileLogic::multiSearch($cleanVars["to_user"]);
                $cleanVars["to_uid"] = $to_user["id"] ?? 0;
            }

            if(isset($cleanVars["wallet"])){
                $wallet = SettingWalletModel::where("id", $cleanVars["wallet"])->first();
                $cleanVars["wallet_id"] = $wallet["id"] ?? 0;
            }

            if(isset($cleanVars["amount"])) {
                $cleanVars["wallet_transaction_detail.amount"] = $cleanVars["amount"];
            }

            # [search amount range]
            $amount_min = $request->get("amount_min");
            $amount_max = $request->get("amount_max");
            $cleanVars[] = $amount_min ? ["wallet_transaction_detail.amount", ">=", $amount_min] : "";
            $cleanVars[] = $amount_max ? ["wallet_transaction_detail.amount", "<=", $amount_max] : "";
            if($amount_min || $amount_max) {
                unset($cleanVars["wallet_transaction_detail.amount"]);
            }

            $before_amount_min = $request->get("before_amount_min");
            $before_amount_max = $request->get("before_amount_max");
            $cleanVars[] = $before_amount_min ? ["before_amount", ">=", $before_amount_min] : "";
            $cleanVars[] = $before_amount_max ? ["before_amount", "<=", $before_amount_max] : "";
            if($before_amount_min || $before_amount_max) {
                unset($cleanVars["before_amount"]);
            }

            $after_amount_min = $request->get("after_amount_min");
            $after_amount_max = $request->get("after_amount_max");
            $cleanVars[] = $after_amount_min ? ["after_amount", ">=", $after_amount_min] : "";
            $cleanVars[] = $after_amount_max ? ["after_amount", "<=", $after_amount_max] : "";
            if($after_amount_min || $after_amount_max) {
                unset($cleanVars["after_amount"]);
            }

            # [unset key]
            unset($cleanVars["user"]);
            unset($cleanVars["from_user"]);
            unset($cleanVars["to_user"]);
            unset($cleanVars["wallet"]);
            unset($cleanVars["amount"]);

            # [search date range]
            $cleanVars = array_merge(
                $cleanVars, 
                HelperLogic::buildDateSearch($request, ["created_at"])
            );

            # [paging query]
            $res = WalletTransactionDetailModel::paging(
                $cleanVars,
                $request->get("page"),
                $request->get("size"),
                [
                    "wallet_transaction_detail.*", 
                    "wallet_transaction.uid", 
                    "wallet_transaction.from_uid", 
                    "wallet_transaction.to_uid", 
                    "wallet_transaction.created_at"
                ],
                ["wallet_transaction_detail.id", "desc"],
                [["wallet_transaction", "wallet_transaction_detail.wallet_transaction_id", "=", "wallet_transaction.id"]]
            );

            # [result]
            if ($res) {
                # [add and edit column using for loop]
                foreach ($res["items"] as $row) {
                    $uid = AccountUserModel::where("id", $row["uid"])->first();
                    $row["user"] = $uid ? $uid["user_id"] : "";

                    $from_uid = AccountUserModel::where("id", $row["from_uid"])->first();
                    $row["from_user"] = $from_uid ? $from_uid["user_id"] : "";

                    $to_uid = AccountUserModel::where("id", $row["to_uid"])->first();
                    $row["to_user"] = $to_uid ? $to_uid["user_id"] : "";

                    $wallet_id = SettingWalletModel::where("id", $row["wallet_id"])->first();
                    $row["wallet"] = $wallet_id ? $wallet_id["code"] : "";
                }

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
