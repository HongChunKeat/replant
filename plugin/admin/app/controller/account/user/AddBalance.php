<?php

namespace plugin\admin\app\controller\account\user;

# library
use plugin\admin\app\controller\Base;
use support\Request;
use Webman\RedisQueue\Redis as RedisQueue;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\AccountUserModel;
use app\model\logic\HelperLogic;

class AddBalance extends Base
{
    # [validation-rule]
    protected $rule = [
        "wallet_id" => "require|number",
        "amount" => "require|float|max:20",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "wallet_id",
        "amount",
    ];

    public function index(Request $request, int $targetId = 0)
    {
        # [validation]
        $this->validation($request->post(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->post(), $this->patternInputs);

        # [checking]
        $this->checking(["id" => $targetId] + $cleanVars);

        # [proceed]
        if (!count($this->error)) {
            # [process with queue]
            RedisQueue::send("admin_wallet", [
                "type" => "editWallet",
                "data" => [
                    "uid" => $targetId,
                    "walletId" => $cleanVars["wallet_id"],
                    "amount" => $cleanVars["amount"],
                    "type" => "add",
                ]
            ]);

            LogAdminModel::log($request, "add_balance", "account_user", $targetId);
            # [result]
            $this->response = [
                "success" => true,
            ];
        }

        # [standard output]
        return $this->output();
    }

    private function checking(array $params = [])
    {
        # [condition]
        if (isset($params["id"])) {
            // check user exist
            if (!AccountUserModel::where(["id" => $params["id"]])->first()) {
                $this->error[] = "user:invalid";
            }
        }
    }
}
