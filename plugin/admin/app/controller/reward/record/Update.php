<?php

namespace plugin\admin\app\controller\reward\record;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\AccountUserModel;
use app\model\database\SettingOperatorModel;
use app\model\database\SettingWalletModel;
use app\model\database\RewardRecordModel;
use app\model\database\UserTreeModel;
use app\model\logic\HelperLogic;

class Update extends Base
{
    # [validation-rule]
    protected $rule = [
        "uid" => "number|max:11",
        "user_tree_id" => "number|max:11",
        "from_uid" => "number|max:11",
        "from_user_tree_id" => "number|max:11",
        "reward_type" => "number|max:11",
        "amount" => "float|max:20",
        "rate" => "float|max:20",
        "distribution_wallet" => "",
        "distribution_value" => "",
        "ref_table" => "",
        "ref_id" => "number|max:11",
        "remark" => "",
        "used_at" => "number|length:8",
        "pay_at" => "date",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "uid",
        "user_tree_id",
        "from_uid",
        "from_user_tree_id",
        "reward_type",
        "amount",
        "rate",
        "distribution_wallet",
        "distribution_value",
        "ref_table",
        "ref_id",
        "remark",
        "used_at",
        "pay_at",
    ];

    public function index(Request $request, int $targetId = 0)
    {
        if ($request->post("distribution_wallet") || $request->post("distribution_value")) {
            $this->rule["distribution_wallet"] .= "|require";
            $this->rule["distribution_value"] .= "|require";
        }

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
                if (!empty($cleanVars["distribution_wallet"]) && !empty($cleanVars["distribution_value"])) {
                    $cleanVars["distribution"] = json_encode(
                        HelperLogic::combineParamsToArray($cleanVars["distribution_wallet"], $cleanVars["distribution_value"])
                    );
                } else {
                    $cleanVars["distribution"] = null;
                }

                # [unset key]
                unset($cleanVars["distribution_wallet"]);
                unset($cleanVars["distribution_value"]);

                $res = RewardRecordModel::where("id", $targetId)->update($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "update", "reward_record", $targetId);
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
        if (!empty($params["uid"])) {
            // check uid exists
            if (!AccountUserModel::where(["id" => $params["uid"]])->first()) {
                $this->error[] = "uid:invalid";
            }
        }

        if (!empty($params["from_uid"])) {
            // check from_uid exists
            if (!AccountUserModel::where(["id" => $params["from_uid"]])->first()) {
                $this->error[] = "from_uid:invalid";
            }
        }

        if (!empty($params["user_tree_id"])) {
            // check user tree exists
            if (!UserTreeModel::where(["id" => $params["user_tree_id"]])->first()) {
                $this->error[] = "user_tree_id:invalid";
            }
        }

        if (!empty($params["from_user_tree_id"])) {
            // check from user tree exists
            if (!UserTreeModel::where(["id" => $params["from_user_tree_id"]])->first()) {
                $this->error[] = "from_user_tree_id:invalid";
            }
        }

        if (!empty($params["reward_type"])) {
            // check reward_type exists
            if (!SettingOperatorModel::where("id", $params["reward_type"])->first()) {
                $this->error[] = "reward_type:invalid";
            }
        }

        if (!empty($params["distribution_wallet"]) && !empty($params["distribution_value"])) {
            $distributionValueBreak = HelperLogic::explodeParams($params["distribution_value"]);

            $checkWallet = SettingWalletModel::whereIn("id", $params["distribution_wallet"])->get();
            if (count($checkWallet) != count($params["distribution_wallet"])) {
                $this->error[] = "distribution_wallet:invalid";
            }

            if (count($params["distribution_wallet"]) != count($distributionValueBreak)) {
                $this->error[] = "distribution_wallet_and_value:invalid";
            }
        }
    }
}
