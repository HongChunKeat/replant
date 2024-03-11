<?php

namespace plugin\admin\app\model\logic;

# system lib
# database & logic
use app\model\database\RewardRecordModel;
use app\model\logic\HelperLogic;

class RewardLogic
{
    public static function rewardRecord(array $params)
    {
        $res = RewardRecordModel::create([
            "sn" => HelperLogic::generateUniqueSN("reward_record"),
            "pay_at" => $params["payAt"] ?? null,
            "used_at" => $params["usedAt"] ?? 0,
            "uid" => $params["uid"] ?? 0,
            "user_pet_id" => $params["userPetId"] ?? 0,
            "from_uid" => $params["fromUid"] ?? 0,
            "from_user_pet_id" => $params["fromUserPetId"] ?? 0,
            "reward_type" => $params["rewardType"] ?? 0,
            "amount" => $params["amount"] ?? 0,
            "rate" => $params["rate"] ?? 0,
            "item_reward" => !empty($params["itemReward"])
                ? json_encode($params["itemReward"])
                : null,
            "pet_reward" => !empty($params["petReward"])
                ? json_encode($params["petReward"])
                : null,
            "distribution" => !empty($params["distribution"])
                ? json_encode($params["distribution"])
                : null,
            "ref_table" => $params["refTable"] ?? "",
            "ref_id" => $params["refId"] ?? 0,
        ]);

        return $res;
    }
}
