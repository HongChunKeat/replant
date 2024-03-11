<?php

namespace plugin\admin\app\controller\user\remark;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\AccountUserModel;
use app\model\database\AdminPermissionModel;
use app\model\database\UserRemarkModel;
use app\model\logic\HelperLogic;

class Update extends Base
{
    # [validation-rule]
    protected $rule = [
        "uid" => "number|max:11",
        "remark" => "max:1000",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "uid",
        "remark"
    ];

    public function index(Request $request, int $targetId = 0)
    {
        # [validation]
        $this->validation($request->post(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->post(), $this->patternInputs, 1);

        # user id
        $cleanVars["admin_id"] = $request->visitor["id"];

        # [checking]
        $this->checking(["id" => $targetId] + $cleanVars);

        # [proceed]
        if (!count($this->error)) {
            $res = "";

            # [process]
            if (count($cleanVars) > 0) {
                unset($cleanVars["admin_id"]);
                $res = UserRemarkModel::where("id", $targetId)->update($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "update", "user_remark", $targetId);
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
        // check uid
        if (!empty($params["uid"])) {
            if (!AccountUserModel::where("id", $params["uid"])->first()) {
                $this->error[] = "uid:invalid";
            }
        }

        if (!empty($params["admin_id"])) {
            $permission = AdminPermissionModel::where("admin_uid", $params["admin_id"])->first();
            $remark = UserRemarkModel::where("id", $params["id"])->first();
            if($remark) {
                if ($params["admin_id"] != $remark["admin_id"] && !in_array("*", json_decode($permission["rule"]))) {
                    $this->error[] = "remark:invalid_action";
                }
            }
        }
    }
}
