<?php

namespace plugin\dapp\app\controller\onboarding;

# library
use plugin\dapp\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\NetworkSponsorModel;
use app\model\database\UserPointModel;

class PointAndReferral extends Base
{
    public function index(Request $request)
    {
        # user id
        $uid = $request->visitor["id"];

        $point = UserPointModel::where("uid", $uid)->sum("point");
        $referral = NetworkSponsorModel::where("upline_uid", $uid)->count("id");

        $this->response = [
            "success" => true,
            "data" => [
                "point" => $point * 1,
                "referral" => $referral
            ]
        ];

        # [standard output]
        return $this->output();
    }
}