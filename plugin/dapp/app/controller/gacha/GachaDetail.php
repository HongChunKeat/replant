<?php

namespace plugin\dapp\app\controller\gacha;

# library
use support\Request;
use plugin\dapp\app\controller\Base;
# database & logic
use app\model\database\SettingGachaModel;
use app\model\logic\HelperLogic;
use app\model\logic\SettingLogic;

class GachaDetail extends Base
{
    # [validation-rule]
    protected $rule = [
        "gacha" => "require",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "gacha",
    ];

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

    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->get(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->get(), $this->patternInputs);

        # [checking]
        $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error)) {
            if (isset($cleanVars["gacha"])) {
                $gacha = SettingLogic::get("gacha", ["name" => $cleanVars["gacha"]]);
                $cleanVars["gacha"] = $gacha["id"] ?? 0;
            }

            $res = SettingGachaModel::where("id", $cleanVars["gacha"])->first();

            if ($res) {
                $res["timeleft"] = null;

                // get the first one only
                $payment = SettingLogic::get("payment", ["id" => $res["payment_id"]]);
                $decode = array_keys(json_decode($payment["formula"], 1));
                $wallet = SettingLogic::get("wallet", ["id" => $decode[0]]);
                $res["payment"] = $wallet["code"];

                if (!empty($res["start_at"]) && !empty($res["end_at"])) {
                    $res["timeleft"] = strtotime($res["end_at"]) . "000";
                }

                $this->response = [
                    "success" => true,
                    "data" => HelperLogic::formatOutput($res, $this->patternOutputs),
                ];
            }
        }

        # [standard output]
        return $this->output();
    }

    private function checking(array $params = [])
    {
        # [condition]
        if (isset($params["gacha"])) {
            $gacha = SettingLogic::get("gacha", ["name" => $params["gacha"], "is_show" => 1]);

            if (!$gacha) {
                $this->error[] = "gacha:invalid";
            } else {
                if (!empty($gacha["start_at"]) || !empty($gacha["end_at"])) {
                    if (
                        (isset($gacha["start_at"]) && time() < strtotime($gacha["start_at"])) ||
                        (isset($gacha["end_at"]) && time() > strtotime($gacha["end_at"]))
                    ) {
                        $this->error[] = "gacha:not_available";
                    }
                }

                $gachaItem = SettingLogic::get("gacha_item", ["gacha_id" => $gacha["id"]], true);
                if (!count($gachaItem)) {
                    $this->error[] = "gacha:no_items";
                }
            }
        }
    }
}