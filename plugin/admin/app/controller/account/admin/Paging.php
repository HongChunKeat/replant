<?php

namespace plugin\admin\app\controller\account\admin;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\AccountAdminModel;
use app\model\logic\HelperLogic;

class Paging extends Base
{
    # [validation-rule]
    protected $rule = [
        "size" => "require|number",
        "page" => "require|number",
        "id" => "number|max:11",
        "admin_id" => "",
        "web3_address" => "length:42|alphaNum",
        "nickname" => "",
        "tag" => "",
        "email" => "email",
        "status" => "in:active,inactivated,freezed,suspended",
        "remark" => "",
        "created_at_start" => "date",
        "created_at_end" => "date",
        "updated_at_start" => "date",
        "updated_at_end" => "date",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "id",
        "admin_id",
        "web3_address",
        "nickname",
        "tag",
        "email",
        "status",
        "remark"
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "admin_id",
        "web3_address",
        "nickname",
        "tag",
        "email",
        "status",
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
            $res = AccountAdminModel::paging(
                $cleanVars,
                $request->get("page"),
                $request->get("size"),
                ["*"],
                ["id", "desc"]
            );

            # [result]
            if ($res) {
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
