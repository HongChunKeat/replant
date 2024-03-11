<?php

namespace plugin\admin\app\controller\auth;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\AdminPermissionModel;
use app\model\database\PermissionTemplateModel;

class Rule extends Base
{
    public function index(Request $request)
    {
        # user id`
        $cleanVars["uid"] = $request->visitor["id"];

        # [proceed]
        $role = AdminPermissionModel::where("admin_uid", $cleanVars["uid"])->first();
        if ($role) {
            $permission = PermissionTemplateModel::where("id", $role["role"])->first();

            if ($permission) {
                $this->response = [
                    "success" => true,
                    "data" => json_decode($permission["rule"]),
                ];
            }
        }

        # [standard output]
        return $this->output();
    }
}