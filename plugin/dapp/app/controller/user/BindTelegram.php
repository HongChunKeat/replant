<?php

namespace plugin\dapp\app\controller\user;

# library
use plugin\dapp\app\controller\Base;
use support\Redis;
use support\Request;
# database & logic
use app\model\database\AccountUserModel;
use app\model\logic\HelperLogic;

class BindTelegram extends Base
{
    public function index(Request $request)
    {
        # user id
        $cleanVars["uid"] = $request->visitor["id"];

        # [checking]
        [$user] = $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error) && $user) {
            # [process]
            if (count($cleanVars) > 0) {
                $token = HelperLogic::randomCode(6);

                // 2 minute
                Redis::setEx("telegram_bind:" . $token, 120, $user["user_id"]);

                # [result]
                $this->response = [
                    "success" => true,
                    "data" => [
                        "code" => $token
                    ],
                ];
            }
        }

        # [standard output]
        return $this->output();
    }

    private function checking(array $params = [])
    {
        # [condition]
        if (isset($params["uid"])) {
            $user = AccountUserModel::where(["id" => $params["uid"], "status" => "active"])->first();
            if (!$user) {
                $this->error[] = "user:missing";
            } else {
                if ($user["telegram"]) {
                    $this->error[] = "user:already_bind";
                }
            }
        }

        return [$user ?? 0];
    }
}