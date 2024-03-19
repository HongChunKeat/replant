<?php

namespace plugin\dapp\app\controller\auth;

# library
use plugin\dapp\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogUserModel;
use app\model\database\AccountUserModel;
use app\model\database\UserNftModel;
use app\model\logic\SettingLogic;
use app\model\logic\HelperLogic;
use plugin\dapp\app\model\logic\UserProfileLogic;

class Ask extends Base
{
    # [validation-rule]
    protected $rule = [
        "address" => "require|length:42|alphaNum",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "address"
    ];

    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->get(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->get(), $this->patternInputs);

        # [checking]
        [$register, $seed] = $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error) && ($this->successTotalCount == $this->successPassedCount)) {
            $res = "";

            # [process]
            if (count($cleanVars) > 0) {
                if ($register && $cleanVars["address"] != "0x0000000000000000000000000000000000000000") {
                    // register for newly minted seed is only in phase 1
                    $user = AccountUserModel::create([
                        "user_id" => HelperLogic::generateUniqueSN("account_user"),
                        "web3_address" => $cleanVars["address"],
                    ]);

                    // addon missing info in user nft
                    if ($user) {
                        UserNftModel::where("id", $seed["id"])->update(["uid" => $user["id"], "ref_id" => $user["id"]]);
                        UserProfileLogic::init($user["id"]);

                        //referral module - direct bind to user 1
                        UserProfileLogic::bindUpline($user["id"], 1);
                    }
                }

                $res = UserProfileLogic::newAuthKey($cleanVars["address"]);
            }

            if ($res) {
                $user = AccountUserModel::where("web3_address", $cleanVars["address"])->first();
                LogUserModel::log($request, "web3_request", "account_user", $user["id"]);
                $this->response = [
                    "success" => true,
                    "data" => $res,
                ];
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
        if (isset($params["address"])) {
            $user = AccountUserModel::where("web3_address", $params["address"])->first();

            // status: active, inactivated, freezed, suspended
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
                    // register for newly minted seed is only in phase 1
                    $phaseOpen = SettingLogic::get("general", ["category" => "version", "code" => "phase_1", "value" => 1]);
                    if (!$phaseOpen) {
                        $this->error[] = "not_available";
                    } else {
                        $seedSetting = SettingLogic::get("nft", ["name" => "plant"]);
                        if (!$seedSetting) {
                            $this->error[] = "setting:missing";
                        } else {
                            // check got user that havent register or not, if uid and ref_id = 0 then is havent register
                            $seed = UserNftModel::where([
                                "uid" => 0,
                                "from_address" => "0x0000000000000000000000000000000000000000",
                                "to_address" => strtolower($params["address"]),
                                "network" => $seedSetting["network"],
                                "token_address" => $seedSetting["token_address"],
                                "ref_table" => "account_user",
                                "ref_id" => 0
                            ])->first();

                            if (!$seed) {
                                $this->error[] = "seed:not_found";
                            } else {
                                $this->successPassedCount++;
                                $register = true;
                            }
                        }
                    }
                }
            }
        }

        return [$register ?? false, $seed ?? 0];
    }
}
