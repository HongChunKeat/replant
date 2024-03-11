<?php

namespace plugin\admin\app\controller\permission\template;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\PermissionTemplateModel;

class Delete extends Base
{
    public function index(Request $request, int $targetId = 0)
    {
        # [process]
        $res = PermissionTemplateModel::where("id", $targetId)->delete();

        if ($res) {
            LogAdminModel::log($request, "delete", "permission_template", $targetId);
            $this->response = [
                "success" => true,
            ];
        }

        # [standard output]
        return $this->output();
    }
}
