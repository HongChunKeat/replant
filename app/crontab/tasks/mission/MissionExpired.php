<?php

namespace app\crontab\tasks\mission;

# library
use WebmanTech\CrontabTask\BaseTask;
# database & logic
use app\model\database\UserMissionModel;
use app\model\database\LogCronjobModel;
use app\model\logic\SettingLogic;

class MissionExpired extends BaseTask
{
    public function handle()
    {
        $log = LogCronjobModel::create([
            "cronjob_code" => "mission_expired",
            "used_at" => date("Ymd"),
        ]);

        try {
            $pending = SettingLogic::get("operator", ["code" => "pending"]);
            $expired = SettingLogic::get("operator", ["code" => "expired"]);

            // mission expired
            UserMissionModel::where("expired_at", "<=", date("Y-m-d H:i:s"))
                ->where("status", $pending["id"])
                ->update(["status" => $expired["id"]]);

            LogCronjobModel::where("id", $log["id"])->update(["completed_at" => date("Y-m-d H:i:s")]);
        } catch (\Exception $e) {
            LogCronjobModel::where("id", $log["id"])->update(["info" => $e]);
        }
    }
}
