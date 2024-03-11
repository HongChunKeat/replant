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

class Create extends Base
{
    # [validation-rule]
    protected $rule = [
        "image" => "require|max:100",
        "name" => "require",
        "description" => "require|max:500",
        "category" => "require",
        "normal_price" => "require|float|egt:0|max:20",
        "sales_price" => "require|float|egt:0|max:20",
        "payment" => "require|number|max:11",
        "is_show" => "require|in:1,0",
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
                if (isset($cleanVars["payment"])) {
                    $cleanVars["payment_id"] = $cleanVars["payment"];
                }

                # [unset key]
                unset($cleanVars["payment"]);

                $res = SettingItemModel::create($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "create", "setting_item", $res["id"]);
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
        if (isset($params["name"])) {
            if (SettingItemModel::where("name", $params["name"])->first()) {
                $this->error[] = "name:exists";
            }
        }

        if (isset($params["payment"])) {
            if (!SettingPaymentModel::where("id", $params["payment"])->first()) {
                $this->error[] = "payment:invalid";
            }
        }
    }
}
