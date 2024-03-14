<?php

namespace plugin\admin\app\controller\user\seed;

# library
use support\Request;
use plugin\admin\app\controller\Base;
# database & logic
use plugin\dapp\app\model\logic\UserProfileLogic;
use app\model\database\AccountUserModel;
use app\model\database\UserSeedModel;
use app\model\logic\HelperLogic;

class Paging extends Base
{
    # [validation-rule]
    protected $rule = [
        "size" => "require|number",
        "page" => "require|number",
        "id" => "number|max:11",
        "uid" => "number|max:11",
        "user" => "",
        "claimable" => "in:1,0",
        "remark" => "",
        "created_at_start" => "date",
        "created_at_end" => "date",
        "updated_at_start" => "date",
        "updated_at_end" => "date",
        "claimed_at_start" => "date",
        "claimed_at_end" => "date",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "id",
        "uid",
        "user",
        "claimable",
        "remark",
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "created_at",
        "updated_at",
        "claimed_at",
        "uid",
        "user",
        "claimable",
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

            # [unset key]
            unset($cleanVars["user"]);

            # [search date range]
            $cleanVars = array_merge(
                $cleanVars,
                HelperLogic::buildDateSearch($request, ["created_at", "updated_at", "claimed_at"])
            );

            # [paging query]
            $res = UserSeedModel::paging(
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
