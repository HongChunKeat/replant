<?php

namespace plugin\dapp\app\controller\team;

# library
use plugin\dapp\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\NetworkSponsorModel;
use app\model\database\AccountUserModel;
use app\model\logic\HelperLogic;

class Listing extends Base
{
    # [outputs-pattern]
    protected $patternOutputs = [
        "user",
    ];

    public function index(Request $request)
    {
        # user id
        $cleanVars["upline_uid"] = $request->visitor["id"];

        # [listing query]
        $res = NetworkSponsorModel::listing(
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
            }

            $this->response = [
                "success" => true,
                "data" => HelperLogic::formatOutput($res, $this->patternOutputs, 1),
            ];
        }

        # [standard output]
        return $this->output();
    }
}
