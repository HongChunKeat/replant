<?php

namespace plugin\admin\app\controller\permission\admin;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\AccountAdminModel;
use app\model\database\AdminPermissionModel;
use app\model\database\PermissionTemplateModel;
use app\model\logic\HelperLogic;

class Update extends Base
{
    # [validation-rule]
    protected $rule = [
        "admin_uid" => "number|max:11",
        "role" => "number|max:11",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "admin_uid",
        "role"
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
                // encode input
                if (!empty($cleanVars["rule"])) {
                    $cleanVars["rule"] = json_encode($cleanVars["rule"]);
                }

                $res = AdminPermissionModel::where("id", $targetId)->update($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "update", "admin_permission", $targetId);
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
        if (!empty($params["admin_uid"])) {
            // Check admin_uid exists
            if (AdminPermissionModel::where("admin_uid", $params["admin_uid"])
                    ->whereNot("id", $params["id"])
                    ->first()
            ) {
                $this->error[] = "admin_uid:exists";
            }

            // check admin_uid
            if (!AccountAdminModel::where("id", $params["admin_uid"])->first()) {
                $this->error[] = "admin_uid:invalid";
            }
        }

        if (!empty($params["role"])) {
            if (!PermissionTemplateModel::where("id", $params["role"])->first()) {
                $this->error[] = "role:invalid";
            }
        }
    }
}
