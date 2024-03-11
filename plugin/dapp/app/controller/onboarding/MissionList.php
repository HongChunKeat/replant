<?php

namespace plugin\dapp\app\controller\onboarding;

# library
use plugin\dapp\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\SettingMissionModel;
use app\model\database\UserMissionModel;
use app\model\logic\HelperLogic;
use app\model\logic\SettingLogic;

class MissionList extends Base
{
    # [outputs-pattern]
    protected $patternOutputs = [
        "name",
        "status",
        "progress",
    ];

    public function index(Request $request)
    {
        # user id
        $uid = $request->visitor["id"];

        # [proceed]
        if (!count($this->error)) {
            $cleanVars["is_show"] = 1;
            $cleanVars["type"] = "onboarding";

            # [paging query]
            $res = SettingMissionModel::listing(
                $cleanVars,
                ["*"],
                ["id", "asc"]
            );

            if ($res) {
                # [add and edit column using for loop]
                foreach ($res as $row) {
                    $row["progress"] = 0;

                    //get user mission
                    $mission = UserMissionModel::where(["uid" => $uid, "mission_id" => $row["id"]])->first();

                    if ($mission) {
                        $status = SettingLogic::get("operator", ["id" => $mission["status"]]);
                        $row["status"] = $status["code"];
                        $row["progress"] = $mission["progress"];
                    }
                }

                $this->response = [
                    "success" => true,
                    "data" => HelperLogic::formatOutput($res, $this->patternOutputs, 1)
                ];
            }
        }

        # [standard output]
        return $this->output();
    }
}