<?php

namespace plugin\admin\app\controller\network\sponsor;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\NetworkSponsorModel;
use app\model\database\AccountUserModel;
use app\model\logic\HelperLogic;

class Read extends Base
{
    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "user", 
        "upline_user",
        "remark",
        "created_at",
        "updated_at",
    ];

    public function index(Request $request, int $targetId = 0)
    {
        # [process]
        $res = NetworkSponsorModel::where("id", $targetId)->first();

        # [result]
        if ($res) {
            $user = AccountUserModel::where("id", $res["uid"])->first();
            $res["user"] = $user ? $user["user_id"] : "";

            $upline_user = AccountUserModel::where("id", $res["upline_uid"])->first();
            $res["upline_user"] = $upline_user ? $upline_user["user_id"] : "";

            $this->response = [
                "success" => true,
                "data" => HelperLogic::formatOutput($res, $this->patternOutputs),
            ];
        }

        # [standard output]
        return $this->output();
    }
}
