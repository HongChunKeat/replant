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
        [$referral] = $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error) && ($this->successTotalCount == $this->successPassedCount) && $referral) {
            # [process]
            if (count($cleanVars) > 0) {
                //referral module
                $res = UserProfileLogic::bindUpline($cleanVars["uid"], $referral["id"]);

                if ($res) {
                    LogUserModel::log($request, "invite_code");
                    $this->response = [
                        "success" => true
                    ];
                }
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
        if (isset($params["uid"])) {
            // check uid exist
            $user = AccountUserModel::where(["id" => $params["uid"], "status" => "active"])->first();
            if (!$user) {
                $this->error[] = "user:missing";
            } else {
                $this->successPassedCount++;

                // Check upline exists
                if (isset($params["invite_code"])) {
                    $referral = UserInviteCodeModel::where("code", $params["invite_code"])->first();

                    if (!$referral || $user["id"] == $referral["uid"]) {
                        $this->error[] = "invite_code:invalid";
                    } else {
                        $this->successPassedCount++;

                        if ($referral["usage"] <= 0) {
                            $this->error[] = "invite_code:has_been_used_up";
                        }

                        $uplineNetwork = NetworkSponsorModel::where("uid", $referral["uid"])->first();
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
        }

        return [$referral ?? 0];
    }
}
