<?php

namespace plugin\admin\app\controller\permission\admin;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\AdminPermissionModel;

class Delete extends Base
{
    public function index(Request $request, int $targetId = 0)
    {
        # [process]
        $res = AdminPermissionModel::where("id", $targetId)->delete();

        if ($res) {
            LogAdminModel::log($request, "delete", "admin_permission", $targetId);
            $this->response = [
                "success" => true,
            ];
        }

        # [standard output]
        return $this->output();
    }
}
