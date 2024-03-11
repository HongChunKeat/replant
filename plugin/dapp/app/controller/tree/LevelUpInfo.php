<?php

namespace plugin\dapp\app\controller\tree;

# library
use plugin\dapp\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\UserLevelModel;
use app\model\logic\HelperLogic;
use app\model\logic\SettingLogic;

class LevelUpInfo extends Base
{
    public function index(Request $request)
    {
        # user id
        $cleanVars["uid"] = $request->visitor["id"];

        $userLevel = UserLevelModel::where(["uid" => $cleanVars["uid"], "is_current" => 1])->first();

        if ($userLevel) {
            // get next level stats
            $nextLevel = SettingLogic::get("level", ["level" => $userLevel["level"] + 1]);

            $itemRequired = [];
            $petRequired = [];

            if ($nextLevel) {
                if (!empty($nextLevel["item_required"])) {
                    $breakItem = json_decode($nextLevel["item_required"]);

                    foreach ($breakItem as $key => $value) {
                        $item = SettingLogic::get("item", ["id" => $key]);
                        if ($item) {
                            $itemRequired[$item["name"]] = [
                                "image" => $item["image"],
                                "quantity" => $value,
                            ];
                        }
                    }
                }

                if (!empty($nextLevel["pet_required"])) {
                    $breakPet = json_decode($nextLevel["pet_required"]);

                    foreach ($breakPet as $key => $value) {
                        $attribute = null;
                        $pet = SettingLogic::get("pet", ["id" => $key]);
                        if ($pet) {
                            $attribute = HelperLogic::buildAttribute("pet_attribute", ["pet_id" => $pet["id"]]);
                            $petRequired[$pet["name"]] = [
                                "image" => $pet["image"],
                                "quality" => $pet["quality"],
                                "rank" => $attribute["rank"] ?? "N",
                                "quantity" => $value,
                            ];
                        }
                    }
                }
            }

            $this->response = [
                "success" => true,
                "data" => [
                    "current_level" => $userLevel["level"],
                    "next_level" => $nextLevel
                        ? $nextLevel["level"]
                        : "max",
                    "item_required" => $itemRequired,
                    "pet_required" => $petRequired,
                ],
            ];
        }

        # [standard output]
        return $this->output();
    }
}