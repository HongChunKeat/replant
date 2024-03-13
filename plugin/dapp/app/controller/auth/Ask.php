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
use app\model\logic\EvmLogic;
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
        [$register] = $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error) && ($this->successTotalCount == $this->successPassedCount)) {
            // if ($register && $cleanVars["address"] != "0x0000000000000000000000000000000000000000") {
            //     $user = AccountUserModel::create([
            //         "user_id" => HelperLogic::generateUniqueSN("account_user"),
            //         "web3_address" => $cleanVars["address"],
            //     ]);

            //     UserProfileLogic::init($user["id"]);
            // }

            // $res = UserProfileLogic::newAuthKey($cleanVars["address"]);

            // if ($res) {
            //     $this->response = [
            //         "success" => true,
            //         "data" => $res,
            //     ];

            //     $user = AccountUserModel::where("web3_address", $cleanVars["address"])->first();
            //     LogUserModel::log($request, "web3_request", "account_user", $user["id"]);
            // }
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
                    // check seed nft count, if have then allow register
                    $seedNft = SettingLogic::get("nft", ["name" => "seed"]);
                    if (!$seedNft) {
                        $this->error[] = "nft:not_found";
                    } else {
                        $network = SettingLogic::get("blockchain_network", ["id" => $seedNft["network"]]);
                        if (!$network) {
                            $this->error[] = "network:not_found";
                        } else {
                            $nftCount = EvmLogic::getBalance("nft", $network["rpc_url"], $seedNft["token_address"], $params["address"]);
                            if ($nftCount <= 0) {
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

        return [$register ?? false];
    }
}
