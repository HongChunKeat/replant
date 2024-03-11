<?php

namespace plugin\dapp\app\controller\missionInfo;

# library
use plugin\dapp\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\SettingMissionModel;
use app\model\database\UserLevelModel;
use app\model\database\UserMissionModel;
use app\model\database\UserStaminaModel;
use app\model\logic\HelperLogic;
use app\model\logic\SettingLogic;

class MissionList extends Base
{
    # [validation-rule]
    protected $rule = [
        "size" => "require|number",
        "page" => "require|number",
        "type" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "type",
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "sn",
        "name",
        "description",
        "level",
        "reward",
        "requirement",
        "action",
        "type",
        "stamina",
        "status",
        "progress",
        "locked",
    ];

    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->get(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->get(), $this->patternInputs);

        # user id
        $uid = $request->visitor["id"];

        # [proceed]
        if (!count($this->error)) {
            // level show by range based on user level
            $level = UserLevelModel::where(["uid" => $uid, "is_current" => 1])->first();

            $startLevel = null;
            $endLevel = null;

            if ($level) {
                if ($level["level"] >= 1 && $level["level"] <= 10) {
                    $startLevel = "1";
                    $endLevel = "10";
                } else if ($level["level"] >= 11 && $level["level"] <= 20) {
                    $startLevel = "11";
                    $endLevel = "20";
                } else if ($level["level"] >= 21) {
                    $startLevel = "21";
                    $endLevel = "999";
                }
            }

            # [paging query]
            // default show all permanent mission
            $table = SettingMissionModel::where("is_show", 1)
                ->where("type", "!=", "onboarding")
                ->where(function ($query) use ($startLevel, $endLevel) {
                    $query->where("type", "permanent");
                    if ($startLevel !== null && $endLevel !== null) {
                        $query->orWhereBetween("level", [$startLevel, $endLevel]);
                    }
                });

            if (isset($cleanVars["type"])) {
                $table->where("type", $cleanVars["type"]);
            }

            $paginator = $table->orderBy("id", "asc")->paginate($request->get("size"), ["*"], "page", $request->get("page"));
            $res = ["items" => $paginator->items(), "count" => $paginator->total()];

            if ($res) {
                # [add and edit column using for loop]
                foreach ($res["items"] as $row) {
                    $row["progress"] = 0;
                    $row["reward"] = [];

                    //get user mission
                    if ($row["type"] == "daily") {
                        $mission = UserMissionModel::where(["uid" => $uid, "mission_id" => $row["id"]])
                            ->whereBetween("created_at", [date("Y-m-d 00:00:00"), date("Y-m-d 23:59:59")])
                            ->first();
                    } else if ($row["type"] == "weekly") {
                        $mission = UserMissionModel::where(["uid" => $uid, "mission_id" => $row["id"]])
                            ->whereBetween("created_at", [
                                date("Y-m-d 00:00:00", strtotime("this saturday -6 day")),
                                date("Y-m-d 23:59:59", strtotime("this saturday"))
                            ])
                            ->first();
                    } else {
                        $mission = UserMissionModel::where(["uid" => $uid, "mission_id" => $row["id"]])->first();
                    }

                    if ($mission) {
                        $status = SettingLogic::get("operator", ["id" => $mission["status"]]);
                        $row["sn"] = $mission["sn"];
                        $row["status"] = $status["code"];
                        $row["progress"] = $mission["progress"];
                    }

                    $stamina = UserStaminaModel::where("uid", $uid)->first();
                    if ($stamina) {
                        $row["stamina"] = round($row["stamina"] * $stamina["usage"]);
                    }

                    // split item reward
                    if (!empty($row["item_reward"])) {
                        $items = [];
                        $itemReward = json_decode($row["item_reward"]);
                        foreach ($itemReward as $key => $value) {
                            $itemFound = SettingLogic::get("item", ["id" => $key]);
                            if ($itemFound) {
                                $items[] = [
                                    "image" => $itemFound["image"],
                                    "name" => $itemFound["name"],
                                    "quantity" => $value,
                                ];
                            }
                        }

                        $row["reward"] = array_merge($row["reward"], $items);
                    }

                    // split pet reward
                    if (!empty($row["pet_reward"])) {
                        $pets = [];
                        $petReward = json_decode($row["pet_reward"]);
                        foreach ($petReward as $key => $value) {
                            $petFound = SettingLogic::get("pet", ["id" => $key]);
                            if ($petFound) {
                                $pets[] = [
                                    "image" => $petFound["image"],
                                    "name" => $petFound["name"],
                                    "quantity" => $value,
                                ];
                            }
                        }

                        $row["reward"] = array_merge($row["reward"], $pets);
                    }

                    // split currency reward
                    if (!empty($row["currency_reward"])) {
                        $currencies = [];
                        $currencyReward = json_decode($row["currency_reward"]);
                        foreach ($currencyReward as $key => $value) {
                            $wallet = SettingLogic::get("wallet", ["id" => $key]);
                            if ($wallet) {
                                $currencies[] = [
                                    "wallet" => $wallet["code"],
                                    "amount" => $value,
                                ];
                            }
                        }

                        $row["reward"] = array_merge($row["reward"], $currencies);
                    }

                    // if mission level higher than user level then locked
                    $row["locked"] = $level && $row["level"] > $level["level"] ? true : false;
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