<?php

namespace plugin\dapp\app\controller\gacha;

# library
use plugin\dapp\app\controller\Base;
# database & logic
use app\model\database\SettingGachaModel;
use app\model\logic\HelperLogic;
use app\model\logic\SettingLogic;

class GachaList extends Base
{
    # [outputs-pattern]
    protected $patternOutputs = [
        "image",
        "name",
        "single_normal_price",
        "single_sales_price",
        "ten_normal_price",
        "ten_sales_price",
        "payment",
        "timeleft"
    ];

    public function index()
    {
        $res = SettingGachaModel::where("is_show", 1)
            ->where("start_at", "<=", date("Y-m-d H:i:s"))
            ->where("end_at", ">=", date("Y-m-d H:i:s"))
            ->orWhere(function($query) {
                $query->whereNull("start_at")->whereNull("end_at")->where("is_show", 1);
            })
            ->orderBy("id", "desc")
            ->get();

        if ($res) {
            foreach ($res as $row) {
                $row["timeleft"] = null;

                // get the first one only
                $payment = SettingLogic::get("payment", ["id" => $row["payment_id"]]);
                $decode = array_keys(json_decode($payment["formula"], 1));
                $wallet = SettingLogic::get("wallet", ["id" => $decode[0]]);
                $row["payment"] = $wallet["code"];

                if(!empty($row["start_at"]) && !empty($row["end_at"])) {
                    $row["timeleft"] = strtotime($row["end_at"])."000";
                }
            }

            $this->response = [
                "success" => true,
                "data" => HelperLogic::formatOutput($res, $this->patternOutputs, 1),
            ];
        }

        # [standard output]
        return $this->output();
    }
}