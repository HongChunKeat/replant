<?php

namespace plugin\admin\app\controller\user\stamina;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\UserStaminaModel;

class Delete extends Base
{
    public function index(Request $request, int $targetId = 0)
    {
        # [process]
        $res = UserStaminaModel::where("id", $targetId)->delete();

        # [result]
        if ($res) {
            LogAdminModel::log($request, "delete", "user_stamina", $targetId);
            $this->response = [
                "success" => true,
            ];
        }

        # [standard output]
        return $this->output();
    }
}
