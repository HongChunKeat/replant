<?php

namespace plugin\admin\app\controller\user\deposit;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\SettingBlockchainNetworkModel;
use app\model\database\SettingCoinModel;
use app\model\database\SettingOperatorModel;
use app\model\database\UserDepositModel;
use app\model\database\AccountUserModel;
use app\model\logic\HelperLogic;

class Update extends Base
{
    # [validation-rule]
    protected $rule = [
        "uid" => "number|max:11",
        "amount" => "float|max:20",
        "status" => "number|max:11",
        "coin" => "number|max:11",
        "txid" => "min:60|max:70|alphaNum",
        "from_address" => "length:42|alphaNum",
        "to_address" => "length:42|alphaNum",
        "network" => "max:48",
        "token_address" => "length:42|alphaNum",
        "completed_at" => "date",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "uid",
        "amount",
        "status",
        "coin",
        "txid",
        "from_address",
        "to_address",
        "network",
        "token_address",
        "completed_at",
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
                if (!empty($cleanVars["coin"])) {
                    $cleanVars["coin_id"] = $cleanVars["coin"];
                }

                # [unset key]
                unset($cleanVars["coin"]);

                # [update query]
                $res = UserDepositModel::where("id", $targetId)->update($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "update", "user_deposit", $targetId);
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

        // check coin_id
        if (!empty($params["coin"])) {
            if (!SettingCoinModel::where("id", $params["coin"])->first()) {
                $this->error[] = "coin:invalid";
            }
        }

        if (!empty($params["network"])) {
            if (!SettingBlockchainNetworkModel::where("id", $params["network"])->first()) {
                $this->error[] = "network:invalid";
            }
        }

        // check status
        if (!empty($params["status"])) {
            $statusList = SettingOperatorModel::where("category", "status")
                ->whereIn("code", ["success", "failed"])
                ->get()
                ->toArray();

            if (!in_array($params["status"], array_column($statusList, "id"))) {
                $this->error[] = "status:invalid";
            }
        }

        // if no log index then need unique txid
        if (!empty($params["txid"])) {
            if (UserDepositModel::where(["txid" => $params["txid"], "log_index" => null])
                ->whereNot("id", $params["id"])
                ->first()
            ) {
                $this->error[] = "txid:exists";
            }
        }
    }
}
