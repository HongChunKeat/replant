<?php

namespace plugin\dapp\app\model\logic;

# system lib
# database & logic
use app\model\database\WalletTransactionModel;
use app\model\database\WalletTransactionDetailModel;
use app\model\logic\HelperLogic;
use app\model\logic\SettingLogic;

class UserWalletLogic
{
    public static function getBalance(int $uid = 0, int $walletId = 0)
    {
        $_response = false;

        $_response = WalletTransactionModel::leftJoin(
            "wallet_transaction_detail",
            "wallet_transaction.id",
            "=",
            "wallet_transaction_detail.wallet_transaction_id"
        )
            ->where([
                "wallet_transaction.uid" => $uid,
                "wallet_transaction_detail.wallet_id" => $walletId,
            ])
            ->sum("wallet_transaction_detail.amount");

        return $_response;
    }

    // UserWalletLogic::add([
    //     "type" => 4,
    //     "uid" => $targetId,
    //     "distribution" => [1 => 150, 2 => 50],
    //     "refTable" => "account_user",
    //     "refId" => 1
    // ]);
    public static function add(array $params)
    {
        $type = $params["type"];
        $uid = $params["uid"];
        $fromUid = $params["fromUid"] ?? 0;
        $toUid = $params["toUid"] ?? 0;
        $distribution = $params["distribution"];
        $refTable = $params["refTable"] ?? "";
        $refId = $params["refId"] ?? 0;
        $total = 0;

        $_response = false;

        $_response = WalletTransactionModel::create([
            "sn" => HelperLogic::generateUniqueSN("wallet_transaction"),
            "transaction_type" => $type,
            "uid" => $uid,
            "from_uid" => $fromUid,
            "to_uid" => $toUid,
            "distribution" => json_encode($distribution),
            "ref_table" => $refTable,
            "ref_id" => $refId,
            "used_at" => date("Ymd"),
        ]);

        $total = self::distribution($distribution, $_response, $uid, true);

        WalletTransactionModel::where("id", $_response["id"])->update(["amount" => $total]);

        return $_response;
    }

    public static function deduct(array $params)
    {
        $type = $params["type"];
        $uid = $params["uid"];
        $fromUid = $params["fromUid"] ?? 0;
        $toUid = $params["toUid"] ?? 0;
        $distribution = $params["distribution"];
        $refTable = $params["refTable"] ?? "";
        $refId = $params["refId"] ?? 0;
        $total = 0;

        $_response = false;

        $_response = WalletTransactionModel::create([
            "sn" => HelperLogic::generateUniqueSN("wallet_transaction"),
            "transaction_type" => $type,
            "uid" => $uid,
            "from_uid" => $fromUid,
            "to_uid" => $toUid,
            "distribution" => json_encode($distribution),
            "ref_table" => $refTable,
            "ref_id" => $refId,
            "used_at" => date("Ymd"),
        ]);

        $total = self::distribution($distribution, $_response, $uid, false);

        WalletTransactionModel::where("id", $_response["id"])->update(["amount" => $total]);

        return $_response;
    }

    private static function distribution($distribution, $_response, $uid, $positive)
    {
        $total = 0;
        foreach ($distribution as $walletId => $amount) {
            $total += abs($amount);

            $userbalance = self::getBalance($uid, $walletId);

            WalletTransactionDetailModel::create([
                "wallet_transaction_id" => $_response["id"],
                "wallet_id" => $walletId,
                "amount" => ($positive)
                    ? $amount
                    : -$amount,
                "before_amount" => $userbalance,
                "after_amount" => ($positive)
                    ? $userbalance + $amount
                    : $userbalance - $amount,
            ]);
        }

        return $total;
    }

    public static function paymentCheck(int $uid, int $paymentId, $distribution)
    {
        $res = false;
        $data = [];
        $calcFormula = [];
        $formula = [];
        $balanceRes = 0;
        $formulaRes = 0;

        //get payment type
        $paymentType = SettingLogic::get("payment", ["id" => $paymentId]);
        if (!$paymentType) {
            $data[] = "payment:missing";
        } else {
            // decode setting payment formula
            $calcFormula = json_decode($paymentType["calc_formula"], true);
            $formula = json_decode($paymentType["formula"], true);

            // formula = 1:100 2:100
            // calcformula = 1:equal 2:min
            foreach ($formula as $formulaWallet => $formulaValue) {
                $walletName = SettingLogic::get("wallet", ["id" => $formulaWallet]);

                if (!$walletName) {
                    $data[] = "payment:invalid_wallet";
                } else {
                    if (isset($distribution[$formulaWallet])) {
                        // check balance
                        $userBalance = UserWalletLogic::getBalance($uid, $formulaWallet);
                        if ($distribution[$formulaWallet] > $userBalance) {
                            $data[] = $walletName["code"] . ":insufficient_balance";
                        } else {
                            $balanceRes++;
                        }

                        if (isset($calcFormula[$formulaWallet])) {
                            // check amount based on calc value : equal min max
                            if ($calcFormula[$formulaWallet] == "min" && $distribution[$formulaWallet] < $formulaValue) {
                                $data[] = $walletName["code"] . ":minimum_" . $formulaValue;
                            } elseif ($calcFormula[$formulaWallet] == "equal" && $distribution[$formulaWallet] != $formulaValue) {
                                $data[] = $walletName["code"] . ":must_be_" . $formulaValue;
                            } elseif ($calcFormula[$formulaWallet] == "max" && $distribution[$formulaWallet] > $formulaValue) {
                                $data[] = $walletName["code"] . ":maximum_" . $formulaValue;
                            } else {
                                $formulaRes++;
                            }
                        }
                    } else {
                        $data[] = $walletName["code"] . ":is_required";
                    }
                }
            }
        }
        // echo $balanceRes."|".count($formula)."|".count($calcFormula)."|".count($distribution)."\n";
        // echo $formulaRes."|".count($formula)."|".count($calcFormula)."|".count($distribution)."\n";

        // need input, setting both match then true need match 3 all same
        // if input 2 setting must have 2, if input 1 setting 2 or vice versa false
        if (
            $balanceRes == count($formula) &&
            $balanceRes == count($calcFormula) &&
            $balanceRes == count($distribution) &&
            $formulaRes == count($formula) &&
            $formulaRes == count($calcFormula) &&
            $formulaRes == count($distribution) &&
            !count($data)
        ) {
            $res = true;
        } else {
            $data[] = "failed";
        }

        return [
            "success" => $res,
            "data" => $data,
        ];
    }
}