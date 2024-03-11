<?php

namespace plugin\dapp\app\controller\user;

# library
use plugin\dapp\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogUserModel;
use app\model\database\AccountUserModel;
use app\model\logic\HelperLogic;

class CharacterCreation extends Base
{
    # [validation-rule]
    protected $rule = [
        "avatar" => "require|max:100",
        "character" => "require|max:100",
        "nickname" => "require|max:20",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "avatar",
        "character",
        "nickname",
    ];

    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->post(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->post(), $this->patternInputs);

        # user id
        $cleanVars["uid"] = $request->visitor["id"];

        # [checking]
        $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error)) {
            $res = "";

            # [process]
            if (count($cleanVars) > 0) {
                $uid = $cleanVars["uid"];

                # [unset key]
                unset($cleanVars["uid"]);

                # [update query]
                $res = AccountUserModel::where("id", $uid)->update($cleanVars);
            }

            # [result]
            if ($res) {
                LogUserModel::log($request, "character_creation");
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
            $user = AccountUserModel::where(["id" => $params["uid"], "status" => "active"])->first();
            if (!$user) {
                $this->error[] = "user:missing";
            } else {
                if (isset($params["nickname"])) {
                    if (AccountUserModel::where("nickname", $params["nickname"])->whereNot("id", $params["uid"])->first()) {
                        $this->error[] = "nickname:exists";
                    }
                }
            }
        }
    }
}