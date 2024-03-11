<?php

namespace plugin\admin\app\controller\setting\rewardAttribute;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\SettingAttributeModel;
use app\model\database\SettingRewardAttributeModel;
use app\model\database\SettingRewardModel;
use app\model\logic\HelperLogic;

class Update extends Base
{
    # [validation-rule]
    protected $rule = [
        "reward" => "number",
        "attribute" => "number",
        "value" => "",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "reward",
        "attribute",
        "value",
        "remark"
    ];

    public function index(Request $request, int $targetId = 0)
    {
        # [validation]
        $this->validation($request->post(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->post(), $this->patternInputs, 1);

        # [checking]
        $this->checking(["id" => $targetId] + $cleanVars);

        # [proceed]
        if (!count($this->error)) {
            $res = "";

            # [process]
            if (count($cleanVars) > 0) {
                if (!empty($cleanVars["reward"])) {
                    $cleanVars["reward_id"] = $cleanVars["reward"];
                }

                if (!empty($cleanVars["attribute"])) {
                    $cleanVars["attribute_id"] = $cleanVars["attribute"];
                }

                # [unset key]
                unset($cleanVars["reward"]);
                unset($cleanVars["attribute"]);

                $res = SettingRewardAttributeModel::where("id", $targetId)->update($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "update", "setting_reward_attribute", $targetId);
                $this->response = [
                    "success" => true,
                ];
            }
        }

        # [standard output]
        return $this->output();
    }

    private function checking(array $params = [])
    {
        # [condition]
        if (!empty($params["reward"])) {
            if (!SettingRewardModel::where("id", $params["reward"])->first()) {
                $this->error[] = "reward:invalid";
            }
        }

        if (!empty($params["attribute"])) {
            if (!SettingAttributeModel::where("id", $params["attribute"])->first()) {
                $this->error[] = "attribute:invalid";
            }
        }

        if (!empty($params["reward"]) || !empty($params["attribute"])) {
            $check = SettingRewardAttributeModel::where("id", $params["id"])->first();

            if (SettingRewardAttributeModel::where([
                "reward_id" => empty($params["reward"])
                    ? $check["reward_id"]
                    : $params["reward"],
                "attribute_id" => empty($params["attribute"])
                    ? $check["attribute_id"]
                    : $params["attribute"],
                ])
                ->whereNot("id", $params["id"])
                ->first()
            ) {
                $this->error[] = "entry:exists";
            }
        }
    }
}