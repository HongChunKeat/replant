<?php

namespace plugin\admin\app\controller\log\cronjob;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\AccountUserModel;
use app\model\database\LogCronjobModel;
use app\model\logic\HelperLogic;

class Update extends Base
{
    # [validation-rule]
    protected $rule = [
        "used_at" => "number|max:14",
        "cronjob_code" => "",
        "info" => "",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = ["used_at", "cronjob_code", "info", "remark"];

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
                $res = LogCronjobModel::where("id", $targetId)->update($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "update", "log_cronjob", $targetId);
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
        if (!empty($params["used_at"]) && !empty($params["cronjob_code"])) {
            // check id exists
            if (LogCronjobModel::where(["used_at" => $params["used_at"],"cronjob_code" => $params["cronjob_code"]])
                    ->whereNot("id", $params["id"])
                    ->first()
            ) {
                $this->error[] = "cronjob_code:exists";
            }
        }
    }
}
