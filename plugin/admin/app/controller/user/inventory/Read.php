<?php

namespace plugin\admin\app\controller\user\inventory;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\UserInventoryModel;
use app\model\database\AccountUserModel;
use app\model\database\SettingItemModel;
use app\model\logic\HelperLogic;

class Read extends Base
{
    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "sn",
        "used_at",
        "removed_at",
        "marketed_at",
        "created_at",
        "updated_at",
        "uid",
        "user",
        "item",
        "remark",
    ];

    public function index(Request $request, int $targetId = 0)
    {
        # [process]
        $res = UserInventoryModel::where("id", $targetId)->first();

        # [result]
        if ($res) {
            $user = AccountUserModel::where("id", $res["uid"])->first();
            $res["user"] = $user ? $user["user_id"] : "";

            $item = SettingItemModel::where("id", $res["item_id"])->first();
            $res["item"] = $item ? $item["name"] : "";

            $this->response = [
                "success" => true,
                "data" => HelperLogic::formatOutput($res, $this->patternOutputs),
            ];
        }

        # [standard output]
        return $this->output();
    }
}
