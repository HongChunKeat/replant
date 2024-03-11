<?php

namespace plugin\dapp\app\controller\auth;

# library
use plugin\dapp\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogUserModel;
use app\model\database\AccountUserModel;
use plugin\dapp\app\model\logic\UserProfileLogic;

class Logout extends Base
{
    public function index(Request $request)
    {
        # user id
        $cleanVars["uid"] = $request->visitor["id"];

        # [proceed]
        $res = "";

        $user = AccountUserModel::where("id", $cleanVars["uid"])->first();
        if ($user) {
            $res = UserProfileLogic::logout($user["user_id"]);
        }

        # [result]
        if ($res) {
            LogUserModel::log($request, "logout");

            $this->response = [
                "success" => true,
                "data" => $res,
            ];
        }

        return $this->output();
    }
}
