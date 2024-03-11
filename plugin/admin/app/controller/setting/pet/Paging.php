<?php

namespace plugin\admin\app\controller\setting\pet;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\SettingPetModel;
use app\model\logic\HelperLogic;

class Paging extends Base
{
    # [validation-rule]
    protected $rule = [
        "size" => "require|number",
        "page" => "require|number",
        "id" => "number|max:11",
        "image" => "max:100",
        "gif" => "max:100",
        "name" => "",
        "quality" => "in:normal,premium",
        "is_show" => "in:1,0",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "id",
        "image",
        "gif",
        "name",
        "quality",
        "is_show",
        "remark"
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "image",
        "gif",
        "name",
        "quality",
        "is_show",
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

            # [paging query]
            $res = SettingPetModel::paging(
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
