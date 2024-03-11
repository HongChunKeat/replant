<?php

namespace plugin\dapp\app\controller\inventoryInfo;

# library
use plugin\dapp\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\UserInventoryModel;
use app\model\logic\HelperLogic;
use app\model\logic\SettingLogic;

class InventoryList extends Base
{
    # [validation-rule]
    protected $rule = [
        "page" => "require|number",
    ];

    protected $patternOutputs = [
        "sn",
        "image",
        "name",
        "description",
        "category",
        "effect",
    ];

    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->get(), $this->rule);

        # user id
        $cleanVars["uid"] = $request->visitor["id"];

        # [proceed]
        if (!count($this->error)) {
            # [paging query]
            $cleanVars[] = ["used_at", null];
            $cleanVars[] = ["removed_at", null];
            $cleanVars[] = ["marketed_at", null];

            # [paging query]
            $res = UserInventoryModel::paging(
                $cleanVars,
                $request->get("page"),
                25,
                ["*"],
                ["id", "desc"]
            );

            # [result]
            if ($res) {
                foreach ($res["items"] as $row) {
                    $item = SettingLogic::get("item", ["id" => $row["item_id"]]);
                    $row["image"] = $item ? $item["image"] : "";
                    $row["name"] = $item ? $item["name"] : "";
                    $row["description"] = $item ? $item["description"] : "";
                    $row["category"] = $item ? $item["category"] : "";
                    $row["effect"] = HelperLogic::buildAttribute("item_attribute", ["item_id" => $row["item_id"]]);
                }

                $refundPrice = HelperLogic::buildAttributeGeneral(["category" => "item", "code" => "refund_price"]);
                $this->response = [
                    "success" => true,
                    "data" => [
                        "data" => HelperLogic::formatOutput($res["items"], $this->patternOutputs, 1),
                        "count" => $res["count"],
                        "last_page" => ceil($res["count"] / 25),
                        "refund_price" => $refundPrice[0]["value"] ?? 0,
                    ],
                ];
            }
        }

        # [standard output]
        return $this->output();
    }
}