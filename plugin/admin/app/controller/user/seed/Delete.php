<?php

namespace plugin\admin\app\controller\user\seed;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\UserSeedModel;

class Delete extends Base
{
    public function index(Request $request, int $targetId = 0)
    {
        # [process]
        $res = UserSeedModel::where("id", $targetId)->delete();

        # [result]
        if ($res) {
            LogAdminModel::log($request, "delete", "user_seed", $targetId);
            $this->response = [
                "success" => true,
            ];
        }

        # [standard output]
        return $this->output();
    }
}
