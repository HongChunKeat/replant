<?php

namespace plugin\admin\app\controller\system\nftUsage;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\NftUsageModel;

class Delete extends Base
{
    public function index(Request $request, int $targetId = 0)
    {
        # [process]
        $res = NftUsageModel::where("id", $targetId)->delete();

        # [result]
        if ($res) {
            LogAdminModel::log($request, "delete", "nft_usage", $targetId);
            $this->response = [
                "success" => true,
            ];
        }

        # [standard output]
        return $this->output();
    }
}
