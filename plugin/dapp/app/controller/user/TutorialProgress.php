<?php

namespace plugin\dapp\app\controller\user;

# library
use plugin\dapp\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\SettingMissionModel;
use app\model\database\UserMissionModel;
use app\model\logic\HelperLogic;
use app\model\logic\SettingLogic;

class TutorialProgress extends Base
{
    # [outputs-pattern]
    protected $patternOutputs = [
        "name",
        "status",
    ];

    public function index(Request $request)
    {
        # user id
        $cleanVars["uid"] = $request->visitor["id"];

        $res = "";

        $res = SettingMissionModel::whereIn("name", [
            "take your first mission",
            "level up your character",
            "hatch your pet",
            "assign your pet"
        ])
            ->get();

        # [result]
        if ($res) {
            foreach ($res as $row) {
                $mission = UserMissionModel::where(["uid" => $cleanVars["uid"], "mission_id" => $row["id"]])->first();

                if ($mission) {
                    $status = SettingLogic::get("operator", ["id" => $mission["status"]]);
                    $row["status"] = $status["code"];
                }
            }

            $this->response = [
                "success" => true,
                "data" => HelperLogic::formatOutput($res, $this->patternOutputs, 1),
            ];
        }

        # [standard output]
        return $this->output();
    }
}