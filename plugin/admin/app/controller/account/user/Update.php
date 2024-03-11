<?php

namespace plugin\admin\app\controller\account\user;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\AccountUserModel;
use app\model\logic\HelperLogic;

class Update extends Base
{
    # [validation-rule]
    protected $rule = [
        "avatar" => "max:100",
        "character" => "max:100",
        "web3_address" => "length:42|alphaNum",
        "nickname" => "max:20",
        "password" => "min:8|max:16",
        "login_id" => "min:8|max:15",
        "tag" => "",
        "email" => "",
        "authenticator" => "",
        "status" => "in:active,inactivated,freezed,suspended",
        "telegram" => "max:100",
        "discord" => "max:100",
        "twitter" => "max:100",
        "google" => "max:100",
        "telegram_name" => "max:100",
        "discord_name" => "max:100",
        "twitter_name" => "max:100",
        "google_name" => "max:100",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "avatar",
        "character",
        "web3_address",
        "nickname",
        "password",
        "login_id",
        "tag",
        "authenticator",
        "status",
        "telegram",
        "discord",
        "twitter",
        "google",
        "telegram_name",
        "discord_name",
        "twitter_name",
        "google_name",
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
                if (!empty($cleanVars["password"])) {
                    $cleanVars["password"] = password_hash($cleanVars["password"], PASSWORD_DEFAULT);
                }

                $res = AccountUserModel::where("id", $targetId)->update($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "update", "account_user", $targetId);
                $this->response = [
                    "success" => true
                ];
            }
        }

        # [standard output]
        return $this->output();
    }

    private function checking(array $params = [])
    {
        # [condition]
        if (!empty($params["web3_address"])) {
            if (AccountUserModel::where("web3_address", $params["web3_address"])
                ->whereNot("id", $params["id"])
                ->first()
            ) {
                $this->error[] = "web3_address:exists";
            }
        }

        if (!empty($params["nickname"])) {
            if (AccountUserModel::where("nickname", $params["nickname"])
                ->whereNot("id", $params["id"])
                ->first()
            ) {
                $this->error[] = "nickname:exists";
            }
        }

        if (!empty($params["login_id"])) {
            if (AccountUserModel::where("login_id", $params["login_id"])
                ->whereNot("id", $params["id"])
                ->first()
            ) {
                $this->error[] = "login_id:exists";
            }
        }

        if (!empty($params["telegram"])) {
            if (AccountUserModel::where("telegram", $params["telegram"])
                ->whereNot("id", $params["id"])
                ->first()
            ) {
                $this->error[] = "telegram:exists";
            }
        }

        if (!empty($params["discord"])) {
            if (AccountUserModel::where("discord", $params["discord"])
                ->whereNot("id", $params["id"])
                ->first()
            ) {
                $this->error[] = "discord:exists";
            }
        }

        if (!empty($params["twitter"])) {
            if (AccountUserModel::where("twitter", $params["twitter"])
                ->whereNot("id", $params["id"])
                ->first()
            ) {
                $this->error[] = "twitter:exists";
            }
        }

        if (!empty($params["google"])) {
            if (AccountUserModel::where("google", $params["google"])
                ->whereNot("id", $params["id"])
                ->first()
            ) {
                $this->error[] = "google:exists";
            }
        }

        if (!empty($params["telegram_name"])) {
            if (AccountUserModel::where("telegram_name", $params["telegram_name"])
                ->whereNot("id", $params["id"])
                ->first()
            ) {
                $this->error[] = "telegram_name:exists";
            }
        }

        if (!empty($params["discord_name"])) {
            if (AccountUserModel::where("discord_name", $params["discord_name"])
                ->whereNot("id", $params["id"])
                ->first()
            ) {
                $this->error[] = "discord_name:exists";
            }
        }

        if (!empty($params["twitter_name"])) {
            if (AccountUserModel::where("twitter_name", $params["twitter_name"])
                ->whereNot("id", $params["id"])
                ->first()
            ) {
                $this->error[] = "twitter_name:exists";
            }
        }

        if (!empty($params["google_name"])) {
            if (AccountUserModel::where("google_name", $params["google_name"])
                ->whereNot("id", $params["id"])
                ->first()
            ) {
                $this->error[] = "google_name:exists";
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
