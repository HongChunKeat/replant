<?php

namespace plugin\admin\app\controller\account\user;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\AccountUserModel;
use app\model\logic\HelperLogic;
use plugin\dapp\app\model\logic\UserProfileLogic;

class Create extends Base
{
    # [validation-rule]
    protected $rule = [
        "avatar" => "max:100",
        "web3_address" => "require|length:42|alphaNum",
        "nickname" => "max:20",
        "password" => "min:8|max:16",
        "login_id" => "min:8|max:15",
        "tag" => "",
        "authenticator" => "",
        "status" => "require|in:active,inactivated,freezed,suspended",
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
                if (isset($cleanVars["password"])) {
                    $cleanVars["password"] = password_hash($cleanVars["password"], PASSWORD_DEFAULT);
                }

                $cleanVars["user_id"] = HelperLogic::generateUniqueSN("account_user");
                $res = AccountUserModel::create($cleanVars);
            }

            # [result]
            if ($res) {
                UserProfileLogic::bindUpline($res["id"]);
                UserProfileLogic::init($res["id"]);

                LogAdminModel::log($request, "create", "account_user", $res["id"]);
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
        if (isset($params["web3_address"])) {
            if (AccountUserModel::where("web3_address", $params["web3_address"])->first()) {
                $this->error[] = "web3_address:exists";
            }
        }

        if (isset($params["nickname"])) {
            if (AccountUserModel::where("nickname", $params["nickname"])->first()) {
                $this->error[] = "nickname:exists";
            }
        }

        if (isset($params["login_id"])) {
            if (AccountUserModel::where("login_id", $params["login_id"])->first()) {
                $this->error[] = "login_id:exists";
            }
        }

        if (isset($params["telegram"])) {
            if (AccountUserModel::where("telegram", $params["telegram"])->first()) {
                $this->error[] = "telegram:exists";
            }
        }

        if (isset($params["discord"])) {
            if (AccountUserModel::where("discord", $params["discord"])->first()) {
                $this->error[] = "discord:exists";
            }
        }

        if (isset($params["twitter"])) {
            if (AccountUserModel::where("twitter", $params["twitter"])->first()) {
                $this->error[] = "twitter:exists";
            }
        }

        if (isset($params["google"])) {
            if (AccountUserModel::where("google", $params["google"])->first()) {
                $this->error[] = "google:exists";
            }
        }

        if (isset($params["telegram_name"])) {
            if (AccountUserModel::where("telegram_name", $params["telegram_name"])->first()) {
                $this->error[] = "telegram_name:exists";
            }
        }

        if (isset($params["discord_name"])) {
            if (AccountUserModel::where("discord_name", $params["discord_name"])->first()) {
                $this->error[] = "discord_name:exists";
            }
        }

        if (isset($params["twitter_name"])) {
            if (AccountUserModel::where("twitter_name", $params["twitter_name"])->first()) {
                $this->error[] = "twitter_name:exists";
            }
        }

        if (isset($params["google_name"])) {
            if (AccountUserModel::where("google_name", $params["google_name"])->first()) {
                $this->error[] = "google_name:exists";
            }
        }

        // check password
        if (isset($params["password"])) {
            if (!preg_match("/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?!.*\s).+$/", $params["password"])) {
                $this->error[] = "password:must_have_capital_letter_lower_letter_and_numeric";
            }
        }
    }
}
