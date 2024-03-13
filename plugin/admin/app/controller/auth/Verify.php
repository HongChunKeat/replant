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

class Verify extends Base
{
    # [validation-rule]
    protected $rule = [
        "address" => "require|length:42|alphaNum",
        "sign" => "require|max:255",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "address",
        "sign",
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
        if (!count($this->error) && ($this->successTotalCount == $this->successPassedCount)) {
            $user = "";

            # [process]
            if (count($cleanVars) > 0) {
                $user = AccountAdminModel::where("web3_address", $cleanVars["address"])->first();
            }

            if ($user) {
                $accessJWT = [
                    "id" => $user["admin_id"],
                    "address" => $user["web3_address"],
                ];

                AccountAdminModel::where("id", $user["id"])->update(["authenticator" => "web3_address"]);
                LogAdminModel::log($request, "login", "account_admin", $user["id"]);
                $this->response = [
                    "success" => true,
                    "data" => AdminProfileLogic::newAccessToken($user["admin_id"], $accessJWT),
                ];
            }
        }

        # [standard output]
        return $this->output();
    }

    private function checking(array $params = [])
    {
        # [init success condition]
        $this->successTotalCount = 1;

        if (isset($params["address"]) && isset($params["sign"])) {
            if (!AdminProfileLogic::verifyAuthKey(strtolower($params["address"]), strtolower($params["sign"]), 1)) {
                $this->error[] = "verify:invalid";
            } else {
                $this->successPassedCount++;
            }
        }
    }
}