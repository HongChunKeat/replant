<?php

namespace plugin\admin\app\controller\user\gacha;

# library
use support\Request;
use plugin\admin\app\controller\Base;
# database & logic
use plugin\dapp\app\model\logic\UserProfileLogic;
use app\model\database\AccountUserModel;
use app\model\database\SettingGachaModel;
use app\model\database\SettingPetModel;
use app\model\database\SettingItemModel;
use app\model\database\SettingWalletModel;
use app\model\database\UserGachaModel;
use app\model\logic\HelperLogic;

class Listing extends Base
{
    # [validation-rule]
    protected $rule = [
        "id" => "number|max:11",
        "uid" => "number|max:11",
        "user" => "",
        "gacha" => "number|max:11",
        "pet" => "number|max:11",
        "item" => "number|max:11",
        "wallet" => "number|max:11",
        "token_reward" => "float|max:11",
        "ref_table" => "",
        "ref_id" => "number|max:11",
        "remark" => "",
        "created_at_start" => "date",
        "created_at_end" => "date",
        "updated_at_start" => "date",
        "updated_at_end" => "date",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "id",
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

    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->get(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->get(), $this->patternInputs);

        # [proceed]
        if (!count($this->error)) {

            # [search join table columns]
            if (isset($cleanVars["user"])) {
                // 4 in 1 search
                $user = UserProfileLogic::multiSearch($cleanVars["user"]);
                $cleanVars["uid"] = $user["id"] ?? 0;
            }

            if (isset($cleanVars["gacha"])) {
                $cleanVars["gacha_id"] = $cleanVars["gacha"];
            }

            if (isset($cleanVars["pet"])) {
                $cleanVars["pet_id"] = $cleanVars["pet"];
            }

            if (isset($cleanVars["item"])) {
                $cleanVars["item_id"] = $cleanVars["item"];
            }

            if (isset($cleanVars["wallet"])) {
                $cleanVars["wallet_id"] = $cleanVars["wallet"];
            }

            # [unset key]
            unset($cleanVars["user"]);
            unset($cleanVars["gacha"]);
            unset($cleanVars["pet"]);
            unset($cleanVars["item"]);
            unset($cleanVars["wallet"]);

            # [search date range]
            $cleanVars = array_merge(
                $cleanVars, 
                HelperLogic::buildDateSearch($request, ["created_at", "updated_at"])
            );

            # [listing query]
            $res = UserGachaModel::listing(
                $cleanVars,
                ["*"],
                ["id", "desc"]
            );

            # [result]
            if ($res) {
                # [add and edit column using for loop]
                foreach ($res as $row) {
                    $user = AccountUserModel::where("id", $row["uid"])->first();
                    $row["user"] = $user ? $user["user_id"] : "";

                    $gacha = SettingGachaModel::where("id", $row["gacha_id"])->first();
                    $row["gacha"] = $gacha ? $gacha["name"] : "";

                    if (isset($row["pet_id"])) {
                        $pet = SettingPetModel::where("id", $row["pet_id"])->first();
                        $row["pet"] = $pet ? $pet["name"] : "";
                    }

                    if (isset($row["item_id"])) {
                        $item = SettingItemModel::where("id", $row["item_id"])->first();
                        $row["item"] = $item ? $item["name"] : "";
                    }

                    if (isset($row["wallet_id"])) {
                        $wallet = SettingWalletModel::where("id", $row["wallet_id"])->first();
                        $row["wallet"] = $wallet ? $wallet["code"] : "";
                    }
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
