<?php

namespace plugin\admin\app\controller\setting\nft;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\SettingBlockchainNetworkModel;
use app\model\database\SettingNftModel;
use app\model\logic\HelperLogic;

class Listing extends Base
{
    # [validation-rule]
    protected $rule = [
        "id" => "number|max:11",
        "name" => "",
        "token_address" => "length:42|alphaNum",
        "network" => "number|max:11",
        "is_active" => "in:0,1",
        "remark" => "",
        "created_at_start" => "date",
        "created_at_end" => "date",
        "updated_at_start" => "date",
        "updated_at_end" => "date",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "id",
        "name",
        "token_address",
        "network",
        "is_active",
        "remark"
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "name",
        "token_address",
        "network",
        "is_active",
        "created_at",
        "updated_at",
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
            if (isset($cleanVars["network"])) {
                $network = SettingBlockchainNetworkModel::where("id", $cleanVars["network"])->first();
                $cleanVars["network"] = $network["id"] ?? 0;
            }

            # [search date range]
            $cleanVars = array_merge(
                $cleanVars,
                HelperLogic::buildDateSearch($request, ["created_at", "updated_at"])
            );

            # [listing query]
            $res = SettingNftModel::listing(
                $cleanVars,
                ["*"],
                ["id", "desc"]
            );

            # [result]
            if ($res) {
                # [add and edit column using for loop]
                foreach ($res as $row) {
                    $row["is_active"] = $row["is_active"] ? "active" : "inactive";

                    $network = SettingBlockchainNetworkModel::where("id", $row["network"])->first();
                    $row["network"] = $network ? $network["code"] : "";
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
