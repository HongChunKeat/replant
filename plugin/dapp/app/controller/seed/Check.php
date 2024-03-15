<?php

namespace plugin\dapp\app\controller\seed;

# library
use plugin\dapp\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\UserSeedModel;

class Check extends Base
{
    public function index(Request $request)
    {
        # user id
        $cleanVars["uid"] = $request->visitor["id"];

        $seed = UserSeedModel::where("uid", $cleanVars["uid"])->first();

        if ($seed) {
            $nextClaimTime = empty($seed["claimed_at"])
                ? strtotime($seed["created_at"]) + 86400
                : strtotime($seed["claimed_at"]) + 86400;

            $time = $nextClaimTime - time();
            if ($time < 0) {
                $time = 0;
            } else {
                $time = $time * 1000;
            }

            $this->response = [
                "success" => true,
                "data" => [
                    "claimable" => $seed["claimable"],
                    "countdown" => $seed["claimable"] ? $time : 0,
                ]
            ];
        }


        # [standard output]
        return $this->output();
    }
}
