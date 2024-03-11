<?php

namespace plugin\admin\app\controller\setting\petAttribute;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\SettingPetAttributeModel;

class Delete extends Base
{
    public function index(Request $request, int $targetId = 0)
    {
        $res = SettingPetAttributeModel::where("id", $targetId)->delete();

        if ($res) {
            LogAdminModel::log($request, "delete", "setting_pet_attribute", $targetId);
            $this->response = [
                "success" => true
            ];
        }

        # [standard output]
        return $this->output();
    }
}