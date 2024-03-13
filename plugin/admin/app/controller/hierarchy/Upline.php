<?php

namespace plugin\admin\app\controller\hierarchy;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\AccountUserModel;
use app\model\database\NetworkSponsorModel;
use app\model\logic\HelperLogic;
use plugin\dapp\app\model\logic\UserProfileLogic;
use plugin\admin\app\model\logic\StatSponsorLogic;

class Upline extends Base
{
    # [validation-rule]
    protected $rule = [
        "user_id" => "require|max:80",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "user_id"
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "user_id",
        "web3_address",
        "personal_point",
        "team_point",
        "downline_count",
        "created_at"
    ];

    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->get(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->get(), $this->patternInputs);

        // 4 in 1 search
        if (isset($cleanVars["user_id"])) {
            $user = UserProfileLogic::multiSearch($cleanVars["user_id"]);

            if ($user) {
                $curUser = $user["id"];
            } else {
                $this->error[] = "user_id:invalid";
            }
        }

        # [proceed]
        if (!count($this->error)) {
            $upline = [];

            //push current user to array
            $currentUser = AccountUserModel::where("id", $curUser)->first();
            $upline[] = $currentUser;

            do {
                //get user network
                $userNetwork = NetworkSponsorModel::where("uid", $curUser)->first();

                if ($userNetwork && ($userNetwork["upline_uid"] > 0)) {
                    $user = AccountUserModel::where("id", $userNetwork["upline_uid"])->first();
                    array_unshift($upline, $user);

                    //change current user to user's upline
                    //keep on looping upward until hit root
                    $curUser = $userNetwork["upline_uid"];
                } else {
                    $curUser = false;
                }
            } while ($curUser);

            # [result]
            if ($upline) {
                # [add and edit column using for loop]
                foreach ($upline as $row) {
                    // self stat record
                    $personalStat = StatSponsorLogic::statList($row["id"]);
                    $row["personal_point"] = $personalStat["point"];

                    // team stat record
                    $teamStat = StatSponsorLogic::statList($row["id"], 0);
                    $row["team_point"] = $teamStat["point"];

                    // downline count
                    $row["downline_count"] = NetworkSponsorModel::where("upline_uid", $row["id"])->count();
                }

                $this->response = [
                    "success" => true,
                    "data" => HelperLogic::formatOutput($upline, $this->patternOutputs, 1),
                ];
            }
        }

        # [standard output]
        return $this->output();
    }
}
