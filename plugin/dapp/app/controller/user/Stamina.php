<?php

namespace plugin\dapp\app\controller\user;

# library
use plugin\dapp\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\UserStaminaModel;
use plugin\dapp\app\model\logic\UserProfileLogic;
use app\model\logic\HelperLogic;

class Stamina extends Base
{
    # [outputs-pattern]
    protected $patternOutputs = [
        "current_stamina",
        "max_stamina",
        "countdown"
    ];

    public function index(Request $request)
    {
        # user id
        $cleanVars["uid"] = $request->visitor["id"];

        $res = UserStaminaModel::where("id", $cleanVars["uid"])->first();

        # [result]
        if ($res) {
            if ($res["current_stamina"] >= $res["max_stamina"]) {
                $res["countdown"] = null;
            } else {
                $res["countdown"] = UserProfileLogic::getCountdown(120) . "000";
            }

            $this->response = [
                "success" => true,
                "data" => HelperLogic::formatOutput($res, $this->patternOutputs),
            ];
        }

        # [standard output]
        return $this->output();
    }
}
