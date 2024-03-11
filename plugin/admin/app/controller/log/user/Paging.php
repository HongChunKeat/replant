<?php

namespace plugin\admin\app\controller\log\user;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use plugin\dapp\app\model\logic\UserProfileLogic;
use app\model\database\LogUserModel;
use app\model\database\AccountUserModel;
use app\model\logic\HelperLogic;

class Paging extends Base
{
    # [validation-rule]
    protected $rule = [
        "size" => "require|number",
        "page" => "require|number",
        "id" => "number|max:11",
        "user" => "max:80",
        "by_user" => "max:80",
        "ip" => "",
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
        "user",
        "by_user",
        "ip",
        "ref_table",
        "ref_id",
        "remark"
    ];

     # [outputs-pattern]
     protected $patternOutputs = [
        "id",
        "created_at",
        "updated_at",
        "uid",
        "user",
        "by_uid",
        "by_user",
        "ip",
        "ref_table",
        "ref_id",
        "remark"
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

            if (isset($cleanVars["by_user"])) {
                // 4 in 1 search
                $by_user = UserProfileLogic::multiSearch($cleanVars["by_user"]);
                $cleanVars["from_uid"] = $by_user["id"] ?? 0;
            }

            # [unset key]
            unset($cleanVars["user"]);
            unset($cleanVars["by_user"]);

            # [search date range]
            $cleanVars = array_merge(
                $cleanVars, 
                HelperLogic::buildDateSearch($request, ["created_at", "updated_at"])
            );

            # [paging query]
            $res = LogUserModel::paging(
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

                    $byUser = AccountUserModel::where("id", $row["by_uid"])->first();
                    $row["by_user"] = $byUser ? $byUser["user_id"] : "";
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
