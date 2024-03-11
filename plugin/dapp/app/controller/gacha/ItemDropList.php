<?php

namespace plugin\dapp\app\controller\gacha;

# library
use support\Request;
use plugin\dapp\app\controller\Base;
# database & logic
use app\model\database\SettingGachaItemModel;
use app\model\database\SettingPetModel;
use app\model\database\SettingItemModel;
use app\model\database\SettingWalletModel;
use app\model\logic\HelperLogic;
use app\model\logic\SettingLogic;
use plugin\admin\app\model\logic\ItemLogic;

class ItemDropList extends Base
{
    # [validation-rule]
    protected $rule = [
        "gacha" => "require",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "gacha",
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "image",
        "name",
        "rank",
        "mining_rate",
        "rarity",
        "category",
    ];

    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->get(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->get(), $this->patternInputs);

        # [checking]
        $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error)) {
            if (isset($cleanVars["gacha"])) {
                $gacha = SettingLogic::get("gacha", ["name" => $cleanVars["gacha"]]);
                $cleanVars["gacha_id"] = $gacha["id"] ?? 0;
            }

            # [unset key]
            unset($cleanVars["gacha"]);

            # [listing query]
            $res = SettingGachaItemModel::listing(
                $cleanVars,
                ["*"],
                ["occurrence", "asc"]
            );

            if ($res) {
                # [add and edit column using for loop]
                foreach ($res as $row) {
                    $row["image"] = null;
                    $row["name"] = null;
                    $row["rank"] = null;
                    $row["mining_rate"] = null;
                    $row["category"] = null;

                    if ($row["ref_table"] == "setting_pet") {
                        $pet = SettingPetModel::where("id", $row["ref_id"])->first();
                        if ($pet) {
                            $attribute = HelperLogic::buildAttribute("pet_attribute", ["pet_id" => $pet["id"]]);
                            $petRank = SettingLogic::get("pet_rank", ["quality" => $pet["quality"], "rank" => $attribute["rank"] ?? "N", "star" => $attribute["star"] ?? 0]);

                            $row["image"] = $pet["image"];
                            $row["name"] = $pet["name"];
                            $row["rank"] = $attribute["rank"];
                            $row["mining_rate"] = $petRank["mining_rate"] ?? 0;
                        }
                    } else if ($row["ref_table"] == "setting_item") {
                        $item = SettingItemModel::where("id", $row["ref_id"])->first();

                        if ($item) {
                            $row["image"] = $item["image"];
                            $row["name"] = $item["name"];
                            $row["category"] = $item["category"];
                        }
                    } else if ($row["ref_table"] == "setting_wallet") {
                        $wallet = SettingWalletModel::where("id", $row["ref_id"])->first();

                        if ($wallet) {
                            $row["image"] = $wallet["image"];
                            $row["name"] = $wallet["code"];
                        }
                    }

                    $row["rarity"] = ItemLogic::itemRarity($row["gacha_id"], $row["occurrence"]);
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

    private function checking(array $params = [])
    {
        # [condition]
        if (isset($params["gacha"])) {
            $gacha = SettingLogic::get("gacha", ["name" => $params["gacha"], "is_show" => 1]);

            if (!$gacha) {
                $this->error[] = "gacha:invalid";
            } else {
                if (!empty($gacha["start_at"]) || !empty($gacha["end_at"])) {
                    if (
                        (isset($gacha["start_at"]) && time() < strtotime($gacha["start_at"])) ||
                        (isset($gacha["end_at"]) && time() > strtotime($gacha["end_at"]))
                    ) {
                        $this->error[] = "gacha:not_available";
                    }
                }

                $gachaItem = SettingLogic::get("gacha_item", ["gacha_id" => $gacha["id"]], true);
                if (!count($gachaItem)) {
                    $this->error[] = "gacha:no_items";
                }
            }
        }
    }
}
