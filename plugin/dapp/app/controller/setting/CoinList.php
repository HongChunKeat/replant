<?php

namespace plugin\dapp\app\controller\setting;

# library
use plugin\dapp\app\controller\Base;
use support\Request;
# database & logic
use app\model\logic\HelperLogic;
use app\model\logic\SettingLogic;
use plugin\dapp\app\model\logic\UserWalletLogic;

class CoinList extends Base
{
    # [validation-rule]
    protected $rule = [
        "wallet" => "max:20",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "wallet",
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "code",
        "wallet",
        "balance",
    ];

    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->get(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->get(), $this->patternInputs);

        # user id
        $uid = $request->visitor["id"];

        # [proceed]
        if (!count($this->error)) {
            $res = "";

            $cleanVars["is_show"] = 1;

            if (isset($cleanVars["wallet"])) {
                $wallet = SettingLogic::get("wallet", ["code" => $cleanVars["wallet"]]);
                $cleanVars["wallet_id"] = $wallet["id"] ?? 0;
            }

            # [unset key]
            unset($cleanVars["wallet"]);

            $res = SettingLogic::get("coin", $cleanVars, true);

            # [result]
            if ($res) {
                # [add and edit column using for loop]
                foreach ($res as $row) {
                    $wallet = SettingLogic::get("wallet", ["id" => $row["wallet_id"]]);
                    $row["wallet"] = $wallet["code"] ?? "";

                    $row["balance"] = UserWalletLogic::getBalance($uid, $row["wallet_id"]) * 1;
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