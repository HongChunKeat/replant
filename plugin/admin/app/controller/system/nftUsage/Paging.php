<?php

namespace plugin\admin\app\controller\system\nftUsage;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\AccountUserModel;
use app\model\database\NftUsageModel;
use app\model\database\SettingNftModel;
use plugin\dapp\app\model\logic\UserProfileLogic;
use app\model\logic\HelperLogic;

class Paging extends Base
{
    # [validation-rule]
    protected $rule = [
        "size" => "require|number",
        "page" => "require|number",
        "id" => "number|max:11",
        "uid" => "number|max:11",
        "user" => "max:80",
        "nft" => "",
        "token_id" => "number|max:11",
        "remark" => "",
    ];

    ## [inputs-pattern]
    protected $patternInputs = [
        "id",
        "uid",
        "user",
        "nft",
        "token_id",
        "remark",
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "uid",
        "user",
        "nft",
        "token_id",
        "remark",
    ];

    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->get(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->get(), $this->patternInputs);

        # [proceed]
        if (!count($this->error)) {
            if (isset($cleanVars["user"])) {
                // 4 in 1 search
                $user = UserProfileLogic::multiSearch($cleanVars["user"]);
                $cleanVars["uid"] = $user["id"] ?? 0;
            }

            if (isset($cleanVars["nft"])) {
                // 4 in 1 search
                $nft = SettingNftModel::where("name", $cleanVars["nft"])->first();
                $cleanVars["nft_id"] = $nft["id"] ?? 0;
            }

            # [unset key]
            unset($cleanVars["user"]);
            unset($cleanVars["nft"]);

            # [process]
            $res = NftUsageModel::paging(
                $cleanVars,
                $request->get("page"),
                $request->get("size"),
                ["*"],
                ["id", "desc"]
            );

            # [result]
            if ($res) {
                # [add and edit column using for loop]
                foreach ($res["items"] as $row) {
                    $uid = AccountUserModel::where("id", $row["uid"])->first();
                    $row["user"] = $uid ? $uid["user_id"] : "";

                    $nft = SettingNftModel::where("id", $row["nft_id"])->first();
                    $row["nft"] = $nft ? $nft["name"] : "";
                }

                $this->response = [
                    "success" => true,
                    "data" => [
                        "data" => HelperLogic::formatOutput($res["items"], $this->patternOutputs, 1),
                        "count" => $res["count"],
                        "last_page" => ceil($res["count"] / $request->get("size")),
                    ],
                ];
            }
        }

        # [standard output]
        return $this->output();
    }
}
