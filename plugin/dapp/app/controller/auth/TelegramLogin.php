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

class TelegramLogin extends Base
{
    public function index(Request $request)
    {
        # [checking]
        [$res, $register] = $this->checking($request->rawBody());

        # [proceed]
        if (!count($this->error) && ($this->successTotalCount == $this->successPassedCount)) {
            if ($res && $res["success"]) {
                if ($register) {
                    $user = AccountUserModel::create([
                        "user_id" => HelperLogic::generateUniqueSN("account_user"),
                        "telegram" => $res["data"]["id"],
                    ]);
                    UserProfileLogic::init($user["id"]);

                    // do mission
                    MissionLogic::missionProgress($user["id"], ["name" => "link telegram"]);
                }

                $user = AccountUserModel::where("telegram", $res["data"]["id"])->first();

                if ($user) {
                    $accessJWT = [
                        "id" => $user["user_id"],
                        "nickname" => $user["nickname"],
                    ];

                    $this->response = [
                        "success" => true,
                        "data" => UserProfileLogic::newAccessToken($user["user_id"], $accessJWT),
                    ];

                    // name
                    if (isset($res["data"]["first_name"]) || isset($res["data"]["last_name"])) {
                        $firstName = isset($res["data"]["first_name"])
                            ? $res["data"]["first_name"]
                            : "";
                        $lastName = isset($res["data"]["last_name"])
                            ? $res["data"]["last_name"]
                            : "";

                        $name = $firstName . " " . $lastName;
                    } else if (isset($res["data"]["username"])) {
                        $name = $res["data"]["username"];
                    } else {
                        $name = $res["data"]["id"];
                    }

                    AccountUserModel::where("id", $user["id"])->update([
                        "authenticator" => "telegram",
                        "telegram_name" => $name
                    ]);
                    LogUserModel::log($request, "telegram_login", "account_user", $user["id"]);
                }
            }
        }

        # [standard output]
        return $this->output();
    }

    private function checking($params = null)
    {
        # [init success condition]
        $this->successTotalCount = 1;

        if ($params != null) {
            $data = json_decode($params, true);
            $res = UserProfileLogic::checkTelegramAuthorization($data);

            if ($res["success"]) {
                # [condition]
                if (isset($res["data"]["id"])) {
                    $user = AccountUserModel::where("telegram", $res["data"]["id"])->first();

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