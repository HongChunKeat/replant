<?php

namespace plugin\admin\app\controller\setting\mission;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\SettingMissionModel;
use app\model\logic\HelperLogic;

class Paging extends Base
{
    # [validation-rule]
    protected $rule = [
        "size" => "require|number",
        "page" => "require|number",
        "id" => "number|max:11",
        "name" => "max:200",
        "description" => "max:500",
        "level" => "number|egt:0|max:11",
        "item_reward" => "",
        "pet_reward" => "",
        "currency_reward" => "",
        "requirement" => "",
        "action" => "in:internal,external,bot",
        "type" => "in:daily,weekly,permanent,limited,onboarding",
        "stamina" => "number|egt:0|max:11",
        "is_show" => "in:1,0",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "id",
        "name",
        "description",
        "level",
        "item_reward",
        "pet_reward",
        "currency_reward",
        "requirement",
        "action",
        "type",
        "stamina",
        "is_show",
        "remark",
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "name",
        "description",
        "level",
        "item_reward",
        "pet_reward",
        "currency_reward",
        "requirement",
        "action",
        "type",
        "stamina",
        "is_show",
        "remark",
    ];

    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->get(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->get(), $this->patternInputs);

        # [proceed]
        if (!count($this->error)) {

            # [paging query]
            $res = SettingMissionModel::paging(
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
                    $row["is_show"] = $row["is_show"] ? "yes" : "no";
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
