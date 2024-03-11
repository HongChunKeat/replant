<?php

namespace plugin\dapp\app\controller\petInfo;

# library
use plugin\dapp\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\UserPetModel;
use plugin\dapp\app\model\logic\UserProfileLogic;
use plugin\admin\app\model\logic\PetLogic;

class MinedAmount extends Base
{
    public function index(Request $request)
    {
        # user id
        $cleanVars["uid"] = $request->visitor["id"];

        $res = "";

        # [proceed]
        $res = UserPetModel::defaultWhere()->where(["uid" => $cleanVars["uid"], "is_active" => 1])->get();

        # [result]
        if ($res) {
            $normal = 0;
            $premium = 0;
            foreach ($res as $row) {
                if ($row["quality"] == "normal") {
                    $normal += PetLogic::countMining($row["id"]);
                } else {
                    $premium += PetLogic::countMining($row["id"]);
                }
            }

            $this->response = [
                "success" => true,
                "data" => [
                    "mined_amount" => [
                        "normal" => $normal,
                        "premium" => $premium
                    ],
                    "countdown" => UserProfileLogic::getCountdown(1) . "000",
                ],
            ];
        }

        # [standard output]
        return $this->output();
    }
}