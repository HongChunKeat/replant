<?php

namespace plugin\admin\app\controller\permission\admin;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\AdminPermissionModel;
use app\model\database\AccountAdminModel;
use app\model\database\PermissionTemplateModel;
use app\model\logic\HelperLogic;

class Paging extends Base
{
    # [validation-rule]
    protected $rule = [
        "size" => "require|number",
        "page" => "require|number",
        "id" => "number|max:11",
        "admin_address" => "length:42|alphaNum",
        "nickname" => "",
        "role" => "number|max:11",
        "created_at_start" => "date",
        "created_at_end" => "date",
        "updated_at_start" => "date",
        "updated_at_end" => "date",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "id",
        "admin_address",
        "nickname",
        "role",
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "admin_address",
        "nickname",
        "role",
        "created_at",
        "updated_at"
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
            if (isset($cleanVars["admin_address"])) {
                $admin = AccountAdminModel::where("web3_address", $cleanVars["admin_address"])->first();
                $cleanVars["admin_uid"] = $admin["id"] ?? 0;
            }

            if (isset($cleanVars["nickname"])) {
                $admin = AccountAdminModel::where("nickname", $cleanVars["nickname"])->first();
                $cleanVars["admin_uid"] = $admin["id"] ?? 0;
            }

            # [unset key]
            unset($cleanVars["admin_address"]);
            unset($cleanVars["nickname"]);

            # [search date range]
            $cleanVars = array_merge(
                $cleanVars, 
                HelperLogic::buildDateSearch($request, ["created_at", "updated_at"])
            );

            # [paging query]
            $res = AdminPermissionModel::paging(
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
                    $admin = AccountAdminModel::where("id", $row["admin_uid"])->first();
                    $row["admin_address"] = $admin ? $admin["web3_address"] : "";
                    $row["nickname"] = $admin ? $admin["nickname"] : "";

                    $role = PermissionTemplateModel::where("id", $row["role"])->first();
                    $row["role"] = $role["template_code"];
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
