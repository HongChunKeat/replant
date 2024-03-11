<?php

namespace plugin\dapp\app\controller\marketInfo;

# library
use plugin\dapp\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\UserInventoryModel;
use app\model\database\UserMarketModel;
use app\model\database\UserPetModel;
use app\model\logic\HelperLogic;
use app\model\logic\SettingLogic;

class OnsalesList extends Base
{
    # [validation-rule]
    protected $rule = [
        "size" => "require|number",
        "page" => "require|number",
    ];

    protected $patternOutputs = [
        "sn",
        "image",
        "name",
        "pet",
        "item",
        "amount",
        "payment",
        "created_at",
    ];

    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->get(), $this->rule);

        # user id
        $cleanVars["seller_uid"] = $request->visitor["id"];

        # [proceed]
        if (!count($this->error)) {
            # [paging query]
            $cleanVars[] = ["removed_at", null];
            $cleanVars[] = ["sold_at", null];

            # [paging query]
            $res = UserMarketModel::paging(
                $cleanVars,
                $request->get("page"),
                $request->get("size"),
                ["*"],
                ["id", "desc"]
            );

            # [result]
            if ($res) {
                foreach ($res["items"] as $row) {
                    $row["image"] = null;
                    $row["name"] = null;
                    $row["pet"] = [];
                    $row["item"] = [];

                    if ($row["ref_table"] == "user_pet") {
                        $userPet = UserPetModel::where("id", $row["ref_id"])->first();
                        if ($userPet) {
                            $pet = SettingLogic::get("pet", ["id" => $userPet["pet_id"]]);
                            if ($pet) {
                                $row["image"] = $pet["image"];
                                $row["name"] = $pet["name"];
                                $row["pet"] = [
                                    "quality" => $userPet["quality"],
                                    "rank" => $userPet["rank"],
                                    "star" => $userPet["star"],
                                    "mining_rate" => $userPet["mining_rate"],
                                ];
                            }
                        }
                    } else if ($row["ref_table"] == "user_inventory") {
                        $userInventory = UserInventoryModel::where("id", $row["ref_id"])->first();
                        if ($userInventory) {
                            $item = SettingLogic::get("item", ["id" => $userInventory["item_id"]]);
                            if ($item) {
                                $row["image"] = $item["image"];
                                $row["name"] = $item["name"];
                                $row["item"] = [
                                    "category" => $item["category"],
                                    "effect" => $item["description"]
                                ];
                            }
                        }
                    }

                    $row["amount"] = $row["amount"] * 1;
                    $wallet = SettingLogic::get("wallet", ["id" => $row["amount_wallet_id"]]);
                    $row["payment"] = $wallet["code"] ?? "";
                }

                $this->response = [
                    "success" => true,
                    "data" => [
                        "data" => HelperLogic::formatOutput($res["items"], $this->patternOutputs, 1),
                        "count" => $res["count"],
                        "last_page" => ceil($res["count"] / 25),
                    ],
                ];
            }
        }

        # [standard output]
        return $this->output();
    }
}