<?php

namespace plugin\admin\app\controller\setting\wallet;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\SettingWalletModel;
use app\model\logic\HelperLogic;

class Paging extends Base
{
    # [validation-rule]
    protected $rule = [
        "size" => "require|number",
        "page" => "require|number",
        "id" => "number|max:11",
        "image" => "max:100",
        "code" => "",
        "is_deposit" => "in:1,0",
        "is_withdraw" => "in:1,0",
        "is_transfer" => "in:1,0",
        "is_swap" => "in:1,0",
        "is_show" => "in:1,0",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "id",
        "image",
        "code",
        "is_deposit",
        "is_withdraw",
        "is_transfer",
        "is_swap",
        "is_show",
        "remark"
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "image",
        "code",
        "is_deposit",
        "is_withdraw",
        "is_transfer",
        "is_swap",
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

            # [paging query]
            $res = SettingWalletModel::paging(
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
                    $row["is_deposit"] = $row["is_deposit"] ? "yes" : "no";
                    $row["is_withdraw"] = $row["is_withdraw"] ? "yes" : "no";
                    $row["is_transfer"] = $row["is_transfer"] ? "yes" : "no";
                    $row["is_swap"] = $row["is_swap"] ? "yes" : "no";
                    $row["is_show"] = $row["is_show"] ? "yes" : "no";
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
