<?php

namespace plugin\admin\app\controller\auth;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\AccountAdminModel;
use plugin\admin\app\model\logic\AdminProfileLogic;

class Logout extends Base
{
    public function index(Request $request)
    {
        # user id
        $cleanVars["uid"] = $request->visitor["id"];

        # [proceed]
        $res = "";

        $user = AccountAdminModel::where("id", $cleanVars["uid"])->first();
        if ($user) {
            $res = AdminProfileLogic::logout($user["admin_id"]);
        }

        # [result]
        if ($res) {
            LogAdminModel::log($request, "logout");

            $this->response = [
                "success" => true,
                "data" => $res,
            ];
        }

        return $this->output();
    }
}
