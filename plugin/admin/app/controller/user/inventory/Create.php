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

class Create extends Base
{
    # [validation-rule]
    protected $rule = [
        "uid" => "require|number|max:11",
        "item" => "require|number|max:11",
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

    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->post(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->post(), $this->patternInputs);

        # [checking]
        $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error)) {
            $res = "";

            # [process]
            if (count($cleanVars) > 0) {
                if (isset($cleanVars["item"])) {
                    $cleanVars["item_id"] = $cleanVars["item"];
                }

                # [unset key]
                unset($cleanVars["item"]);

                $cleanVars["sn"] = HelperLogic::generateUniqueSN("user_inventory");
                $res = UserInventoryModel::create($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "create", "user_inventory", $res["id"]);
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
        if (isset($params["uid"])) {
            if (!AccountUserModel::where("id", $params["uid"])->first()) {
                $this->error[] = "uid:invalid";
            }
        }

        if (isset($params["item"])) {
            if (!SettingItemModel::where("id", $params["item"])->first()) {
                $this->error[] = "item:invalid";
            }
        }
    }
}
