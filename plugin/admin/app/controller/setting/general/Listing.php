<?php

namespace plugin\admin\app\controller\setting\general;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\SettingGeneralModel;
use app\model\logic\HelperLogic;

class Listing extends Base
{
    # [validation-rule]
    protected $rule = [
        "id" => "number|max:11",
        "category" => "",
        "code" => "",
        "is_show" => "in:1,0",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "id",
        "category",
        "code",
        "is_show",
        "remark"
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "category",
        "code",
        "value",
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

            # [listing query]
            $res = SettingGeneralModel::listing(
                $cleanVars,
                ["*"],
                ["id", "desc"]
            );

            # [result]
            if ($res) {
                # [add and edit column using for loop]
                foreach ($res as $row) {
                    $row["is_show"] = $row["is_show"] ? "yes" : "no";
                }

                $this->response = [
                    "success" => true,
                    "data" => HelperLogic::formatOutput($res, $this->patternOutputs, 1),
                ];
            }
        }

        # [standard output]
        return $this->output();
    }
}
