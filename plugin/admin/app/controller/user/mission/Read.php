<?php

namespace plugin\admin\app\controller\user\mission;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\SettingMissionModel;
use app\model\database\UserMissionModel;
use app\model\database\SettingOperatorModel;
use app\model\database\AccountUserModel;
use app\model\logic\HelperLogic;

class Read extends Base
{
    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "sn",
        "expired_at",
        "created_at",
        "updated_at",
        "uid",
        "user",
        "mission",
        "status",
        "progress",
        "remark",
    ];

    public function index(Request $request, int $targetId = 0)
    {
        # [process]
        $res = UserMissionModel::where("id", $targetId)->first();

        # [result]
        if ($res) {
            $user = AccountUserModel::where("id", $res["uid"])->first();
            $res["user"] = $user ? $user["user_id"] : "";

            $mission = SettingMissionModel::where("id", $res["mission_id"])->first();
            $res["mission"] = $mission ? $mission["name"] : "";

            $status = SettingOperatorModel::where("id", $res["status"])->first();
            $res["status"] = $status ? $status["code"] : "";

            $this->response = [
                "success" => true,
                "data" => HelperLogic::formatOutput($res, $this->patternOutputs),
            ];
        }

        # [standard output]
        return $this->output();
    }
}
