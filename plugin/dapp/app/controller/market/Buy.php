<?php

namespace plugin\dapp\app\controller\market;

# library
use Webman\RedisQueue\Redis as RedisQueue;
use plugin\dapp\app\controller\Base;
use support\Request;
use support\Redis;
# database & logic
use app\model\database\LogUserModel;
use app\model\database\AccountUserModel;
use app\model\database\UserMarketModel;
use app\model\logic\HelperLogic;
use app\model\logic\SettingLogic;
use plugin\dapp\app\model\logic\UserWalletLogic;

class Buy extends Base
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
        // check maintenance
        $stop_market = SettingLogic::get("general", ["category" => "maintenance", "code" => "stop_market", "value" => 1]);
        if ($stop_market) {
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
        Redis::get("market_item_buy-lock:" . $cleanVars["uid"])
            ? $this->error[] = "market_item_buy:lock"
            : Redis::set("market_item_buy-lock:" . $cleanVars["uid"], 1);

        # [checking]
        [$item] = $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error) && ($this->successTotalCount == $this->successPassedCount) && $item) {
            # [process]
            if (count($cleanVars) > 0) {
                # [process with queue]
                RedisQueue::send("user_wallet", [
                    "type" => "marketItemBuy",
                    "data" => [
                        "uid" => $cleanVars["uid"],
                        "sn" => $cleanVars["sn"],
                    ]
                ]);

                LogUserModel::log($request, "market_item_buy");

                # [result]
                $this->response = [
                    "success" => true,
                ];
            }
        }

        // remove redis lock
        Redis::del("market_item_buy-lock:" . $cleanVars["uid"]);

        # [standard output]
        return $this->output();
    }

    private function checking(array $params = [])
    {
        # [init success condition]
        $this->successTotalCount = 5;

        # [condition]
        if (isset($params["uid"]) && isset($params["sn"])) {
            // check uid exist
            $user = AccountUserModel::where(["id" => $params["uid"], "status" => "active"])->first();
            if (!$user) {
                $this->error[] = "user:missing";
            } else {
                $this->successPassedCount++;
                // check exist in market list
                $item = UserMarketModel::defaultWhere()->where("sn", $params["sn"])->first();
                if (!$item) {
                    $this->error[] = "market_item:not_found";
                } else {
                    $this->successPassedCount++;
                    if ($item["seller_uid"] == $params["uid"]) {
                        $this->error[] = "market_item:cannot_buy_your_own_item";
                    } else {
                        $this->successPassedCount++;
                    }

                    // check wallet and balance
                    $wallet = SettingLogic::get("wallet", ["id" => $item["amount_wallet_id"]]);
                    if (!$wallet) {
                        $this->error[] = "setting:missing";
                    } else {
                        $this->successPassedCount++;
                        $balance = UserWalletLogic::getBalance($params["uid"], $wallet["id"]);
                        if ($item["amount"] > $balance) {
                            $this->error[] = $wallet["code"] . ":insufficient_balance";
                        } else {
                            $this->successPassedCount++;
                        }
                    }
                }
            }
        }

        return [$item ?? 0];
    }
}