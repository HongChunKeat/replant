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

class Update extends Base
{
    # [validation-rule]
    protected $rule = [
        "code" => "",
        "filter" => "",
        "formula_wallet" => "",
        "formula_value" => "",
        "calc_formula_wallet" => "",
        "calc_formula_value" => "",
        "is_active" => "in:0,1",
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

    public function index(Request $request, int $targetId = 0)
    {
        if ($request->post("formula_wallet") || $request->post("formula_value")) {
            $this->rule["formula_wallet"] .= "|require";
            $this->rule["formula_value"] .= "|require";
        }

        if ($request->post("calc_formula_wallet") || $request->post("calc_formula_value")) {
            $this->rule["calc_formula_wallet"] .= "|require";
            $this->rule["calc_formula_value"] .= "|require";
        }

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
                // encode input
                if (!empty($cleanVars["filter"])) {
                    $filters = HelperLogic::explodeParams($cleanVars["filter"]);

                    $filterArray = [];
                    foreach($filters as $filter){
                        $filterArray[] = $filter;
                    }

                    $cleanVars["filter"] = json_encode($filterArray);
                }

                if (!empty($cleanVars["formula_wallet"]) && !empty($cleanVars["formula_value"])) {
                    $cleanVars["formula"] = json_encode(
                        HelperLogic::combineParamsToArray($cleanVars["formula_wallet"], $cleanVars["formula_value"])
                    );
                }

                if (!empty($cleanVars["calc_formula_wallet"]) && !empty($cleanVars["calc_formula_value"])) {
                    $cleanVars["calc_formula"] = json_encode(
                        HelperLogic::combineParamsToArray($cleanVars["calc_formula_wallet"], $cleanVars["calc_formula_value"])
                    );
                }

                # [unset key]
                unset($cleanVars["formula_wallet"]);
                unset($cleanVars["formula_value"]);
                unset($cleanVars["calc_formula_wallet"]);
                unset($cleanVars["calc_formula_value"]);

                $res = SettingPaymentModel::where("id", $targetId)->update($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "update", "setting_payment", $targetId);
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
        if (!empty($params["code"])) {
            if (SettingPaymentModel::where("code", $params["code"])
                    ->whereNot("id", $params["id"])
                    ->first()
            ) {
                $this->error[] = "code:exists";
            }
        }

        if (!empty($params["formula_wallet"]) && !empty($params["formula_value"])) {
            $formulaValueBreak = HelperLogic::explodeParams($params["formula_value"]);

            $checkWallet = SettingWalletModel::whereIn("id", $params["formula_wallet"])->get();
            if (count($checkWallet) != count($params["formula_wallet"])) {
                $this->error[] = "formula_wallet:invalid";
            }

            if (count($params["formula_wallet"]) != count($formulaValueBreak)) {
                $this->error[] = "formula_wallet_and_value:invalid";
            }
        }

        if (!empty($params["calc_formula_wallet"]) && !empty($params["calc_formula_value"])) {
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
