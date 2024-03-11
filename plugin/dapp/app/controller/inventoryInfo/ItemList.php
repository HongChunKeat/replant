<?php

namespace plugin\dapp\app\controller\inventoryInfo;

# library
use plugin\dapp\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\UserInventoryModel;
use app\model\logic\HelperLogic;

class ItemList extends Base
{
    # [validation-rule]
    protected $rule = [
        "category" => "require|max:200",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "category",
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "sn",
        "image",
        "name",
        "category",
        "effect",
    ];

    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->post(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->post(), $this->patternInputs);

        # user id
        $cleanVars["uid"] = $request->visitor["id"];

        # [proceed]
        if (!count($this->error)) {
            $res = UserInventoryModel::defaultWhere()
                ->leftJoin("setting_item", "user_inventory.item_id", "=", "setting_item.id")
                ->where("user_inventory.uid", $cleanVars["uid"])
                ->whereIn("setting_item.category", $cleanVars["category"])
                ->get();

            if ($res) {
                foreach ($res as $row) {
                    $row["effect"] = HelperLogic::buildAttribute("item_attribute", ["item_id" => $row["item_id"]]);
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