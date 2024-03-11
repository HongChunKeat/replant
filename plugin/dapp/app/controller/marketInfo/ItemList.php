<?php

namespace plugin\dapp\app\controller\marketInfo;

# library
use plugin\dapp\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\SettingItemModel;
use app\model\database\UserInventoryModel;
use app\model\database\UserMarketModel;
use app\model\logic\HelperLogic;
use app\model\logic\SettingLogic;

class ItemList extends Base
{
    # [validation-rule]
    protected $rule = [
        "category" => "",
        "payment" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "category",
        "payment",
    ];

    protected $patternOutputs = [
        "image",
        "name",
        "category",
        "quantity",
        "price",
        "payment",
    ];

    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->post(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->post(), $this->patternInputs);

        # [proceed]
        if (!count($this->error)) {
            # [paging query]
            $table = SettingItemModel::query();

            if (isset($cleanVars["payment"])) {
                $payment = [];
                foreach ($cleanVars["payment"] as $name) {
                    $wallet = SettingLogic::get("wallet", ["code" => $name]);
                    if ($wallet) {
                        $payment[] = $wallet["id"];
                    }
                }
            }

            if (isset($cleanVars["category"])) {
                $table->whereIn("category", $cleanVars["category"]);
            }

            $res = $table->orderBy("id", "asc")->get();

            # [result]
            if ($res) {
                foreach ($res as $row) {
                    $row["quantity"] = 0;
                    $row["price"] = 0;
                    $row["payment"] = null;

                    $inventory = UserInventoryModel::where("item_id", $row["id"])
                        ->whereNull("used_at")
                        ->whereNull("removed_at")
                        ->whereNotNull("marketed_at")
                        ->get()
                        ->toArray();

                    if ($inventory) {
                        //get lowest price in market
                        $marketItem = UserMarketModel::defaultWhere()->where("ref_table", "user_inventory")
                            ->whereIn("ref_id", array_column($inventory, "id"));

                        //filter by payment
                        if (isset($payment)) {
                            $marketItem = $marketItem->whereIn("amount_wallet_id", $payment);
                        }

                        $marketItem = $marketItem->orderBy("amount", "asc")->first();

                        if ($marketItem) {
                            $row["quantity"] = count($inventory);
                            $row["price"] = $marketItem["amount"] * 1;
                            $wallet = SettingLogic::get("wallet", ["id" => $marketItem["amount_wallet_id"]]);
                            $row["payment"] = $wallet["code"] ?? "";
                        }
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