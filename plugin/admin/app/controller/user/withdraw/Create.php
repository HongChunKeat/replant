<?php

namespace plugin\admin\app\controller\user\withdraw;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\SettingBlockchainNetworkModel;
use app\model\database\SettingCoinModel;
use app\model\database\SettingOperatorModel;
use app\model\database\SettingWalletModel;
use app\model\database\UserWithdrawModel;
use app\model\database\AccountUserModel;
use app\model\logic\HelperLogic;

class Create extends Base
{
    # [validation-rule]
    protected $rule = [
        "uid" => "require|number|max:11",
        "amount" => "require|float|max:20",
        "fee" => "require|float|max:20",
        "distribution" => "require",
        "status" => "require|number|max:11",
        "amount_wallet" => "require|number|max:11",
        "fee_wallet" => "require|number|max:11",
        "coin" => "require|number|max:11",
        "txid" => "require|min:60|max:70|alphaNum",
        "from_address" => "require|length:42|alphaNum",
        "to_address" => "require|length:42|alphaNum",
        "network" => "require|number|max:11",
        "token_address" => "require|length:42|alphaNum",
        "completed_at" => "date",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "uid",
        "amount",
        "fee",
        "distribution",
        "status",
        "amount_wallet",
        "fee_wallet",
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
                if (isset($cleanVars["amount_wallet"])) {
                    $cleanVars["amount_wallet_id"] = $cleanVars["amount_wallet"];
                }
    
                if (isset($cleanVars["fee_wallet"])) {
                    $cleanVars["fee_wallet_id"] = $cleanVars["fee_wallet"];
                }
    
                if (isset($cleanVars["coin"])) {
                    $cleanVars["to_coin_id"] = $cleanVars["coin"];
                }

                # [unset key]
                unset($cleanVars["amount_wallet"]);
                unset($cleanVars["fee_wallet"]);
                unset($cleanVars["coin"]);

                $cleanVars["sn"] = HelperLogic::generateUniqueSN("user_withdraw");

                # [create query]
                $res = UserWithdrawModel::create($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "create", "user_withdraw", $res["id"]);
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

        // check amount wallet
        if (isset($params["amount_wallet"])) {
            if (!SettingWalletModel::where("id", $params["amount_wallet"])->first()) {
                $this->error[] = "amount_wallet:invalid";
            }
        }

        // check fee wallet
        if (isset($params["fee_wallet"])) {
            if (!SettingWalletModel::where("id", $params["fee_wallet"])->first()) {
                $this->error[] = "fee_wallet:invalid";
            }
        }

        // check to coin
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
                ->whereIn("code", ["pending", "accepted", "processing", "rejected", "success", "failed"])
                ->get()
                ->toArray();

            if (!in_array($params["status"], array_column($statusList, "id"))) {
                $this->error[] = "status:invalid";
            }
        }

        // if no log index then need unique txid
        if (isset($params["txid"])) {
            if (UserWithdrawModel::where(["txid" => $params["txid"], "log_index" => null])->first()) {
                $this->error[] = "txid:exists";
            }
        }
    }
}
