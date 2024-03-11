<?php

namespace plugin\dapp\app\controller\walletInfo;

# library
use plugin\dapp\app\controller\Base;
use support\Request;
# database & logic
use app\model\logic\HelperLogic;
use app\model\logic\SettingLogic;
use plugin\dapp\app\model\logic\UserWalletLogic;

class Wallet extends Base
{
    # [validation-rule]
    protected $rule = [
        "wallet" => "require|max:20",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "wallet",
    ];

    protected $patternOutputs = [
        "image",
        "code",
        "amount",
    ];

    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->get(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->get(), $this->patternInputs);

        # user id
        $cleanVars["uid"] = $request->visitor["id"];

        # [proceed]
        if (!count($this->error)) {
            $res = "";

            if(isset($cleanVars["wallet"])){
                $wallet = SettingLogic::get("wallet", ["code" => $cleanVars["wallet"]]);
                $cleanVars["wallet_id"] = $wallet["id"] ?? 0;
            }

            $res = SettingLogic::get("wallet", ["id" => $cleanVars["wallet_id"]]);

            # [result]
            if($res) {
                $res["amount"] = UserWalletLogic::getBalance($cleanVars["uid"], $cleanVars["wallet_id"]) * 1;

                $this->response = [
                    "success" => true,
                    "data" => HelperLogic::formatOutput($res, $this->patternOutputs),
                ];
            }
        }

        # [standard output]
        return $this->output();
    }
}