<?php

namespace plugin\admin\app\controller\user\withdraw;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\UserWithdrawModel;

class Delete extends Base
{
    public function index(Request $request, int $targetId = 0)
    {
        $res = UserWithdrawModel::where("id", $targetId)->delete();

        if ($res) {
            LogAdminModel::log($request, "delete", "user_withdraw", $targetId);
            $this->response = [
                "success" => true,
            ];
        }

        # [standard output]
        return $this->output();
    }
}
