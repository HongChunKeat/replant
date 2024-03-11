<?php

namespace plugin\admin\app\controller\setting\level;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\SettingLevelModel;

class Delete extends Base
{
    public function index(Request $request, int $targetId = 0)
    {
        $res = SettingLevelModel::where("id", $targetId)->delete();

        if ($res) {
            LogAdminModel::log($request, "delete", "setting_level", $targetId);
            $this->response = [
                "success" => true
            ];
        }

        # [standard output]
        return $this->output();
    }
}