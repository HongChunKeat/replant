<?php

namespace plugin\admin\app\controller\user\nft;

# library
use support\Request;
use plugin\admin\app\controller\Base;
# database & logic
use plugin\dapp\app\model\logic\UserProfileLogic;
use app\model\database\SettingBlockchainNetworkModel;
use app\model\database\SettingOperatorModel;
use app\model\database\UserNftModel;
use app\model\database\AccountUserModel;
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
        "status" => "number|max:11",
        "txid" => "min:60|max:70|alphaNum",
        "from_address" => "length:42|alphaNum",
        "to_address" => "length:42|alphaNum",
        "network" => "max:48",
        "token_address" => "length:42|alphaNum",
        "ref_table" => "",
        "ref_id" => "number|max:11",
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
        "status",
        "txid",
        "from_address",
        "to_address",
        "network",
        "token_address",
        "ref_table",
        "ref_id",
        "remark"
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "sn",
        "uid",
        "user",
        "status",
        "txid",
        "log_index",
        "from_address",
        "to_address",
        "network",
        "token_address",
        "ref_table",
        "ref_id",
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

            # [unset key]
            unset($cleanVars["user"]);

            # [search date range]
            $cleanVars = array_merge(
                $cleanVars,
                HelperLogic::buildDateSearch($request, ["created_at", "updated_at"])
            );

            # [paging query]
            $res = UserNftModel::paging(
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

                    $status = SettingOperatorModel::where("id", $row["status"])->first();
                    $row["status"] = $status ? $status["code"] : "";
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