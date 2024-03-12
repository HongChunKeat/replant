<?php

namespace app\crontab\tasks\withdraw;

# library
use support\Log;
use support\Redis;
use Webman\RedisQueue\Redis as RedisQueue;
use WebmanTech\CrontabTask\BaseTask;
# database & logic
use app\model\database\UserWithdrawModel;
use app\model\database\SettingWithdrawModel;
use app\model\logic\EvmLogic;
use app\model\logic\HelperLogic;
use app\model\logic\SettingLogic;

class WithdrawApprove extends BaseTask
{
    public function handle()
    {
        // only if txid and log_index are empty, because create must have txid and log_index cant enter from create or update
        // valid withdraw = txid and log_index null
        $accepted = SettingLogic::get("operator", ["code" => "accepted"]);
        $transactions = UserWithdrawModel::where("status", $accepted["id"])
            ->whereNull("txid")
            ->whereNull("log_index")
            ->get();

        foreach ($transactions as $transaction) {
            // reset for each loop
            $success = 0;

            $settingWithdraw = SettingWithdrawModel::where([
                "coin_id" => $transaction["to_coin_id"],
                "address" => $transaction["from_address"],
                "token_address" => $transaction["token_address"]
            ])->first();

            if ($settingWithdraw) {
                try {
                    $network = SettingLogic::get("blockchain_network", ["id" => $transaction["network"]]);

                    $tokenDecimal = EvmLogic::getDecimals($network["rpc_url"], $transaction["token_address"]);
                    $transferAmount = bcmul($transaction["amount"], bcpow("10", $tokenDecimal)); //amount times 10 power 18

                    if ($transferAmount > 0) {
                        $success++;
                    } else {
                        Log::error("user_withdraw_id:" . $transaction["id"] . " | transferAmount:invalid");
                    }

                    if ($success == 1) {
                        // Send to blockchain network to generate txid
                        $txId = EvmLogic::transfer(
                            $network["rpc_url"],
                            $network["chain_id"],
                            $transferAmount,
                            $transaction["token_address"],
                            $transaction["from_address"],
                            HelperLogic::decrypt($settingWithdraw["private_key"]),
                            $transaction["to_address"]
                        );

                        if (!empty($txId)) {
                            $statusProcessing = SettingLogic::get("operator", ["code" => "processing"]);

                            UserWithdrawModel::where("id", $transaction["id"])
                                ->update(["status" => $statusProcessing["id"], "txid" => $txId]);

                            Redis::del("withdraw_approve_error:" . $transaction["id"]);
                        } else {
                            // retry 5 times status failed function
                            $numErr = Redis::get("withdraw_approve_error:" . $transaction["id"]);
                            Redis::set("withdraw_approve_error:" . $transaction["id"], $numErr + 1);

                            Log::error("user_withdraw_id:" . $transaction["id"] . " | empty txid");
                        }
                    }
                } catch (\Exception $e) {
                    // retry 5 times status failed function
                    $numErr = Redis::get("withdraw_approve_error:" . $transaction["id"]);
                    Redis::set("withdraw_approve_error:" . $transaction["id"], $numErr + 1);

                    Log::error("user_withdraw_id:" . $transaction["id"] . " | " . $e);
                }
            } else {
                Log::error("user_withdraw_id:" . $transaction["id"] . " | setting:missing");
            }

            // failed if retry more than 5 times
            if (Redis::get("withdraw_approve_error:" . $transaction["id"]) > 5) {
                # [process with queue]
                RedisQueue::send("user_wallet", [
                    "type" => "withdrawRefund",
                    "data" => [
                        "id" => $transaction["id"]
                    ]
                ]);

                Redis::del("withdraw_approve_error:" . $transaction["id"]);
            }
        }
    }
}
