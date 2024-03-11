<?php

namespace plugin\dapp\app\controller\gacha;

# library
use support\Request;
use plugin\dapp\app\controller\Base;
# database & logic
use app\model\database\UserGachaModel;
use app\model\logic\HelperLogic;
use app\model\logic\SettingLogic;
use plugin\admin\app\model\logic\ItemLogic;

class GachaHistory extends Base
{
    # [validation-rule]
    protected $rule = [
        "size" => "require|number",
        "page" => "require|number",
        "gacha" => "require",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "gacha",
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "name",
        "rarity",
        "token_reward",
        "created_at",
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
            if (isset($cleanVars["gacha"])) {
                $gacha = SettingLogic::get("gacha", ["name" => $cleanVars["gacha"]]);
                $cleanVars["gacha_id"] = $gacha["id"] ?? 0;
            }

            # [unset key]
            unset($cleanVars["gacha"]);

            # [paging query]
            $res = UserGachaModel::paging(
                $cleanVars,
                $request->get("page"),
                $request->get("size"),
                ["*"],
                ["id", "desc"]
            );

            if ($res) {
                # [add and edit column using for loop]
                foreach ($res["items"] as $row) {
                    $row["name"] = null;
                    $row["token_reward"] = $row["token_reward"] * 1;

                    if (!empty($row["pet_id"])) {
                        $pet = SettingLogic::get("pet", ["id" => $row["pet_id"]]);

                        if ($pet) {
                            $row["name"] = $pet["name"];
                            $refTable = "setting_pet";
                            $refId = $pet["id"];
                        }
                    }

                    if (!empty($row["item_id"])) {
                        $item = SettingLogic::get("item", ["id" => $row["item_id"]]);

                        if ($item) {
                            $row["name"] = $item["name"];
                            $refTable = "setting_item";
                            $refId = $item["id"];
                        }
                    }

                    if (!empty($row["wallet_id"])) {
                        $wallet = SettingLogic::get("wallet", ["id" => $row["wallet_id"]]);

                        if ($wallet) {
                            $row["name"] = $wallet["code"];
                            $refTable = "setting_wallet";
                            $refId = $wallet["id"];
                        }
                    }

                    if (isset($refTable) && isset($refId)) {
                        $gachaItem = SettingLogic::get("gacha_item", ["gacha_id" => $row["gacha_id"], "ref_table" => $refTable, "ref_id" => $refId]);

                        if ($gachaItem) {
                            $row["rarity"] = ItemLogic::itemRarity($row["gacha_id"], $gachaItem["occurrence"]);
                        }
                    }
                }

                $this->response = [
                    "success" => true,
                    "data" => [
                        "data" => HelperLogic::formatOutput($res["items"], $this->patternOutputs, 1),
                        "count" => $res["count"],
                        "last_page" => ceil($res["count"] / $request->get("size")),
                    ],
                ];
            }
        }

        # [standard output]
        return $this->output();
    }
}
