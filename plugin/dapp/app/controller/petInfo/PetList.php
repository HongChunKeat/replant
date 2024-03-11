<?php

namespace plugin\dapp\app\controller\petInfo;

# library
use plugin\dapp\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\UserPetModel;
use app\model\logic\HelperLogic;
use app\model\logic\SettingLogic;
use plugin\admin\app\model\logic\PetLogic;

class PetList extends Base
{
    # [validation-rule]
    protected $rule = [
        "size" => "require|number",
        "page" => "require|number",
        "gif" => "require|in:1,0",
        "quality" => "in:normal,premium",
        "is_active" => "in:1,0",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "gif",
        "quality",
        "is_active"
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "sn",
        "image",
        "name",
        "quality",
        "rank",
        "star",
        "mining_rate",
        "health",
        "status",
        "mined_amount",
        "is_active",
    ];

    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->get(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->get(), $this->patternInputs);

        # user id
        $cleanVars["uid"] = $request->visitor["id"];

        # [proceed]
        if (!count($this->error)) {
            $gif = $cleanVars["gif"];
            unset($cleanVars["gif"]);
            $cleanVars[] = ["removed_at", null];
            $cleanVars[] = ["marketed_at", null];

            // unassign not healthy, unhealthy pet when fetch list
            PetLogic::petAutoUnassign($cleanVars["uid"]);

            # [paging query]
            $res = UserPetModel::paging(
                $cleanVars,
                $request->get("page"),
                $request->get("size"),
                ["*"],
                ["id", "desc"]
            );

            if ($res) {
                # [add and edit column using for loop]
                foreach ($res["items"] as $row) {
                    $pet = SettingLogic::get("pet", ["id" => $row["pet_id"]]);
                    $row["name"] = $pet["name"];
                    $row["image"] = $gif
                        ? $pet["gif"]
                        : $pet["image"];

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