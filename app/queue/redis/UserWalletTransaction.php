<?php

namespace app\queue\redis;

# system lib
use Webman\RedisQueue\Consumer;
# database & logic
use app\model\database\AccountUserModel;
use app\model\database\UserDepositModel;
use app\model\database\UserWithdrawModel;
use app\model\database\UserSeedModel;
use app\model\database\SettingGeneralModel;
use plugin\admin\app\model\logic\RewardLogic;
use plugin\dapp\app\model\logic\UserWalletLogic;
use app\model\logic\EvmLogic;
use app\model\logic\SettingLogic;
use app\model\logic\HelperLogic;

class UserWalletTransaction implements Consumer
{
    // queue name
    public $queue = "user_wallet";

    // connection name refer config/plugin/webman/redis-queue/redis.php
    public $connection = "default";

    // process
    public function consume($queue)
    {
        switch ($queue["type"]) {
            case "deposit":
                $this->deposit($queue["data"]);
                break;
            case "withdraw":
                $this->withdraw($queue["data"]);
                break;
            case "withdrawRefund":
                $this->withdrawRefund($queue["data"]);
                break;
            case "swap":
                $this->swap($queue["data"]);
                break;
            case "transfer":
                $this->transfer($queue["data"]);
                break;
            case "claimSeedPoint":
                $this->claimSeedPoint($queue["data"]);
                break;
            case "treeUpgrade":
                $this->treeUpgrade($queue["data"]);
                break;
        }
    }

    private function deposit($data)
    {
        $deposit = $data["deposit"];
        $status = $data["status"];
        $amount = $data["amount"];
        $tokenAddress = $data["tokenAddress"];
        $fromAddress = $data["fromAddress"];
        $toAddress = $data["toAddress"];
        $logIndex = $data["logIndex"];

        $success = 0;

        // Check amount valid or not
        if ($amount > 0) {
            $success++;
        }

        // Check if current user address same as from address
        if (strtolower($deposit["from_address"]) == $fromAddress) {
            $success++;
        }

        // Check setting_deposit to_address based on the provided api result
        if (strtolower($deposit["to_address"]) == $toAddress) {
            $success++;
        }

        // Check setting_deposit token_address based on the provided api result
        if (strtolower($deposit["token_address"]) == $tokenAddress) {
            $success++;
        }

        // Check if txid already exists in user_deposit but didnt check status
        $userDeposit = UserDepositModel::where(["txid" => $deposit["txid"], "log_index" => $logIndex])->first();
        if (!$userDeposit) {
            $success++;
        }

        if ($success == 5) {
            // get setting id
            $success = SettingLogic::get("operator", ["code" => "success"]);
            $failed = SettingLogic::get("operator", ["code" => "failed"]);
            $topUp = SettingLogic::get("operator", ["code" => "top_up"]);

            // pass or failed by checking the transaction status
            UserDepositModel::where("id", $deposit["id"])
                ->update([
                    "amount" => $amount,
                    "status" => $status == 1 ? $success["id"] : $failed["id"],
                    "log_index" => $logIndex,
                    "completed_at" => date("Y-m-d H:i:s"),
                ]);

            // if success then add balance
            if ($status == 1) {
                //find wallet id from the coin
                $coin = SettingLogic::get("coin", ["id" => $deposit["coin_id"]]);

                UserWalletLogic::add([
                    "type" => $topUp["id"],
                    "uid" => $deposit["uid"],
                    "fromUid" => $deposit["uid"],
                    "toUid" => $deposit["uid"],
                    "distribution" => [$coin["wallet_id"] => round($amount, 8)],
                    "refTable" => "user_deposit",
                    "refId" => $deposit["id"],
                ]);
            }
        }
    }

    private function withdraw($data)
    {
        $uid = $data["uid"];
        $amount = $data["amount"];
        $coinId = $data["coinId"];
        $fromAddress = $data["fromAddress"];
        $toAddress = $data["toAddress"];
        $network = $data["network"];
        $tokenAddress = $data["tokenAddress"];

        $error = 0;
        $success = 0;

        // check min max
        $withdrawMin = SettingLogic::get("general", ["category" => "withdraw", "code" => "withdraw_min"]);
        if ($withdrawMin && $withdrawMin["value"] > 0) {
            if ($amount < $withdrawMin["value"]) {
                $error++;
            }
        }

        $withdrawMax = SettingLogic::get("general", ["category" => "withdraw", "code" => "withdraw_max"]);
        if ($withdrawMax && $withdrawMax["value"] > 0) {
            if ($amount > $withdrawMax["value"]) {
                $error++;
            }
        }

        // get setting id
        $settingCoin = SettingLogic::get("coin", ["id" => $coinId]);
        $settingWallet = SettingLogic::get("wallet", ["id" => $settingCoin["wallet_id"]]);

        // Get amount fee
        $settingFee = SettingLogic::get("general", ["category" => "withdraw", "code" => "withdraw_fee"]);
        $settingFeeWallet = SettingLogic::get("general", ["category" => "withdraw", "code" => "withdraw_fee_wallet"]);
        if (!$settingFee || !$settingFeeWallet) {
            $error++;
        } else {
            $success++;
            $feeRate = $settingFee["value"] / 100 ?? 0;
            $fees = $amount * $feeRate;

            if ($settingWallet["id"] == $settingFeeWallet["value"]) {
                // check amount
                $userBalance = UserWalletLogic::getBalance($uid, $settingWallet["id"]);
                if ($amount > $userBalance) {
                    $error++;
                } else {
                    $success++;
                }

                // for same wallet
                $finalAmount = $amount - $fees;
            } else {
                // check amount
                $userBalance = UserWalletLogic::getBalance($uid, $settingWallet["id"]);
                if ($amount > $userBalance) {
                    $error++;
                } else {
                    $success++;
                }

                // check fee
                $feeBalance = UserWalletLogic::getBalance($uid, $settingFeeWallet["value"]);
                if ($fees > $feeBalance) {
                    $error++;
                }

                $finalAmount = $amount;
            }
        }

        if (!$error && $success == 2) {
            $status = SettingLogic::get("operator", ["code" => "pending"]);
            $withdraw = SettingLogic::get("operator", ["code" => "withdraw"]);
            $withdrawFee = SettingLogic::get("operator", ["code" => "withdraw_fee"]);

            $res = UserWithdrawModel::create([
                "sn" => HelperLogic::generateUniqueSN("user_withdraw"),
                "uid" => $uid,
                "amount" => $finalAmount,
                "fee" => $fees,
                "distribution" => json_encode([$settingWallet["id"] => round($finalAmount, 8)]),
                "status" => $status["id"],
                "amount_wallet_id" => $settingWallet["id"],
                "fee_wallet_id" => $settingFeeWallet["value"],
                "txid" => null,
                "log_index" => null,
                "to_coin_id" => $coinId,
                "from_address" => $fromAddress,
                "to_address" => $toAddress,
                "network" => $network,
                "token_address" => $tokenAddress,
            ]);

            // deduct wallet
            UserWalletLogic::deduct([
                "type" => $withdraw["id"],
                "uid" => $uid,
                "fromUid" => $uid,
                "toUid" => $uid,
                "distribution" => [$settingWallet["id"] => round($finalAmount, 8)],
                "refTable" => "user_withdraw",
                "refId" => $res["id"],
            ]);

            // deduct fee wallet
            UserWalletLogic::deduct([
                "type" => $withdrawFee["id"],
                "uid" => $uid,
                "fromUid" => $uid,
                "toUid" => $uid,
                "distribution" => [$settingFeeWallet["value"] => round($fees, 8)],
                "refTable" => "user_withdraw",
                "refId" => $res["id"],
            ]);
        }
    }

    private function withdrawRefund($data)
    {
        // Withdraw reject or refund
        $id = $data["id"];
        $logIndex = $data["logIndex"] ?? null;

        // get setting id
        $failed = SettingLogic::get("operator", ["code" => "failed"]);
        $withdrawRefund = SettingLogic::get("operator", ["code" => "withdraw_refund"]);
        $withdrawRefundFee = SettingLogic::get("operator", ["code" => "withdraw_refund_fee"]);

        // update status and record time
        UserWithdrawModel::where("id", $id)->update([
            "status" => $failed["id"],
            "log_index" => $logIndex,
            "completed_at" => date("Y-m-d H:i:s"),
        ]);

        $withdraw = UserWithdrawModel::where("id", $id)->first();

        // refund amount and fee
        UserWalletLogic::add([
            "type" => $withdrawRefund["id"],
            "uid" => $withdraw["uid"],
            "fromUid" => $withdraw["uid"],
            "toUid" => $withdraw["uid"],
            "distribution" => [$withdraw["amount_wallet_id"] => round($withdraw["amount"], 8)],
            "refTable" => "user_withdraw",
            "refId" => $withdraw["id"],
        ]);

        UserWalletLogic::add([
            "type" => $withdrawRefundFee["id"],
            "uid" => $withdraw["uid"],
            "fromUid" => $withdraw["uid"],
            "toUid" => $withdraw["uid"],
            "distribution" => [$withdraw["fee_wallet_id"] => round($withdraw["fee"], 8)],
            "refTable" => "user_withdraw",
            "refId" => $withdraw["id"],
        ]);
    }

    private function swap($data)
    {
        $uid = $data["uid"];
        $fromWallet = $data["fromWallet"];
        $toWallet = $data["toWallet"];
        $fromAmount = $data["fromAmount"];

        $error = 0;
        $success = 0;

        // get setting
        $setting = SettingLogic::get("wallet_attribute", [
            "from_wallet_id" => $fromWallet,
            "to_wallet_id" => $toWallet,
            "to_self" => 1,
            "is_show" => 1
        ]);

        if (!$setting) {
            $error++;
        } else {
            $success++;
            // check balance for from amount
            $fromBalance = UserWalletLogic::getBalance($uid, $fromWallet);
            if ($fromAmount > $fromBalance) {
                $error++;
            } else {
                $success++;
            }

            if ($setting["to_self_fee"] > 0) {
                // check balance for fee
                $fees = $fromAmount * ($setting["to_self_fee"] / 100);

                $feeBalance = UserWalletLogic::getBalance($uid, $setting["fee_wallet_id"]);
                if ($fees > $feeBalance) {
                    $error++;
                }
            }
        }

        if (!$error && $success == 2) {
            $swapFrom = SettingLogic::get("operator", ["code" => "swap_from"]);
            $swapTo = SettingLogic::get("operator", ["code" => "swap_to"]);
            $swapFee = SettingLogic::get("operator", ["code" => "swap_fee"]);

            // convert amount based on rate
            $toAmount = $fromAmount * $setting["to_self_rate"];

            // deduct from_wallet
            UserWalletLogic::deduct([
                "type" => $swapFrom["id"],
                "uid" => $uid,
                "fromUid" => $uid,
                "toUid" => $uid,
                "distribution" => [$fromWallet => round($fromAmount, 8)],
                "refTable" => "account_user",
                "refId" => $uid,
            ]);

            // add to_wallet
            UserWalletLogic::add([
                "type" => $swapTo["id"],
                "uid" => $uid,
                "fromUid" => $uid,
                "toUid" => $uid,
                "distribution" => [$toWallet => round($toAmount, 8)],
                "refTable" => "account_user",
                "refId" => $uid,
            ]);

            //deduct fee
            if ($setting["to_self_fee"] > 0) {
                UserWalletLogic::deduct([
                    "type" => $swapFee["id"],
                    "uid" => $uid,
                    "fromUid" => $uid,
                    "toUid" => $uid,
                    "distribution" => [$setting["fee_wallet_id"] => round($fees, 8)],
                    "refTable" => "account_user",
                    "refId" => $uid,
                ]);
            }
        }
    }

    private function transfer($data)
    {
        $fromUid = $data["fromUid"];
        $toUid = $data["toUid"];
        $walletId = $data["walletId"];
        $amount = $data["amount"];

        $error = 0;
        $success = 0;

        // get setting
        $setting = SettingLogic::get("wallet_attribute", [
            "from_wallet_id" => $walletId,
            "to_wallet_id" => $walletId,
            "to_other" => 1,
            "is_show" => 1
        ]);

        if (!$setting) {
            $error++;
        } else {
            $success++;
            // check balance for amount
            $userBalance = UserWalletLogic::getBalance($fromUid, $walletId);
            if ($amount > $userBalance) {
                $error++;
            } else {
                $success++;
            }

            if ($setting["to_other_fee"] > 0) {
                // check balance for fee
                $fees = $amount * ($setting["to_other_fee"] / 100);

                $feeBalance = UserWalletLogic::getBalance($fromUid, $setting["fee_wallet_id"]);
                if ($fees > $feeBalance) {
                    $error++;
                }
            }
        }

        if (!$error && $success == 2) {
            $transferOut = SettingLogic::get("operator", ["code" => "transfer_out"]);
            $transferIn = SettingLogic::get("operator", ["code" => "transfer_in"]);
            $transferFee = SettingLogic::get("operator", ["code" => "transfer_fee"]);

            // convert amount based on rate
            $toAmount = $amount * $setting["to_other_rate"];

            // deduct from sender
            UserWalletLogic::deduct([
                "type" => $transferOut["id"],
                "uid" => $fromUid,
                "fromUid" => $fromUid,
                "toUid" => $toUid,
                "distribution" => [$walletId => round($amount, 8)],
                "refTable" => "account_user",
                "refId" => $fromUid,
            ]);

            // add to recipient
            UserWalletLogic::add([
                "type" => $transferIn["id"],
                "uid" => $toUid,
                "fromUid" => $fromUid,
                "toUid" => $toUid,
                "distribution" => [$walletId => round($toAmount, 8)],
                "refTable" => "account_user",
                "refId" => $toUid,
            ]);

            //deduct fee
            if ($setting["to_other_fee"] > 0) {
                UserWalletLogic::deduct([
                    "type" => $transferFee["id"],
                    "uid" => $fromUid,
                    "fromUid" => $fromUid,
                    "toUid" => $toUid,
                    "distribution" => [$setting["fee_wallet_id"] => round($fees, 8)],
                    "refTable" => "account_user",
                    "refId" => $fromUid,
                ]);
            }
        }
    }

    private function claimSeedPoint($data)
    {
        $uid = $data["uid"];

        $error = 0;
        $success = 0;

        //check setting
        $setting = SettingGeneralModel::whereIn("code", [
            "gen1_nft_multiplier",
            "gen2_nft_multiplier",
            "reward_wallet",
            "reward_amount",
            "reward_distribution"
        ])->where("is_show", 1)->count();

        if ($setting != 5) {
            $error++;
        } else {
            $success++;

            // check seed nft setting
            $seedNft = SettingLogic::get("nft", ["name" => "seed"]);
            if (!$seedNft) {
                $error++;
            } else {
                $success++;
                $seedNetwork = SettingLogic::get("blockchain_network", ["id" => $seedNft["network"]]);
                if (!$seedNetwork) {
                    $error++;
                } else {
                    $success++;
                }
            }

            // check seed
            $seed = UserSeedModel::where(["uid" => $uid, "claimable" => 1])->first();
            if (!$seed) {
                $error++;
            } else {
                $success++;

                // check if already 24 hour or 86400 seconds
                $startTime = empty($seed["claimed_at"])
                    ? $seed["created_at"]
                    : $seed["claimed_at"];
                $dff = time() - strtotime($startTime);
                if ($dff < 86400) {
                    $error++;
                } else {
                    $success++;
                }
            }
        }

        if (!$error && $success == 5) {
            // need run first cause reward calculation need time
            // check seed nft count, if have then claimable for next round, if none then not claimable
            $user = AccountUserModel::where("id", $uid)->first();
            $seedCount = EvmLogic::getBalance("nft", $seedNetwork["rpc_url"], $seedNft["token_address"], $user["web3_address"]);

            UserSeedModel::where("id", $seed["id"])->update([
                "claimed_at" => date("Y-m-d H:i:s"),
                "claimable" => ($seedCount <= 0) ? 0 : 1
            ]);

            RewardLogic::seedReward($uid, $seed);
        }
    }

    private function treeUpgrade($data)
    {
        //     $uid = $data["uid"];
        //     $sn = $data["sn"];
        //     $items = $data["items"];

        //     $error = 0;
        //     $success = 0;

        //     // check pet
        //     $userPet = UserPetModel::defaultWhere()->where(["uid" => $uid, "sn" => $sn])->first();

        //     if (!$userPet) {
        //         $error++;
        //     } else {
        //         $success++;
        //         $itemCheck = ItemLogic::checkPetUpgrade($uid, $userPet, $items);

        //         if (!$itemCheck["success"]) {
        //             $error++;
        //         } else {
        //             $success++;
        //         }
        //     }

        //     if (!$error && $success == 2) {
        //         // auto claim mining reward - only trigger if this pet is active
        //         if ($userPet["is_active"]) {
        //             PetLogic::petMiningReward($uid);
        //         }

        //         // remove item used
        //         UserInventoryModel::defaultWhere()->where("uid", $uid)->whereIn("sn", $items)
        //             ->update(["used_at" => date("Y-m-d H:i:s")]);

        //         // get next star stats
        //         $nextStar = SettingLogic::get("pet_rank", ["quality" => $userPet["quality"], "rank" => $userPet["rank"], "star" => $userPet["star"] + 1]);

        //         // set pet to next star stats
        //         UserPetModel::defaultWhere()->where("id", $userPet["id"])
        //             ->update([
        //                 "star" => $nextStar["star"],
        //                 "mining_rate" => $nextStar["mining_rate"]
        //             ]);
        //     }
    }
}
