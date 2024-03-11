<?php

namespace plugin\dapp\app\controller\onboarding;

# library
use plugin\dapp\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\AccountUserModel;
use app\model\database\UserNftModel;
use app\model\database\UserPointModel;
use app\model\logic\SettingLogic;

class NftCheck extends Base
{
    public function index(Request $request)
    {
        # user id
        $cleanVars["uid"] = $request->visitor["id"];

        # [checking]
        [$user] = $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error) && ($this->successTotalCount == $this->successPassedCount)) {
            # [process]
            if (count($cleanVars) > 0) {
                $pending = SettingLogic::get("operator", ["code" => "pending"]);

                // check if this user got pending user point nft or not
                $userNft = UserNftModel::where(["uid" => $cleanVars["uid"], "status" => $pending["id"], "ref_table" => "user_point"])
                    ->whereNull("txid")
                    ->first();

                if ($userNft) {
                    $oldMessage = explode("_", $userNft["message"]);
                    $userPoint = UserPointModel::where("id", $userNft["ref_id"])->first();
                    $point = SettingLogic::get("general", ["code" => "nft_price", "category" => "onboarding"]);

                    # [result]
                    $this->response = [
                        "success" => true,
                        "data" => [
                            "sn" => $userNft["sn"],
                            "address" => $user["web3_address"],
                            "point" => $userPoint
                                ? $userPoint["point"] * -1
                                : $point["value"] * 1,
                            "timestamp" => $oldMessage[2] * 1,
                            "signed_message" => $userNft["signed_message"]
                        ],
                    ];
                }
            }
        }

        # [standard output]
        return $this->output();
    }

    private function checking(array $params = [])
    {
        # [init success condition]
        $this->successTotalCount = 2;

        # [condition]
        if (isset($params["uid"])) {
            // check uid exist
            $user = AccountUserModel::where(["id" => $params["uid"], "status" => "active"])->first();
            if (!$user) {
                $this->error[] = "user:missing";
            } else {
                $this->successPassedCount++;
                if (empty($user["web3_address"])) {
                    $this->error[] = "user:no_web3_address";
                } else {
                    $this->successPassedCount++;
                }
            }
        }

        return [$user ?? 0];
    }
}