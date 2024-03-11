<?php

namespace plugin\dapp\app\controller\marketInfo;

# library
use plugin\dapp\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\AccountUserModel;
use app\model\database\UserMarketModel;
use app\model\logic\HelperLogic;
use app\model\logic\SettingLogic;

class ItemMarketList extends Base
{
    # [validation-rule]
    protected $rule = [
        "size" => "require|number",
        "page" => "require|number",
        "name" => "require",
        "payment" => "in:xtendo,gtendo",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "name",
        "payment",
    ];

    protected $patternOutputs = [
        "sn",
        "seller",
        "image",
        "name",
        "price",
        "payment",
        "locked",
    ];

    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->get(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->get(), $this->patternInputs);

        # user id
        $uid = $request->visitor["id"];

        # [proceed]
        if (!count($this->error)) {
            // set default
            $cleanVars["ref_table"] = "user_inventory";
            $cleanVars[] = ["user_market.removed_at", null];
            $cleanVars[] = ["user_market.sold_at", null];

            if (isset($cleanVars["payment"])) {
                $wallet = SettingLogic::get("wallet", ["code" => $cleanVars["payment"]]);
                $cleanVars["user_market.amount_wallet_id"] = $wallet["id"] ?? 0;
            }

            if (isset($cleanVars["name"])) {
                $item = SettingLogic::get("item", ["name" => $cleanVars["name"]]);
                $cleanVars["user_inventory.item_id"] = $item["id"] ?? 0;
            }
            unset($cleanVars["payment"]);
            unset($cleanVars["name"]);

            # [paging query]
            $res = UserMarketModel::paging(
                $cleanVars,
                $request->get("page"),
                $request->get("size"),
                [
                    "user_market.sn",
                    "user_market.seller_uid",
                    "user_market.amount",
                    "user_market.amount_wallet_id",
                    "user_market.removed_at",
                    "user_market.sold_at",
                    "user_inventory.item_id"
                ],
                ["user_market.amount", "asc"],
                [["user_inventory", "user_market.ref_id", "=", "user_inventory.id"]]
            );

            # [result]
            if ($res) {
                foreach ($res["items"] as $row) {
                    $row["image"] = null;
                    $row["name"] = null;

                    $seller = AccountUserModel::where("id", $row["seller_uid"])->first();
                    $row["seller"] = $seller
                        ? $seller["nickname"] ?? $seller["user_id"]
                        : "";

                    $item = SettingLogic::get("item", ["id" => $row["item_id"]]);
                    if ($item) {
                        $row["image"] = $item["image"];
                        $row["name"] = $item["name"];
                    }

                    $row["price"] = $row["amount"] * 1;
                    $wallet = SettingLogic::get("wallet", ["id" => $row["amount_wallet_id"]]);
                    $row["payment"] = $wallet["code"] ?? "";

                    // locked if is self
                    $row["locked"] = $row["seller_uid"] == $uid ? true : false;
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