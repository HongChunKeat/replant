<?php

namespace plugin\dapp\app\controller\user;

# library
use plugin\dapp\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\AccountUserModel;
use app\model\logic\HelperLogic;
use plugin\admin\app\model\logic\MissionLogic;
use plugin\dapp\app\model\logic\UserProfileLogic;

class BindX extends Base
{
    # [validation-rule]
    protected $rule = [
        "code" => "require|max:500",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "code",
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
        [$res] = $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error)) {
            # [process]
            if (count($cleanVars) > 0) {
                if ($res && $res["success"]) {
                    # [update query]
                    $user = AccountUserModel::where("id", $cleanVars["uid"])->update([
                        "twitter" => $res["data"]["id"],
                        "twitter_name" => isset($res["data"]["username"])
                            ? $res["data"]["username"]
                            : $res["data"]["id"]
                    ]);

                    if ($user) {
                        // do mission
                        MissionLogic::missionProgress($cleanVars["uid"], ["name" => "link X"]);

                        # [result]
                        $this->response = [
                            "success" => true,
                        ];
                    }
                }
            }
        }

        # [standard output]
        return $this->output();
    }

    private function checking(array $params = [])
    {
        # [condition]
        if (isset($params["uid"]) && isset($params["code"])) {
            $user = AccountUserModel::where(["id" => $params["uid"], "status" => "active"])->first();
            if (!$user) {
                $this->error[] = "user:missing";
            } else {
                if ($user["twitter"]) {
                    $this->error[] = "user:already_bind";
                } else {
                    $res = UserProfileLogic::checkXAuthorization($params["code"], true);

                    if (!$res["success"]) {
                        $this->error[] = $res["msg"];
                    } else {
                        if (isset($res["data"]["id"])) {
                            if (AccountUserModel::where("twitter", $res["data"]["id"])->whereNot("id", $params["uid"])->first()) {
                                $this->error[] = "twitter:exists";
                            }
                        }
                    }
                }
            }
        }

        return [$res ?? false];
    }
}