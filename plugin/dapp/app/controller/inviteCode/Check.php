<?php

namespace plugin\dapp\app\controller\inviteCode;

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

class Check extends Base
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
        $phaseOpen = SettingLogic::get("general", ["category" => "version", "code" => "phase_2", "value" => 1]);
        if (!$phaseOpen) {
            $this->error[] = "not_available";
            return $this->output();
        }

        # [validation]
        $this->validation($request->post(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->post(), $this->patternInputs);

        // get and set redis lock
        if (isset($cleanVars["address"])) {
            Redis::get("invite_code_check-lock:" . $cleanVars["address"])
                ? $this->error[] = "invite_code_check:lock"
                : Redis::set("invite_code_check-lock:" . $cleanVars["address"], 1);
        }

        # [checking]
        $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error) && ($this->successTotalCount == $this->successPassedCount)) {
            # [process]
            if (count($cleanVars) > 0) {
                LogUserModel::log($request, "invite_code_check");
                $this->response = [
                    "success" => true
                ];
            }
        }

        // remove redis lock
        if (isset($cleanVars["address"])) {
            Redis::del("invite_code_check-lock:" . $cleanVars["address"]);
        }

        # [standard output]
        return $this->output();
    }

    private function checking(array $params = [])
    {
        # [init success condition]
        $this->successTotalCount = 4;

        # [condition]
        if (isset($params["address"]) && isset($params["invite_code"])) {
            // check uid exist
            $user = AccountUserModel::where(["web3_address" => $params["address"], "status" => "active"])->first();
            if ($user) {
                $this->error[] = "address:exists";
            } else {
                $this->successPassedCount++;

                $seed = SettingLogic::get("nft", ["name" => "seed"]);
                if (!$seed) {
                    $this->error[] = "setting:missing";
                } else {
                    $this->successPassedCount++;
                }

                // Check invite code
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
}
