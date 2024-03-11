<?php

namespace plugin\dapp\app\controller\inventoryInfo;

# library
use plugin\dapp\app\controller\Base;
# database & logic
use app\model\logic\HelperLogic;
use app\model\logic\SettingLogic;

class PagePrice extends Base
{
    public function index()
    {
        # [proceed]
        $res = HelperLogic::buildAttributeGeneral(["code" => "inventory_page_price"]);

        # [result]
        if (count($res) > 0) {
            $output = [];
            foreach ($res as $row) {
                $wallet = SettingLogic::get("wallet", ["id" => $row["key"]]);

                $output[] = [
                    "price" => $row["value"] . " " . $wallet["code"],
                ];
            }

            $this->response = [
                "success" => true,
                "data" => $output,
            ];
        }

        # [standard output]
        return $this->output();
    }
}