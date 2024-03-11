<?php

namespace plugin\admin\app\controller\user\gacha;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\AccountUserModel;
use app\model\database\SettingGachaModel;
use app\model\database\SettingPetModel;
use app\model\database\SettingItemModel;
use app\model\database\SettingWalletModel;
use app\model\database\UserGachaModel;
use app\model\logic\HelperLogic;

class Read extends Base
{
    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "created_at",
        "updated_at",
        "uid",
        "user",
        "gacha",
        "pet",
        "item",
        "wallet",
        "token_reward",
        "ref_table",
        "ref_id",
        "remark",
    ];

    public function index(Request $request, int $targetId = 0)
    {
        # [process]
        $res = UserGachaModel::where("id", $targetId)->first();

        # [result]
        if ($res) {
            $user = AccountUserModel::where("id", $res["uid"])->first();
            $res["user"] = $user ? $user["user_id"] : "";

            $gacha = SettingGachaModel::where("id", $res["gacha_id"])->first();
            $res["gacha"] = $gacha ? $gacha["name"] : "";

            if (isset($res["pet_id"])) {
                $pet = SettingPetModel::where("id", $res["pet_id"])->first();
                $res["pet"] = $pet ? $pet["name"] : "";
            }

            if (isset($res["item_id"])) {
                $item = SettingItemModel::where("id", $res["item_id"])->first();
                $res["item"] = $item ? $item["name"] : "";
            }

            if (isset($res["wallet_id"])) {
                $wallet = SettingWalletModel::where("id", $res["wallet_id"])->first();
                $res["wallet"] = $wallet ? $wallet["code"] : "";
            }

            $this->response = [
                "success" => true,
                "data" => HelperLogic::formatOutput($res, $this->patternOutputs),
            ];
        }

        # [standard output]
        return $this->output();
    }
}