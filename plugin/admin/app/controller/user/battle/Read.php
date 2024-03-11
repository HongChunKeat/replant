<?php

namespace plugin\admin\app\controller\user\battle;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\UserBattleModel;
use app\model\database\AccountUserModel;
use app\model\database\SettingOperatorModel;
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
        "user_pet_id",
        "opponent_uid",
        "opponent",
        "opponent_pet_id",
        "result",
        "remark",
    ];

    public function index(Request $request, int $targetId = 0)
    {
        # [process]
        $res = UserBattleModel::where("id", $targetId)->first();

        # [result]
        if ($res) {
            $user = AccountUserModel::where("id", $res["uid"])->first();
            $res["user"] = $user ? $user["user_id"] : "";

            $opponent = AccountUserModel::where("id", $res["opponent_uid"])->first();
            $res["opponent"] = $opponent ? $opponent["user_id"] : "";

            $result = SettingOperatorModel::where("id", $res["result"])->first();
            $res["result"] = $result ? $result["code"] : "";

            $this->response = [
                "success" => true,
                "data" => HelperLogic::formatOutput($res, $this->patternOutputs),
            ];
        }

        # [standard output]
        return $this->output();
    }
}
