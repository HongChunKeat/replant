<?php

namespace plugin\admin\app\controller\permission\warehouse;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\PermissionWarehouseModel;
use app\model\logic\HelperLogic;

class Listing extends Base
{
    # [validation-rule]
    protected $rule = [
        "id" => "number|max:11",
        "code" => "",
        "from_site" => "",
        "path" => "max:255",
        "action" => "in:POST,GET,PUT,DELETE,PATCH",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "id",
        "code",
        "from_site",
        "path",
        "action",
        "remark",
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "code",
        "from_site",
        "path",
        "action",
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

            # [listing query]
            $res = PermissionWarehouseModel::listing(
                $cleanVars,
                ["*"],
                ["id", "desc"]
            );

            # [result]
            if ($res) {
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
