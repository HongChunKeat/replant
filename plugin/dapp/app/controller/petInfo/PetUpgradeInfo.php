<?php

namespace plugin\dapp\app\controller\petInfo;

# library
use plugin\dapp\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\UserPetModel;
use app\model\logic\HelperLogic;
use app\model\logic\SettingLogic;

class PetUpgradeInfo extends Base
{
    # [validation-rule]
    protected $rule = [
        "sn" => "require",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "sn",
    ];

    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->get(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->get(), $this->patternInputs);

        # user id
        $cleanVars["uid"] = $request->visitor["id"];

        $userPet = UserPetModel::defaultWhere()->where(["uid" => $cleanVars["uid"], "sn" => $cleanVars["sn"]])->first();

        if ($userPet) {
            // get next star stats
            $nextStar = SettingLogic::get("pet_rank", ["quality" => $userPet["quality"], "rank" => $userPet["rank"], "star" => $userPet["star"] + 1]);

            $itemRequired = [];

            if ($nextStar) {
                if (isset($nextStar["item_required"])) {
                    $breakItem = json_decode($nextStar["item_required"]);

                    foreach ($breakItem as $key => $value) {
                        $item = SettingLogic::get("item", ["id" => $key]);
                        if($item) {
                            $itemRequired[$item["name"]] = [
                                "image" => $item["image"],
                                "quantity" => $value,
                            ];
                        }
                    }
                }
            }

            $this->response = [
                "success" => true,
                "data" => [
                    "current_star" => $userPet["star"],
                    "next_star" => $nextStar
                        ? $nextStar["star"]
                        : "max",
                    "item_required" => $itemRequired,
                ],
            ];
        }

        # [standard output]
        return $this->output();
    }
}