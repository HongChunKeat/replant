<?php

namespace plugin\admin\app\controller\account\user;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\SettingWalletModel;
use app\model\database\SettingOperatorModel;
use app\model\database\UserWithdrawModel;
use app\model\logic\HelperLogic;
use plugin\dapp\app\model\logic\UserProfileLogic;
use plugin\dapp\app\model\logic\UserWalletLogic;

class Details extends Base
{
    # [validation-rule]
    protected $rule = [
        "user_id" => "require|max:80",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "user_id",
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "user_id",
        "web3_address",
        "nickname",
        "login_id",
        "status",
        "telegram",
        "discord",
        "twitter",
        "google",
        "telegram_name",
        "discord_name",
        "twitter_name",
        "google_name",
        "created_at",
        "wallet",
        "total_wallet",
        "total_withdraw"
    ];

    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->get(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->get(), $this->patternInputs);

        # [proceed]
        if (!count($this->error)) {

            # [process]
            // 4 in 1 search
            $res = UserProfileLogic::multiSearch($cleanVars["user_id"]);

            # [result]
            if ($res) {
                $success = SettingOperatorModel::where("code", "success")->first();

                $totalAmount = 0;
                $res["wallet"] = SettingWalletModel::select("id", "code")->get();

                foreach ($res["wallet"] as $row) {
                    $amount = UserWalletLogic::getBalance($res["id"], $row["id"]);
                    $totalAmount += $amount;
                    $row["amount"] = $amount;
                }
                $res["total_wallet"] = $totalAmount;
                $res["total_withdraw"] = UserWithdrawModel::where(["status" => $success["id"], "uid" => $res["id"]])->sum("amount");

                $this->response = [
                    "success" => true,
                    "data" => HelperLogic::formatOutput($res, $this->patternOutputs),
                ];
            }
        }

        # [standard output]
        return $this->output();
    }
}
