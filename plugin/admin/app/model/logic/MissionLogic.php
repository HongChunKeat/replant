<?php

namespace plugin\admin\app\model\logic;

# system lib
# database & logic
use app\model\database\UserInventoryModel;
use app\model\database\UserMissionModel;
use app\model\database\UserPetModel;
use plugin\dapp\app\model\logic\UserWalletLogic;
use app\model\logic\HelperLogic;
use app\model\logic\SettingLogic;

class MissionLogic
{
    public static function missionProgress($uid, $params)
    {
        $mission = SettingLogic::get("mission", $params);

        $pending = SettingLogic::get("operator", ["code" => "pending"]);
        $completed = SettingLogic::get("operator", ["code" => "completed"]);

        if ($mission) {
            $userMission = UserMissionModel::where([
                "uid" => $uid,
                "mission_id" => $mission["id"],
                "status" => $pending["id"]
            ])->first();

            if ($userMission) {
                // init value
                $progress = 0;

                // action = internal / bot
                // requirement will be null or number
                if ($mission["action"] == "internal" || $mission["action"] == "bot") {
                    // if requirement = null progress direct 100% and completed
                    if (empty($mission["requirement"])) {
                        $progress = 100;
                    }
                    // if requirement = number need count progress
                    else if (is_numeric($mission["requirement"])) {
                        $progress = self::calculateProgress($userMission["progress"], $mission["requirement"]);
                    }
                }
                // action = external
                // requirement will be link so direct complete
                else if ($mission["action"] == "external") {
                    $progress = 100;
                }

                // update user mission
                UserMissionModel::where("id", $userMission["id"])
                    ->update([
                        "progress" => $progress,
                        "status" => ($progress >= 100)
                            ? $completed["id"]
                            : $pending["id"]
                    ]);
            }
        }
    }

    public static function calculateProgress($progress, $totalProgress)
    {
        // must round off cause odd number will have decimal and will cause issue
        // 4/8 * 100 = 50, x/8 * 100 = 50
        // x = (50/100) * 8 = 4
        // change progress back to number, skipped if zero
        if ($progress > 0) {
            $progress = round(($progress / 100) * $totalProgress);
        }

        // add one to progress
        $progress++;

        // change progress to percentage
        $progressPercent = round(($progress / $totalProgress) * 100);

        return $progressPercent;
    }

    public static function claimReward($uid, $userMissionId)
    {
        $completed = SettingLogic::get("operator", ["code" => "completed"]);
        $claimed = SettingLogic::get("operator", ["code" => "claimed"]);
        $missionReward = SettingLogic::get("operator", ["code" => "mission_reward"]);

        $userMission = UserMissionModel::where(["id" => $userMissionId, "status" => $completed["id"]])->first();

        if ($userMission) {
            $mission = SettingLogic::get("mission", ["id" => $userMission["mission_id"]]);

            if ($mission) {
                // must update first
                UserMissionModel::where("id", $userMission["id"])->update(["status" => $claimed["id"]]);

                // reward record
                RewardLogic::rewardRecord([
                    "payAt" => date("Y-m-d H:i:s"),
                    "usedAt" => date("Ymd"),
                    "uid" => $uid,
                    "fromUid" => $uid,
                    "rewardType" => $missionReward["id"],
                    "itemReward" => !empty($mission["item_reward"])
                        ? json_decode($mission["item_reward"])
                        : null,
                    "petReward" => !empty($mission["pet_reward"])
                        ? json_decode($mission["pet_reward"])
                        : null,
                    "distribution" => !empty($mission["currency_reward"])
                        ? json_decode($mission["currency_reward"])
                        : null,
                    "refTable" => "user_mission",
                    "refId" => $userMission["id"],
                ]);

                if (!empty($mission["item_reward"])) {
                    $itemBulk = [];
                    $items = json_decode($mission["item_reward"]);

                    // 1 (id) : 2 (quantity)
                    foreach ($items as $itemId => $quantity) {
                        //loop quantity
                        for ($i = 1; $i <= $quantity; $i++) {
                            $item = SettingLogic::get("item", ["id" => $itemId]);

                            if ($item) {
                                $itemBulk[] = [
                                    "sn" => HelperLogic::generateUniqueSN("user_inventory"),
                                    "uid" => $uid,
                                    "item_id" => $item["id"],
                                    "created_at" => date("Y-m-d H:i:s"),
                                    "updated_at" => date("Y-m-d H:i:s"),
                                ];
                            }
                        }
                    }

                    UserInventoryModel::insert($itemBulk);
                }

                if (!empty($mission["pet_reward"])) {
                    $petBulk = [];
                    $pets = json_decode($mission["pet_reward"]);

                    // 1 (id) : 2 (quantity)
                    foreach ($pets as $petId => $quantity) {
                        //loop quantity
                        for ($i = 1; $i <= $quantity; $i++) {
                            $pet = SettingLogic::get("pet", ["id" => $petId]);

                            if ($pet) {
                                $attribute = HelperLogic::buildAttribute("pet_attribute", ["pet_id" => $petId]);
                                $petRank = SettingLogic::get("pet_rank", ["quality" => $pet["quality"], "rank" => $attribute["rank"] ?? "N", "star" => $attribute["star"] ?? 0]);

                                $petBulk[] = [
                                    "sn" => HelperLogic::generateUniqueSN("user_pet"),
                                    "uid" => $uid,
                                    "pet_id" => $pet["id"],
                                    "quality" => $pet["quality"],
                                    "rank" => $attribute["rank"] ?? "N",
                                    "star" => $attribute["star"] ?? 0,
                                    "mining_rate" => $petRank["mining_rate"] ?? 0,
                                    "is_active" => 0,
                                    "created_at" => date("Y-m-d H:i:s"),
                                    "updated_at" => date("Y-m-d H:i:s"),
                                ];
                            }
                        }
                    }

                    UserPetModel::insert($petBulk);
                }

                if (!empty($mission["currency_reward"])) {
                    UserWalletLogic::add([
                        "type" => $missionReward["id"],
                        "uid" => $uid,
                        "fromUid" => $uid,
                        "toUid" => $uid,
                        "distribution" => json_decode($mission["currency_reward"]),
                        "refTable" => "user_mission",
                        "refId" => $userMission["id"],
                    ]);
                }
            }
        }
    }
}