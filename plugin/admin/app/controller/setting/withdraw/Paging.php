<?php

namespace plugin\admin\app\controller\setting\withdraw;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\SettingOperatorModel;
use app\model\database\SettingBlockchainNetworkModel;
use app\model\database\SettingCoinModel;
use app\model\database\SettingWithdrawModel;
use app\model\database\UserWithdrawModel;
use app\model\logic\HelperLogic;

class Paging extends Base
{
    # [validation-rule]
    protected $rule = [
        "size" => "require|number",
        "page" => "require|number",
        "id" => "number|max:11",
        "coin" => "number|max:11",
        "token_address" => "length:42|alphaNum",
        "network" => "number|max:11",
        "address" => "length:42|alphaNum",
        "is_active" => "in:0,1",
        "remark" => "",
        "created_at_start" => "date",
        "created_at_end" => "date",
        "updated_at_start" => "date",
        "updated_at_end" => "date",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "id",
        "coin",
        "token_address",
        "network",
        "address",
        "is_active",
        "remark"
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "coin_id",
        "coin",
        "token_address",
        "network",
        "address",
        "private_key",
        "is_active",
        "total_withdraw",
        "created_at",
        "updated_at",
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
            if (isset($cleanVars["network"])) {
                $network = SettingBlockchainNetworkModel::where("id", $cleanVars["network"])->first();
                $cleanVars["network"] = $network["id"] ?? 0;
            }

            if (isset($cleanVars["coin"])) {
                $coin = SettingCoinModel::where("id", $cleanVars["coin"])->first();
                $cleanVars["coin_id"] = $coin["id"] ?? 0;
            }

            # [unset key]
            unset($cleanVars["coin"]);

            # [search date range]
            $cleanVars = array_merge(
                $cleanVars, 
                HelperLogic::buildDateSearch($request, ["created_at", "updated_at"])
            );

            # [paging query]
            $res = SettingWithdrawModel::paging(
                $cleanVars,
                $request->get("page"),
                $request->get("size"),
                ["*"],
                ["id", "desc"]
            );

            # [result]
            if ($res) {
                $success = SettingOperatorModel::where("code", "success")->first();
                
                # [add and edit column using for loop]
                foreach ($res["items"] as $row) {
                    $row["is_active"] = $row["is_active"] ? "active" : "inactive";

                    $network = SettingBlockchainNetworkModel::where("id", $row["network"])->first();
                    $row["network"] = $network ? $network["code"] : "";

                    $wallet = SettingCoinModel::where("id", $row["coin_id"])->first();
                    $row["coin"] = $wallet ? $wallet["code"] : "";

                    $row["private_key"] = isset($row["private_key"]) ? "available" : "none";

                    $row["total_withdraw"] = UserWithdrawModel::where(["status" => $success["id"], "from_address" => $row["address"]])->sum("amount");
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
