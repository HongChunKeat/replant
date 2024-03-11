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

class Create extends Base
{
    # [validation-rule]
    protected $rule = [
        "uid" => "require|number|max:11",
        "amount" => "require|float|max:20",
        "status" => "require|number|max:11",
        "coin" => "require|number|max:11",
        "txid" => "require|min:60|max:70|alphaNum",
        "from_address" => "require|length:42|alphaNum",
        "to_address" => "require|length:42|alphaNum",
        "network" => "require|max:48",
        "token_address" => "require|length:42|alphaNum",
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

    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->post(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->post(), $this->patternInputs);

        # [checking]
        $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error)) {
            $res = "";

            # [process]
            if (count($cleanVars) > 0) {
                if (isset($cleanVars["coin"])) {
                    $cleanVars["coin_id"] = $cleanVars["coin"];
                }

                # [unset key]
                unset($cleanVars["coin"]);

                $cleanVars["sn"] = HelperLogic::generateUniqueSN("user_deposit");

                # [create query]
                $res = UserDepositModel::create($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "create", "user_deposit", $res["id"]);
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
        if (isset($params["uid"])) {
            if (!AccountUserModel::where("id", $params["uid"])->first()) {
                $this->error[] = "uid:invalid";
            }
        }

        // check coin_id
        if (isset($params["coin"])) {
            if (!SettingCoinModel::where("id", $params["coin"])->first()) {
                $this->error[] = "coin:invalid";
            }
        }

        // check network
        if (isset($params["network"])) {
            if (!SettingBlockchainNetworkModel::where("id", $params["network"])->first()) {
                $this->error[] = "network:invalid";
            }
        }

        // check status
        if (isset($params["status"])) {
            $statusList = SettingOperatorModel::where("category", "status")
                ->whereIn("code", ["success", "failed"])
                ->get()
                ->toArray();

            if (!in_array($params["status"], array_column($statusList, "id"))) {
                $this->error[] = "status:invalid";
            }
        }

        // if no log index then need unique txid
        if (isset($params["txid"])) {
            if (UserDepositModel::where(["txid" => $params["txid"], "log_index" => null])->first()) {
                $this->error[] = "txid:exists";
            }
        }
    }
}
