<?php

namespace plugin\admin\app\controller\account\admin;

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
        "web3_address" => "length:42|alphaNum",
        "nickname" => "max:20",
        "password" => "min:8|max:16",
        "tag" => "",
        "email" => "",
        "authenticator" => "",
        "status" => "in:active,inactivated,freezed,suspended",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "web3_address", 
        "nickname", 
        "password",
        "tag", 
        "email", 
        "authenticator", 
        "status", 
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
                $cleanVars["nickname"] = $request->post("nickname");
                $cleanVars["email"] = strtolower($request->post("email"));

                if(!empty($cleanVars["password"])) {
                    $cleanVars["password"] = password_hash($cleanVars["password"], PASSWORD_DEFAULT);
                }

                $res = AccountAdminModel::where("id", $targetId)->update($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "update", "account_admin", $targetId);
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
        // Check web3_address exists
        if (!empty($params["web3_address"])) {
            if (AccountAdminModel::where("web3_address", $params["web3_address"])
                ->whereNot("id", $params["id"])
                ->first()
            ) {
                $this->error[] = "web3_address:exists";
            }
        }

        // Check nickname exists
        if (!empty($params["nickname"])) {
            if (AccountAdminModel::where("nickname", $params["nickname"])
                ->whereNot("id", $params["id"])
                ->first()
            ) {
                $this->error[] = "nickname:exists";
            }
        }

        // Check email exists, email allow null
        if (!empty($params["email"])) {
            if (AccountAdminModel::where("email", $params["email"])
                ->whereNot("id", $params["id"])
                ->first()
            ) {
                $this->error[] = "email:exists";
            }
        }

        // check password
        if (!empty($params["password"])) {
            if (!preg_match("/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?!.*\s).+$/", $params["password"])) {
                $this->error[] = "password:must_have_capital_letter_lower_letter_and_numeric";
            }
        }
    }
}
