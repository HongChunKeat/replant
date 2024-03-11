<?php

namespace plugin\admin\app\controller\setting\gacha;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\SettingGachaModel;

class Delete extends Base
{
    public function index(Request $request, int $targetId = 0)
    {
        $res = SettingGachaModel::where("id", $targetId)->delete();

        if ($res) {
            LogAdminModel::log($request, "delete", "setting_gacha", $targetId);
            $this->response = [
                "success" => true
            ];
        }

        # [standard output]
        return $this->output();
    }
}