<?php

namespace plugin\admin\app\controller\reward\record;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\RewardRecordModel;
use app\model\database\AccountUserModel;
use app\model\database\SettingOperatorModel;
use plugin\dapp\app\model\logic\UserProfileLogic;
use app\model\logic\HelperLogic;

class Paging extends Base
{
    # [validation-rule]
    protected $rule = [
        "size" => "require|number",
        "page" => "require|number",
        "id" => "number|max:11",
        "sn" => "",
        "used_at" => "number|length:8",
        "user" => "max:80",
        "user_tree_id" => "",
        "from_user" => "max:80",
        "from_user_tree_id" => "",
        "reward_type" => "number|max:11",
        "amount" => "float|max:20",
        "amount_min" => "float|max:20",
        "amount_max" => "float|max:20",
        "rate" => "float|max:20",
        "distribution" => "",
        "ref_table" => "",
        "ref_id" => "",
        "remark" => "",
        "created_at_start" => "date",
        "created_at_end" => "date",
        "updated_at_start" => "date",
        "updated_at_end" => "date",
        "pay_at_start" => "date",
        "pay_at_end" => "date",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "id",
        "sn",
        "used_at",
        "user",
        "user_tree_id",
        "from_user",
        "from_user_tree_id",
        "reward_type",
        "amount",
        "rate",
        "distribution",
        "ref_table",
        "ref_id",
        "remark",
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "sn",
        "created_at",
        "updated_at",
        "pay_at",
        "used_at",
        "uid",
        "user",
        "user_tree_id",
        "from_uid",
        "from_user",
        "from_user_tree_id",
        "reward_type",
        "amount",
        "rate",
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

            # [search amount range]
            $amount_min = $request->get("amount_min");
            $amount_max = $request->get("amount_max");
            $cleanVars[] = $amount_min ? ["amount", ">=", $amount_min] : "";
            $cleanVars[] = $amount_max ? ["amount", "<=", $amount_max] : "";
            if ($amount_min || $amount_max) {
                unset($cleanVars["amount"]);
            }

            # [unset key]
            unset($cleanVars["user"]);
            unset($cleanVars["from_user"]);

            # [search date range]
            $cleanVars = array_merge(
                $cleanVars,
                HelperLogic::buildDateSearch($request, ["created_at", "updated_at", "pay_at"])
            );

            # [process]
            $res = RewardRecordModel::paging(
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

                    // type
                    $reward_type = SettingOperatorModel::where("id", $row["reward_type"])->first();
                    $row["reward_type"] = $reward_type ? $reward_type["code"] : "";
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
                                ? round(RewardRecordModel::where($filter)->sum("amount"), 8)
                                : round(RewardRecordModel::sum("amount"), 8),
                        ]
                    ],
                ];
            }
        }

        # [standard output]
        return $this->output();
    }
}
