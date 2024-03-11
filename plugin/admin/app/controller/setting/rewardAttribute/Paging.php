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

class Paging extends Base
{
    # [validation-rule]
    protected $rule = [
        "size" => "require|number",
        "page" => "require|number",
        "id" => "number|max:11",
        "reward" => "number|max:11",
        "attribute" => "number|max:11",
        "value" => "",
        "remark" => ""
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "id",
        "reward",
        "attribute",
        "value",
        "remark"
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "reward",
        "attribute",
        "value",
        "remark"
    ];

    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->get(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->get(), $this->patternInputs);

        # [proceed]
        if (!count($this->error)) {

            // type
            if (isset($cleanVars["reward"])) {
                $reward = SettingRewardModel::where("id", $cleanVars["reward"])->first();
                $cleanVars["reward_id"] = $reward["id"] ?? 0;
            }

            if (isset($cleanVars["attribute"])) {
                $attribute = SettingAttributeModel::where("id", $cleanVars["attribute"])->first();
                $cleanVars["attribute_id"] = $attribute["id"] ?? 0;
            }

            # [unset key]
            unset($cleanVars["reward"]);
            unset($cleanVars["attribute"]);

            # [paging query]
            $res = SettingRewardAttributeModel::paging(
                $cleanVars,
                $request->get("page"),
                $request->get("size"),
                ["*"],
                ["id", "desc"]
            );

            # [result]
            if ($res) {
                # [add and edit column using for loop]
                foreach ($res["items"] as $row) {
                    // type
                    $reward_id = SettingRewardModel::where("id", $row["reward_id"])->first();
                    $row["reward"] = $reward_id ? $reward_id["code"] : "";

                    $attribute_id = SettingAttributeModel::where("id", $row["attribute_id"])->first();
                    $row["attribute"] = $attribute_id ? $attribute_id["code"] : "";
                }

                $this->response = [
                    "success" => true,
                    "data" => [
                        "data" => HelperLogic::formatOutput($res["items"], $this->patternOutputs, 1),
                        "count" => $res["count"],
                        "last_page" => ceil($res["count"] / $request->get("size")),
                    ],
                ];
            }
        }

        # [standard output]
        return $this->output();
    }
}
