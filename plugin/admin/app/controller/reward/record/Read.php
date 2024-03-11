<?php

namespace plugin\admin\app\controller\reward\record;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\RewardRecordModel;
use app\model\database\AccountUserModel;
use app\model\database\SettingOperatorModel;
use app\model\logic\HelperLogic;

class Read extends Base
{
    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "sn",
        "created_at",
        "updated_at",
        "pay_at",
        "used_at",
        "uid",
        "user",
        "user_pet_id",
        "from_uid",
        "from_user",
        "from_user_pet_id",
        "reward_type",
        "amount",
        "rate",
        "item_reward",
        "item_reward_quantity",
        "pet_reward",
        "pet_reward_quantity",
        "distribution_wallet",
        "distribution_value",
        "ref_table",
        "ref_id",
        "remark",
    ];

    public function index(Request $request, int $targetId = 0)
    {
        # [process]
        $res = RewardRecordModel::where("id", $targetId)->first();

        # [result]
        if ($res) {
            // address
            $uid = AccountUserModel::where("id", $res["uid"])->first();
            $res["user"] = $uid ? $uid["user_id"] : "";

            $from_uid = AccountUserModel::where("id", $res["from_uid"])->first();
            $res["from_user"] = $from_uid ? $from_uid["user_id"] : "";

            $reward_type = SettingOperatorModel::where("id", $res["reward_type"])->first();
            $res["reward_type"] = $reward_type ? $reward_type["code"] : "";

            if(isset($res["item_reward"])){
                [$res["item_reward"], $res["item_reward_quantity"]] = HelperLogic::splitJsonParams($res["item_reward"]);
            }

            if(isset($res["pet_reward"])){
                [$res["pet_reward"], $res["pet_reward_quantity"]] = HelperLogic::splitJsonParams($res["pet_reward"]);
            }

            if(isset($res["distribution"])){
                [$res["distribution_wallet"], $res["distribution_value"]] = HelperLogic::splitJsonParams($res["distribution"]);
            }

            $this->response = [
                "success" => true,
                "data" => HelperLogic::formatOutput($res, $this->patternOutputs),
            ];
        }

        # [standard output]
        return $this->output();
    }
}
