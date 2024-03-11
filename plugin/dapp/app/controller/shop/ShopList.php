<?php

namespace plugin\dapp\app\controller\shop;

# library
use plugin\dapp\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\SettingItemModel;
use app\model\logic\HelperLogic;
use app\model\logic\SettingLogic;

class ShopList extends Base
{
    # [validation-rule]
    protected $rule = [
        "category" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "category",
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "image",
        "name",
        "description",
        "category",
        "normal_price",
        "sales_price",
        "payment",
        "effect",
    ];

    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->get(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->get(), $this->patternInputs);

        # [proceed]
        if (!count($this->error)) {
            $cleanVars[] = ["normal_price", ">", 0];
            $cleanVars[] = ["sales_price", ">", 0];
            $cleanVars["is_show"] = 1;

            # [paging query]
            $res = SettingItemModel::listing(
                $cleanVars,
                ["*"],
                ["id", "asc"]
            );

            if ($res) {
                foreach ($res as $row) {
                    $row["effect"] = HelperLogic::buildAttribute("item_attribute", ["item_id" => $row["id"]]);

                    // get the first one only
                    $payment = SettingLogic::get("payment", ["id" => $row["payment_id"]]);
                    $decode = array_keys(json_decode($payment["formula"], 1));
                    $wallet = SettingLogic::get("wallet", ["id" => $decode[0]]);
                    $row["payment"] = $wallet["code"];
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