<?php

namespace plugin\admin\app\controller\setting\gacha;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\SettingGachaModel;
use app\model\database\SettingPaymentModel;
use app\model\logic\HelperLogic;

class Update extends Base
{
    # [validation-rule]
    protected $rule = [
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
        if ($request->post("start_at") || $request->post("end_at")) {
            $this->rule["start_at"] .= "|require";
            $this->rule["end_at"] .= "|require";
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
                if (!empty($cleanVars["payment"])) {
                    $cleanVars["payment_id"] = $cleanVars["payment"];
                }

                # [unset key]
                unset($cleanVars["payment"]);

                $res = SettingGachaModel::where("id", $targetId)->update($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "update", "setting_gacha", $targetId);
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
        if (!empty($params["name"])) {
            if (SettingGachaModel::where("name", $params["name"])
                    ->whereNot("id", $params["id"])
                    ->first()
            ) {
                $this->error[] = "name:exists";
            }
        }

        if (!empty($params["payment"])) {
            if (!SettingPaymentModel::where("id", $params["payment"])->first()) {
                $this->error[] = "payment:invalid";
            }
        }

        if(!empty($params["start_at"]) && !empty($params["end_at"])) {
            if(strtotime($params["start_at"]) >= strtotime($params["end_at"])) {
                $this->error[] = "start_and_end_at:invalid";
            }
        }
    }
}