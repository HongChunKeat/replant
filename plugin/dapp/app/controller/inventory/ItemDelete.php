<?php

namespace plugin\dapp\app\controller\inventory;

# library
use Webman\RedisQueue\Redis as RedisQueue;
use plugin\dapp\app\controller\Base;
use support\Request;
use support\Redis;
# database & logic
use app\model\database\LogUserModel;
use app\model\database\AccountUserModel;
use app\model\database\UserInventoryModel;
use app\model\logic\HelperLogic;
use app\model\logic\SettingLogic;

class ItemDelete extends Base
{
    # [validation-rule]
    protected $rule = [
        "items" => "require|max:500",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "items",
    ];

    public function index(Request $request)
    {
        // check maintenance
        $stop_item = SettingLogic::get("general", ["category" => "maintenance", "code" => "stop_item", "value" => 1]);
        if ($stop_item) {
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
        Redis::get("item_delete-lock:" . $cleanVars["uid"])
            ? $this->error[] = "item_delete:lock"
            : Redis::set("item_delete-lock:" . $cleanVars["uid"], 1);

        # [checking]
        $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error) && ($this->successTotalCount == $this->successPassedCount)) {
            # [process]
            if (count($cleanVars) > 0) {
                # [process with queue]
                RedisQueue::send("user_wallet", [
                    "type" => "itemDelete",
                    "data" => [
                        "uid" => $cleanVars["uid"],
                        "items" => $cleanVars["items"],
                    ]
                ]);

                LogUserModel::log($request, "item_delete");

                # [result]
                $this->response = [
                    "success" => true,
                ];
            }
        }

        // remove redis lock
        Redis::del("item_delete-lock:" . $cleanVars["uid"]);

        # [standard output]
        return $this->output();
    }

    private function checking(array $params = [])
    {
        # [init success condition]
        $this->successTotalCount = 2;

        # [condition]
        if (isset($params["uid"]) && isset($params["items"])) {
            // check uid exist
            $user = AccountUserModel::where(["id" => $params["uid"], "status" => "active"])->first();
            if (!$user) {
                $this->error[] = "user:missing";
            } else {
                $this->successPassedCount++;
                // check item
                $count = UserInventoryModel::defaultWhere()->where("uid", $params["uid"])->whereIn("sn", $params["items"])->count();

                if (count($params["items"]) != $count) {
                    $this->error[] = "item:invalid";
                } else {
                    $this->successPassedCount++;
                }
            }
        }
    }
}