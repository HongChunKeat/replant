<?php

namespace app\queue\redis;

# system lib
use support\Db;
use Webman\RedisQueue\Consumer;
# database & logic
use app\model\database\UserInventoryModel;
use app\model\database\UserLevelModel;
use app\model\database\UserDepositModel;
use app\model\database\UserMarketModel;
use app\model\database\UserWithdrawModel;
use app\model\database\UserPetModel;
use plugin\dapp\app\model\logic\UserWalletLogic;
use plugin\admin\app\model\logic\PetLogic;
use plugin\admin\app\model\logic\ItemLogic;
use plugin\admin\app\model\logic\MissionLogic;
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
            case "purchase":
                $this->purchase($queue["data"]);
                break;
            case "itemDelete":
                $this->itemDelete($queue["data"]);
                break;
            case "petDelete":
                $this->petDelete($queue["data"]);
                break;
            case "petSlotBuy":
                $this->petSlotBuy($queue["data"]);
                break;
            case "inventoryPageBuy":
                $this->inventoryPageBuy($queue["data"]);
                break;
            case "petMiningReward":
                $this->petMiningReward($queue["data"]);
                break;
            case "petAssign":
                $this->petAssign($queue["data"]);
                break;
            case "petUpgrade":
                $this->petUpgrade($queue["data"]);
                break;
            case "petFeed":
                $this->petFeed($queue["data"]);
                break;
            case "petRevive":
                $this->petRevive($queue["data"]);
                break;
            case "marketItemBuy":
                $this->marketItemBuy($queue["data"]);
                break;
            case "marketItemSell":
                $this->marketItemSell($queue["data"]);
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

    private function purchase($data)
    {
        $uid = $data["uid"];
        $itemID = $data["item"];
        $quantity = $data["quantity"] ?? 1;

        $error = 0;
        $success = 0;

        // check item
        $item = SettingLogic::get("item", ["id" => $itemID]);

        if (!$item) {
            $error++;
        } else {
            $success++;
            if ($item["sales_price"] <= 0 || $item["normal_price"] <= 0) {
                $error++;
            } else {
                $success++;
                $payment = SettingLogic::get("payment", ["id" => $item["payment_id"]]);
                if (!$payment) {
                    $error++;
                } else {
                    $success++;
                    // get the first wallet only
                    $wallet = array_keys(json_decode($payment["formula"], 1));

                    $total = $item["sales_price"] * $quantity;

                    $payment = UserWalletLogic::paymentCheck($uid, $item["payment_id"], [$wallet[0] => $total]);
                    if (!$payment["success"]) {
                        $error++;
                    } else {
                        $success++;
                    }
                }
            }
        }

        if (!$error && $success == 4) {
            // get setting id
            $purchaseType = SettingLogic::get("operator", ["code" => "purchase"]);

            # [process]
            for ($i = 0; $i < $quantity; $i++) {
                $purchase = UserInventoryModel::create([
                    "sn" => HelperLogic::generateUniqueSN("user_inventory"),
                    "uid" => $uid,
                    "item_id" => $item["id"]
                ]);

                // deduct balance
                UserWalletLogic::deduct([
                    "type" => $purchaseType["id"],
                    "uid" => $uid,
                    "fromUid" => $uid,
                    "toUid" => $uid,
                    "distribution" => [$wallet[0] => round($item["sales_price"], 8)],
                    "refTable" => "user_inventory",
                    "refId" => $purchase["id"],
                ]);
            }

            // do mission
            MissionLogic::missionProgress($uid, ["name" => "purchase from shop 5 times"]);
        }
    }

    private function itemDelete($data)
    {
        $uid = $data["uid"];
        $items = $data["items"];
        $inventoryId = "";

        $error = 0;
        $success = 0;

        // check item
        $res = UserInventoryModel::defaultWhere()->where("uid", $uid)->whereIn("sn", $items)->get();

        if (count($items) != count($res)) {
            $error++;
        } else {
            $success++;
        }

        if (!$error && $success == 1 && count($res) > 0) {
            $itemRefund = SettingLogic::get("operator", ["code" => "item_refund"]);
            $refundPrice = HelperLogic::buildAttributeGeneral(["category" => "item", "code" => "refund_price"]);

            UserInventoryModel::defaultWhere()->whereIn("sn", $items)->update(["removed_at" => date("Y-m-d H:i:s")]);

            foreach ($res as $item) {
                $inventoryId .= $item["id"] . ",";
            }

            UserWalletLogic::add([
                "type" => $itemRefund["id"],
                "uid" => $uid,
                "fromUid" => $uid,
                "toUid" => $uid,
                "distribution" => [$refundPrice[0]["key"] => round($refundPrice[0]["value"] * count($items), 8)],
                "refTable" => "user_inventory",
                "refId" => rtrim($inventoryId, ","),
            ]);
        }
    }

    private function petDelete($data)
    {
        $uid = $data["uid"];
        $pets = $data["pets"];

        $error = 0;
        $success = 0;

        // check pet
        $res = UserPetModel::defaultWhere()->where("uid", $uid)->whereIn("sn", $pets)->get();

        if (count($pets) != count($res)) {
            $error++;
        } else {
            $success++;
        }

        if (!$error && $success == 1 && count($res) > 0) {
            $petRefund = SettingLogic::get("operator", ["code" => "pet_refund"]);
            $normalRefundPrice = HelperLogic::buildAttributeGeneral(["category" => "pet", "code" => "normal_refund_price"]);
            $premiumRefundPrice = HelperLogic::buildAttributeGeneral(["category" => "pet", "code" => "premium_refund_price"]);

            UserPetModel::defaultWhere()->whereIn("sn", $pets)->update(["removed_at" => date("Y-m-d H:i:s")]);

            foreach ($res as $pet) {
                if ($pet["quality"] == "premium") {
                    $chosenRefund = $premiumRefundPrice[0];
                } else {
                    $chosenRefund = $normalRefundPrice[0];
                }

                UserWalletLogic::add([
                    "type" => $petRefund["id"],
                    "uid" => $uid,
                    "fromUid" => $uid,
                    "toUid" => $uid,
                    "distribution" => [$chosenRefund["key"] => round($chosenRefund["value"], 8)],
                    "refTable" => "user_pet",
                    "refId" => $pet["id"],
                ]);
            }
        }
    }

    private function petSlotBuy($data)
    {
        $uid = $data["uid"];

        $error = 0;
        $success = 0;

        $level = UserLevelModel::where(["uid" => $uid, "is_current" => 1])->first();
        if (!$level) {
            $error++;
        } else {
            $success++;
            $attribute = HelperLogic::buildAttributeGeneral(["code" => "pet_slot_price"]);

            if (!count($attribute)) {
                $error++;
            } else {
                $success++;
                if ($level["pet_slots"] >= (count($attribute) + 1)) {
                    $error++;
                } else {
                    $success++;
                    // need minus 1
                    $selected = $attribute[$level["pet_slots"] - 1];

                    $userBalance = UserWalletLogic::getBalance($uid, $selected["key"]);
                    if ($selected["value"] > $userBalance) {
                        $error++;
                    } else {
                        $success++;
                    }
                }
            }
        }

        if (!$error && $success == 4) {
            $unlock = SettingLogic::get("operator", ["code" => "pet_slot_unlock"]);

            UserLevelModel::where(["uid" => $uid, "is_current" => 1])
                ->update(["pet_slots" => Db::raw("pet_slots + 1")]);

            UserWalletLogic::deduct([
                "type" => $unlock["id"],
                "uid" => $uid,
                "fromUid" => $uid,
                "toUid" => $uid,
                "distribution" => [$selected["key"] => round($selected["value"], 8)],
                "refTable" => "account_user",
                "refId" => $uid,
            ]);
        }
    }

    private function inventoryPageBuy($data)
    {
        $uid = $data["uid"];

        $error = 0;
        $success = 0;

        $level = UserLevelModel::where(["uid" => $uid, "is_current" => 1])->first();
        if (!$level) {
            $error++;
        } else {
            $success++;
            $attribute = HelperLogic::buildAttributeGeneral(["code" => "inventory_page_price"]);

            if (!count($attribute)) {
                $error++;
            } else {
                $success++;
                if ($level["inventory_pages"] >= (count($attribute) + 1)) {
                    $error++;
                } else {
                    $success++;
                    // need minus 1
                    $selected = $attribute[$level["inventory_pages"] - 1];

                    $userBalance = UserWalletLogic::getBalance($uid, $selected["key"]);
                    if ($selected["value"] > $userBalance) {
                        $error++;
                    } else {
                        $success++;
                    }
                }
            }
        }

        if (!$error && $success == 4) {
            $unlock = SettingLogic::get("operator", ["code" => "inventory_page_unlock"]);

            UserLevelModel::where(["uid" => $uid, "is_current" => 1])
                ->update(["inventory_pages" => Db::raw("inventory_pages + 1")]);

            UserWalletLogic::deduct([
                "type" => $unlock["id"],
                "uid" => $uid,
                "fromUid" => $uid,
                "toUid" => $uid,
                "distribution" => [$selected["key"] => round($selected["value"], 8)],
                "refTable" => "account_user",
                "refId" => $uid,
            ]);
        }
    }

    private function petMiningReward($data)
    {
        $uid = $data["uid"];

        PetLogic::petMiningReward($uid);
    }

    private function petAssign($data)
    {
        $uid = $data["uid"];
        $pets = $data["pets"];

        $error = 0;
        $success = 0;

        // check pet
        $level = UserLevelModel::where(["uid" => $uid, "is_current" => 1])->first();
        if (!$level) {
            $error++;
        } else {
            $success++;
            if (count($pets) > $level["pet_slots"]) {
                $error++;
            } else {
                $success++;
            }
        }

        // can only assign healthy or unhealthy
        $count = 0;
        $assignPets = UserPetModel::defaultWhere()->where("uid", $uid)->whereIn("sn", $pets)->get();

        foreach ($assignPets as $pet) {
            $health = PetLogic::countHealth($pet["id"]);
            $status = PetLogic::checkHealth($health);

            if (in_array($status, ["healthy", "unhealthy"])) {
                $count++;
            }
        }

        if (count($pets) != $count) {
            $error++;
        } else {
            $success++;
        }

        if (!$error && $success == 3) {
            // auto claim mining reward
            PetLogic::petMiningReward($uid);

            // for unassign pet that is not in current assign pet
            // find all current active pet - if not in pets means it has been unassigned
            $activePet = UserPetModel::defaultWhere()->where(["uid" => $uid, "is_active" => 1])->get();

            if (count($activePet) > 0) {
                foreach ($activePet as $pet) {
                    if (!in_array($pet["sn"], $pets)) {
                        UserPetModel::defaultWhere()->where("id", $pet["id"])
                            ->update([
                                "is_active" => 0,
                                "health_pause_at" => date("Y-m-d H:i:s")
                            ]);
                    }
                }
            }

            // for current assign pet
            if (count($pets) > 0) {
                foreach ($assignPets as $pet) {
                    if (!empty($pet["health_pause_at"])) {
                        $dff = strtotime($pet["health_end_at"]) - strtotime($pet["health_pause_at"]);

                        // if more than 12 h then capped it at 12 h
                        $dff = $dff > (43200)
                            ? 43200
                            : $dff;

                        $endTime = date("Y-m-d H:i:s", time() + $dff);
                    } else {
                        $endTime = $pet["health_end_at"];
                    }

                    // mining cutoff will use current time for all assign pet everytime assign
                    UserPetModel::defaultWhere()->where("id", $pet["id"])
                        ->update([
                            "is_active" => 1,
                            "mining_cutoff_at" => date("Y-m-d H:i:s"),
                            "health_pause_at" => null,
                            "health_end_at" => $endTime
                        ]);
                }
            }
        }
    }

    private function petUpgrade($data)
    {
        $uid = $data["uid"];
        $sn = $data["sn"];
        $items = $data["items"];

        $error = 0;
        $success = 0;

        // check pet
        $userPet = UserPetModel::defaultWhere()->where(["uid" => $uid, "sn" => $sn])->first();

        if (!$userPet) {
            $error++;
        } else {
            $success++;
            $itemCheck = ItemLogic::checkPetUpgrade($uid, $userPet, $items);

            if (!$itemCheck["success"]) {
                $error++;
            } else {
                $success++;
            }
        }

        if (!$error && $success == 2) {
            // auto claim mining reward - only trigger if this pet is active
            if ($userPet["is_active"]) {
                PetLogic::petMiningReward($uid);
            }

            // remove item used
            UserInventoryModel::defaultWhere()->where("uid", $uid)->whereIn("sn", $items)
                ->update(["used_at" => date("Y-m-d H:i:s")]);

            // get next star stats
            $nextStar = SettingLogic::get("pet_rank", ["quality" => $userPet["quality"], "rank" => $userPet["rank"], "star" => $userPet["star"] + 1]);

            // set pet to next star stats
            UserPetModel::defaultWhere()->where("id", $userPet["id"])
                ->update([
                    "star" => $nextStar["star"],
                    "mining_rate" => $nextStar["mining_rate"]
                ]);
        }
    }

    private function petFeed($data)
    {
        $uid = $data["uid"];
        $pet = $data["pet"];
        $item = $data["item"];
        $health = $data["health"];
        $status = $data["status"];

        // auto claim mining reward
        PetLogic::petMiningReward($uid);

        $attr = HelperLogic::buildAttribute("item_attribute", ["item_id" => $item["id"]]);
        $replenish = $attr ? $attr["replenish"] : 0;

        // find x% * 12h = how many seconds
        $replenishTime = $health + $replenish >= 100
            ? (100 - $health) / 100
            : $replenish / 100;

        // if fainted use current time, instead of health end time
        $replenishStartTime = ($status == "fainted")
            ? time()
            : strtotime($pet["health_end_at"]);

        // works by adding additional time to health end or current time if is fainted
        UserPetModel::defaultWhere()->where("id", $pet["id"])
            ->update([
                "health_end_at" => date(
                    "Y-m-d H:i:s",
                    $replenishStartTime + round($replenishTime * 43200)
                )
            ]);

        // do mission
        MissionLogic::missionProgress($uid, ["name" => "feed your pet 5 times"]);
    }

    private function petRevive($data)
    {
        $uid = $data["uid"];
        $pet = $data["pet"];

        // auto claim mining reward
        PetLogic::petMiningReward($uid);

        // current time plus 12h to reach 100% health, if not active health pause need put so the hp wont auto deduct
        UserPetModel::defaultWhere()->where("id", $pet["id"])
            ->update([
                "health_end_at" => date("Y-m-d H:i:s", strtotime("+12 hour")),
                "health_pause_at" => $pet["is_active"]
                    ? null
                    : date("Y-m-d H:i:s"),
            ]);
    }

    private function marketItemBuy($data)
    {
        $uid = $data["uid"];
        $sn = $data["sn"];

        $error = 0;
        $success = 0;

        // check exist in market list
        $item = UserMarketModel::defaultWhere()->where("sn", $sn)->first();
        if (!$item) {
            $error++;
        } else {
            $success++;
            if ($item["seller_uid"] == $uid) {
                $error++;
            } else {
                $success++;
            }

            // check wallet and balance
            $wallet = SettingLogic::get("wallet", ["id" => $item["amount_wallet_id"]]);
            if (!$wallet) {
                $error++;
            } else {
                $success++;
                $balance = UserWalletLogic::getBalance($uid, $wallet["id"]);
                if ($item["amount"] > $balance) {
                    $error++;
                } else {
                    $success++;
                }
            }
        }

        if (!$error && $success == 4) {
            // change ownership to buyer
            if ($item["ref_table"] == "user_pet") {
                UserPetModel::where("id", $item["ref_id"])->update([
                    "uid" => $uid,
                    "marketed_at" => null
                ]);
            } else if ($item["ref_table"] == "user_inventory") {
                UserInventoryModel::where("id", $item["ref_id"])->update([
                    "uid" => $uid,
                    "marketed_at" => null
                ]);
            }

            // update details
            UserMarketModel::defaultWhere()->where("id", $item["id"])->update([
                "buyer_uid" => $uid,
                "sold_at" => date("Y-m-d H:i:s")
            ]);

            $marketSalesPaymentOut = SettingLogic::get("operator", ["code" => "market_sales_payment_out"]);
            $marketSalesPaymentIn = SettingLogic::get("operator", ["code" => "market_sales_payment_in"]);

            // deduct from buyer
            UserWalletLogic::deduct([
                "type" => $marketSalesPaymentOut["id"],
                "uid" => $uid,
                "fromUid" => $uid,
                "toUid" => $item["seller_uid"],
                "distribution" => [$wallet["id"] => round($item["amount"], 8)],
                "refTable" => "user_market",
                "refId" => $item["id"],
            ]);

            // add to seller
            UserWalletLogic::add([
                "type" => $marketSalesPaymentIn["id"],
                "uid" => $item["seller_uid"],
                "fromUid" => $uid,
                "toUid" => $item["seller_uid"],
                "distribution" => [$wallet["id"] => round($item["amount"], 8)],
                "refTable" => "user_market",
                "refId" => $item["id"],
            ]);
        }
    }

    private function marketItemSell($data)
    {
        $uid = $data["uid"];
        $source = $data["source"];
        $sn = $data["sn"];
        $payment = $data["payment"];
        $price = $data["price"];

        $error = 0;
        $success = 0;

        // check min max
        $salesMin = SettingLogic::get("general", ["category" => "market", "code" => "sales_min"]);
        if ($salesMin && $salesMin["value"] > 0) {
            if ($price < $salesMin["value"]) {
                $error++;
            }
        }

        $salesMax = SettingLogic::get("general", ["category" => "market", "code" => "sales_max"]);
        if ($salesMax && $salesMax["value"] > 0) {
            if ($price > $salesMax["value"]) {
                $error++;
            }
        }

        // check source and sn
        if ($source == "pet") {
            $pet = UserPetModel::defaultWhere()->where(["uid" => $uid, "sn" => $sn])->first();

            if (!$pet) {
                $error++;
            } else {
                if ($pet["is_active"]) {
                    $error++;
                }

                // hatching, healthy, unhealthy can sell
                $health = PetLogic::countHealth($pet["id"]);
                $status = PetLogic::checkHealth($health);
                if (!in_array($status, ["hatching", "healthy", "unhealthy"])) {
                    $error++;
                }
            }
        } else if ($source == "item") {
            $item = UserInventoryModel::defaultWhere()->where(["uid" => $uid, "sn" => $sn])->first();

            if (!$item) {
                $error++;
            }
        } else {
            $error++;
        }

        // check exist in market list
        if (isset($pet) || isset($item)) {
            $check = UserMarketModel::defaultWhere()->where([
                "seller_uid" => $uid,
                "ref_table" => ($source == "pet")
                    ? "user_pet"
                    : "user_inventory",
                "ref_id" => ($source == "pet")
                    ? $pet["id"]
                    : $item["id"],
            ])->first();

            if ($check) {
                $error++;
            } else {
                $success++;
            }
        }

        // check payment and balance
        $wallet = SettingLogic::get("wallet", ["code" => $payment]);
        if (!$wallet) {
            $error++;
        } else {
            $success++;
            $settingFee = SettingLogic::get("general", ["category" => "market", "code" => "sales_fee"]);
            $settingFeeWallet = SettingLogic::get("general", ["category" => "market", "code" => "sales_fee_wallet"]);

            if (!$settingFee || !$settingFeeWallet) {
                $error++;
            } else {
                $success++;
                $feeWallet = SettingLogic::get("wallet", ["id" => $settingFeeWallet["value"]]);
                if (!$feeWallet) {
                    $error++;
                } else {
                    $success++;
                    $fee = $price * ($settingFee["value"] / 100);
                    $balance = UserWalletLogic::getBalance($uid, $feeWallet["id"]);
                    if ($fee > $balance) {
                        $error++;
                    } else {
                        $success++;
                    }
                }
            }
        }

        if (!$error && $success == 5) {
            // update to market at
            if ($source == "pet") {
                UserPetModel::defaultWhere()->where("id", $pet["id"])->update([
                    "marketed_at" => date("Y-m-d H:i:s")
                ]);
            } else if ($source == "item") {
                UserInventoryModel::defaultWhere()->where("id", $item["id"])->update([
                    "marketed_at" => date("Y-m-d H:i:s")
                ]);
            }

            $res = UserMarketModel::create([
                "sn" => HelperLogic::generateUniqueSN("user_market"),
                "seller_uid" => $uid,
                "amount" => $price,
                "fee" => $fee,
                "amount_wallet_id" => $wallet["id"],
                "fee_wallet_id" => $feeWallet["id"],
                "ref_table" => ($source == "pet")
                    ? "user_pet"
                    : "user_inventory",
                "ref_id" => ($source == "pet")
                    ? $pet["id"]
                    : $item["id"]
            ]);

            $marketFee = SettingLogic::get("operator", ["code" => "market_fee"]);

            // deduct wallet
            UserWalletLogic::deduct([
                "type" => $marketFee["id"],
                "uid" => $uid,
                "fromUid" => $uid,
                "toUid" => $uid,
                "distribution" => [$feeWallet["id"] => round($fee, 8)],
                "refTable" => "user_market",
                "refId" => $res["id"],
            ]);
        }
    }
}