<?php

namespace plugin\admin\app\controller\network\sponsor;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\NetworkSponsorModel;

class Delete extends Base
{
    public function index(Request $request, int $targetId = 0)
    {
        # [process]
        $res = NetworkSponsorModel::where("id", $targetId)->delete();

        if ($res) {
            LogAdminModel::log($request, "delete", "network_sponsor", $targetId);
            $this->response = [
                "success" => true,
            ];
        }

        # [standard output]
        return $this->output();
    }
}
