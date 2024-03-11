<?php

namespace plugin\dapp\app\controller\petInfo;

# library
use plugin\dapp\app\controller\Base;
# database & logic
use app\model\logic\HelperLogic;
use app\model\logic\SettingLogic;

class SlotPrice extends Base
{
    public function index()
    {
        # [proceed]
        $res = HelperLogic::buildAttributeGeneral(["code" => "pet_slot_price"]);

        # [result]
        if (count($res) > 0) {
            $output = [];
            $slotCount = 2;
            foreach ($res as $row) {
                $wallet = SettingLogic::get("wallet", ["id" => $row["key"]]);
                $level = SettingLogic::get("level", ["pet_slots" => $slotCount]);

                $output[] = [
                    "level" => $level["level"],
                    "price" => $row["value"] . " " . $wallet["code"],
                ];

                $slotCount++;
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