<?php

namespace plugin\dapp\app\controller\setting;

# library
use plugin\dapp\app\controller\Base;
# database & logic
use app\model\logic\SettingLogic;

class DepositSetting extends Base
{
    public function index()
    {
        $min = SettingLogic::get("general", ["category" => "deposit", "code" => "deposit_min"]);
        $max = SettingLogic::get("general", ["category" => "deposit", "code" => "deposit_max"]);
        $wallet = SettingLogic::get("wallet", ["is_deposit" => 1, "is_show" => 1]);

        # [result]
        $this->response = [
            "success" => true,
            "data" => [
                "min" => $min["value"] * 1 ?? 0,
                "max" => $max["value"] * 1 ?? 0,
                "wallet" => $wallet["code"],
            ],
        ];

        # [standard output]
        return $this->output();
    }
}