<?php

namespace plugin\admin\app\controller\user\battle;

# library
use support\Request;
use plugin\admin\app\controller\Base;
# database & logic
use plugin\dapp\app\model\logic\UserProfileLogic;
use app\model\database\UserBattleModel;
use app\model\database\AccountUserModel;
use app\model\database\SettingOperatorModel;
use app\model\logic\HelperLogic;

class Listing extends Base
{
    # [validation-rule]
    protected $rule = [
        "id" => "number|max:11",
        "sn" => "",
        "uid" => "number|max:11",
        "user" => "",
        "user_pet_id" => "number|max:11",
        "opponent_uid" => "number|max:11",
        "opponent" => "",
        "opponent_pet_id" => "number|max:11",
        "result" => "number|max:11",
        "remark" => "",
        "created_at_start" => "date",
        "created_at_end" => "date",
        "updated_at_start" => "date",
        "updated_at_end" => "date",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "id",
        "sn",
        "uid",
        "user",
        "user_pet_id",
        "opponent_uid",
        "opponent",
        "opponent_pet_id",
        "result",
        "remark",
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "sn",
        "created_at",
        "updated_at",
        "uid",
        "user",
        "user_pet_id",
        "opponent_uid",
        "opponent",
        "opponent_pet_id",
        "result",
        "remark",
    ];

    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->get(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->get(), $this->patternInputs);

        # [proceed]
        if (!count($this->error)) {

            # [search join table columns]
            if (isset($cleanVars["user"])) {
                // 4 in 1 search
                $user = UserProfileLogic::multiSearch($cleanVars["user"]);
                $cleanVars["uid"] = $user["id"] ?? 0;
            }

            if (isset($cleanVars["opponent"])) {
                // 4 in 1 search
                $opponent = UserProfileLogic::multiSearch($cleanVars["opponent"]);
                $cleanVars["opponent_uid"] = $opponent["id"] ?? 0;
            }

            # [unset key]
            unset($cleanVars["user"]);
            unset($cleanVars["opponent"]);

            # [search date range]
            $cleanVars = array_merge(
                $cleanVars, 
                HelperLogic::buildDateSearch($request, ["created_at", "updated_at"])
            );

            # [listing query]
            $res = UserBattleModel::listing(
                $cleanVars,
                ["*"],
                ["id", "desc"]
            );

            # [result]
            if ($res) {
                # [add and edit column using for loop]
                foreach ($res as $row) {
                    $user = AccountUserModel::where("id", $row["uid"])->first();
                    $row["user"] = $user ? $user["user_id"] : "";

                    $opponent = AccountUserModel::where("id", $row["opponent_uid"])->first();
                    $row["opponent"] = $opponent ? $opponent["user_id"] : "";

                    $result = SettingOperatorModel::where("id", $row["result"])->first();
                    $row["result"] = $result ? $result["code"] : "";
                }

                $this->response = [
                    "success" => true,
                    "data" => HelperLogic::formatOutput($res, $this->patternOutputs, 1),
                ];
            }
        }

        # [standard output]
        return $this->output();
    }
}
