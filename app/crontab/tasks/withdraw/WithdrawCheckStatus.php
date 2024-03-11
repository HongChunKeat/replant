<?php

namespace app\crontab\tasks\withdraw;

# library
use support\Log;
use Webman\RedisQueue\Redis as RedisQueue;
use WebmanTech\CrontabTask\BaseTask;
# database & logic
use app\model\database\UserWithdrawModel;
use app\model\logic\EvmLogic;
use app\model\logic\SettingLogic;

class WithdrawCheckStatus extends BaseTask
{
    public function handle()
    {
        $processing = SettingLogic::get("operator", ["code" => "processing"]);
        $transactions = UserWithdrawModel::where("status", $processing["id"])->whereNotNull("txid")->get();

        foreach ($transactions as $transaction) {
            // reset for each loop
            $success = 0;

            $network = SettingLogic::get("blockchain_network", ["id" => $transaction["network"]]);
            if (!$network) {
                Log::error("user_withdraw_id:" . $transaction["id"] . " | network:invalid");
            } else {
                try {
                    $receipt = EvmLogic::getTransactionReceipt($network["rpc_url"], $transaction["txid"]);

                    // Check network api response result
                    if (empty($receipt)) {
                        // Check if receipt return is null
                        Log::error("user_withdraw_id:" . $transaction["id"] . " | txid:invalid");
                    } else {
                        //decode data
                        $data = EvmLogic::decodeTransaction($receipt);
                        $status = $data["status"];
                        $amount = $data["amount"];
                        $tokenAddress = $data["tokenAddress"];
                        $fromAddress = $data["fromAddress"];
                        $toAddress = $data["toAddress"];
                        $logIndex = $data["logIndex"];

                        // Check amount valid or not
                        if ($amount > 0) {
                            $success++;
                        } else {
                            Log::error("user_withdraw_id:" . $transaction["id"] . " | amount:invalid");
                        }

                        if (strtolower($transaction["from_address"]) == $fromAddress) {
                            $success++;
                        } else {
                            Log::error("user_withdraw_id:" . $transaction["id"] . " | from_address:invalid");
                        }

                        if (strtolower($transaction["to_address"]) == $toAddress) {
                            $success++;
                        } else {
                            Log::error("user_withdraw_id:" . $transaction["id"] . " | to_address:invalid");
                        }

                        if (strtolower($transaction["token_address"]) == $tokenAddress) {
                            $success++;
                        } else {
                            Log::error("user_withdraw_id:" . $transaction["id"] . " | token_address:invalid");
                        }

                        // Check if txid already exists in database but didnt check status
                        if (!UserWithdrawModel::where(["txid" => $transaction["txid"], "log_index" => $logIndex])->first()) {
                            $success++;
                        } else {
                            Log::error("user_withdraw_id:" . $transaction["id"] . " | txid:exists");
                        }

                        if ($success == 5) {
                            // 1:success
                            if ($status == 1) {
                                $success = SettingLogic::get("operator", ["code" => "success"]);

                                UserWithdrawModel::where("id", $transaction["id"])
                                    ->update([
                                        "status" => $success["id"],
                                        "log_index" => $logIndex,
                                        "completed_at" => date("Y-m-d H:i:s")
                                    ]);
                            } else {
                                # failed for failed transaction
                                RedisQueue::send("user_wallet", [
                                    "type" => "withdrawRefund",
                                    "data" => [
                                        "id" => $transaction["id"],
                                        "logIndex" => $logIndex
                                    ]
                                ]);
                            }
                        } else {
                            # the only reason of failed validation is only when txid not inserted from cronjob, or someone changed the txid, so in this case wont have refund
                            # failed for failed validation, not failed transaction
                            $failed = SettingLogic::get("operator", ["code" => "failed"]);

                            UserWithdrawModel::where("id", $transaction["id"])
                                ->update([
                                    "status" => $failed["id"],
                                    "log_index" => $logIndex,
                                    "completed_at" => date("Y-m-d H:i:s")
                                ]);
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("user_withdraw_id:" . $transaction["id"] . " | " . $e);
                }
            }
        }
    }
}
