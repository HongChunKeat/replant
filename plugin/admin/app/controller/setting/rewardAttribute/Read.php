<?php

namespace plugin\admin\app\controller\setting\rewardAttribute;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\SettingAttributeModel;
use app\model\database\SettingRewardAttributeModel;
use app\model\database\SettingRewardModel;
use app\model\logic\HelperLogic;

class Read extends Base
{
    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "reward",
        "attribute",
        "value",
        "remark"
    ];


    public function index(Request $request, int $targetId = 0)
    {
        $res = SettingRewardAttributeModel::where("id", $targetId)->first();

        # [result]
        if ($res) {
            // type
            $reward_id = SettingRewardModel::where("id", $res["reward_id"])->first();
            $res["reward"] = $reward_id ? $reward_id["code"] : "";

            $attribute_id = SettingAttributeModel::where("id", $res["attribute_id"])->first();
            $res["attribute"] = $attribute_id ? $attribute_id["code"] : "";

            $this->response = [
                "success" => true,
                "data" => HelperLogic::formatOutput($res, $this->patternOutputs),
            ];
        }

        # [standard output]
        return $this->output();
    }
}
