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

class Read extends Base
{
    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "admin_address",
        "nickname",
        "role",
        "created_at",
        "updated_at"
    ];

    public function index(Request $request, int $targetId = 0)
    {
        # [process]
        $res = AdminPermissionModel::where("id", $targetId)->first();

        # [result]
        if ($res) {
            $admin = AccountAdminModel::where("id", $res["admin_uid"])->first();
            $res["admin_address"] = $admin ? $admin["web3_address"] : "";
            $res["nickname"] = $admin ? $admin["nickname"] : "";

            $role = PermissionTemplateModel::where("id", $res["role"])->first();
            $res["role"] = $role["template_code"];

            $this->response = [
                "success" => true,
                "data" => HelperLogic::formatOutput($res, $this->patternOutputs),
            ];
        }

        # [standard output]
        return $this->output();
    }
}
