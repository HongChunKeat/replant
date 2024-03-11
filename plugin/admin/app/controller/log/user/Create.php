<?php

namespace plugin\admin\app\controller\log\user;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\AccountUserModel;
use app\model\database\LogUserModel;
use app\model\logic\HelperLogic;

class Create extends Base
{
    # [validation-rule]
    protected $rule = [
        "uid" => "require|number|max:11",
        "by_uid" => "require|number|max:11",
        "ip" => "",
        "ref_table" => "",
        "ref_id" => "number|max:11",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = ["uid", "by_uid", "ip", "ref_table", "ref_id", "remark"];

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
                $res = LogUserModel::create($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "create", "log_user", $res["id"]);
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
            // check uid exists
            if (
                !AccountUserModel::where([
                    "id" => $params["uid"],
                ])->first()
            ) {
                $this->error[] = "uid:invalid";
            }
        }
        
        if (isset($params["by_uid"])) {
            // check by_uid exists
            if (
                !AccountUserModel::where([
                    "id" => $params["by_uid"],
                ])->first()
            ) {
                $this->error[] = "by_uid:invalid";
            }
        }
    }
}
