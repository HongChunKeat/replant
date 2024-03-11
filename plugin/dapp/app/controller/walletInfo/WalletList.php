<?php

namespace plugin\dapp\app\controller\walletInfo;

# library
use plugin\dapp\app\controller\Base;
use support\Request;
# database & logic
use app\model\logic\HelperLogic;
use app\model\logic\SettingLogic;
use plugin\dapp\app\model\logic\UserWalletLogic;

class WalletList extends Base
{
    # [outputs-pattern]
    protected $patternOutputs = [
        "image",
        "code",
        "amount"
    ];

    public function index(Request $request)
    {
        # user id
        $cleanVars["uid"] = $request->visitor["id"];

        # [proceed]
        if (!count($this->error)) {
            $res = SettingLogic::get("wallet", ["is_show" => 1], true);

            if ($res) {
                # [add and edit column using for loop]
                foreach ($res as $row) {
                    $row["amount"] = UserWalletLogic::getBalance($cleanVars["uid"], $row["id"]) * 1;
                }

                # [result]
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