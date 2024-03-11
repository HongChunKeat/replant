<?php

namespace plugin\admin\app\controller\account\admin;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\AccountAdminModel;
use app\model\database\AdminPermissionModel;

class Delete extends Base
{
    public function index(Request $request, int $targetId = 0)
    {
        # [process]
        $res = AccountAdminModel::where("id", $targetId)->delete();

        if ($res) {
            AdminPermissionModel::where("admin_uid", $targetId)->delete();
            LogAdminModel::log($request, "delete", "account_admin", $targetId);
            $this->response = [
                "success" => true,
            ];
        }

        # [standard output]
        return $this->output();
    }
}
