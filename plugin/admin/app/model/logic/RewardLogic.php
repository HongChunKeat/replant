<?php

namespace plugin\admin\app\model\logic;

# database & logic
use app\model\database\RewardRecordModel;
use app\model\logic\HelperLogic;

class RewardLogic
{
    public static function rewardRecord(array $params)
    {
        $res = RewardRecordModel::create([
            "sn" => HelperLogic::generateUniqueSN("reward_record"),
            "pay_at" => $params["pay_at"] ?? null,
            "used_at" => $params["used_at"] ?? 0,
            "uid" => $params["uid"] ?? 0,
            "user_tree_id" => $params["user_tree_id"] ?? 0,
            "from_uid" => $params["from_uid"] ?? 0,
            "from_user_tree_id" => $params["from_user_tree_id"] ?? 0,
            "reward_type" => $params["reward_type"] ?? 0,
            "amount" => $params["amount"] ?? 0,
            "rate" => $params["rate"] ?? 0,
            "distribution" => !empty($params["distribution"])
                ? json_encode($params["distribution"])
                : null,
            "ref_table" => $params["ref_table"] ?? "",
            "ref_id" => $params["ref_id"] ?? 0,
        ]);

        return $res;
    }
}
