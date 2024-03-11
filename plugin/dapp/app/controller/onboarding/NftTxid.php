<?php

namespace plugin\dapp\app\controller\onboarding;

# library
use plugin\dapp\app\controller\Base;
use support\Request;
use support\Redis;
# database & logic
use app\model\database\LogUserModel;
use app\model\database\AccountUserModel;
use app\model\database\UserNftModel;
use app\model\logic\SettingLogic;
use app\model\logic\HelperLogic;

class NftTxid extends Base
{
    # [validation-rule]
    protected $rule = [
        "sn" => "require",
        "txid" => "require|min:60|max:70|alphaNum"
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "sn",
        "txid",
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
        Redis::get("onboarding_nft_txid-lock:" . $cleanVars["uid"])
            ? $this->error[] = "onboarding_nft_txid:lock"
            : Redis::set("onboarding_nft_txid-lock:" . $cleanVars["uid"], 1);

        # [checking]
        $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error) && ($this->successTotalCount == $this->successPassedCount)) {
            $res = "";

            # [process]
            if (count($cleanVars) > 0) {
                $processing = SettingLogic::get("operator", ["code" => "processing"]);

                $res = UserNftModel::where("sn", $cleanVars["sn"])
                    ->update([
                        "status" => $processing["id"],
                        "txid" => $cleanVars["txid"],
                    ]);
            }

            # [result]
            if ($res) {
                LogUserModel::log($request, "onboarding_nft_txid");
                $this->response = [
                    "success" => true,
                ];
            }
        }

        // remove redis lock
        Redis::del("onboarding_nft_txid-lock:" . $cleanVars["uid"]);

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

        // check sn exist
        if (isset($params["sn"])) {
            $nft = UserNftModel::where("sn", $params["sn"])->first();
            if (!$nft) {
                $this->error[] = "nft:missing";
            } else {
                $this->successPassedCount++;
                $pending = SettingLogic::get("operator", ["code" => "pending"]);
                if ($nft["status"] != $pending["id"] || !empty($nft["txid"])) {
                    $this->error[] = "nft:invalid";
                } else {
                    $this->successPassedCount++;
                }
            }
        }

        // Check if txid format is valid (starts with "0x")
        if (isset($params["txid"])) {
            if (!empty($params["txid"]) && str_starts_with($params["txid"], "0x") === false) {
                $this->error[] = "txid:invalid_format";
            } else {
                $this->successPassedCount++;
            }
        }
    }
}