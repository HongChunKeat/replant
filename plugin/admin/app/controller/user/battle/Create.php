<?php

namespace plugin\admin\app\controller\user\battle;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\UserPetModel;
use app\model\database\UserBattleModel;
use app\model\database\AccountUserModel;
use app\model\database\SettingOperatorModel;
use app\model\logic\HelperLogic;

class Create extends Base
{
    # [validation-rule]
    protected $rule = [
        "uid" => "require|number|max:11",
        "user_pet_id" => "require|number|max:11",
        "opponent_uid" => "require|number|max:11",
        "opponent_pet_id" => "require|number|max:11",
        "result" => "require|number|max:11",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "uid",
        "user_pet_id",
        "opponent_uid",
        "opponent_pet_id",
        "result",
        "remark",
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
                $cleanVars["sn"] = HelperLogic::generateUniqueSN("user_battle");
                $res = UserBattleModel::create($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "create", "user_battle", $res["id"]);
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
            if (!AccountUserModel::where("id", $params["uid"])->first()) {
                $this->error[] = "uid:invalid";
            }
        }

        if (isset($params["user_pet_id"])) {
            if (!UserPetModel::where("id", $params["user_pet_id"])->first()) {
                $this->error[] = "user_pet_id:invalid";
            }
        }

        if (isset($params["opponent_uid"])) {
            if (!AccountUserModel::where("id", $params["opponent_uid"])->first()) {
                $this->error[] = "opponent_uid:invalid";
            }
        }

        if (isset($params["opponent_pet_id"])) {
            if (!UserPetModel::where("id", $params["opponent_pet_id"])->first()) {
                $this->error[] = "opponent_pet_id:invalid";
            }
        }

        if (isset($params["result"])) {
            if (!SettingOperatorModel::where("id", $params["result"])->where("category", "battle")->first()) {
                $this->error[] = "result:invalid";
            }
        }
    }
}
