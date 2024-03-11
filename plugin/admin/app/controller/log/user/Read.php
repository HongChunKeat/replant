<?php

namespace plugin\admin\app\controller\log\user;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\LogUserModel;
use app\model\database\AccountUserModel;
use app\model\logic\HelperLogic;

class Read extends Base
{
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

    public function index(Request $request, int $targetId = 0)
    {
        # [process]
        $res = LogUserModel::where("id", $targetId)->first();

        # [result]
        if ($res) {
            $user = AccountUserModel::where("id", $res["uid"])->first();
            $res["user"] = $user ? $user["user_id"] : "";

            $byUser = AccountUserModel::where("id", $res["by_uid"])->first();
            $res["by_user"] = $byUser ? $byUser["user_id"] : "";

            $this->response = [
                "success" => true,
                "data" => HelperLogic::formatOutput($res, $this->patternOutputs),
            ];
        }

        # [standard output]
        return $this->output();
    }
}
