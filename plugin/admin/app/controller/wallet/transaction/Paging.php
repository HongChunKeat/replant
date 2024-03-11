<?php

namespace plugin\admin\app\controller\wallet\transaction;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use plugin\dapp\app\model\logic\UserProfileLogic;
use app\model\database\WalletTransactionModel;
use app\model\database\AccountUserModel;
use app\model\database\SettingOperatorModel;
use app\model\logic\HelperLogic;

class Paging extends Base
{
    # [validation-rule]
    protected $rule = [
        "size" => "require|number",
        "page" => "require|number",
        "id" => "number|max:11",
        "sn" => "",
        "used_at" => "number|max:14",
        "transaction_type" => "number|max:11",
        "user" => "max:80",
        "from_user" => "max:80",
        "to_user" => "max:80",
        "amount" => "float|max:20",
        "amount_min" => "float|max:20",
        "amount_max" => "float|max:20",
        "distribution" => "",
        "ref_table" => "",
        "ref_id" => "number|max:11",
        "remark" => "",
        "created_at_start" => "date",
        "created_at_end" => "date",
        "updated_at_start" => "date",
        "updated_at_end" => "date",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "id",
        "sn",
        "used_at",
        "transaction_type",
        "user",
        "from_user",
        "to_user",
        "amount",
        "distribution",
        "ref_table",
        "ref_id",
        "remark"
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "sn",
        "created_at",
        "updated_at",
        "used_at",
        "uid",
        "user",
        "from_uid",
        "from_user",
        "to_uid",
        "to_user",
        "transaction_type",
        "amount",
        "distribution",
        "ref_table",
        "ref_id",
        "remark",
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

            # [search amount range]
            $amount_min = $request->get("amount_min");
            $amount_max = $request->get("amount_max");
            $cleanVars[] = $amount_min ? ["amount", ">=", $amount_min] : "";
            $cleanVars[] = $amount_max ? ["amount", "<=", $amount_max] : "";
            if($amount_min || $amount_max) {
                unset($cleanVars["amount"]);
            }

            # [unset key]
            unset($cleanVars["user"]);
            unset($cleanVars["from_user"]);
            unset($cleanVars["to_user"]);

            # [search date range]
            $cleanVars = array_merge(
                $cleanVars, 
                HelperLogic::buildDateSearch($request, ["created_at", "updated_at"])
            );

            # [paging query]
            $res = WalletTransactionModel::paging(
                $cleanVars,
                $request->get("page"),
                $request->get("size"),
                ["*"],
                ["id", "desc"]
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

                    $transaction_type = SettingOperatorModel::where("id", $row["transaction_type"])->first();
                    $row["transaction_type"] = $transaction_type ? $transaction_type["code"] : "";
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
