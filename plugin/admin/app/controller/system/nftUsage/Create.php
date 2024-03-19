<?php

namespace plugin\admin\app\controller\system\nftUsage;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\AccountUserModel;
use app\model\database\NftUsageModel;
use app\model\database\SettingNftModel;
use app\model\logic\HelperLogic;

class Create extends Base
{
    # [validation-rule]
    protected $rule = [
        "uid" => "require|number|max:11",
        "nft_id" => "require|number|max:11",
        "token_id" => "require|number|max:11",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "uid",
        "nft_id",
        "token_id",
        "remark",
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
                $res = NftUsageModel::create($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "create", "nft_usage", $res["id"]);
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
        // check uid exists
        if (isset($params["uid"])) {
            if (!AccountUserModel::where(["id" => $params["uid"]])->first()) {
                $this->error[] = "uid:invalid";
            }
        }

        // check nft_id exists
        if (isset($params["nft_id"])) {
            if (!SettingNftModel::where(["id" => $params["nft_id"]])->first()) {
                $this->error[] = "nft_id:invalid";
            }
        }

        if (isset($params["nft_id"]) && isset($params["token_id"])) {
            if (NftUsageModel::where(["nft_id" => $params["nft_id"], "token_id" => $params["token_id"]])->first()) {
                $this->error[] = "entry:exists";
            }
        }
    }
}
