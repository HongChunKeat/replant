<?php

namespace plugin\admin\app\controller\user\inventory;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\SettingItemModel;
use app\model\database\UserInventoryModel;
use app\model\database\AccountUserModel;
use app\model\logic\HelperLogic;

class Update extends Base
{
    # [validation-rule]
    protected $rule = [
        "uid" => "number|max:11",
        "item" => "number|max:11",
        "used_at" => "date",
        "removed_at" => "date",
        "marketed_at" => "date",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "uid",
        "item",
        "used_at",
        "removed_at",
        "marketed_at",
        "remark",
    ];

    public function index(Request $request, int $targetId = 0)
    {
        # [validation]
        $this->validation($request->post(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->post(), $this->patternInputs, 1);

        # [checking]
        $this->checking(["id" => $targetId] + $cleanVars);

        # [proceed]
        if (!count($this->error)) {
            $res = "";

            # [process]
            if (count($cleanVars) > 0) {
                if (!empty($cleanVars["item"])) {
                    $cleanVars["item_id"] = $cleanVars["item"];
                }

                # [unset key]
                unset($cleanVars["item"]);

                $res = UserInventoryModel::where("id", $targetId)->update($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "update", "user_inventory", $targetId);
                $this->response = [
                    "success" => true,
                ];
            }
        }

        # [standard output]
        return $this->output();
    }

    private function checking(array $params = [])
    {
        # [condition]
        if (!empty($params["uid"])) {
            if (!AccountUserModel::where("id", $params["uid"])->first()) {
                $this->error[] = "uid:invalid";
            }
        }

        if (!empty($params["item"])) {
            if (!SettingItemModel::where("id", $params["item"])->first()) {
                $this->error[] = "item:invalid";
            }
        }
    }
}
