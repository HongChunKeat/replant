<?php

namespace plugin\admin\app\controller\permission\warehouse;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\PermissionWarehouseModel;
use app\model\logic\HelperLogic;

class Create extends Base
{
    # [validation-rule]
    protected $rule = [
        "code" => "require",
        "from_site" => "require",
        "path" => "require|max:255",
        "action" => "require|in:POST,GET,PUT,DELETE,PATCH",
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
                $res = PermissionWarehouseModel::create($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "create", "permission_warehouse", $res["id"]);
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
        if (isset($params["code"])) {
            // Check code exists
            if (PermissionWarehouseModel::where("code", $params["code"])->first()) {
                $this->error[] = "code:exists";
            }
        }

        if (isset($params["path"])) {
            // Check path exists
            if (PermissionWarehouseModel::where("path", $params["path"])->first()) {
                $this->error[] = "path:exists";
            }
        }
    }
}
