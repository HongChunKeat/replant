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
use app\model\logic\EvmLogic;

class Create extends Base
{
    # [validation-rule]
    protected $rule = [
        "uid" => "require|number|max:11",
        "message" => "require|max:255",
        "signed_message" => "require|max:255",
        "status" => "require|number|max:11",
        "txid" => "require|min:60|max:70|alphaNum",
        "from_address" => "require|length:42|alphaNum",
        "to_address" => "require|length:42|alphaNum",
        "network" => "require|number|max:11",
        "token_address" => "require|length:42|alphaNum",
        "completed_at" => "date",
        "ref_table" => "require",
        "ref_id" => "require|number|max:11",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "uid",
        "message",
        "signed_message",
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
                $cleanVars["sn"] = HelperLogic::generateUniqueSN("user_nft");
                $res = UserNftModel::create($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "create", "user_nft", $res["id"]);
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

        // check network
        if (isset($params["network"])) {
            if (!SettingBlockchainNetworkModel::where("id", $params["network"])->first()) {
                $this->error[] = "network:invalid";
            }
        }

        // check status
        if (isset($params["status"])) {
            $statusList = SettingOperatorModel::where("category", "status")
                ->whereIn("code", ["processing", "success", "failed"])
                ->get()
                ->toArray();

            if (!in_array($params["status"], array_column($statusList, "id"))) {
                $this->error[] = "status:invalid";
            }
        }
        
        // check signed message
        if(isset($params["message"]) && isset($params["signed_message"])) {
            $signedMessage = EvmLogic::signMessage($params["message"]);
            if(!$signedMessage) {
                $this->error[] = "signed_message:unable_to_sign";
            } else {
                if($signedMessage != $params["signed_message"]) {
                    $this->error[] = "signed_message:does_not_match_message";
                }
            }
        }

        // if no log index then need unique txid
        if (isset($params["txid"])) {
            if (UserNftModel::where(["txid" => $params["txid"], "log_index" => null])->first()) {
                $this->error[] = "txid:exists";
            }
        }
    }
}
