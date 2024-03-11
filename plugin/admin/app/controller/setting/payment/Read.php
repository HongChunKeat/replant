<?php

namespace plugin\admin\app\controller\setting\payment;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\SettingWalletModel;
use app\model\database\SettingPaymentModel;
use app\model\logic\HelperLogic;

class Read extends Base
{
    # [outputs-pattern]
    protected $patternOutputs = [
        "id", 
        "code", 
        "filter", 
        "formula_wallet",
        "formula_value",
        "calc_formula_wallet",
        "calc_formula_value",
        "is_active",
        "remark"
    ];

    public function index(Request $request, int $targetId = 0)
    {
        $res = SettingPaymentModel::where("id", $targetId)->first();
        
        # [result]
        if ($res) {
            $res["is_active"] = $res["is_active"] ? "active" : "inactive";

            $res["filter"] = json_decode($res["filter"])[0];

            [$res["formula_wallet"], $res["formula_value"]] = HelperLogic::splitJsonParams($res["formula"]);
            [$res["calc_formula_wallet"], $res["calc_formula_value"]] = HelperLogic::splitJsonParams($res["calc_formula"]);

            $this->response = [
                "success" => true,
                "data" => HelperLogic::formatOutput($res, $this->patternOutputs),
            ];
        }

        # [standard output]
        return $this->output();
    }
}
