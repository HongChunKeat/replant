<?php

namespace plugin\admin\app\controller\user\deposit;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use plugin\dapp\app\model\logic\UserProfileLogic;
use app\model\database\AccountUserModel;
use app\model\database\SettingBlockchainNetworkModel;
use app\model\database\SettingCoinModel;
use app\model\database\SettingOperatorModel;
use app\model\database\UserDepositModel;
use app\model\logic\HelperLogic;

class Paging extends Base
{
    # [validation-rule]
    protected $rule = [
        "size" => "require|number",
        "page" => "require|number",
        "id" => "number|max:11",
        "sn" => "",
        "uid" => "number|max:11",
        "user" => "max:80",
        "amount" => "float|max:20",
        "amount_min" => "float|max:20",
        "amount_max" => "float|max:20",
        "status" => "number|max:11",
        "coin" => "number|max:11",
        "txid" => "min:60|max:70|alphaNum",
        "from_address" => "length:42|alphaNum",
        "to_address" => "length:42|alphaNum",
        "network" => "max:48",
        "token_address" => "length:42|alphaNum",
        "remark" => "",
        "created_at_start" => "date",
        "created_at_end" => "date",
        "updated_at_start" => "date",
        "updated_at_end" => "date",
        "completed_at_start" => "date",
        "completed_at_end" => "date",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "id",
        "sn",
        "uid",
        "user",
        "amount",
        "status",
        "coin",
        "txid",
        "from_address",
        "to_address",
        "network",
        "token_address",
        "remark"
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "sn",
        "uid",
        "user",
        "amount",
        "status",
        "coin",
        "txid",
        "log_index",
        "from_address",
        "to_address",
        "network",
        "token_address",
        "remark",
        "created_at",
        "updated_at",
        "completed_at",
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

            if (isset($cleanVars["coin"])) {
                $coinInfo = SettingCoinModel::where("id", $cleanVars["coin"])->first();
                $cleanVars["coin_id"] = $coinInfo["id"] ?? 0;
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
            unset($cleanVars["coin"]);

            # [search date range]
            $cleanVars = array_merge(
                $cleanVars, 
                HelperLogic::buildDateSearch($request, ["created_at", "updated_at", "completed_at"])
            );

            # [paging query]
            $res = UserDepositModel::paging(
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
                    $user = AccountUserModel::where("id", $row["uid"])->first();
                    $row["user"] = $user ? $user["user_id"] : "";

                    $network = SettingBlockchainNetworkModel::where("id", $row["network"])->first();
                    $row["network"] = $network ? $network["code"] : "";

                    $coin = SettingCoinModel::where("id", $row["coin_id"])->first();
                    $row["coin"] = $coin ? $coin["code"] : "";

                    $status = SettingOperatorModel::where("id", $row["status"])->first();
                    $row["status"] = $status ? $status["code"] : "";
                }

                // meta filter
                $filter = HelperLogic::cleanTableParams($cleanVars);

                $this->response = [
                    "success" => true,
                    "data" => [
                        "data" => HelperLogic::formatOutput($res["items"], $this->patternOutputs, 1),
                        "count" => $res["count"],
                        "last_page" => ceil($res["count"] / $request->get("size")),
                        "meta" => [
                            "total_amount" => (count($filter) > 0)
                                ? round(UserDepositModel::where($filter)->sum("amount"), 4)
                                : round(UserDepositModel::sum("amount"), 4),
                        ]
                    ],
                ];
            }
        }

        # [standard output]
        return $this->output();
    }
}
