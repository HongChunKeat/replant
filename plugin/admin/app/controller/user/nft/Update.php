<?php

namespace plugin\admin\app\controller\user\nft;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\AccountUserModel;
use app\model\database\UserNftModel;
use app\model\database\SettingBlockchainNetworkModel;
use app\model\database\SettingOperatorModel;
use app\model\logic\HelperLogic;

class Update extends Base
{
    # [validation-rule]
    protected $rule = [
        "uid" => "number|max:11",
        "status" => "number|max:11",
        "txid" => "min:60|max:70|alphaNum",
        "from_address" => "length:42|alphaNum",
        "to_address" => "length:42|alphaNum",
        "network" => "number|max:11",
        "token_address" => "length:42|alphaNum",
        "completed_at" => "date",
        "ref_table" => "",
        "ref_id" => "number|max:11",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "uid",
        "status",
        "txid",
        "log_index",
        "from_address",
        "to_address",
        "network",
        "token_address",
        "completed_at",
        "ref_table",
        "ref_id",
        "remark"
    ];

    public function index(Request $request, int $targetId = 0)
    {
        # [validation]
        $this->validation($request->post(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->post(), $this->patternInputs, 1);

        # [checking]
        $this->checking(["id" => $targetId] + $cleanVars);

        # [proceed]
        if (!count($this->error)) {
            $res = "";

            # [process]
            if (count($cleanVars) > 0) {
                $res = UserNftModel::where("id", $targetId)->update($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "update", "user_nft", $targetId);
                $this->response = [
                    "success" => true,
                ];
            }
        }

        # [standard output]
        return $this->output();
    }

    private function checking(array $params = [])
    {
        # [condition]
        // check uid
        if (!empty($params["uid"])) {
            if (!AccountUserModel::where("id", $params["uid"])->first()) {
                $this->error[] = "uid:invalid";
            }
        }

        // check network
        if (!empty($params["network"])) {
            if (!SettingBlockchainNetworkModel::where("id", $params["network"])->first()) {
                $this->error[] = "network:invalid";
            }
        }

        // check status
        if (!empty($params["status"])) {
            $statusList = SettingOperatorModel::where("category", "status")
                ->whereIn("code", ["processing", "success", "failed"])
                ->get()
                ->toArray();

            if (!in_array($params["status"], array_column($statusList, "id"))) {
                $this->error[] = "status:invalid";
            }
        }

        // if no log index then need unique txid
        if (!empty($params["txid"])) {
            if (UserNftModel::where(["txid" => $params["txid"], "log_index" => null])
                ->whereNot("id", $params["id"])
                ->first()
            ) {
                $this->error[] = "txid:exists";
            }
        }
    }
}
