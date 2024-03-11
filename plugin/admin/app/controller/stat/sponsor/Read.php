<?php

namespace plugin\admin\app\controller\stat\sponsor;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\StatSponsorModel;
use app\model\database\AccountUserModel;
use app\model\logic\HelperLogic;

class Read extends Base
{
    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "created_at",
        "updated_at",
        "used_at",
        "uid",
        "user",
        "from_uid",
        "from_user",
        "stat_type",
        "amount",
        "is_personal",
        "is_cumulative",
        "remark",
    ];

    public function index(Request $request, int $targetId = 0)
    {
        # [process]
        $res = StatSponsorModel::where("id", $targetId)->first();

        # [result]
        if ($res) {
            $res["is_personal"] = $res["is_personal"] ? "yes" : "no";
            $res["is_cumulative"] = $res["is_cumulative"] ? "yes" : "no";

            // address
            $uid = AccountUserModel::where("id", $res["uid"])->first();
            $res["user"] = $uid ? $uid["user_id"] : "";

            $from_uid = AccountUserModel::where("id", $res["from_uid"])->first();
            $res["from_user"] = $from_uid ? $from_uid["user_id"] : "";

            $this->response = [
                "success" => true,
                "data" => HelperLogic::formatOutput($res, $this->patternOutputs),
            ];
        }

        # [standard output]
        return $this->output();
    }
}
