<?php

namespace plugin\admin\app\controller\setting\payment;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\SettingWalletModel;
use app\model\database\SettingPaymentModel;
use app\model\logic\HelperLogic;

class Create extends Base
{
    # [validation-rule]
    protected $rule = [
        "code" => "require",
        "filter" => "require",
        "formula_wallet" => "require",
        "formula_value" => "require",
        "calc_formula_wallet" => "require",
        "calc_formula_value" => "require",
        "is_active" => "require|in:0,1",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "code", 
        "filter", 
        "formula_wallet",
        "formula_value",
        "calc_formula_wallet",
        "calc_formula_value",
        "is_active",
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
                // encode input
                if (isset($cleanVars["filter"])) {
                    $filters = HelperLogic::explodeParams($cleanVars["filter"]);

                    $filterArray = [];
                    foreach($filters as $filter){
                        $filterArray[] = $filter;
                    }

                    $cleanVars["filter"] = json_encode($filterArray);
                }

                if (isset($cleanVars["formula_wallet"]) && isset($cleanVars["formula_value"])) {
                    $cleanVars["formula"] = json_encode(
                        HelperLogic::combineParamsToArray($cleanVars["formula_wallet"], $cleanVars["formula_value"])
                    );
                }

                if (isset($cleanVars["calc_formula_wallet"]) && isset($cleanVars["calc_formula_value"])) {
                    $cleanVars["calc_formula"] = json_encode(
                        HelperLogic::combineParamsToArray($cleanVars["calc_formula_wallet"], $cleanVars["calc_formula_value"])
                    );
                }

                # [unset key]
                unset($cleanVars["formula_wallet"]);
                unset($cleanVars["formula_value"]);
                unset($cleanVars["calc_formula_wallet"]);
                unset($cleanVars["calc_formula_value"]);

                $res = SettingPaymentModel::create($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "create", "setting_payment", $res["id"]);
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
        if (isset($params["code"])) {
            if (SettingPaymentModel::where("code", $params["code"])->first()) {
                $this->error[] = "code:exists";
            }
        }

        if (isset($params["formula_wallet"]) && isset($params["formula_value"])) {
            $formulaValueBreak = HelperLogic::explodeParams($params["formula_value"]);

            $checkWallet = SettingWalletModel::whereIn("id", $params["formula_wallet"])->get();
            if (count($checkWallet) != count($params["formula_wallet"])) {
                $this->error[] = "formula_wallet:invalid";
            }

            if (count($params["formula_wallet"]) != count($formulaValueBreak)) {
                $this->error[] = "formula_wallet_and_value:invalid";
            }
        }

        if (isset($params["calc_formula_wallet"]) && isset($params["calc_formula_value"])) {
            $calcFormulaValueBreak = HelperLogic::explodeParams($params["calc_formula_value"]);

            $checkWallet = SettingWalletModel::whereIn("id", $params["calc_formula_wallet"])->get();
            if (count($checkWallet) != count($params["calc_formula_wallet"])) {
                $this->error[] = "calc_formula_wallet:invalid";
            }

            if (array_diff($calcFormulaValueBreak, ["min", "max", "equal"])) {
                $this->error[] = "calc_formula_value:invalid";
            }

            if (count($params["calc_formula_wallet"]) != count($calcFormulaValueBreak)) {
                $this->error[] = "calc_formula_wallet_and_value:invalid";
            }
        }
    }
}
