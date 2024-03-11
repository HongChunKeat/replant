<?php

namespace plugin\dapp\app\controller\user;

# library
use plugin\dapp\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\AccountUserModel;
use app\model\database\NetworkSponsorModel;
use app\model\database\UserLevelModel;
use app\model\database\UserMissionModel;
use app\model\database\UserPetModel;
use app\model\logic\HelperLogic;
use app\model\logic\SettingLogic;

class GetProfile extends Base
{
    # [outputs-pattern]
    protected $patternOutputs = [
        "upline",
        "user_id",
        "avatar",
        "character",
        "web3_address",
        "nickname",
        "login_id",
        "telegram",
        "discord",
        "twitter",
        "google",
        "level",
        "pet_slots",
        "inventory_pages",
        "pet_owned",
        "mission_completed",
        "mining_power"
    ];

    public function index(Request $request)
    {
        # user id
        $cleanVars["uid"] = $request->visitor["id"];

        $res = AccountUserModel::where("id", $cleanVars["uid"])->first();

        # [result]
        if ($res) {
            $res["upline"] = null;
            $res["telegram"] = $res["telegram_name"];
            $res["discord"] = $res["discord_name"];
            $res["twitter"] = $res["twitter_name"];
            $res["google"] = $res["google_name"];

            $upline = NetworkSponsorModel::where("uid", $res["id"])->first();
            if ($upline) {
                $uplineAccount = AccountUserModel::where("id", $upline["upline_uid"])->first();
                $res["upline"] = $uplineAccount ? $uplineAccount["user_id"] : "";
            }

            $level = UserLevelModel::where(["uid" => $res["id"], "is_current" => 1])->first();
            $res["level"] = $level["level"] ?? 1;
            $res["pet_slots"] = $level["pet_slots"] ?? 1;
            $res["inventory_pages"] = $level["inventory_pages"] ?? 1;

            $pets = UserPetModel::defaultWhere()->where(["uid" => $res["id"]])->get();

            $totalPet = 0;
            $normalPetMining = 0;
            $premiumPetMining = 0;
            foreach ($pets as $pet) {
                $totalPet++;

                if ($pet["is_active"] == 1) {
                    if ($pet["quality"] == "normal") {
                        $normalPetMining += $pet["mining_rate"];
                    } else {
                        $premiumPetMining += $pet["mining_rate"];
                    }
                }
            }
            $res["pet_owned"] = $totalPet;
            $res["mining_power"] = [
                "normal" => $normalPetMining,
                "premium" => $premiumPetMining
            ];

            $completed = SettingLogic::get("operator", ["code" => "completed"]);
            $claimed = SettingLogic::get("operator", ["code" => "claimed"]);
            $res["mission_completed"] = UserMissionModel::where("uid", $res["id"])->whereIn("status", [$completed["id"], $claimed["id"]])->count();

            $this->response = [
                "success" => true,
                "data" => HelperLogic::formatOutput($res, $this->patternOutputs),
            ];
        }

        # [standard output]
        return $this->output();
    }
}