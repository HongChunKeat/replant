<?php

namespace plugin\admin\app\controller\setting\payment;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\SettingPaymentModel;
use app\model\logic\HelperLogic;

class Listing extends Base
{
    # [validation-rule]
    protected $rule = [
        "id" => "number|max:11",
        "code" => "",
        "is_active" => "in:0,1",
        "remark" => ""
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "id", 
        "code", 
        "is_active",
        "remark"
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "id", 
        "code", 
        "filter", 
        "formula", 
        "calc_formula", 
        "is_active",
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
            $res = SettingPaymentModel::listing(
                $cleanVars,
                ["*"],
                ["id", "desc"]
            );

            # [result]
            if ($res) {
                # [add and edit column using for loop]
                foreach ($res as $row) {
                    $row["is_active"] = $row["is_active"] ? "active" : "inactive";
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
