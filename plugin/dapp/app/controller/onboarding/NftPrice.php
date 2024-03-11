<?php

namespace plugin\dapp\app\controller\onboarding;

# library
use plugin\dapp\app\controller\Base;
# database & logic
use app\model\logic\SettingLogic;

class NftPrice extends Base
{
    public function index()
    {
        $res = SettingLogic::get("general", ["code" => "nft_price", "category" => "onboarding"]);

        if ($res) {
            $this->response = [
                "success" => true,
                "data" => $res["value"] * 1
            ];
        }

        # [standard output]
        return $this->output();
    }
}