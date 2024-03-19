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

class Update extends Base
{
    # [validation-rule]
    protected $rule = [
        "uid" => "number|max:11",
        "nft_id" => "number|max:11",
        "token_id" => "number|max:11",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "uid",
        "nft_id",
        "token_id",
        "remark",
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
                $res = NftUsageModel::where("id", $targetId)->update($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "update", "nft_usage", $targetId);
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
        if (!empty($params["uid"])) {
            if (!AccountUserModel::where(["id" => $params["uid"]])->first()) {
                $this->error[] = "uid:invalid";
            }
        }

        // check nft_id exists
        if (!empty($params["nft_id"])) {
            if (!SettingNftModel::where(["id" => $params["nft_id"]])->first()) {
                $this->error[] = "nft_id:invalid";
            }
        }

        if (!empty($params["nft_id"]) || !empty($params["token_id"])) {
            $check = NftUsageModel::where("id", $params["id"])->first();

            if (NftUsageModel::where([
                "nft_id" => empty($params["nft_id"])
                    ? $check["nft_id"]
                    : $params["nft_id"],
                "token_id" => empty($params["token_id"])
                    ? $check["token_id"]
                    : $params["token_id"],
            ])
                ->whereNot("id", $params["id"])
                ->first()
            ) {
                $this->error[] = "entry:exists";
            }
        }
    }
}
