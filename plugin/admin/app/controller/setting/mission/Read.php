<?php

namespace plugin\admin\app\controller\setting\mission;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\SettingMissionModel;
use app\model\logic\HelperLogic;

class Read extends Base
{
    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "name",
        "description",
        "level",
        "item_reward",
        "item_reward_quantity",
        "pet_reward",
        "pet_reward_quantity",
        "currency_reward_wallet",
        "currency_reward_value",
        "requirement",
        "action",
        "type",
        "stamina",
        "is_show",
        "remark",
    ];

    public function index(Request $request, int $targetId = 0)
    {
        # [process]
        $res = SettingMissionModel::where("id", $targetId)->first();

        # [result]
        if ($res) {
            $res["is_show"] = $res["is_show"] ? "yes" : "no";

            if(isset($res["item_reward"])){
                [$res["item_reward"], $res["item_reward_quantity"]] = HelperLogic::splitJsonParams($res["item_reward"]);
            }

            if(isset($res["pet_reward"])){
                [$res["pet_reward"], $res["pet_reward_quantity"]] = HelperLogic::splitJsonParams($res["pet_reward"]);
            }

            if(isset($res["currency_reward"])){
                [$res["currency_reward_wallet"], $res["currency_reward_value"]] = HelperLogic::splitJsonParams($res["currency_reward"]);
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
