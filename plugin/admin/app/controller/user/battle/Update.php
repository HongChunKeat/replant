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

class Update extends Base
{
    # [validation-rule]
    protected $rule = [
        "uid" => "number|max:11",
        "user_pet_id" => "number|max:11",
        "opponent_uid" => "number|max:11",
        "opponent_pet_id" => "number|max:11",
        "result" => "number|max:11",
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
                $res = UserBattleModel::where("id", $targetId)->update($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "update", "user_battle", $targetId);
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
        if (!empty($params["uid"])) {
            if (!AccountUserModel::where("id", $params["uid"])->first()) {
                $this->error[] = "uid:invalid";
            }
        }

        if (!empty($params["user_pet_id"])) {
            if (!UserPetModel::where("id", $params["user_pet_id"])->first()) {
                $this->error[] = "user_pet_id:invalid";
            }
        }

        if (!empty($params["opponent_uid"])) {
            if (!AccountUserModel::where("id", $params["opponent_uid"])->first()) {
                $this->error[] = "opponent_uid:invalid";
            }
        }

        if (!empty($params["opponent_pet_id"])) {
            if (!UserPetModel::where("id", $params["opponent_pet_id"])->first()) {
                $this->error[] = "opponent_pet_id:invalid";
            }
        }

        if (!empty($params["result"])) {
            if (!SettingOperatorModel::where("id", $params["result"])->where("category", "battle")->first()) {
                $this->error[] = "result:invalid";
            }
        }
    }
}
