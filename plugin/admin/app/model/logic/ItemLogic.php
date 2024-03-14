<?php

namespace plugin\admin\app\model\logic;

# system lib
# database & logic
use app\model\database\UserInventoryModel;
use app\model\database\UserPetModel;
use app\model\logic\HelperLogic;
use app\model\logic\SettingLogic;

class ItemLogic
{
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
}