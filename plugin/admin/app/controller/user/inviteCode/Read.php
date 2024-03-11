<?php

namespace plugin\admin\app\controller\user\inviteCode;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\AccountUserModel;
use app\model\database\UserInviteCodeModel;
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
        "code",
        "usage",
        "remark",
    ];

    public function index(Request $request, int $targetId = 0)
    {
        # [process]
        $res = UserInviteCodeModel::where("id", $targetId)->first();

        # [result]
        if ($res) {
            $user = AccountUserModel::where("id", $res["uid"])->first();
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
