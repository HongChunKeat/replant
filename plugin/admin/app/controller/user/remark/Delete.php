<?php

namespace plugin\admin\app\controller\user\remark;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\AdminPermissionModel;
use app\model\database\UserRemarkModel;

class Delete extends Base
{
    public function index(Request $request, int $targetId = 0)
    {
        # [checking]
        $this->checking(["admin_id" => $request->visitor["id"], "id" => $targetId]);

        # [proceed]
        if (!count($this->error)) {
            # [delete query]
            $res = UserRemarkModel::where("id", $targetId)->delete();

            # [result]
            if ($res) {
                LogAdminModel::log($request, "delete", "user_remark", $targetId);
                $this->response = [
                    "success" => true,
                ];
            }
        }

        # [standard output]
        return $this->output();
    }

    private function checking(array $params = [])
    {
        # [condition]
        if (isset($params["admin_id"])) {
            $permission = AdminPermissionModel::where("admin_uid", $params["admin_id"])->first();
            $remark = UserRemarkModel::where("id", $params["id"])->first();
            if($remark) {
                if ($params["admin_id"] != $remark["admin_id"] && !in_array("*", json_decode($permission["rule"]))) {
                    $this->error[] = "remark:invalid_action";
                }
            }
        }
    }
}
