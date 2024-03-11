<?php

namespace plugin\admin\app\controller\user\deposit;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\UserDepositModel;

class Delete extends Base
{
    public function index(Request $request, int $targetId = 0)
    {
        $res = UserDepositModel::where("id", $targetId)->delete();

        if ($res) {
            LogAdminModel::log($request, "delete", "user_deposit", $targetId);
            $this->response = [
                "success" => true,
            ];
        }

        # [standard output]
        return $this->output();
    }
}
