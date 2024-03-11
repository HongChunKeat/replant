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
use app\model\logic\HelperLogic;
use plugin\admin\app\model\logic\MissionLogic;
use plugin\dapp\app\model\logic\UserProfileLogic;

class BindUpline extends Base
{
    # [validation-rule]
    protected $rule = [
        "referral" => "require",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "referral",
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
        Redis::get("bind_upline-lock:" . $cleanVars["uid"])
            ? $this->error[] = "bind_upline:lock"
            : Redis::set("bind_upline-lock:" . $cleanVars["uid"], 1);

        # [checking]
        [$referral] = $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error) && ($this->successTotalCount == $this->successPassedCount) && $referral) {
            # [process]
            if (count($cleanVars) > 0) {
                //referral module
                $res = UserProfileLogic::bindUpline($cleanVars["uid"], $referral["id"]);

                if ($res) {
                    // do mission
                    MissionLogic::missionProgress($cleanVars["uid"], ["name" => "bind a referral"]);

                    // for referral
                    MissionLogic::missionProgress($referral["id"], ["name" => "invite 3 users into the game"]);

                    LogUserModel::log($request, "bind_referral");
                    $this->response = [
                        "success" => true
                    ];
                }
            }
        }

        // remove redis lock
        Redis::del("bind_upline-lock:" . $cleanVars["uid"]);

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
            }
        }

        // Check upline exists
        if (isset($params["referral"])) {
            $self = AccountUserModel::where("id", $params["uid"])->first();

            // 4 in 1 search
            $referral = UserProfileLogic::multiSearch($params["referral"]);

            if (!$referral || $self["id"] == $referral["id"]) {
                $this->error[] = "referral:invalid";
            } else {
                $this->successPassedCount++;
                $uplineNetwork = NetworkSponsorModel::where("uid", $referral["id"])->first();
                if (!$uplineNetwork) {
                    $this->error[] = "referral:not_verified";
                } else {
                    $this->successPassedCount++;
                }

                $selfNetwork = NetworkSponsorModel::where("uid", $self["id"])->first();
                if ($selfNetwork) {
                    $this->error[] = "user:already_verified";
                } else {
                    $this->successPassedCount++;
                }
            }
        }

        return [$referral ?? 0];
    }
}