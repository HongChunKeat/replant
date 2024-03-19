<?php

namespace plugin\admin\app\controller\system\nftUsage;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\AccountUserModel;
use app\model\database\NftUsageModel;
use app\model\database\SettingNftModel;
use app\model\logic\HelperLogic;

class Read extends Base
{
    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "uid",
        "user",
        "nft",
        "token_id",
        "remark",
    ];

    public function index(Request $request, int $targetId = 0)
    {
        # [process]
        $res = NftUsageModel::where("id", $targetId)->first();

        # [result]
        if ($res) {
            // address
            $uid = AccountUserModel::where("id", $res["uid"])->first();
            $res["user"] = $uid ? $uid["user_id"] : "";

            $nft = SettingNftModel::where("id", $res["nft_id"])->first();
            $res["nft"] = $nft ? $nft["name"] : "";

            $this->response = [
                "success" => true,
                "data" => HelperLogic::formatOutput($res, $this->patternOutputs),
            ];
        }

        # [standard output]
        return $this->output();
    }
}
