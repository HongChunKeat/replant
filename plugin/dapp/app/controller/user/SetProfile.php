<?php

namespace plugin\dapp\app\controller\user;

# library
use plugin\dapp\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogUserModel;
use app\model\database\AccountUserModel;
use plugin\admin\app\model\logic\MissionLogic;
use app\model\logic\HelperLogic;

class SetProfile extends Base
{
    # [validation-rule]
    protected $rule = [
        "web3_address" => "length:42|alphaNum",
        "nickname" => "max:20",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "web3_address",
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
        $unsetVars = $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error)) {
            $res = "";

            # [process]
            if (count($cleanVars) > 0) {
                $uid = $cleanVars["uid"];

                # [unset key]
                unset($cleanVars["uid"]);

                // Remove fields that are not allowed to be edited
                if (isset($unsetVars)) {
                    foreach ($unsetVars as $unsetdata) {
                        unset($cleanVars[$unsetdata]);
                    }
                }

                # [update query]
                $res = AccountUserModel::where("id", $uid)->update($cleanVars);
            }

            # [result]
            if ($res) {
                if(isset($cleanVars["web3_address"])) {
                    // do mission
                    MissionLogic::missionProgress($uid, ["name" => "link web3 address"]);
                }
                
                LogUserModel::log($request, "set_profile");
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
                if (isset($params["web3_address"])) {
                    if (AccountUserModel::where("web3_address", $params["web3_address"])->whereNot("id", $params["uid"])->first()) {
                        $this->error[] = "web3_address:exists";
                    }
                }
                if (isset($params["nickname"])) {
                    if (AccountUserModel::where("nickname", $params["nickname"])->whereNot("id", $params["uid"])->first()) {
                        $this->error[] = "nickname:exists";
                    }
                }
                // if (isset($params["telegram"])) {
                //     if (AccountUserModel::where("telegram", $params["telegram"])->whereNot("id", $params["uid"])->first()) {
                //         $this->error[] = "telegram:exists";
                //     }
                // }
                // if (isset($params["discord"])) {
                //     if (AccountUserModel::where("discord", $params["discord"])->whereNot("id", $params["uid"])->first()) {
                //         $this->error[] = "discord:exists";
                //     }
                // }
                // if (isset($params["twitter"])) {
                //     if (AccountUserModel::where("twitter", $params["twitter"])->whereNot("id", $params["uid"])->first()) {
                //         $this->error[] = "twitter:exists";
                //     }
                // }
                // if (isset($params["google"])) {
                //     if (AccountUserModel::where("google", $params["google"])->whereNot("id", $params["uid"])->first()) {
                //         $this->error[] = "google:exists";
                //     }
                // }
            }
        }

        // field that able to insert once if empty and not editable afterward
        $fieldsToCheck = ["web3_address"];
        $notAbleEdit = [];

        // Identify fields that cannot be edited and add them to the $notAbleEdit array
        foreach ($fieldsToCheck as $field) {
            if (isset($params[$field]) && isset($user->$field)) {
                array_push($notAbleEdit, $field);
            }
        }

        return $notAbleEdit ?? [];
    }
}