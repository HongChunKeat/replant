<?php

namespace app\crontab\tasks\nft;

# library
use support\Log;
use WebmanTech\CrontabTask\BaseTask;
# database & logic
use app\model\database\UserNftModel;
use app\model\database\AccountUserModel;
use app\model\database\UserInviteCodeModel;
use plugin\dapp\app\model\logic\UserProfileLogic;
use app\model\logic\EvmLogic;
use app\model\logic\SettingLogic;
use app\model\logic\HelperLogic;

class NftCheckStatus extends BaseTask
{
    public function handle()
    {
        $processing = SettingLogic::get("operator", ["code" => "processing"]);
        $transactions = UserNftModel::where("status", $processing["id"])->whereNotNull("txid")->get();

        foreach ($transactions as $transaction) {
            // reset for each loop
            $validation = false;

            $network = SettingLogic::get("blockchain_network", ["id" => $transaction["network"]]);
            if (!$network) {
                Log::error("user_nft_id:" . $transaction["id"] . " | network:invalid");
            } else {
                try {
                    $receipt = EvmLogic::getTransactionReceipt($network["rpc_url"], $transaction["txid"]);

                    // Check if receipt return is null
                    if (empty($receipt)) {
                        Log::error("user_nft_id:" . $transaction["id"] . " | txid:invalid");
                    } else {
                        $status = hexdec($receipt["status"]); // 1:success

                        // loop each log check whether got met requirement or not
                        for ($i = 0; $i < count($receipt["logs"]); $i++) {
                            $data = [];
                            //decode data
                            $data = EvmLogic::decodeTransaction($receipt, $i);
                            if (
                                $data["action"] == "0xddf252ad1be2c89b69c2b068fc378daa952ba7f163c4a11628f55a4df523b3ef" &&
                                strtolower($transaction["token_address"]) == $data["tokenAddress"] &&
                                strtolower($transaction["to_address"]) == $data["toAddress"]
                            ) {
                                $validation = true;
                                $amount = $data["amount"];
                                $tokenAddress = $data["tokenAddress"];
                                $fromAddress = $data["fromAddress"];
                                $toAddress = $data["toAddress"];
                                $logIndex = $data["logIndex"];
                            }
                        }

                        // if validation true
                        if ($validation) {
                            // Check if txid already exists in database
                            if (!UserNftModel::where(["txid" => $transaction["txid"], "log_index" => $logIndex])->first()) {
                                // 1:success
                                if ($status == 1) {
                                    $success = SettingLogic::get("operator", ["code" => "success"]);

                                    //nft mint from_address not from setting address, its 0x0000000000000000000000000000000000000000
                                    if ($transaction["ref_table"] == "user_invite_code") {
                                        // deduct usage
                                        $inviteCode = UserInviteCodeModel::where("id", $transaction["ref_id"])->first();
                                        if ($inviteCode) {
                                            UserInviteCodeModel::where("id", $inviteCode["id"])->update([
                                                "usage" => $inviteCode["usage"] - 1
                                            ]);
                                        }

                                        // register
                                        $user = AccountUserModel::create([
                                            "user_id" => HelperLogic::generateUniqueSN("account_user"),
                                            "web3_address" => $transaction["to_address"],
                                        ]);

                                        if ($user) {
                                            UserNftModel::where("id", $transaction["id"])
                                                ->update([
                                                    "uid" => $user["id"],
                                                    "from_address" => $fromAddress,
                                                    "status" => $success["id"],
                                                    "log_index" => $logIndex,
                                                    "completed_at" => date("Y-m-d H:i:s")
                                                ]);

                                            UserProfileLogic::init($user["id"]);

                                            //referral module
                                            UserProfileLogic::bindUpline($user["id"], $inviteCode["uid"]);
                                        }
                                    }
                                } else {
                                    # failed for failed transaction
                                    self::fail($transaction, $logIndex, $fromAddress);
                                }
                            } else {
                                # failed for failed validation, not failed transaction
                                self::fail($transaction, $logIndex, $fromAddress);
                                Log::error("user_nft_id:" . $transaction["id"] . " | txid:exists");
                            }
                        } else {
                            # failed for failed validation, not failed transaction
                            self::fail($transaction);
                            Log::error("user_nft_id:" . $transaction["id"] . " | validation:failed");
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("user_nft_id:" . $transaction["id"] . " | " . $e);
                }
            }
        }
    }

    private function fail($transaction, $logIndex = null, $fromAddress = null)
    {
        $failed = SettingLogic::get("operator", ["code" => "failed"]);

        UserNftModel::where("id", $transaction["id"])
            ->update([
                "from_address" => !empty($fromAddress) ? $fromAddress : null,
                "status" => $failed["id"],
                "log_index" => !empty($logIndex) ? $logIndex : null,
                "completed_at" => date("Y-m-d H:i:s"),
            ]);
    }
}
