<?php

namespace plugin\admin\app\model\logic;

# system lib
# database & logic
use app\model\database\UserPetModel;
use app\model\logic\SettingLogic;
use plugin\dapp\app\model\logic\UserWalletLogic;

class PetLogic
{
    public static function checkHealth($health)
    {
        $status = false;

        if ($health == 0) {
            // 0 = fainted
            $status = "fainted";
        } else if ($health > 0 && $health < 26) {
            // 0.1 - 25.9 = unhealthy
            $status = "unhealthy";
        } else if ($health >= 26) {
            // 26 >= healthy
            $status = "healthy";
        } else if ($health == -1) {
            // -1 = hatching
            $status = "hatching";
        } else if ($health == -2) {
            // -2 = coma
            $status = "coma";
        } else if ($health == -3) {
            // -3 = dead
            $status = "dead";
        }

        return $status;
    }

    /*
        1. health deduct only work if pet is active, active 1 use current time, active 0 use health pause
        2. if pet active and current time more than health end mean pet already fainted
            2a. if unassigned, health pause will higher than health end, but it will still be fainted
            2b. if still assigned, pet will still be fainted
        3. coma and dead will start counting if pet is fainted and is count based on health end
        4. health pause is to maintain health, used at after hatching, revive, and unassign
            4a. unactive pet wont fainted even if current time exceed health end, because health pause retained its health
            4b. if pet that have health pause is assigned, its health will carry over by adding it on current time (current time exceed health end or not doesnt matter at here)
            4b example. unassign at 8.00, health end at 8.30 = left 30 minutes of health. assign back at 8.15, health end become 8.45 by adding 30 minutes to current time. formula: health_end = current_time + (dff = health_end - health_pause)
    */
    public static function countHealth(int $id)
    {
        $health = 0;

        $pet = UserPetModel::where("id", $id)->first();

        if (!empty($pet["health_end_at"])) {
            // minus 12h to find start date
            $startTime = strtotime($pet["health_end_at"]) - 43200;

            // if active then use current time else use health_pause_at
            if ($pet["is_active"]) {
                $currentTime = time();
            } else {
                $currentTime = !empty($pet["health_pause_at"])
                    ? strtotime($pet["health_pause_at"])
                    : time();
            }

            $health = 100 - ((($currentTime - $startTime) / 43200) * 100);

            // capped it at 0 - 100
            if ($health > 100) {
                $health = 100;
            } else if ($health < 0) {
                $health = 0;
            }

            // dead and coma start count whenever health 0
            if ($health == 0) {
                $coma = self::petComa($pet["health_end_at"]);
                if ($coma) {
                    // -2 = coma
                    $health = -2;
                }

                // only normal pet got dead
                $dead = self::petDead($pet["health_end_at"]);
                if ($dead && $pet["quality"] == "normal") {
                    // -3 = dead
                    $health = -3;
                }
            }
        } else {
            // -1 = hatching
            $health = -1;
        }

        return round($health, 4);
    }

    public static function countMining(int $id)
    {
        $mined = 0;

        // everytime user unassign pet, use reviver, upgrade pet must auto claim
        $pet = UserPetModel::where(["id" => $id, "is_active" => 1])->first();

        if (!empty($pet["health_end_at"])) {
            // if mining_cutoff_at = null then use created at
            $startTime = !empty($pet["mining_cutoff_at"])
                ? strtotime($pet["mining_cutoff_at"])
                : strtotime($pet["created_at"]);

            // if current time exceed health end mean if pet coma then end time use health end (coma time)
            $endTime = time() > strtotime($pet["health_end_at"])
                ? strtotime($pet["health_end_at"])
                : time();

            // if start higher than end time then direct 0 claim nothing
            if ($startTime < $endTime) {
                // Calculate the difference in minutes, round down so 25.5 = 25
                $diffInSeconds = $endTime - $startTime;
                $diffInMinutes = floor($diffInSeconds / 60);

                // mining rate is per minute
                $mined = $diffInMinutes * $pet["mining_rate"];
            }
        }

        return round($mined, 4);
    }

    public static function petComa($healthEnd)
    {
        $res = false;

        $days = SettingLogic::get("general", ["code" => "coma_count_days"]);
        $diffInSeconds = time() - strtotime($healthEnd);

        // Calculate the difference in days, round down so 65000 / 86400 = 0.75 wont count as 1 day
        $diffInDays = floor($diffInSeconds / 86400);

        // if 3 days then coma
        if ($diffInDays >= $days["value"]) {
            $res = true;
        }

        return $res;
    }

    public static function petDead($healthEnd)
    {
        $res = false;

        $days = SettingLogic::get("general", ["code" => "dead_count_days"]);
        $diffInSeconds = time() - strtotime($healthEnd);

        // Calculate the difference in days, round down so 65000 / 86400 = 0.75 wont count as 1 day
        $diffInDays = floor($diffInSeconds / 86400);

        // if 30 days then dead
        if ($diffInDays >= $days["value"]) {
            $res = true;
        }

        return $res;
    }

    /*
        manual claim - petMiningReward
        auto claim - petAssign, petAutoUnassign, petUpgrade, petFeed, petRevive
            petAssign - always trigger whenever calling the function
            petAutoUnassign (from pet list) - only trigger if pet health 0 (not -> healthy, unhealthy)
            petUpgrade (is active only) - only trigger if the pet is still active
            petFeed - trigger when feed pet food
            petRevive - trigger when use pet revive on coma pet
    */
    public static function petMiningReward($uid)
    {
        $response = false;

        // get current active pet
        $pets = UserPetModel::defaultWhere()->where(["uid" => $uid, "is_active" => 1])->get();

        $amount = 0;
        foreach ($pets as $pet) {
            $amount += self::countMining($pet["id"]);
        }

        if ($amount > 0) {
            foreach ($pets as $pet) {
                self::claimMining($pet);
            }
            $response = true;
        }

        return $response;
    }

    public static function petAutoUnassign($uid)
    {
        // unassign not healthy, unhealthy pet
        $pets = UserPetModel::defaultWhere()->where(["uid" => $uid, "is_active" => 1])->get();

        foreach ($pets as $pet) {
            $health = self::countHealth($pet["id"]);
            $status = self::checkHealth($health);

            if (!in_array($status, ["healthy", "unhealthy"])) {
                self::claimMining($pet);

                UserPetModel::defaultWhere()->where("id", $pet["id"])
                    ->update([
                        "is_active" => 0,
                        "health_pause_at" => date("Y-m-d H:i:s")
                    ]);
            }
        }
    }

    private static function claimMining($pet)
    {
        $miningReward = SettingLogic::get("operator", ["code" => "mining_reward"]);

        $wallet = SettingLogic::get("wallet", [
            "code" => $pet["quality"] == "normal"
                ? "xtendo"
                : "rtendo"
        ]);

        $minedAmount = self::countMining($pet["id"]);

        if ($minedAmount > 0) {
            UserWalletLogic::add([
                "type" => $miningReward["id"],
                "uid" => $pet["uid"],
                "fromUid" => $pet["uid"],
                "toUid" => $pet["uid"],
                "distribution" => [$wallet["id"] => round($minedAmount, 8)],
                "refTable" => "user_pet",
                "refId" => $pet["id"],
            ]);

            // reward record
            RewardLogic::rewardRecord([
                "payAt" => date("Y-m-d H:i:s"),
                "usedAt" => date("Ymd"),
                "uid" => $pet["uid"],
                "userPetId" => $pet["id"],
                "fromUid" => $pet["uid"],
                "fromUserPetId" => $pet["id"],
                "rewardType" => $miningReward["id"],
                "amount" => round($minedAmount, 8),
                "distribution" => [$wallet["id"] => round($minedAmount, 8)],
                "refTable" => "user_pet",
                "refId" => $pet["id"],
            ]);
        }

        // update mining_cutoff_at everytime claim mining reward
        // cannot update first because count mining need mining_cutoff_at to count reward
        UserPetModel::defaultWhere()->where("id", $pet["id"])->update(["mining_cutoff_at" => date("Y-m-d H:i:s")]);
    }
}