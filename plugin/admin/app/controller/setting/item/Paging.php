<?php

namespace plugin\admin\app\controller\setting\item;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\SettingItemModel;
use app\model\database\SettingPaymentModel;
use app\model\logic\HelperLogic;

class Paging extends Base
{
    # [validation-rule]
    protected $rule = [
        "size" => "require|number",
        "page" => "require|number",
        "id" => "number|max:11",
        "image" => "max:100",
        "name" => "",
        "description" => "max:500",
        "category" => "",
        "normal_price" => "float|egt:0|max:20",
        "sales_price" => "float|egt:0|max:20",
        "payment" => "number|max:11",
        "is_show" => "in:1,0",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "id",
        "image",
        "name",
        "description",
        "category",
        "normal_price",
        "sales_price",
        "payment",
        "is_show",
        "remark"
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "image",
        "name",
        "description",
        "category",
        "normal_price",
        "sales_price",
        "payment",
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

            if (isset($cleanVars["payment"])) {
                $payment = SettingPaymentModel::where("id", $cleanVars["payment"])->first();
                $cleanVars["payment_id"] = $payment["id"] ?? 0;
            }

            # [unset key]
            unset($cleanVars["payment"]);

            # [paging query]
            $res = SettingItemModel::paging(
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

                    $payment = SettingPaymentModel::where("id", $row["payment_id"])->first();
                    $row["payment"] = $payment ? $payment["code"] : "";
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
