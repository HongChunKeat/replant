<?php

namespace plugin\admin\app\controller\account\user;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\AccountUserModel;

class Delete extends Base
{
    public function index(Request $request, int $targetId = 0)
    {
        # [process]
        $res = AccountUserModel::where("id", $targetId)->delete();

        if ($res) {
            LogAdminModel::log($request, "delete", "account_user", $targetId);
            $this->response = [
                "success" => true,
            ];
        }

        # [standard output]
        return $this->output();
    }
}
