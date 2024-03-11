<?php

namespace app\crontab\tasks\stamina;

# library
use WebmanTech\CrontabTask\BaseTask;
# database & logic
use app\model\database\UserStaminaModel;
use app\model\database\LogCronjobModel;

class StaminaResetUsage extends BaseTask
{
    public function handle()
    {
        
        $log = LogCronjobModel::create([
            "cronjob_code" => "stamina_reset_usage",
            "used_at" => date("Ymd"),
        ]);

        try {
            UserStaminaModel::query()->update(["usage" => 1]);

            LogCronjobModel::where("id", $log["id"])->update(["completed_at" => date("Y-m-d H:i:s")]);
        } catch (\Exception $e) {
            LogCronjobModel::where("id", $log["id"])->update(["info" => $e]);
        }
    }
}