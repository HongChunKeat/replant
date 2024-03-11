<?php

namespace plugin\admin\app\controller\setting\item;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\SettingItemModel;
use app\model\database\SettingPaymentModel;
use app\model\logic\HelperLogic;

class Update extends Base
{
    # [validation-rule]
    protected $rule = [
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

                $res = SettingItemModel::where("id", $targetId)->update($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "update", "setting_item", $targetId);
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
            if (SettingItemModel::where("name", $params["name"])
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
    }
}
