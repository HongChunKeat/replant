<?php

namespace plugin\admin\app\controller\setting\coin;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\SettingCoinModel;
use app\model\database\SettingWalletModel;
use app\model\logic\HelperLogic;

class Paging extends Base
{
    # [validation-rule]
    protected $rule = [
        "size" => "require|number",
        "page" => "require|number",
        "id" => "number|max:11",
        "code" => "",
        "wallet" => "number|max:11",
        "is_show" => "in:1,0",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "id",
        "code",
        "wallet",
        "is_show",
        "remark"
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "code",
        "wallet",
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

            # [search join table columns]
            if (isset($cleanVars["wallet"])) {
                $wallet = SettingWalletModel::where("id", $cleanVars["wallet"])->first();
                $cleanVars["wallet_id"] = $wallet["id"] ?? 0;
            }

            # [unset key]
            unset($cleanVars["wallet"]);

            # [paging query]
            $res = SettingCoinModel::paging(
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

                    $wallet = SettingWalletModel::where("id", $row["wallet_id"])->first();
                    $row["wallet"] = $wallet ? $wallet["code"] : "";
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
