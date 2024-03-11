<?php

namespace plugin\admin\app\controller\setting\gacha;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\SettingGachaModel;
use app\model\database\SettingPaymentModel;
use app\model\logic\HelperLogic;

class Read extends Base
{
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

    public function index(Request $request, int $targetId = 0)
    {
        # [process]
        $res = SettingGachaModel::where("id", $targetId)->first();

        # [result]
        if ($res) {
            $res["is_show"] = $res["is_show"] ? "yes" : "no";

            $payment = SettingPaymentModel::where("id", $res["payment_id"])->first();
            $res["payment"] = $payment ? $payment["code"] : "";

            $this->response = [
                "success" => true,
                "data" => HelperLogic::formatOutput($res, $this->patternOutputs),
            ];
        }

        # [standard output]
        return $this->output();
    }
}
