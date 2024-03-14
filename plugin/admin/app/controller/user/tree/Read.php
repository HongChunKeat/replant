<?php

namespace plugin\admin\app\controller\user\tree;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\AccountUserModel;
use app\model\database\UserTreeModel;
use app\model\logic\HelperLogic;

class Read extends Base
{
    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "sn",
        "created_at",
        "updated_at",
        "uid",
        "user",
        "level",
        "health",
        "mined_amount",
        "is_active",
        "remark",
    ];

    public function index(Request $request, int $targetId = 0)
    {
        # [process]
        $res = UserTreeModel::where("id", $targetId)->first();

        # [result]
        if ($res) {
            $res["is_active"] = $res["is_active"] ? "active" : "inactive";

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
