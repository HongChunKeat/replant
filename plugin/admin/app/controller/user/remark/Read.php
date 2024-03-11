<?php

namespace plugin\admin\app\controller\user\remark;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\AccountUserModel;
use app\model\database\AccountAdminModel;
use app\model\database\UserRemarkModel;
use app\model\logic\HelperLogic;

class Read extends Base
{
    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "created_at",
        "updated_at",
        "admin_id",
        "admin_nickname",
        "admin",
        "uid",
        "nickname",
        "user",
        "remark",
    ];

    public function index(Request $request, int $targetId = 0)
    {
        # [process]
        $res = UserRemarkModel::where("id", $targetId)->first();

        # [result]
        if ($res) {
            $admin = AccountAdminModel::where("id", $res["admin_id"])->first();
            $res["admin_nickname"] = $admin ? $admin["nickname"] : "";
            $res["admin"] = $admin ? $admin["web3_address"] : "";

            $user = AccountUserModel::where("id", $res["uid"])->first();
            $res["nickname"] = $user ? $user["nickname"] : "";
            $res["user"] = $user ? $user["user_id"] : "";

            $this->response = [
                "success" => true,
                "data" => HelperLogic::formatOutput($res, $this->patternOutputs),
            ];
        }

        # [standard output]
        return $this->output();
    }
}
