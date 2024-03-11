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
use app\model\database\UserPointModel;
use app\model\logic\SettingLogic;
use app\model\logic\HelperLogic;
use app\model\logic\EvmLogic;

class PurchaseNFT extends Base
{
    public function index(Request $request)
    {
        # user id
        $cleanVars["uid"] = $request->visitor["id"];

        // get and set redis lock
        Redis::get("onboarding_purchase_nft-lock:" . $cleanVars["uid"])
            ? $this->error[] = "onboarding_purchase_nft:lock"
            : Redis::set("onboarding_purchase_nft-lock:" . $cleanVars["uid"], 1);

        # [checking]
        [$user, $point, $timestamp, $message, $signedMessage] = $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error) && ($this->successTotalCount == $this->successPassedCount)) {
            $res = "";

            # [process]
            if (count($cleanVars) > 0) {
                $pending = SettingLogic::get("operator", ["code" => "pending"]);
                $contractId = SettingLogic::get("general", ["code" => "nft_contract_id", "category" => "onboarding"]);
                $contract = SettingLogic::get("nft", ["id" => $contractId["value"]]);

                $deductPoint = UserPointModel::create([
                    "uid" => $cleanVars["uid"],
                    "from_uid" => $cleanVars["uid"],
                    "point" => $point * -1,
                    "source" => "purchase_nft"
                ]);

                $res = UserNftModel::create([
                    "sn" => HelperLogic::generateUniqueSN("user_nft"),
                    "uid" => $cleanVars["uid"],
                    "message" => $message,
                    "signed_message" => $signedMessage,
                    "status" => $pending["id"],
                    "to_address" => $user["web3_address"],
                    "network" => $contract["network"],
                    "token_address" => $contract["token_address"],
                    "ref_table" => "user_point",
                    "ref_id" => $deductPoint["id"]
                ]);
            }

            # [result]
            if ($res) {
                LogUserModel::log($request, "onboarding_purchase_nft");
                $this->response = [
                    "success" => true,
                    "data" => [
                        "sn" => $res["sn"],
                        "address" => $user["web3_address"],
                        "point" => $point * 1,
                        "timestamp" => $timestamp * 1,
                        "signed_message" => $signedMessage
                    ],
                ];
            }
        }

        // remove redis lock
        Redis::del("onboarding_purchase_nft-lock:" . $cleanVars["uid"]);

        # [standard output]
        return $this->output();
    }

    private function checking(array $params = [])
    {
        # [init success condition]
        $this->successTotalCount = 6;

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

                $point = SettingLogic::get("general", ["code" => "nft_price", "category" => "onboarding"]);
                if (!$point) {
                    $this->error[] = "setting:missing";
                } else {
                    $this->successPassedCount++;
                    $pending = SettingLogic::get("operator", ["code" => "pending"]);
                    $processing = SettingLogic::get("operator", ["code" => "processing"]);
                    $success = SettingLogic::get("operator", ["code" => "success"]);

                    // check if there is any user point nft that is pending, processing, or success
                    $exist = UserNftModel::where(["uid" => $params["uid"], "ref_table" => "user_point"])
                        ->whereIn("status", [$pending["id"], $processing["id"], $success["id"]])
                        ->first();
                    if ($exist) {
                        $this->error[] = "nft:already_purchased";
                    } else {
                        $this->successPassedCount++;
                        $pointBalance = UserPointModel::where("uid", $params["uid"])->sum("point");
                        if ($point["value"] > $pointBalance) {
                            $this->error[] = "point:insufficient";
                        } else {
                            $this->successPassedCount++;
                            $timestamp = time();
                            $message = strtolower($user["web3_address"]) . "_" . $point["value"] . "_" . $timestamp;

                            $signedMessage = EvmLogic::signMessage($message);
                            if (!$signedMessage) {
                                $this->error[] = "signed_message:unable_to_sign";
                            } else {
                                $this->successPassedCount++;
                            }
                        }
                    }
                }
            }
        }

        return [$user ?? 0, $point["value"] ?? 0, $timestamp ?? 0, $message ?? 0, $signedMessage ?? 0];
    }
}