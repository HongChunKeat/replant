<?php

namespace plugin\admin\app\controller\setting\walletAttribute;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\SettingWalletAttributeModel;
use app\model\database\SettingWalletModel;
use app\model\logic\HelperLogic;

class Listing extends Base
{
    # [validation-rule]
    protected $rule = [
        "id" => "number|max:11",
        "from_wallet" => "number|max:11",
        "to_wallet" => "number|max:11",
        "fee_wallet" => "number|max:11",
        "to_self" => "in:0,1",
        "to_other" => "in:0,1",
        "to_self_fee" => "float|egt:0|max:20",
        "to_other_fee" => "float|egt:0|max:20",
        "to_self_rate" => "float|max:20",
        "to_other_rate" => "float|max:20",
        "is_show" => "in:0,1",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "id",
        "from_wallet",
        "to_wallet",
        "fee_wallet",
        "to_self",
        "to_other",
        "to_self_fee",
        "to_other_fee",
        "to_self_rate",
        "to_other_rate",
        "is_show",
        "remark",
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "from_wallet_id",
        "from_wallet",
        "to_wallet_id",
        "to_wallet",
        "fee_wallet_id",
        "fee_wallet",
        "to_self",
        "to_other",
        "to_self_fee",
        "to_other_fee",
        "to_self_rate",
        "to_other_rate",
        "is_show",
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

            # [search join table columns]
            if (isset($cleanVars["from_wallet"])) {
                $from_wallet = SettingWalletModel::where("id", $cleanVars["from_wallet"])->first();
                $cleanVars["from_wallet_id"] = $from_wallet["id"] ?? 0;
            }

            if (isset($cleanVars["to_wallet"])) {
                $to_wallet = SettingWalletModel::where("id", $cleanVars["to_wallet"])->first();
                $cleanVars["to_wallet_id"] = $to_wallet["id"] ?? 0;
            }

            if (isset($cleanVars["fee_wallet"])) {
                $fee_wallet = SettingWalletModel::where("id", $cleanVars["fee_wallet"])->first();
                $cleanVars["fee_wallet_id"] = $fee_wallet["id"] ?? 0;
            }

            # [unset key]
            unset($cleanVars["from_wallet"]);
            unset($cleanVars["to_wallet"]);
            unset($cleanVars["fee_wallet"]);

            # [listing query]
            $res = SettingWalletAttributeModel::listing(
                $cleanVars,
                ["*"],
                ["id", "desc"]
            );

            # [result]
            if ($res) {
                # [add and edit column using for loop]
                foreach ($res as $row) {
                    $row["to_self"] = $row["to_self"] ? "yes" : "no";
                    $row["to_other"] = $row["to_other"] ? "yes" : "no";
                    $row["is_admin"] = $row["is_admin"] ? "yes" : "no";
                    $row["is_show"] = $row["is_show"] ? "yes" : "no";

                    $from_wallet_id = SettingWalletModel::where("id", $row["from_wallet_id"])->first();
                    $row["from_wallet"] = $from_wallet_id ? $from_wallet_id["code"] : "";

                    $to_wallet_id = SettingWalletModel::where("id", $row["to_wallet_id"])->first();
                    $row["to_wallet"] = $to_wallet_id ? $to_wallet_id["code"] : "";

                    $fee_wallet_id = SettingWalletModel::where("id", $row["fee_wallet_id"])->first();
                    $row["fee_wallet"] = $fee_wallet_id ? $fee_wallet_id["code"] : "";
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
