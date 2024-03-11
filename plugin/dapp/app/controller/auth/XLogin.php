<?php

namespace plugin\dapp\app\controller\auth;

# library
use plugin\dapp\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogUserModel;
use app\model\database\AccountUserModel;
use app\model\logic\SettingLogic;
use app\model\logic\HelperLogic;
use plugin\admin\app\model\logic\MissionLogic;
use plugin\dapp\app\model\logic\UserProfileLogic;

class XLogin extends Base
{
    # [validation-rule]
    protected $rule = [
        "code" => "require|max:500",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "code",
    ];

    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->post(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->post(), $this->patternInputs);

        # [checking]
        [$res, $register] = $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error) && ($this->successTotalCount == $this->successPassedCount)) {
            if ($res && $res["success"]) {
                if ($register) {
                    $user = AccountUserModel::create([
                        "user_id" => HelperLogic::generateUniqueSN("account_user"),
                        "twitter" => $res["data"]["id"],
                    ]);
                    UserProfileLogic::init($user["id"]);

                    // do mission
                    MissionLogic::missionProgress($user["id"], ["name" => "link X"]);
                }

                $user = AccountUserModel::where("twitter", $res["data"]["id"])->first();

                if ($user) {
                    $accessJWT = [
                        "id" => $user["user_id"],
                        "nickname" => $user["nickname"],
                    ];

                    $this->response = [
                        "success" => true,
                        "data" => UserProfileLogic::newAccessToken($user["user_id"], $accessJWT),
                    ];

                    AccountUserModel::where("id", $user["id"])->update([
                        "authenticator" => "twitter",
                        "twitter_name" => isset($res["data"]["username"])
                            ? $res["data"]["username"]
                            : $res["data"]["id"]
                    ]);
                    LogUserModel::log($request, "twitter_login", "account_user", $user["id"]);
                }
            }
        }

        # [standard output]
        return $this->output();
    }

    private function checking(array $params = [])
    {
        # [init success condition]
        $this->successTotalCount = 1;

        # [condition]
        if (isset($params["code"])) {
            $res = UserProfileLogic::checkXAuthorization($params["code"]);

            if ($res["success"]) {
                # [condition]
                if (isset($res["data"]["id"])) {
                    $user = AccountUserModel::where("twitter", $res["data"]["id"])->first();

                    // status: normal, inactivated, freezed, suspended
                    if ($user) {
                        if ($user["status"] === "inactivated") {
                            $this->error[] = "account:inactivated";
                        } else if ($user["status"] === "freezed") {
                            $this->error[] = "account:freezed";
                        } else if ($user["status"] === "suspended") {
                            $this->error[] = "account:suspended";
                        } else if ($user["status"] === "active") {
                            $this->successPassedCount++;
                        }
                    } else {
                        //register
                        if (SettingLogic::get("general", ["category" => "maintenance", "code" => "stop_register", "value" => 1])) {
                            $this->error[] = "under_maintenance";
                        } else {
                            $this->successPassedCount++;
                            $register = true;
                        }
                    }
                }
            } else {
                $this->error[] = $res["msg"];
            }
        }

        return [$res ?? false, $register ?? false];
    }
}