<?php

namespace plugin\admin\app\controller\account\user;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\AccountUserModel;
use app\model\logic\HelperLogic;

class Paging extends Base
{
    # [validation-rule]
    protected $rule = [
        "size" => "require|number",
        "page" => "require|number",
        "id" => "number|max:11",
        "user_id" => "",
        "avatar" => "max:100",
        "web3_address" => "length:42|alphaNum",
        "nickname" => "",
        "login_id" => "min:8|max:15",
        "tag" => "",
        "status" => "in:active,inactivated,freezed,suspended",
        "telegram" => "max:100",
        "discord" => "max:100",
        "twitter" => "max:100",
        "google" => "max:100",
        "telegram_name" => "max:100",
        "discord_name" => "max:100",
        "twitter_name" => "max:100",
        "google_name" => "max:100",
        "remark" => "",
        "created_at_start" => "date",
        "created_at_end" => "date",
        "updated_at_start" => "date",
        "updated_at_end" => "date",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "id",
        "user_id",
        "avatar",
        "web3_address",
        "nickname",
        "login_id",
        "tag",
        "authenticator",
        "status",
        "telegram",
        "discord",
        "twitter",
        "google",
        "telegram_name",
        "discord_name",
        "twitter_name",
        "google_name",
        "remark"
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "user_id",
        "avatar",
        "web3_address",
        "nickname",
        "login_id",
        "tag",
        "authenticator",
        "status",
        "telegram",
        "discord",
        "twitter",
        "google",
        "telegram_name",
        "discord_name",
        "twitter_name",
        "google_name",
        "remark",
        "created_at",
        "updated_at",
    ];

    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->get(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->get(), $this->patternInputs);

        # [proceed]
        if (!count($this->error)) {

            # [search date range]
            $cleanVars = array_merge(
                $cleanVars, 
                HelperLogic::buildDateSearch($request, ["created_at", "updated_at"])
            );

            # [paging query]
            $res = AccountUserModel::paging(
                $cleanVars,
                $request->get("page"),
                $request->get("size"),
                ["*"],
                ["id", "desc"]
            );

            # [result]
            if ($res) {
                // meta filter
                $filter = HelperLogic::cleanTableParams($cleanVars);

                $this->response = [
                    "success" => true,
                    "data" => [
                        "data" => HelperLogic::formatOutput($res["items"], $this->patternOutputs, 1),
                        "count" => $res["count"],
                        "last_page" => ceil($res["count"] / $request->get("size")),
                        "meta" => [
                            "total" => (count($filter) > 0)
                                ? AccountUserModel::where($filter)->count("id")
                                : AccountUserModel::count("id"),
                            "active" => (count($filter) > 0)
                                ? AccountUserModel::where($filter)->where("status", "active")->count("id")
                                : AccountUserModel::where("status", "active")->count("id"),
                            "inactivated" => (count($filter) > 0)
                                ? AccountUserModel::where($filter)->where("status", "inactivated")->count("id")
                                : AccountUserModel::where("status", "inactivated")->count("id"),
                            "freezed" => (count($filter) > 0)
                                ? AccountUserModel::where($filter)->where("status", "freezed")->count("id")
                                : AccountUserModel::where("status", "freezed")->count("id"),
                            "suspended" => (count($filter) > 0)
                                ? AccountUserModel::where($filter)->where("status", "suspended")->count("id")
                                : AccountUserModel::where("status", "suspended")->count("id"),
                        ]
                    ],
                ];
            }
        }

        # [standard output]
        return $this->output();
    }
}
