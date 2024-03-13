<?php

namespace plugin\dapp\app\controller\auth;

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
use app\model\logic\SettingLogic;
use plugin\dapp\app\model\logic\UserProfileLogic;

class InviteCodeRegister extends Base
{
    # [validation-rule]
    protected $rule = [
        "address" => "require",
        "invite_code" => "require",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "address",
        "invite_code",
    ];

    public function index(Request $request)
    {
        // check phase
        $phaseOpen = SettingLogic::get("general", ["category" => "version", "code" => "phase_1.1", "value" => 1]);
        if (!$phaseOpen) {
            $this->error[] = "not_available";
            return $this->output();
        }

        # [validation]
        $this->validation($request->post(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->post(), $this->patternInputs);

        // get and set redis lock
        Redis::get("invite_code_register-lock:" . $cleanVars["address"])
            ? $this->error[] = "invite_code_register:lock"
            : Redis::set("invite_code_register-lock:" . $cleanVars["address"], 1);

        # [checking]
        [$inviteCode] = $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error) && ($this->successTotalCount == $this->successPassedCount) && $inviteCode) {
            $res = "";

            # [process]
            if (count($cleanVars) > 0) {
                // register
                $user = AccountUserModel::create([
                    "user_id" => HelperLogic::generateUniqueSN("account_user"),
                    "web3_address" => $cleanVars["address"],
                ]);

                if ($user) {
                    UserProfileLogic::init($user["id"]);

                    //referral module
                    $res = UserProfileLogic::bindUpline($user["id"], $inviteCode["uid"]);
                }
            }

            if ($res) {
                UserInviteCodeModel::where("id", $inviteCode["id"])->update(["usage" => $inviteCode["usage"] - 1]);

                LogUserModel::log($request, "invite_code_register");
                $this->response = [
                    "success" => true
                ];
            }
        }

        // remove redis lock
        Redis::del("invite_code_register-lock:" . $cleanVars["address"]);

        # [standard output]
        return $this->output();
    }

    private function checking(array $params = [])
    {
        # [init success condition]
        $this->successTotalCount = 3;

        # [condition]
        if (isset($params["address"])) {
            // check uid exist
            $user = AccountUserModel::where(["web3_address" => $params["address"], "status" => "active"])->first();
            if ($user) {
                $this->error[] = "address:already_exist";
            } else {
                $this->successPassedCount++;

                // Check upline exists
                if (isset($params["invite_code"])) {
                    $inviteCode = UserInviteCodeModel::where("code", $params["invite_code"])->first();

                    if (!$inviteCode) {
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
                    }
                }
            }
        }

        return [$inviteCode ?? 0];
    }
}
