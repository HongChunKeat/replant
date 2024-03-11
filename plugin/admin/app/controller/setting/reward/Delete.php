<?php

namespace plugin\admin\app\controller\setting\reward;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\SettingRewardModel;

class Delete extends Base
{
    public function index(Request $request, int $targetId = 0)
    {
        $res = SettingRewardModel::where("id", $targetId)->delete();

        if ($res) {
            LogAdminModel::log($request, "delete", "setting_reward", $targetId);
            $this->response = [
                "success" => true,
            ];
        }

        # [standard output]
        return $this->output();
    }
}
