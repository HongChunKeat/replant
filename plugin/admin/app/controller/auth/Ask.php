<?php

namespace plugin\admin\app\controller\auth;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\AccountAdminModel;
use plugin\admin\app\model\logic\AdminProfileLogic;
use app\model\logic\HelperLogic;

class Ask extends Base
{
    # [validation-rule]
    protected $rule = [
        "address" => "require|length:42|alphaNum",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "address"
    ];

    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->get(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->get(), $this->patternInputs);

        # [checking]
        $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error) && ($this->successTotalCount == $this->successPassedCount)) {
            $res = AdminProfileLogic::newAuthKey($cleanVars["address"]);

            # [result]
            if ($res) {
                $this->response = [
                    "success" => true,
                    "data" => $res,
                ];

                $user = AccountAdminModel::where("web3_address", $cleanVars["address"])->first();
                LogAdminModel::log($request, "request", "account_admin", $user["id"]);
            }
        }

        # [standard output]
        return $this->output();
    }

    private function checking(array $params = [])
    {
        # [init success condition]
        $this->successTotalCount = 1;

        # [condition]
        if (isset($params["address"])) {
            $user = AccountAdminModel::where("web3_address", $params["address"])->first();

            // status: normal, inactivated, freezed, suspended
            if ($user) {
                if ($user["status"] === "inactivated") {
                    $this->error[] = "account:inactivated";
                } else if ($user["status"] === "freezed") {
                    $this->error[] = "account:freezed";
                } else if ($user["status"] === "suspended") {
                    $this->error[] = "account:suspended";
                } else if ($user["status"] === "active") {
                    $this->successPassedCount++;
                }
            } else {
                $this->error[] = "account:missing";
            }
        }
    }
}
