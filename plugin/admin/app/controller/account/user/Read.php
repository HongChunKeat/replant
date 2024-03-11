<?php

namespace plugin\admin\app\controller\account\user;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\AccountUserModel;
use app\model\logic\HelperLogic;

class Read extends Base
{
    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "user_id",
        "avatar",
        "character",
        "web3_address",
        "nickname",
        "login_id",
        "tag",
        "authenticator",
        "status",
        "telegram",
        "discord",
        "twitter",
        "google",
        "telegram_name",
        "discord_name",
        "twitter_name",
        "google_name",
        "remark",
        "created_at",
        "updated_at",
    ];

    public function index(Request $request, int $targetId = 0)
    {
        # [process]
        $res = AccountUserModel::where("id", $targetId)->first();

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
