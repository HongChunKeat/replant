<?php

namespace plugin\admin\app\controller\hierarchy;

# library
use plugin\admin\app\controller\Base;
use support\Db;
use support\Request;
# database & logic
use app\model\database\AccountUserModel;
use app\model\database\NetworkSponsorModel;
use app\model\logic\HelperLogic;
use plugin\dapp\app\model\logic\UserProfileLogic;
use plugin\admin\app\model\logic\StatSponsorLogic;

class Downline extends Base
{
    # [validation-rule]
    protected $rule = [
        "user_id" => "max:80",
        "from_search" => "in:1,0",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "user_id",
        "from_search",
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "user_id",
        "web3_address",
        "personal_point",
        "team_point",
        "downline_count",
    ];

    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->get(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->get(), $this->patternInputs);

        # for emulator only need find id by address
        if (isset($cleanVars["user_id"])) {
            // 4 in 1 search
            $user = UserProfileLogic::multiSearch($cleanVars["user_id"]);

            $cleanVars["uid"] = $user["id"] ?? 1;
        } else {
            $cleanVars["uid"] = 0;
        }

        # [proceed]
        if (!count($this->error)) {
            $res = [];

            # [search date range]
            //default start date
            // if(empty($request->get("start_date"))) {
            //     $startDate = date("2000-01-01 00:00:00");
            // }
            // else {
            //     $startDate = $request->get("start_date") . " 00:00:00";
            // }

            // //default end date
            // if(empty($request->get("end_date"))) {
            //     $endDate = date("Y-m-d 23:59:59");
            // } else {
            //     // set end date to 23:59:59 of that day
            //     $endDate = $request->get("end_date") . " 23:59:59";
            // }

            // show self only
            if (isset($cleanVars["from_search"]) && $cleanVars["from_search"] && $cleanVars["uid"] > 0) {
                $currentUser = AccountUserModel::select(Db::raw("id as uid"))->where("id", $cleanVars["uid"])->first();
                $res[] = $currentUser;
            }

            // get downline
            if (empty($cleanVars["from_search"])) {
                $down = NetworkSponsorModel::select("uid")->where("upline_uid", $cleanVars["uid"])->get();
                foreach ($down as $d) {
                    $res[] = $d;
                }
            }

            # [result]
            if (count($res) > 0) {
                # [add and edit column using for loop]
                foreach ($res as $row) {
                    $downline = AccountUserModel::where("id", $row["uid"])->first();
                    $row["id"] = $downline["id"] ?? "";
                    $row["user_id"] = $downline["user_id"] ?? "";
                    $row["web3_address"] = $downline["web3_address"] ?? "";

                    // self stat record
                    $personalStat = StatSponsorLogic::statList($downline["id"]);
                    $row["personal_point"] = $personalStat["point"];

                    // team stat record
                    $teamStat = StatSponsorLogic::statList($downline["id"], 0);
                    $row["team_point"] = $teamStat["point"];

                    // downline count
                    $row["downline_count"] = NetworkSponsorModel::where("upline_uid", $downline["id"])->count();
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
