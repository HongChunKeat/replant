<?php

namespace plugin\dapp\app\controller\team;

# library
use plugin\dapp\app\controller\Base;
use support\Request;
use support\Redis;
# database & logic
use app\model\database\LogUserModel;
use app\model\database\NetworkSponsorModel;
use app\model\database\AccountUserModel;
use app\model\database\UserInviteCodeModel;
use app\model\logic\HelperLogic;
use plugin\dapp\app\model\logic\UserProfileLogic;

class BindUpline extends Base
{
    # [validation-rule]
    protected $rule = [
        "invite_code" => "require",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "invite_code",
    ];

    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->post(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->post(), $this->patternInputs);

        # user id
        $cleanVars["uid"] = $request->visitor["id"];

        // get and set redis lock
        Redis::get("invite_code-lock:" . $cleanVars["uid"])
            ? $this->error[] = "invite_code:lock"
            : Redis::set("invite_code-lock:" . $cleanVars["uid"], 1);

        # [checking]
        [$inviteCode] = $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error) && ($this->successTotalCount == $this->successPassedCount) && $inviteCode) {
            $res = "";

            # [process]
            if (count($cleanVars) > 0) {
                //referral module
                $res = UserProfileLogic::bindUpline($cleanVars["uid"], $inviteCode["uid"]);
            }

            if ($res) {
                UserInviteCodeModel::where("id", $inviteCode["id"])->update(["usage" => $inviteCode["usage"] - 1]);

                LogUserModel::log($request, "invite_code");
                $this->response = [
                    "success" => true
                ];
            }
        }

        // remove redis lock
        Redis::del("invite_code-lock:" . $cleanVars["uid"]);

        # [standard output]
        return $this->output();
    }

    private function checking(array $params = [])
    {
        # [init success condition]
        $this->successTotalCount = 4;

        # [condition]
        if (isset($params["uid"]) && isset($params["invite_code"])) {
            // check uid exist
            $user = AccountUserModel::where(["id" => $params["uid"], "status" => "active"])->first();
            if (!$user) {
                $this->error[] = "user:missing";
            } else {
                $this->successPassedCount++;

                // Check upline exists
                $inviteCode = UserInviteCodeModel::where("code", $params["invite_code"])->first();

                if (!$inviteCode || $user["id"] == $inviteCode["uid"]) {
                    $this->error[] = "invite_code:invalid";
                } else {
                    $this->successPassedCount++;

                    if ($inviteCode["usage"] <= 0) {
                        $this->error[] = "invite_code:has_been_used_up";
                    }

                    $uplineNetwork = NetworkSponsorModel::where("uid", $inviteCode["uid"])->first();
                    if (!$uplineNetwork) {
                        $this->error[] = "referral:not_verified";
                    } else {
                        $this->successPassedCount++;
                    }

                    $selfNetwork = NetworkSponsorModel::where("uid", $user["id"])->first();
                    if ($selfNetwork) {
                        $this->error[] = "user:already_verified";
                    } else {
                        $this->successPassedCount++;
                    }
                }
            }
        }

        return [$inviteCode ?? 0];
    }
}
