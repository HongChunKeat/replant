<?php

namespace plugin\dapp\app\controller\wallet;

# library
use plugin\dapp\app\controller\Base;
use support\Request;
use support\Redis;
# database & logic
use app\model\database\LogUserModel;
use app\model\database\AccountUserModel;
use app\model\database\UserDepositModel;
use app\model\logic\HelperLogic;
use app\model\logic\SettingLogic;

class Deposit extends Base
{
    # [validation-rule]
    protected $rule = [
        "id" => "require|number|max:11",
        "txid" => "require|min:60|max:70|alphaNum",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "id",
        "txid"
    ];

    public function index(Request $request)
    {
        // check maintenance
        $stop_deposit = SettingLogic::get("general", ["category" => "maintenance", "code" => "stop_deposit", "value" => 1]);
        if ($stop_deposit) {
            $this->error[] = "under_maintenance";
            return $this->output();
        }

        # [validation]
        $this->validation($request->post(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->post(), $this->patternInputs);

        # user id
        $cleanVars["uid"] = $request->visitor["id"];

        // get and set redis lock
        Redis::get("deposit-lock:" . $cleanVars["uid"])
            ? $this->error[] = "deposit:lock"
            : Redis::set("deposit-lock:" . $cleanVars["uid"], 1);

        # [checking]
        [$tokenAddress, $fromAddress, $toAddress, $network, $coinId] = $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error) && ($this->successTotalCount == $this->successPassedCount)) {
            # [process]
            if (count($cleanVars) > 0) {
                $processing = SettingLogic::get("operator", ["code" => "processing"]);

                UserDepositModel::create([
                    "sn" => HelperLogic::generateUniqueSN("user_deposit"),
                    "uid" => $cleanVars["uid"],
                    "status" => $processing["id"],
                    "coin_id" => $coinId,
                    "txid" => $cleanVars["txid"],
                    "from_address" => $fromAddress,
                    "to_address" => $toAddress,
                    "network" => $network,
                    "token_address" => $tokenAddress,
                ]);

                LogUserModel::log($request, "deposit");

                # [result]
                $this->response = [
                    "success" => true
                ];
            }
        }

        // remove redis lock
        Redis::del("deposit-lock:" . $cleanVars["uid"]);

        # [standard output]
        return $this->output();
    }

    private function checking(array $params = [])
    {
        # [init success condition]
        $this->successTotalCount = 5;

        # [condition]
        if (isset($params["uid"]) && isset($params["id"]) && isset($params["txid"])) {
            // check uid exist
            $user = AccountUserModel::where(["id" => $params["uid"], "status" => "active"])->first();
            if (!$user) {
                $this->error[] = "user:missing";
            } else {
                $this->successPassedCount++;
                // must have web 3 address
                if (empty($user["web3_address"])) {
                    $this->error[] = "user:no_web3_address";
                } else {
                    $this->successPassedCount++;
                    // Check if txid format is valid (starts with "0x")
                    if ($params["txid"] != "" && str_starts_with($params["txid"], "0x") === false) {
                        $this->error[] = "txid:invalid_format";
                    } else {
                        $this->successPassedCount++;
                    }

                    $settingDeposit = SettingLogic::get("deposit", ["id" => $params["id"], "is_active" => 1]);
                    // Check if setting_deposit is_active based on the provided id
                    if (!$settingDeposit) {
                        $this->error[] = "deposit:invalid";
                    } else {
                        $this->successPassedCount++;
                        $network = SettingLogic::get("blockchain_network", ["id" => $settingDeposit["network"]]);
                        // Check if setting_blockchain_network is based on the provided setting_deposit network
                        if (!$network) {
                            $this->error[] = "network:invalid";
                        } else {
                            $this->successPassedCount++;
                        }
                    }
                }
            }
        }

        // Returning the value of "uid" from $params array if it exists, otherwise default to 0
        return [
            $settingDeposit["token_address"] ?? "",
            $user["web3_address"] ?? "",
            $settingDeposit["address"] ?? "",
            $network["id"] ?? 0,
            $settingDeposit["coin_id"] ?? "",
        ];
    }
}
