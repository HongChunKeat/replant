<?php

namespace plugin\admin\app\controller\permission\warehouse;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\PermissionWarehouseModel;
use app\model\logic\HelperLogic;

class Update extends Base
{
    # [validation-rule]
    protected $rule = [
        "code" => "",
        "from_site" => "",
        "path" => "max:255",
        "action" => "in:POST,GET,PUT,DELETE,PATCH",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "code",
        "from_site",
        "path",
        "action",
        "remark"
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
                $res = PermissionWarehouseModel::where("id", $targetId)->update($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "update", "permission_warehouse", $targetId);
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
        if (!empty($params["code"])) {
            // Check code exists
            if (PermissionWarehouseModel::where("code", $params["code"])
                ->whereNot("id", $params["id"])
                ->first()
            ) {
                $this->error[] = "code:exists";
            }
        }

        if (!empty($params["path"])) {
            // Check path exists
            if (PermissionWarehouseModel::where("path", $params["path"])
                ->whereNot("id", $params["id"])
                ->first()
            ) {
                $this->error[] = "path:exists";
            }
        }
    }
}
