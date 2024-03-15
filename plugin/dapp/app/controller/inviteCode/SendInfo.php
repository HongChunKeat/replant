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
use app\model\database\UserNftModel;
use app\model\logic\HelperLogic;
use app\model\logic\SettingLogic;

class SendInfo extends Base
{
    # [validation-rule]
    protected $rule = [
        "address" => "require",
        "invite_code" => "require",
        "txid" => "require|min:60|max:70|alphaNum",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "address",
        "invite_code",
        "txid",
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
        Redis::get("invite_code_send_info-lock:" . $cleanVars["address"])
            ? $this->error[] = "invite_code_send_info:lock"
            : Redis::set("invite_code_send_info-lock:" . $cleanVars["address"], 1);

        # [checking]
        [$inviteCode, $seed] = $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error) && ($this->successTotalCount == $this->successPassedCount)) {
            $res = "";

            # [process]
            if (count($cleanVars) > 0) {
                $processing = SettingLogic::get("operator", ["code" => "processing"]);

                $res = UserNftModel::create([
                    "sn" => HelperLogic::generateUniqueSN("user_nft"),
                    "status" => $processing["id"],
                    "txid" => $cleanVars["txid"],
                    "to_address" => $cleanVars["address"],
                    "network" => $seed["network"],
                    "token_address" => $seed["token_address"],
                    "ref_table" => "user_invite_code",
                    "ref_id" => $inviteCode["id"]
                ]);
            }

            if ($res) {
                LogUserModel::log($request, "invite_code_send_info");
                $this->response = [
                    "success" => true
                ];
            }
        }

        // remove redis lock
        Redis::del("invite_code_send_info-lock:" . $cleanVars["address"]);

        # [standard output]
        return $this->output();
    }

    private function checking(array $params = [])
    {
        # [init success condition]
        $this->successTotalCount = 5;

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

        if (isset($params["txid"])) {
            // Check if txid format is valid (starts with "0x")
            if (!empty($params["txid"]) && str_starts_with($params["txid"], "0x") === false) {
                $this->error[] = "txid:invalid_format";
            } else {
                $this->successPassedCount++;

                $processing = SettingLogic::get("operator", ["code" => "processing"]);
                $success = SettingLogic::get("operator", ["code" => "success"]);

                if (UserNftModel::where("txid", $params["txid"])
                    ->whereIn("status", [$processing["id"], $success["id"]])
                    ->first()
                ) {
                    $this->error[] = "nft:exists";
                }
            }
        }

        return [$inviteCode ?? 0, $seed ?? 0];
    }
}
