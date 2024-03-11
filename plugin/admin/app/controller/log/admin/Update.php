<?php

namespace plugin\admin\app\controller\log\admin;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\AccountAdminModel;
use app\model\logic\HelperLogic;

class Update extends Base
{
    # [validation-rule]
    protected $rule = [
        "admin_uid" => "require|number|max:11",
        "by_admin_uid" => "require|number|max:11",
        "ip" => "",
        "ref_table" => "",
        "ref_id" => "number|max:11",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = ["admin_uid", "by_admin_uid", "ip", "ref_table", "ref_id", "remark"];

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
                $res = LogAdminModel::where("id", $targetId)->update($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "update", "log_admin", $targetId);
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
            // check admin_uid exists
            if (
                !AccountAdminModel::where([
                    "id" => $params["admin_uid"],
                ])->first()
            ) {
                $this->error[] = "admin_uid:invalid";
            }
        }

        if (!empty($params["by_admin_uid"])) {
            // check by_admin_uid exists
            if (
                !AccountAdminModel::where([
                    "id" => $params["by_admin_uid"],
                ])->first()
            ) {
                $this->error[] = "by_admin_uid:invalid";
            }
        }
    }
}
