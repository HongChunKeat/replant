<?php

namespace plugin\admin\app\controller\log\admin;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\AccountAdminModel;
use app\model\logic\HelperLogic;

class Read extends Base
{
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

    public function index(Request $request, int $targetId = 0)
    {
        # [process]
        $res = LogAdminModel::where("id", $targetId)->first();

        # [result]
        if ($res) {
            $admin = AccountAdminModel::where("id", $res["admin_uid"])->first();
            $res["admin_address"] = $admin ? $admin["web3_address"] : "";

            $byAdmin = AccountAdminModel::where("id", $res["by_admin_uid"])->first();
            $res["by_admin_address"] = $byAdmin ? $byAdmin["web3_address"] : "";

            $this->response = [
                "success" => true,
                "data" => HelperLogic::formatOutput($res, $this->patternOutputs),
            ];
        }

        # [standard output]
        return $this->output();
    }
}
