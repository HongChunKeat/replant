<?php

namespace plugin\admin\app\controller\account\admin;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\AccountAdminModel;
use app\model\logic\HelperLogic;

class Read extends Base
{
    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "admin_id",
        "web3_address",
        "nickname",
        "tag",
        "email",
        "status",
        "remark",
        "created_at",
        "updated_at",
    ];

    public function index(Request $request, int $targetId = 0)
    {
        # [process]
        $res = AccountAdminModel::where("id", $targetId)->first();

        # [result]
        if ($res) {
            $this->response = [
                "success" => true,
                "data" => HelperLogic::formatOutput($res, $this->patternOutputs),
            ];
        }

        # [standard output]
        return $this->output();
    }
}
