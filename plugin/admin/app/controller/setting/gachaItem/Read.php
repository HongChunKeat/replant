<?php

namespace plugin\admin\app\controller\setting\gachaItem;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\SettingGachaItemModel;
use app\model\database\SettingGachaModel;
use app\model\database\SettingItemModel;
use app\model\database\SettingPetModel;
use app\model\database\SettingWalletModel;
use app\model\logic\HelperLogic;
use plugin\admin\app\model\logic\ItemLogic;

class Read extends Base
{
    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "gacha",
        "ref_table",
        "ref_id",
        "ref_name",
        "token_reward",
        "occurrence",
        "drop_rate",
        "remark"
    ];

    public function index(Request $request, int $targetId = 0)
    {
        # [process]
        $res = SettingGachaItemModel::where("id", $targetId)->first();

        # [result]
        if ($res) {
            $gacha = SettingGachaModel::where("id", $res["gacha_id"])->first();
            $res["gacha"] = $gacha ? $gacha["name"] : "";

            if($res["ref_table"] == "setting_pet") {
                $item = SettingPetModel::where("id", $res["ref_id"])->first();
                $res["ref_name"] = $item ? $item["name"] : "";
            } else if ($res["ref_table"] == "setting_item") {
                $item = SettingItemModel::where("id", $res["ref_id"])->first();
                $res["ref_name"] = $item ? $item["name"] : "";
            } else if ($res["ref_table"] == "setting_wallet") {
                $item = SettingWalletModel::where("id", $res["ref_id"])->first();
                $res["ref_name"] = $item ? $item["code"] : "";
            }

            $itemDetails = ItemLogic::itemDropRate($res["gacha_id"], $res["id"]);
            $res["drop_rate"] = $itemDetails["drop_rate"];

            $this->response = [
                "success" => true,
                "data" => HelperLogic::formatOutput($res, $this->patternOutputs),
            ];
        }

        # [standard output]
        return $this->output();
    }
}
