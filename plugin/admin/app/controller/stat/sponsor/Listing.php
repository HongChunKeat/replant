<?php

namespace plugin\admin\app\controller\stat\sponsor;

# library
use support\Request;
use plugin\admin\app\controller\Base;
# database & logic
use app\model\database\StatSponsorModel;
use app\model\database\AccountUserModel;
use plugin\dapp\app\model\logic\UserProfileLogic;
use app\model\logic\HelperLogic;

class Listing extends Base
{
    # [validation-rule]
    protected $rule = [
        "id" => "number|max:11",
        "used_at" => "number|length:8",
        "user" => "max:80",
        "from_user" => "max:80",
        "stat_type" => "",
        "amount" => "float|max:11",
        "amount_min" => "float|max:20",
        "amount_max" => "float|max:20",
        "is_personal" => "in:1,0",
        "is_cumulative" => "in:1,0",
        "remark" => "",
        "created_at_start" => "date",
        "created_at_end" => "date",
        "updated_at_start" => "date",
        "updated_at_end" => "date",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "id",
        "used_at",
        "user",
        "from_user",
        "stat_type",
        "amount",
        "is_personal",
        "is_cumulative",
        "remark",
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "created_at",
        "updated_at",
        "used_at",
        "uid",
        "user",
        "from_uid",
        "from_user",
        "stat_type",
        "amount",
        "is_personal",
        "is_cumulative",
        "remark",
    ];

    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->get(), $this->rule);

        # [proceed]
        if (!count($this->error)) {
            # [clean variables]
            $cleanVars = HelperLogic::cleanParams($request->get(), $this->patternInputs);

            # [search join table columns]
            if (isset($cleanVars["user"])) {
                // 4 in 1 search
                $user = UserProfileLogic::multiSearch($cleanVars["user"]);
                $cleanVars["uid"] = $user["id"] ?? 0;
                unset($cleanVars['user']);
            }

            if (isset($cleanVars["from_user"])) {
                // 4 in 1 search
                $from_user = UserProfileLogic::multiSearch($cleanVars["from_user"]);
                $cleanVars['from_uid'] = $from_user["id"] ?? 0;
                unset($cleanVars['from_user']);
            }

            # [search amount range]
            $amount_min = $request->get("amount_min");
            $amount_max = $request->get("amount_max");
            $cleanVars[] = $amount_min ? ["amount", ">=", $amount_min] : "";
            $cleanVars[] = $amount_max ? ["amount", "<=", $amount_max] : "";
            if($amount_min || $amount_max) {
                unset($cleanVars["amount"]);
            }

            # [search date range]
            $created_at_start = $request->get("created_at_start");
            $created_at_end = $request->get("created_at_end");
            $updated_at_start = $request->get("updated_at_start");
            $updated_at_end = $request->get("updated_at_end");
            $cleanVars[] = $created_at_start ? ["created_at", ">=", $created_at_start . " 00:00:00"] : "";
            $cleanVars[] = $created_at_end ? ["created_at", "<=", $created_at_end . " 23:59:59"] : "";
            $cleanVars[] = $updated_at_start ? ["updated_at", ">=", $updated_at_start . " 00:00:00"] : "";
            $cleanVars[] = $updated_at_end ? ["updated_at", "<=", $updated_at_end . " 23:59:59"] : "";

            # [listing query]
            $res = StatSponsorModel::listing(
                $cleanVars,
                ["*"],
                ["id", "desc"]
            );

            # [result]
            if ($res) {
                # [add and edit column using for loop]
                foreach ($res as $row) {
                    $row["is_personal"] = $row["is_personal"] ? "yes" : "no";
                    $row["is_cumulative"] = $row["is_cumulative"] ? "yes" : "no";

                    $uid = AccountUserModel::where("id", $row["uid"])->first();
                    $row["user"] = $uid ? $uid["user_id"] : "";

                    $from_uid = AccountUserModel::where("id", $row["from_uid"])->first();
                    $row["from_user"] = $from_uid ? $from_uid["user_id"] : "";
                }

                $this->response = [
                    "success" => true,
                    "data" => HelperLogic::formatOutput($res, $this->patternOutputs, 1),
                ];
            }
        }

        # [standard output]
        return $this->output();
    }
}
