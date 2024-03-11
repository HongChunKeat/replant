<?php

namespace plugin\dapp\app\controller\shop;

# library
use Webman\RedisQueue\Redis as RedisQueue;
use plugin\dapp\app\controller\Base;
use support\Request;
use support\Redis;
# database & logic
use app\model\database\LogUserModel;
use app\model\database\AccountUserModel;
use app\model\database\UserInventoryModel;
use app\model\database\UserLevelModel;
use plugin\dapp\app\model\logic\UserWalletLogic;
use app\model\logic\HelperLogic;
use app\model\logic\SettingLogic;

class Purchase extends Base
{
    # [validation-rule]
    protected $rule = [
        "name" => "require",
        "quantity" => "require|number|max:11"
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "name",
        "quantity",
    ];

    public function index(Request $request)
    {
        // check maintenance
        $stop_purchase = SettingLogic::get("general", ["category" => "maintenance", "code" => "stop_purchase", "value" => 1]);
        if ($stop_purchase) {
            $this->error[] = "under_maintenance";
            return $this->output();
        }

        # [validation]
        $this->validation($request->post(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->post(), $this->patternInputs);

        # user id
        $cleanVars["uid"] = $request->visitor["id"];

        // get and set redis lock
        Redis::get("purchase-lock:" . $cleanVars["uid"])
            ? $this->error[] = "purchase:lock"
            : Redis::set("purchase-lock:" . $cleanVars["uid"], 1);

        # [checking]
        [$item] = $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error) && ($this->successTotalCount == $this->successPassedCount) && $item) {
            # [process]
            if (count($cleanVars) > 0) {
                # [process with queue]
                RedisQueue::send("user_wallet", [
                    "type" => "purchase",
                    "data" => [
                        "uid" => $cleanVars["uid"],
                        "item" => $item["id"],
                        "quantity" => $cleanVars["quantity"],
                    ]
                ]);

                LogUserModel::log($request, "purchase");

                # [result]
                $this->response = [
                    "success" => true,
                ];
            }
        }

        // remove redis lock
        Redis::del("purchase-lock:" . $cleanVars["uid"]);

        # [standard output]
        return $this->output();
    }

    private function checking(array $params = [])
    {
        # [init success condition]
        $this->successTotalCount = 7;

        # [condition]
        if (isset($params["uid"]) && isset($params["name"]) && isset($params["quantity"])) {
            // check uid exist
            $user = AccountUserModel::where(["id" => $params["uid"], "status" => "active"])->first();
            if (!$user) {
                $this->error[] = "user:missing";
            } else {
                $this->successPassedCount++;
                $userLevel = UserLevelModel::where(["uid" => $params["uid"], "is_current" => 1])->first();

                if (!$userLevel) {
                    $this->error[] = "user:level_missing";
                } else {
                    $this->successPassedCount++;
                    // check inventory is over limit or not
                    $maxStorage = $userLevel["inventory_pages"] * 25;

                    $currentStorage = UserInventoryModel::defaultWhere()->where("uid", $params["uid"])->count();

                    if ($currentStorage + $params["quantity"] > $maxStorage) {
                        $this->error[] = "inventory:full";
                    } else {
                        $this->successPassedCount++;
                    }
                }

                // check item
                $item = SettingLogic::get("item", ["name" => $params["name"]]);

                if (!$item) {
                    $this->error[] = "item:missing";
                } else {
                    $this->successPassedCount++;
                    if ($item["sales_price"] <= 0 || $item["normal_price"] <= 0) {
                        $this->error[] = "item:invalid_action";
                    } else {
                        $this->successPassedCount++;
                        $payment = SettingLogic::get("payment", ["id" => $item["payment_id"]]);
                        if (!$payment) {
                            $this->error[] = "payment:missing";
                        } else {
                            $this->successPassedCount++;
                            // get the first wallet only
                            $wallet = array_keys(json_decode($payment["formula"], 1));

                            $total = $item["sales_price"] * $params["quantity"];

                            $payment = UserWalletLogic::paymentCheck($params["uid"], $item["payment_id"], [$wallet[0] => $total]);
                            if (!$payment["success"]) {
                                $this->error[] = $payment["data"][0];
                            } else {
                                $this->successPassedCount++;
                            }
                        }
                    }
                }
            }
        }

        return [$item ?? 0];
    }
}