<?php

namespace app\crontab\tasks\nft;

# library
use support\Log;
use WebmanTech\CrontabTask\BaseTask;
# database & logic
use app\model\database\UserNftModel;
use app\model\database\UserPointModel;
use app\model\logic\EvmLogic;
use app\model\logic\SettingLogic;

class NftCheckStatus extends BaseTask
{
    public function handle()
    {
        $processing = SettingLogic::get("operator", ["code" => "processing"]);
        $transactions = UserNftModel::where("status", $processing["id"])->whereNotNull("txid")->get();

        foreach ($transactions as $transaction) {
            // reset for each loop
            $success = 0;

            $network = SettingLogic::get("blockchain_network", ["id" => $transaction["network"]]);
            if (!$network) {
                Log::error("user_nft_id:" . $transaction["id"] . " | network:invalid");
            } else {
                try {
                    $receipt = EvmLogic::getTransactionReceipt($network["rpc_url"], $transaction["txid"]);

                    // Check network api response result
                    if (empty($receipt)) {
                        // Check if receipt return is null
                        Log::error("user_nft_id:" . $transaction["id"] . " | txid:invalid");
                    } else {
                        //decode data
                        $data = EvmLogic::decodeTransaction($receipt);
                        $status = $data["status"];
                        $tokenAddress = $data["tokenAddress"];
                        $fromAddress = $data["fromAddress"];
                        $toAddress = $data["toAddress"];
                        $logIndex = $data["logIndex"];

                        if (strtolower($transaction["to_address"]) == $toAddress) {
                            $success++;
                        } else {
                            Log::error("user_nft_id:" . $transaction["id"] . " | to_address:invalid");
                        }

                        if (strtolower($transaction["token_address"]) == $tokenAddress) {
                            $success++;
                        } else {
                            Log::error("user_nft_id:" . $transaction["id"] . " | token_address:invalid");
                        }

                        // Check if txid already exists in database but didnt check status
                        if (!UserNftModel::where(["txid" => $transaction["txid"], "log_index" => $logIndex])->first()) {
                            $success++;
                        } else {
                            Log::error("user_nft_id:" . $transaction["id"] . " | txid:exists");
                        }

                        if ($success == 3) {
                            // 1:success
                            if ($status == 1) {
                                $success = SettingLogic::get("operator", ["code" => "success"]);

                                //nft from_address not from setting address, its 0x0000000000000000000000000000000000000000
                                UserNftModel::where("id", $transaction["id"])
                                    ->update([
                                        "from_address" => $fromAddress,
                                        "status" => $success["id"],
                                        "log_index" => $logIndex,
                                        "completed_at" => date("Y-m-d H:i:s")
                                    ]);
                            } else {
                                # failed for failed transaction
                                self::fail($transaction, $logIndex);
                            }
                        } else {
                            # failed for failed validation, not failed transaction
                            self::fail($transaction, $logIndex);
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("user_nft_id:" . $transaction["id"] . " | " . $e);
                }
            }
        }
    }

    private function fail($transaction, $logIndex)
    {
        $failed = SettingLogic::get("operator", ["code" => "failed"]);

        UserNftModel::where("id", $transaction["id"])
            ->update([
                "status" => $failed["id"],
                "log_index" => $logIndex,
                "completed_at" => date("Y-m-d H:i:s"),
            ]);

        // refund
        if ($transaction["ref_table"] == "user_point") {
            $point = UserPointModel::where("id", $transaction["ref_id"])->first();
            UserPointModel::create([
                "uid" => $transaction["uid"],
                "from_uid" => $transaction["uid"],
                "point" => $point["point"] * -1,
                "source" => "refund_nft"
            ]);
        }
    }
}
