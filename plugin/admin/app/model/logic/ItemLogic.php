<?php

namespace plugin\admin\app\model\logic;

# system lib
use Webman\RedisQueue\Redis as RedisQueue;
# database & logic
use app\model\database\UserInventoryModel;
use app\model\database\UserPetModel;
use app\model\database\UserStaminaModel;
use app\model\database\UserGachaModel;
use app\model\database\SettingGachaItemModel;
use app\model\logic\HelperLogic;
use app\model\logic\SettingLogic;
use plugin\admin\app\model\logic\MissionLogic;
use plugin\dapp\app\model\logic\UserWalletLogic;

class ItemLogic
{
    public static function itemCheck($uid = "", $itemId = "", $targetSn = "")
    {
        $res = false;
        $data = [];

        $item = SettingLogic::get("item", ["id" => $itemId]);

        if ($item) {
            // character item
            if (in_array($item["category"], ["character food", "potion"])) {
                $stamina = UserStaminaModel::where("uid", $uid)->first();

                if ($stamina["current_stamina"] >= $stamina["max_stamina"]) {
                    $data[] = "stamina:already_maxed";
                }
            }
            // pet item
            else if (in_array($item["category"], ["pet food", "tools"])) {
                $pet = UserPetModel::defaultWhere()->where(["uid" => $uid, "sn" => $targetSn])->first();

                if (!$pet) {
                    $data[] = "pet:not_found";
                } else {
                    $health = PetLogic::countHealth($pet["id"]);
                    $status = PetLogic::checkHealth($health);

                    if ($item["category"] == "pet food") {
                        if (!in_array($status, ["fainted", "healthy", "unhealthy"])) {
                            $data[] = "item:unable_to_feed";
                        }

                        if ($health >= 100) {
                            $data[] = "health:already_maxed";
                        }
                    } else if ($item["category"] == "tools") {
                        if ($item["name"] == "egg hatcher" && $status != "hatching") {
                            $data[] = "item:can_only_use_on_egg";
                        }

                        if ($item["name"] == "pet revival" && $status != "coma") {
                            $data[] = "item:can_only_feed_to_coma_pet";
                        }
                    }
                }
            }
        } else {
            $data[] = "item:not_found";
        }

        if (!count($data)) {
            $res = true;
        }

        return [
            "success" => $res,
            "data" => $data,
        ];
    }

    public static function useItem($uid = "", $itemId = "", $targetSn = "")
    {
        // must fetch again to prevent parallel running bug
        $inventory = UserInventoryModel::defaultWhere()->where(["uid" => $uid, "item_id" => $itemId])
            ->orderBy("id")
            ->first();

        if ($inventory) {
            $item = SettingLogic::get("item", ["id" => $itemId]);

            if ($item) {
                // must update first
                UserInventoryModel::defaultWhere()->where("id", $inventory["id"])
                    ->update(["used_at" => date("Y-m-d H:i:s")]);

                // character item
                if (in_array($item["category"], ["character food", "potion"])) {
                    $stamina = UserStaminaModel::where("uid", $uid)->first();

                    if ($stamina["current_stamina"] < $stamina["max_stamina"]) {
                        $attr = HelperLogic::buildAttribute("item_attribute", ["item_id" => $item["id"]]);
                        $amount = $attr ? $attr["replenish"] : 0;

                        // if food then direct, if potion then percent of max stamina
                        if ($item["category"] == "character food") {
                            $replenish = $amount;
                        } else if ($item["category"] == "potion") {
                            $replenish = round($stamina["max_stamina"] * ($amount / 100));
                        }

                        // if exceed maximum then direct use max
                        UserStaminaModel::where("id", $stamina["id"])->update([
                            "current_stamina" => $replenish + $stamina["current_stamina"] >= $stamina["max_stamina"]
                                ? $stamina["max_stamina"]
                                : $stamina["current_stamina"] + $replenish
                        ]);
                    }
                }
                // pet item
                else if (in_array($item["category"], ["pet food", "tools"])) {
                    $pet = UserPetModel::defaultWhere()->where(["uid" => $uid, "sn" => $targetSn])->first();

                    if ($pet) {
                        $health = PetLogic::countHealth($pet["id"]);
                        $status = PetLogic::checkHealth($health);

                        if ($item["category"] == "pet food") {
                            // all pet food are same direct regen so no need specify name
                            if (in_array($status, ["fainted", "healthy", "unhealthy"])) {
                                // feed and auto claim mining reward
                                RedisQueue::send("user_wallet", [
                                    "type" => "petFeed",
                                    "data" => [
                                        "uid" => $uid,
                                        "pet" => $pet,
                                        "item" => $item,
                                        "health" => $health,
                                        "status" => $status
                                    ]
                                ]);
                            }
                        } else if ($item["category"] == "tools") {
                            // tools need specify name cause each have dff effect
                            if ($item["name"] == "egg hatcher" && $status == "hatching") {
                                UserPetModel::defaultWhere()->where("id", $pet["id"])
                                    ->update([
                                        "health_end_at" => date("Y-m-d H:i:s", strtotime("+12 hour")),
                                        "health_pause_at" => date("Y-m-d H:i:s")
                                    ]);
                            }

                            if ($item["name"] == "pet revival" && $status == "coma") {
                                // revive and auto claim mining reward
                                RedisQueue::send("user_wallet", [
                                    "type" => "petRevive",
                                    "data" => [
                                        "uid" => $uid,
                                        "pet" => $pet
                                    ]
                                ]);
                            }
                        }
                    }
                }
            }
        }
    }

    public static function checkLevelUp($uid, $level, $items)
    {
        $res = false;
        $data = [];
        $itemRes = 0;
        $petRes = 0;
        $itemQuantities = [];
        $itemRequired = [];
        $petRequired = [];

        $nextLevel = SettingLogic::get("level", ["level" => $level]);
        if (!$nextLevel) {
            $data[] = "level:maxed";
        } else {
            $itemRequired = !empty($nextLevel["item_required"])
                ? json_decode($nextLevel["item_required"], 1)
                : [];

            $petRequired = !empty($nextLevel["pet_required"])
                ? json_decode($nextLevel["pet_required"], 1)
                : [];

            // check item and make it into array
            $itemCount = 0;
            $old = "";
            foreach ($items as $itemSn) {
                // check duplicate
                if ($itemSn == $old) {
                    $data[] = "item:duplicate_found";
                }
                $old = $itemSn;

                $check = UserInventoryModel::defaultWhere()->where(["uid" => $uid, "sn" => $itemSn])->first();

                if ($check) {
                    $itemCount++;

                    // check item
                    $item = SettingLogic::get("item", ["id" => $check["item_id"]]);

                    if (!$item) {
                        $data[] = "item:not_found";
                    } else {
                        if ($item["category"] != "character level") {
                            $data[] = "item:invalid_action";
                        } else {
                            // Increment the quantity for the corresponding item ID
                            if (empty($itemQuantities[$item["id"]])) {
                                $itemQuantities[$item["id"]] = 1;
                            } else {
                                $itemQuantities[$item["id"]]++;
                            }
                        }
                    }
                }
            }

            if (count($items) != $itemCount) {
                $data[] = "item:invalid";
            }

            // check item match requirement or not
            foreach ($itemRequired as $itemId => $quantity) {
                // $name = SettingLogic::get("item", ["id" => $itemId]);

                if (!empty($itemQuantities[$itemId])) {
                    if ($itemQuantities[$itemId] != $quantity) {
                        $data[] = "item:requirement_not_met";
                        // $data[] = "item:" . $name["name"] . "_need_to_have_" . $quantity;
                    } else {
                        $itemRes++;
                    }
                } else {
                    $data[] = "item:requirement_not_met";
                }
            }

            // check pet match requirement or not
            foreach ($petRequired as $petId => $quantity) {
                // $name = SettingLogic::get("pet", ["id" => $petId]);

                $userPetCount = UserPetModel::defaultWhere()->where(["uid" => $uid, "pet_id" => $petId])->count();

                if ($userPetCount < $quantity) {
                    $data[] = "pet:requirement_not_met";
                    // $data[] = "pet:" . $name["name"] . "_need_to_have_atleast_" . $quantity;
                } else {
                    $petRes++;
                }
            }
        }

        // echo $itemRes ."|". count($itemRequired) . "|" . count($itemQuantities) ."\n";

        if (
            $itemRes == count($itemRequired) &&
            $itemRes == count($itemQuantities) &&
            $petRes == count($petRequired) &&
            !count($data)
        ) {
            $res = true;
        } else {
            $data[] = "failed";
        }

        return [
            "success" => $res,
            "data" => $data,
        ];
    }

    public static function checkPetUpgrade($uid, $userPet, $items)
    {
        $res = false;
        $data = [];
        $itemRes = 0;
        $itemQuantities = [];
        $itemRequired = [];

        $nextStar = SettingLogic::get("pet_rank", ["quality" => $userPet["quality"], "rank" => $userPet["rank"], "star" => $userPet["star"] + 1]);
        if (!$nextStar) {
            $data[] = "star:maxed";
        } else {
            $itemRequired = !empty($nextStar["item_required"])
                ? json_decode($nextStar["item_required"], 1)
                : [];

            // check item and make it into array
            $itemCount = 0;
            $old = "";
            foreach ($items as $itemSn) {
                // check duplicate
                if ($itemSn == $old) {
                    $data[] = "item:duplicate_found";
                }
                $old = $itemSn;

                $check = UserInventoryModel::defaultWhere()->where(["uid" => $uid, "sn" => $itemSn])->first();

                if ($check) {
                    $itemCount++;

                    // check item
                    $item = SettingLogic::get("item", ["id" => $check["item_id"]]);

                    if (!$item) {
                        $data[] = "item:not_found";
                    } else {
                        if ($item["category"] != "pet level") {
                            $data[] = "item:invalid_action";
                        } else {
                            // Increment the quantity for the corresponding item ID
                            if (empty($itemQuantities[$item["id"]])) {
                                $itemQuantities[$item["id"]] = 1;
                            } else {
                                $itemQuantities[$item["id"]]++;
                            }
                        }
                    }
                }
            }

            if (count($items) != $itemCount) {
                $data[] = "item:invalid";
            }

            // check item match requirement or not
            foreach ($itemRequired as $itemId => $quantity) {
                // $name = SettingLogic::get("item", ["id" => $itemId]);

                if (!empty($itemQuantities[$itemId])) {
                    if ($itemQuantities[$itemId] != $quantity) {
                        $data[] = "item:requirement_not_met";
                        // $data[] = "item:" . $name["name"] . "_need_to_have_" . $quantity;
                    } else {
                        $itemRes++;
                    }
                } else {
                    $data[] = "item:requirement_not_met";
                }
            }
        }

        // echo $itemRes ."|". count($itemRequired) . "|" . count($itemQuantities) ."\n";

        if (
            $itemRes == count($itemRequired) &&
            $itemRes == count($itemQuantities) &&
            !count($data)
        ) {
            $res = true;
        } else {
            $data[] = "failed";
        }

        return [
            "success" => $res,
            "data" => $data,
        ];
    }

    public static function gacha($uid, $gacha, $multi)
    {
        $res = false;
        $data = [];

        $items = SettingLogic::get("gacha_item", ["gacha_id" => $gacha], true);

        if (count($items) > 0) {
            //make it as associative array of id => occurrence
            $itemArray = [];
            foreach ($items as $item) {
                $itemDetails = self::itemDropRate($gacha, $item["id"]);
                $itemArray[$item["id"]] = $itemDetails["occurrence"];
            }

            // if true then is 10x draw
            if ($multi == 1) {
                $selected = HelperLogic::randomWeight($itemArray, 10);
            } else {
                $selected = HelperLogic::randomWeight($itemArray);
            }

            if ($selected["success"]) {
                $bulk = [];

                // insert item data
                foreach ($selected["data"] as $id) {
                    $image = null;
                    $name = null;
                    $rank = null;
                    $tokenReward = 0;

                    $gachaItem = SettingLogic::get("gacha_item", ["id" => $id]);

                    if ($gachaItem["ref_table"] == "setting_pet") {
                        $pet = SettingLogic::get("pet", ["id" => $gachaItem["ref_id"]]);

                        if ($pet) {
                            $attribute = HelperLogic::buildAttribute("pet_attribute", ["pet_id" => $pet["id"]]);
                            $petRank = SettingLogic::get("pet_rank", ["quality" => $pet["quality"], "rank" => $attribute["rank"] ?? "N", "star" => $attribute["star"] ?? 0]);

                            $userPet = UserPetModel::create([
                                "sn" => HelperLogic::generateUniqueSN("user_pet"),
                                "uid" => $uid,
                                "pet_id" => $pet["id"],
                                "quality" => $pet["quality"],
                                "rank" => $attribute["rank"] ?? "N",
                                "star" => $attribute["star"] ?? 0,
                                "mining_rate" => $petRank["mining_rate"] ?? 0,
                                "is_active" => 0,
                            ]);

                            $bulk[] = [
                                "uid" => $uid,
                                "gacha_id" => $gacha,
                                "pet_id" => $pet["id"],
                                "item_id" => null,
                                "wallet_id" => null,
                                "token_reward" => null,
                                "ref_table" => "user_pet",
                                "ref_id" => $userPet["id"],
                                "created_at" => date("Y-m-d H:i:s"),
                                "updated_at" => date("Y-m-d H:i:s"),
                            ];

                            $image = $pet["image"];
                            $name = $pet["name"];
                            $rank = $attribute["rank"];
                        }
                    } else if ($gachaItem["ref_table"] == "setting_item") {
                        $item = SettingLogic::get("item", ["id" => $gachaItem["ref_id"]]);

                        if ($item) {
                            $inventory = UserInventoryModel::create([
                                "sn" => HelperLogic::generateUniqueSN("user_inventory"),
                                "uid" => $uid,
                                "item_id" => $item["id"],
                            ]);

                            $bulk[] = [
                                "uid" => $uid,
                                "gacha_id" => $gacha,
                                "pet_id" => null,
                                "item_id" => $item["id"],
                                "wallet_id" => null,
                                "token_reward" => null,
                                "ref_table" => "user_inventory",
                                "ref_id" => $inventory["id"],
                                "created_at" => date("Y-m-d H:i:s"),
                                "updated_at" => date("Y-m-d H:i:s"),
                            ];

                            $image = $item["image"];
                            $name = $item["name"];
                        }
                    } else if ($gachaItem["ref_table"] == "setting_wallet") {
                        $wallet = SettingLogic::get("wallet", ["id" => $gachaItem["ref_id"]]);
                        $tokenRewardOperator = SettingLogic::get("operator", ["code" => "gacha_token_reward"]);

                        if ($wallet) {
                            $transaction = UserWalletLogic::add([
                                "type" => $tokenRewardOperator["id"],
                                "uid" => $uid,
                                "fromUid" => $uid,
                                "toUid" => $uid,
                                "distribution" => [$wallet["id"] => round($gachaItem["token_reward"], 8)],
                                "refTable" => "setting_gacha_item",
                                "refId" => $gachaItem["id"],
                            ]);

                            $bulk[] = [
                                "uid" => $uid,
                                "gacha_id" => $gacha,
                                "pet_id" => null,
                                "item_id" => null,
                                "wallet_id" => $wallet["id"],
                                "token_reward" => $gachaItem["token_reward"],
                                "ref_table" => "wallet_transaction",
                                "ref_id" => $transaction["id"],
                                "created_at" => date("Y-m-d H:i:s"),
                                "updated_at" => date("Y-m-d H:i:s"),
                            ];

                            $image = $wallet["image"];
                            $name = $wallet["code"];
                            $tokenReward = $gachaItem["token_reward"];
                        }
                    }

                    $rarity = self::itemRarity($gacha, $gachaItem["occurrence"]);

                    $data[] = [
                        "image" => $image,
                        "name" => $name,
                        "rank" => $rank,
                        "rarity" => $rarity,
                        "token_reward" => $tokenReward * 1
                    ];
                }

                // bulk insert data
                UserGachaModel::insert($bulk);
            }
        }

        if (count($data) > 0) {
            $res = true;
        }

        return [
            "success" => $res,
            "data" => $data,
        ];
    }

    public static function itemRarity($gacha, $occurrence)
    {
        $dropRate = 0;
        $rarity = "common";

        $sum = SettingGachaItemModel::where("gacha_id", $gacha)->sum("occurrence");
        $dropRate = round(($occurrence / $sum) * 100, 3);

        if ($dropRate < 1) {
            $rarity = "red";
        } else if ($dropRate <= 2) {
            $rarity = "gold";
        } else if ($dropRate <= 5) {
            $rarity = "purple";
        }

        return $rarity;
    }

    public static function itemDropRate($gacha, $gachaItemId)
    {
        $occurrence = 0;
        $rarity = "common";
        $dropRate = 0;

        $items = SettingLogic::get("gacha_item", ["gacha_id" => $gacha], true);

        if (count($items) > 0) {
            $sum = 0;
            $itemArray = [];
            foreach ($items as $item) {
                $rarity = self::itemRarity($gacha, $item["occurrence"]);

                // decrease drop rate for rare item
                if (in_array($rarity, ["red", "gold"])) {
                    $times = 1;
                } else if ($rarity == "purple") {
                    $times = 2;
                } else {
                    $times = 1000;
                }

                $finalOccurrence = $item["occurrence"] * $times;
                $sum += $finalOccurrence;
                $itemArray[$item["id"]] = [
                    "occurrence" => $finalOccurrence,
                    "rarity" => $rarity
                ];
            }

            // find occurrence, rarity and drop rate of that item
            $occurrence  = $itemArray[$gachaItemId]["occurrence"];
            $rarity = $itemArray[$gachaItemId]["rarity"];
            $dropRate = round(($itemArray[$gachaItemId]["occurrence"] / $sum) * 100, 4);
        }

        return [
            "occurrence" => $occurrence,
            "rarity" => $rarity,
            "drop_rate" => $dropRate,
        ];
    }
}