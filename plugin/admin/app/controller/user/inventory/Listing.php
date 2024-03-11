<?php

namespace plugin\admin\app\controller\user\inventory;

# library
use support\Request;
use plugin\admin\app\controller\Base;
# database & logic
use plugin\dapp\app\model\logic\UserProfileLogic;
use app\model\database\UserInventoryModel;
use app\model\database\AccountUserModel;
use app\model\database\SettingItemModel;
use app\model\logic\HelperLogic;

class Listing extends Base
{
    # [validation-rule]
    protected $rule = [
        "id" => "number|max:11",
        "sn" => "",
        "uid" => "number|max:11",
        "user" => "",
        "item" => "number|max:11",
        "remark" => "",
        "used_at_start" => "date",
        "used_at_end" => "date",
        "removed_at_start" => "date",
        "removed_at_end" => "date",
        "marketed_at_start" => "date",
        "marketed_at_end" => "date",
        "created_at_start" => "date",
        "created_at_end" => "date",
        "updated_at_start" => "date",
        "updated_at_end" => "date",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "id",
        "sn",
        "uid",
        "user",
        "item",
        "remark",
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "sn",
        "used_at",
        "removed_at",
        "marketed_at",
        "created_at",
        "updated_at",
        "uid",
        "user",
        "item",
        "remark",
    ];

    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->get(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->get(), $this->patternInputs);

        # [proceed]
        if (!count($this->error)) {

            # [search join table columns]
            if (isset($cleanVars["user"])) {
                // 4 in 1 search
                $user = UserProfileLogic::multiSearch($cleanVars["user"]);
                $cleanVars["uid"] = $user["id"] ?? 0;
            }

            if (isset($cleanVars["item"])) {
                $item = SettingItemModel::where("id", $cleanVars["item"])->first();
                $cleanVars["item_id"] = $item["id"] ?? 0;
            }

            # [unset key]
            unset($cleanVars["user"]);
            unset($cleanVars["item"]);

            # [search date range]
            $cleanVars = array_merge(
                $cleanVars, 
                HelperLogic::buildDateSearch($request, ["created_at", "updated_at", "used_at", "removed_at", "marketed_at"])
            );

            # [listing query]
            $res = UserInventoryModel::listing(
                $cleanVars,
                ["*"],
                ["id", "desc"]
            );

            # [result]
            if ($res) {
                # [add and edit column using for loop]
                foreach ($res as $row) {
                    $user = AccountUserModel::where("id", $row["uid"])->first();
                    $row["user"] = $user ? $user["user_id"] : "";

                    $item = SettingItemModel::where("id", $row["item_id"])->first();
                    $row["item"] = $item ? $item["name"] : "";
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
