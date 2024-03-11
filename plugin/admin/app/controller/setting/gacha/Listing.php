<?php

namespace plugin\admin\app\controller\setting\gacha;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\SettingGachaModel;
use app\model\database\SettingPaymentModel;
use app\model\logic\HelperLogic;

class Listing extends Base
{
    # [validation-rule]
    protected $rule = [
        "id" => "number|max:11",
        "image" => "",
        "name" => "",
        "single_normal_price" => "float|max:11",
        "single_sales_price" => "float|max:11",
        "ten_normal_price" => "float|max:11",
        "ten_sales_price" => "float|max:11",
        "payment" => "number|max:11",
        "is_show" => "in:1,0",
        "start_at" => "date",
        "end_at" => "date",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "id",
        "image",
        "name",
        "single_normal_price",
        "single_sales_price",
        "ten_normal_price",
        "ten_sales_price",
        "payment",
        "is_show",
        "start_at",
        "end_at",
        "remark",
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "image",
        "name",
        "single_normal_price",
        "single_sales_price",
        "ten_normal_price",
        "ten_sales_price",
        "payment",
        "is_show",
        "start_at",
        "end_at",
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

            if (isset($cleanVars["payment"])) {
                $payment = SettingPaymentModel::where("id", $cleanVars["payment"])->first();
                $cleanVars["payment_id"] = $payment["id"] ?? 0;
            }

            # [unset key]
            unset($cleanVars["payment"]);

            # [listing query]
            $res = SettingGachaModel::listing(
                $cleanVars,
                ["*"],
                ["id", "desc"]
            );

            # [result]
            if ($res) {
                # [add and edit column using for loop]
                foreach ($res as $row) {
                    $row["is_show"] = $row["is_show"] ? "yes" : "no";

                    $payment = SettingPaymentModel::where("id", $row["payment_id"])->first();
                    $row["payment"] = $payment ? $payment["code"] : "";
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
