<?php

namespace plugin\admin\app\controller\user\point;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\UserPointModel;
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
        "from_uid",
        "from_user",
        "point",
        "source",
        "remark",
    ];

    public function index(Request $request, int $targetId = 0)
    {
        # [process]
        $res = UserPointModel::where("id", $targetId)->first();

        # [result]
        if ($res) {
            $user = AccountUserModel::where("id", $res["uid"])->first();
            $res["user"] = $user ? $user["user_id"] : "";

            $from_user = AccountUserModel::where("id", $res["from_uid"])->first();
            $res["from_user"] = $from_user ? $from_user["user_id"] : "";

            $this->response = [
                "success" => true,
                "data" => HelperLogic::formatOutput($res, $this->patternOutputs),
            ];
        }

        # [standard output]
        return $this->output();
    }
}
