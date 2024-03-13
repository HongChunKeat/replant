<?php

namespace plugin\dapp\app\controller\auth;

# library
use plugin\dapp\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogUserModel;
use app\model\database\AccountUserModel;
use plugin\dapp\app\model\logic\UserProfileLogic;
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
                $user = AccountUserModel::where("web3_address", $cleanVars["address"])->first();
            }

            if ($user) {
                $accessJWT = [
                    "id" => $user["user_id"],
                    "nickname" => $user["nickname"],
                ];

                AccountUserModel::where("id", $user["id"])->update(["authenticator" => "web3_address"]);
                LogUserModel::log($request, "web3_login", "account_user", $user["id"]);
                $this->response = [
                    "success" => true,
                    "data" => UserProfileLogic::newAccessToken($user["user_id"], $accessJWT),
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
            if (!UserProfileLogic::verifyAuthKey(strtolower($params["address"]), strtolower($params["sign"]), 1)) {
                $this->error[] = "verify:invalid";
            } else {
                $this->successPassedCount++;
            }
        }
    }
}