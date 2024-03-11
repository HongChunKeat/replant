<?php

namespace plugin\admin\app\controller\user\pet;

# library
use support\Request;
use plugin\admin\app\controller\Base;
# database & logic
use plugin\dapp\app\model\logic\UserProfileLogic;
use app\model\database\AccountUserModel;
use app\model\database\SettingPetModel;
use app\model\database\UserPetModel;
use app\model\logic\HelperLogic;
use plugin\admin\app\model\logic\PetLogic;

class Paging extends Base
{
    # [validation-rule]
    protected $rule = [
        "size" => "require|number",
        "page" => "require|number",
        "id" => "number|max:11",
        "sn" => "",
        "uid" => "number|max:11",
        "user" => "",
        "pet" => "number|max:11",
        "quality" => "in:normal,premium",
        "rank" => "",
        "star" => "number|egt:0|max:11",
        "mining_rate" => "float|egt:0|max:11",
        "is_active" => "in:0,1",
        "remark" => "",
        "mining_cutoff_at_start" => "date",
        "mining_cutoff_at_end" => "date",
        "health_pause_at_start" => "date",
        "health_pause_at_end" => "date",
        "health_end_at_start" => "date",
        "health_end_at_end" => "date",
        "removed_at_start" => "date",
        "removed_at_end" => "date",
        "marketed_at_start" => "date",
        "marketed_at_end" => "date",
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
        "quality",
        "pet",
        "rank",
        "star",
        "mining_rate",
        "is_active",
        "remark",
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "sn",
        "mining_cutoff_at",
        "health_pause_at",
        "health_end_at",
        "removed_at",
        "marketed_at",
        "created_at",
        "updated_at",
        "uid",
        "user",
        "quality",
        "pet",
        "rank",
        "star",
        "mining_rate",
        "health",
        "status",
        "mined_amount",
        "is_active",
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

            if (isset($cleanVars["pet"])) {
                $pet = SettingPetModel::where("id", $cleanVars["pet"])->first();
                $cleanVars["pet_id"] = $pet["id"] ?? 0;
            }

            # [unset key]
            unset($cleanVars["user"]);
            unset($cleanVars["pet"]);

            # [search date range]
            $cleanVars = array_merge(
                $cleanVars, 
                HelperLogic::buildDateSearch($request, ["created_at", "updated_at", "mining_cutoff_at", "health_pause_at", "health_end_at", "removed_at", "marketed_at"])
            );

            # [paging query]
            $res = UserPetModel::paging(
                $cleanVars,
                $request->get("page"),
                $request->get("size"),
                ["*"],
                ["id", "desc"]
            );

            # [result]
            if ($res) {
                # [add and edit column using for loop]
                foreach ($res["items"] as $row) {
                    $row["is_active"] = $row["is_active"] ? "active" : "inactive";

                    $user = AccountUserModel::where("id", $row["uid"])->first();
                    $row["user"] = $user ? $user["user_id"] : "";

                    $pet = SettingPetModel::where("id", $row["pet_id"])->first();
                    $row["pet"] = $pet ? $pet["name"] : "";

                    $health = PetLogic::countHealth($row["id"]);
                    $row["health"] = $health < 0 ? 0 : $health;
                    $row["status"] = PetLogic::checkHealth($health);
                    $row["mined_amount"] = PetLogic::countMining($row["id"]);
                }

                $this->response = [
                    "success" => true,
                    "data" => [
                        "data" => HelperLogic::formatOutput($res["items"], $this->patternOutputs, 1),
                        "count" => $res["count"],
                        "last_page" => ceil($res["count"] / $request->get("size")),
                    ],
                ];
            }
        }

        # [standard output]
        return $this->output();
    }
}
