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
            $validation = false;

            $network = SettingLogic::get("blockchain_network", ["id" => $transaction["network"]]);
            if (!$network) {
                Log::error("user_deposit_id:" . $transaction["id"] . " | network:invalid");
            } else {
                try {
                    $receipt = EvmLogic::getTransactionReceipt($network["rpc_url"], $transaction["txid"]);

                    // Check if receipt return is null
                    if (empty($receipt)) {
                        Log::error("user_deposit_id:" . $transaction["id"] . " | txid:invalid");
                    } else {
                        $status = hexdec($receipt["status"]); // 1:success

                        // loop each log check whether got met requirement or not
                        for ($i = 0; $i < count($receipt["logs"]); $i++) {
                            $data = [];
                            //decode data
                            $data = EvmLogic::decodeTransaction($receipt, $i);
                            if (
                                $data["action"] == "0xddf252ad1be2c89b69c2b068fc378daa952ba7f163c4a11628f55a4df523b3ef" &&
                                $data["amount"] > 0 &&
                                strtolower($transaction["token_address"]) == $data["tokenAddress"] &&
                                strtolower($transaction["from_address"]) == $data["fromAddress"] &&
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
                            if (!UserDepositModel::where(["txid" => $transaction["txid"], "log_index" => $logIndex])->first()) {
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
                                Log::error("user_deposit_id:" . $transaction["id"] . " | txid:exists");
                            }
                        } else {
                            # failed for failed validation, not failed transaction
                            self::fail($transaction);
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("user_deposit_id:" . $transaction["id"] . " | " . $e);
                }
            }
        }
    }

    private function fail($transaction)
    {
        $failed = SettingLogic::get("operator", ["code" => "failed"]);

        UserDepositModel::where("id", $transaction["id"])
            ->update([
                "status" => $failed["id"],
                "completed_at" => date("Y-m-d H:i:s"),
            ]);
    }
}
