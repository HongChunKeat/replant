<?php

namespace app\crontab\tasks\deposit;

# library
use support\Log;
use Webman\RedisQueue\Redis as RedisQueue;
use WebmanTech\CrontabTask\BaseTask;
# database & logic
use app\model\database\UserDepositModel;
use app\model\logic\EvmLogic;
use app\model\logic\SettingLogic;

class DepositCheckStatus extends BaseTask
{
    public function handle()
    {
        $processing = SettingLogic::get("operator", ["code" => "processing"]);
        $transactions = UserDepositModel::where("status", $processing["id"])->whereNotNull("txid")->get();

        foreach ($transactions as $transaction) {
            // reset for each loop
            $success = 0;

            $network = SettingLogic::get("blockchain_network", ["id" => $transaction["network"]]);
            if (!$network) {
                Log::error("user_deposit_id:" . $transaction["id"] . " | network:invalid");
            } else {
                try {
                    $receipt = EvmLogic::getTransactionReceipt($network["rpc_url"], $transaction["txid"]);

                    // Check network api response result
                    if (empty($receipt)) {
                        // Check if receipt return is null
                        Log::error("user_deposit_id:" . $transaction["id"] . " | txid:invalid");
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
                            Log::error("user_deposit_id:" . $transaction["id"] . " | amount:invalid");
                        }

                        if (strtolower($transaction["from_address"]) == $fromAddress) {
                            $success++;
                        } else {
                            Log::error("user_deposit_id:" . $transaction["id"] . " | from_address:invalid");
                        }

                        if (strtolower($transaction["to_address"]) == $toAddress) {
                            $success++;
                        } else {
                            Log::error("user_deposit_id:" . $transaction["id"] . " | to_address:invalid");
                        }

                        if (strtolower($transaction["token_address"]) == $tokenAddress) {
                            $success++;
                        } else {
                            Log::error("user_deposit_id:" . $transaction["id"] . " | token_address:invalid");
                        }

                        // Check if txid already exists in database but didnt check status
                        if (!UserDepositModel::where(["txid" => $transaction["txid"], "log_index" => $logIndex])->first()) {
                            $success++;
                        } else {
                            Log::error("user_deposit_id:" . $transaction["id"] . " | txid:exists");
                        }

                        if ($success == 5) {
                            # check transaction status pass or failed in queue
                            RedisQueue::send("user_wallet", [
                                "type" => "deposit",
                                "data" => [
                                    "deposit" => $transaction,
                                    "status" => $status,
                                    "amount" => $amount,
                                    "tokenAddress" => $tokenAddress,
                                    "fromAddress" => $fromAddress,
                                    "toAddress" => $toAddress,
                                    "logIndex" => $logIndex,
                                ]
                            ]);
                        } else {
                            # failed for failed validation, not failed transaction
                            $failed = SettingLogic::get("operator", ["code" => "failed"]);

                            UserDepositModel::where("id", $transaction["id"])
                                ->update([
                                    "amount" => $amount,
                                    "status" => $failed["id"],
                                    "log_index" => $logIndex,
                                    "completed_at" => date("Y-m-d H:i:s"),
                                ]);
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("user_deposit_id:" . $transaction["id"] . " | " . $e);
                }
            }
        }
    }
}
