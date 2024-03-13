<?php

namespace plugin\dapp\app\controller\user;

# library
use plugin\dapp\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\AccountUserModel;
use app\model\database\NetworkSponsorModel;
use app\model\database\UserInviteCodeModel;
use app\model\logic\HelperLogic;

class GetProfile extends Base
{
    # [outputs-pattern]
    protected $patternOutputs = [
        "upline",
        "user_id",
        "avatar",
        "web3_address",
        "nickname",
        "login_id",
        "invite_code",
        "telegram",
        "discord",
        "twitter",
        "google",
    ];

    public function index(Request $request)
    {
        # user id
        $cleanVars["uid"] = $request->visitor["id"];

        $res = AccountUserModel::where("id", $cleanVars["uid"])->first();

        # [result]
        if ($res) {
            $res["upline"] = null;
            $res["telegram"] = $res["telegram_name"];
            $res["discord"] = $res["discord_name"];
            $res["twitter"] = $res["twitter_name"];
            $res["google"] = $res["google_name"];

            $upline = NetworkSponsorModel::where("uid", $res["id"])->first();
            if ($upline) {
                $uplineAccount = AccountUserModel::where("id", $upline["upline_uid"])->first();
                $res["upline"] = $uplineAccount ? $uplineAccount["user_id"] : "";
            }

            $inviteCode = UserInviteCodeModel::where("uid", $res["id"])->first();
            $res["invite_code"] = $inviteCode["code"] ?? "";

            $this->response = [
                "success" => true,
                "data" => HelperLogic::formatOutput($res, $this->patternOutputs),
            ];
        }

        # [standard output]
        return $this->output();
    }
}
