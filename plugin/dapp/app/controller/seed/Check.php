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
            $time = 0;
            if (!empty($seed["claimed_at"])) {
                $time = (strtotime($seed["claimed_at"]) + 86400) - time();
                $time = $time < 0 ? 0 : $time * 1000;
            }

            // if empty claimed at, is_active or claimable not 1 then countdown = null
            $this->response = [
                "success" => true,
                "data" => [
                    "is_active" => $seed["is_active"],
                    "countdown" => empty($seed["claimed_at"]) || $seed["is_active"] != 1 || $seed["claimable"] != 1
                        ? null
                        : $time
                ]
            ];
        }


        # [standard output]
        return $this->output();
    }
}
