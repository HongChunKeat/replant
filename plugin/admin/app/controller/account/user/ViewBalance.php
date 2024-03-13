<?php

namespace plugin\admin\app\controller\account\user;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\SettingWalletModel;
use app\model\logic\HelperLogic;
use plugin\dapp\app\model\logic\UserWalletLogic;

class ViewBalance extends Base
{
    # [outputs-pattern]
    protected $patternOutputs = [
        "code",
        "amount"
    ];

    public function index(Request $request, int $targetId = 0)
    {
        # [proceed]
        $res = SettingWalletModel::select("id", "code")->get();

        if ($res) {
            # [add and edit column using for loop]
            foreach ($res as $row) {
                $row["amount"] = UserWalletLogic::getBalance($targetId, $row["id"]);
            }

            # [result]
            $this->response = [
                "success" => true,
                "data" => HelperLogic::formatOutput($res, $this->patternOutputs, 1),
            ];
        }

        # [standard output]
        return $this->output();
    }
}
