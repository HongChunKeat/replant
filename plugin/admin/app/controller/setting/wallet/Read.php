<?php

namespace plugin\admin\app\controller\setting\wallet;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\SettingWalletModel;
use app\model\logic\HelperLogic;

class Read extends Base
{
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

    public function index(Request $request, int $targetId = 0)
    {
        $res = SettingWalletModel::where("id", $targetId)->first();

        # [result]
        if ($res) {
            $res["is_deposit"] = $res["is_deposit"] ? "yes" : "no";
            $res["is_withdraw"] = $res["is_withdraw"] ? "yes" : "no";
            $res["is_transfer"] = $res["is_transfer"] ? "yes" : "no";
            $res["is_swap"] = $res["is_swap"] ? "yes" : "no";
            $res["is_show"] = $res["is_show"] ? "yes" : "no";

            $this->response = [
                "success" => true,
                "data" => HelperLogic::formatOutput($res, $this->patternOutputs),
            ];
        }

        # [standard output]
        return $this->output();
    }
}
