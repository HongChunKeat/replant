<?php

namespace plugin\admin\app\controller\log\admin;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\AccountAdminModel;
use app\model\logic\HelperLogic;

class Paging extends Base
{
    # [validation-rule]
    protected $rule = [
        "size" => "require|number",
        "page" => "require|number",
        "id" => "number|max:11",
        "admin_address" => "length:42|alphaNum",
        "by_admin_address" => "length:42|alphaNum",
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
        "admin_address",
        "by_admin_address",
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
        "admin_uid",
        "admin_address",
        "by_admin_uid",
        "by_admin_address",
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
            if (isset($cleanVars["admin_address"])) {
                $admin = AccountAdminModel::where("web3_address", $cleanVars["admin_address"])->first();
                $cleanVars["admin_uid"] = $admin["id"] ?? 0;
            }

            if (isset($cleanVars["by_admin_address"])) {
                $byAdmin = AccountAdminModel::where("web3_address", $cleanVars["by_admin_address"])->first();
                $cleanVars["by_admin_uid"] = $byAdmin["id"] ?? 0;
            }

            # [unset key]
            unset($cleanVars["admin_address"]);
            unset($cleanVars["by_admin_address"]);

            # [search date range]
            $cleanVars = array_merge(
                $cleanVars, 
                HelperLogic::buildDateSearch($request, ["created_at", "updated_at"])
            );

            # [paging query]
            $res = LogAdminModel::paging(
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

                    $byAdmin = AccountAdminModel::where("id", $row["by_admin_uid"])->first();
                    $row["by_admin_address"] = $byAdmin ? $byAdmin["web3_address"] : "";
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
