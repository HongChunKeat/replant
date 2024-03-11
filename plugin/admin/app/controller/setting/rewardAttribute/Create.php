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

class Create extends Base
{
    # [validation-rule]
    protected $rule = [
        "reward" => "require|number",
        "attribute" => "require|number",
        "value" => "require",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "reward",
        "attribute",
        "value",
        "remark"
    ];

    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->post(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->post(), $this->patternInputs);

        # [checking]
        $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error)) {
            $res = "";

            # [process]
            if (count($cleanVars) > 0) {
                if (isset($cleanVars["reward"])) {
                    $cleanVars["reward_id"] = $cleanVars["reward"];
                }

                if (isset($cleanVars["attribute"])) {
                    $cleanVars["attribute_id"] = $cleanVars["attribute"];
                }

                # [unset key]
                unset($cleanVars["reward"]);
                unset($cleanVars["attribute"]);

                $res = SettingRewardAttributeModel::create($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "create", "setting_reward_attribute", $res["id"]);
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
        if (isset($params["reward"])) {
            if (!SettingRewardModel::where("id", $params["reward"])->first()) {
                $this->error[] = "reward:invalid";
            }
        }

        if (isset($params["attribute"])) {
            if (!SettingAttributeModel::where("id", $params["attribute"])->first()) {
                $this->error[] = "attribute:invalid";
            }
        }

        if (isset($params["reward"]) && isset($params["attribute"])) {
            if (SettingRewardAttributeModel::where(["reward_id" => $params["reward"], "attribute_id" => $params["attribute"]])->first()) {
                $this->error[] = "entry:exists";
            }
        }
    }
}
