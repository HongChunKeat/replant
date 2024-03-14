<?php

namespace plugin\admin\app\controller\user\seed;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\AccountUserModel;
use app\model\database\UserSeedModel;
use app\model\logic\HelperLogic;

class Read extends Base
{
    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "created_at",
        "updated_at",
        "claimed_at",
        "uid",
        "user",
        "claimable",
        "remark",
    ];

    public function index(Request $request, int $targetId = 0)
    {
        # [process]
        $res = UserSeedModel::where("id", $targetId)->first();

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
