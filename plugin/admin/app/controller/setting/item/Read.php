<?php

namespace plugin\admin\app\controller\setting\item;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\SettingItemModel;
use app\model\database\SettingPaymentModel;
use app\model\logic\HelperLogic;

class Read extends Base
{
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

    public function index(Request $request, int $targetId = 0)
    {
        $res = SettingItemModel::where("id", $targetId)->first();

        # [result]
        if ($res) {
            $res["is_show"] = $res["is_show"] ? "yes" : "no";

            $payment = SettingPaymentModel::where("id", $res["payment_id"])->first();
            $res["payment"] = $payment ? $payment["name"] : "";

            $this->response = [
                "success" => true,
                "data" => HelperLogic::formatOutput($res, $this->patternOutputs),
            ];
        }

        # [standard output]
        return $this->output();
    }
}
