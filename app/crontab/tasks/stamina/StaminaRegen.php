<?php

namespace app\crontab\tasks\stamina;

# library
use support\Db;
use WebmanTech\CrontabTask\BaseTask;
# database & logic
use app\model\database\UserStaminaModel;

class StaminaRegen extends BaseTask
{
    public function handle()
    {
        // stamina regen
        UserStaminaModel::where("current_stamina", "<", Db::raw("max_stamina"))
            ->update(["current_stamina" => Db::raw("current_stamina + 1")]);
    }
}