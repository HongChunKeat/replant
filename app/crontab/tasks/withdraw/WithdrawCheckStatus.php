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
            $validation = false;

            $network = SettingLogic::get("blockchain_network", ["id" => $transaction["network"]]);
            if (!$network) {
                Log::error("user_withdraw_id:" . $transaction["id"] . " | network:invalid");
            } else {
                try {
                    $receipt = EvmLogic::getTransactionReceipt($network["rpc_url"], $transaction["txid"]);

                    // Check if receipt return is null
                    if (empty($receipt)) {
                        Log::error("user_withdraw_id:" . $transaction["id"] . " | txid:invalid");
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
                            if (!UserWithdrawModel::where(["txid" => $transaction["txid"], "log_index" => $logIndex])->first()) {
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
                                # failed for failed validation, not failed transaction
                                self::fail($transaction, $logIndex);
                                Log::error("user_withdraw_id:" . $transaction["id"] . " | txid:exists");
                            }
                        } else {
                            # failed for failed validation, not failed transaction
                            self::fail($transaction);
                            Log::error("user_withdraw_id:" . $transaction["id"] . " | validation:failed");
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("user_withdraw_id:" . $transaction["id"] . " | " . $e);
                }
            }
        }
    }

    # the only reason of failed validation is only when txid not inserted from cronjob, or someone changed the txid, so in this case wont have refund
    private function fail($transaction, $logIndex = null)
    {
        $failed = SettingLogic::get("operator", ["code" => "failed"]);

        UserWithdrawModel::where("id", $transaction["id"])
            ->update([
                "status" => $failed["id"],
                "log_index" => !empty($logIndex) ? $logIndex : null,
                "completed_at" => date("Y-m-d H:i:s"),
            ]);
    }
}
