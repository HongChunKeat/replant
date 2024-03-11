<?php

namespace plugin\admin\app\controller\user\withdraw;

# library
use plugin\admin\app\controller\Base;
use support\Request;
use Webman\RedisQueue\Redis as RedisQueue;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\AccountUserModel;
use app\model\database\SettingBlockchainNetworkModel;
use app\model\database\SettingCoinModel;
use app\model\database\SettingOperatorModel;
use app\model\database\SettingWalletModel;
use app\model\database\UserWithdrawModel;
use app\model\logic\HelperLogic;
use app\model\logic\SettingLogic;

class Update extends Base
{
    # [validation-rule]
    protected $rule = [
        "uid" => "number|max:11",
        "amount" => "float|max:20",
        "fee" => "float|max:20",
        "distribution" => "",
        "status" => "number|max:11",
        "amount_wallet" => "number|max:11",
        "fee_wallet" => "number|max:11",
        "coin" => "number|max:11",
        "txid" => "min:60|max:70|alphaNum",
        "log_index" => "",
        "from_address" => "length:42|alphaNum",
        "to_address" => "length:42|alphaNum",
        "network" => "max:11",
        "token_address" => "length:42|alphaNum",
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
        "log_index",
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
                if (!empty($cleanVars["amount_wallet"])) {
                    $cleanVars["amount_wallet_id"] = $cleanVars["amount_wallet"];
                }

                if (!empty($cleanVars["fee_wallet"])) {
                    $cleanVars["fee_wallet_id"] = $cleanVars["fee_wallet"];
                }

                if (!empty($cleanVars["coin"])) {
                    $cleanVars["to_coin_id"] = $cleanVars["coin"];
                }

                # [unset key]
                unset($cleanVars["amount_wallet"]);
                unset($cleanVars["fee_wallet"]);
                unset($cleanVars["coin"]);

                # [update query]
                $res = UserWithdrawModel::where("id", $targetId)->update($cleanVars);

                // If status reject will send queue
                if (!empty($cleanVars["status"])) {
                    $statusRejected = SettingLogic::get("operator", ["code" => "rejected"]);

                    $withdraw = UserWithdrawModel::where("id", $targetId)->first();

                    if ($withdraw) {
                        if (
                            $cleanVars["status"] == $statusRejected["id"] &&
                            empty($withdraw["log_index"])
                        ) {
                            RedisQueue::send("user_wallet", [
                                "type" => "withdrawRefund",
                                "data" => [
                                    "id" => $targetId
                                ]
                            ]);
                        }
                    }
                }
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "update", "user_withdraw", $targetId);
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

        // check amount wallet
        if (!empty($params["amount_wallet"])) {
            if (!SettingWalletModel::where("id", $params["amount_wallet"])->first()) {
                $this->error[] = "amount_wallet:invalid";
            }
        }

        // check fee wallet
        if (!empty($params["fee_wallet"])) {
            if (!SettingWalletModel::where("id", $params["fee_wallet"])->first()) {
                $this->error[] = "fee_wallet:invalid";
            }
        }

        // check to coin
        if (!empty($params["coin"])) {
            if (!SettingCoinModel::where("id", $params["coin"])->first()) {
                $this->error[] = "coin:invalid";
            }
        }

        // check network
        if (!empty($params["network"])) {
            if (!SettingBlockchainNetworkModel::where("id", $params["network"])->first()) {
                $this->error[] = "network:invalid";
            }
        }

        // check status if not pending not allow to change status
        if (!empty($params["status"])) {
            $userWithdraw = UserWithdrawModel::where("id", $params["id"])->first();
            $statusPending = SettingOperatorModel::where("category", "status")->where("code", "pending")->select("id")->first();

            if ($userWithdraw) {
                if ($userWithdraw["status"] != $statusPending["id"]) {
                    $this->error[] = "status:must_be_pending_to_edit";
                } else {
                    $statusList = SettingOperatorModel::where("category", "status")
                        ->whereIn("code", ["accepted", "processing", "rejected", "success", "failed"])
                        ->get()
                        ->toArray();

                    if (!in_array($params["status"], array_column($statusList, "id"))) {
                        $this->error[] = "status:invalid";
                    }
                }
            }
        }

        // if no log index then need unique txid
        if (!empty($params["txid"])) {
            if (
                UserWithdrawModel::where(["txid" => $params["txid"], "log_index" => null])
                    ->whereNot("id", $params["id"])
                    ->first()
            ) {
                $this->error[] = "txid:exists";
            }
        }
    }
}
