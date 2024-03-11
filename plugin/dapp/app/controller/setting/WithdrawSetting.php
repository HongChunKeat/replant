<?php

namespace plugin\dapp\app\controller\setting;

# library
use plugin\dapp\app\controller\Base;
# database & logic
use app\model\logic\SettingLogic;

class WithdrawSetting extends Base
{
    public function index()
    {
        $fee = SettingLogic::get("general", ["category" => "withdraw", "code" => "withdraw_fee"]);
        $feeWalletSetting = SettingLogic::get("general", ["category" => "withdraw", "code" => "withdraw_fee_wallet"]);
        $feeWallet = SettingLogic::get("wallet", ["id" => $feeWalletSetting["value"]]);
        $min = SettingLogic::get("general", ["category" => "withdraw", "code" => "withdraw_min"]);
        $max = SettingLogic::get("general", ["category" => "withdraw", "code" => "withdraw_max"]);

        # [result]
        $this->response = [
            "success" => true,
            "data" => [
                "fee" => $fee["value"] * 1 ?? 0,
                "fee_wallet" => $feeWallet["code"] ?? null,
                "min" => $min["value"] * 1 ?? 0,
                "max" => $max["value"] * 1 ?? 0,
            ],
        ];

        # [standard output]
        return $this->output();
    }
}