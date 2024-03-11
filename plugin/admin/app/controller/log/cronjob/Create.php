<?php

namespace plugin\admin\app\controller\log\cronjob;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\LogCronjobModel;
use app\model\logic\HelperLogic;

class Create extends Base
{
    # [validation-rule]
    protected $rule = [
        "used_at" => "number|max:14",
        "cronjob_code" => "require",
        "info" => "",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = ["used_at", "cronjob_code", "info", "remark"];

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
                $res = LogCronjobModel::create($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "create", "log_cronjob", $res["id"]);
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
        if (isset($params["used_at"]) && isset($params["cronjob_code"])) {
            // check cronjob exists
            if (LogCronjobModel::where(["used_at" => $params["used_at"], "cronjob_code" => $params["cronjob_code"]])->first()) {
                $this->error[] = "cronjob_code:exists";
            }
        }
    }
}
