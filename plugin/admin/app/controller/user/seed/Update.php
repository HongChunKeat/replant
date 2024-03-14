<?php

namespace plugin\admin\app\controller\user\seed;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\AccountUserModel;
use app\model\database\UserSeedModel;
use app\model\logic\HelperLogic;

class Update extends Base
{
    # [validation-rule]
    protected $rule = [
        "uid" => "number|max:11",
        "claimable" => "in:1,0",
        "claimed_at" => "date",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "uid",
        "claimable",
        "claimed_at",
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
                $res = UserSeedModel::where("id", $targetId)->update($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "update", "user_seed", $targetId);
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

            if (
                UserSeedModel::where("uid", $params["uid"])
                    ->whereNot("id", $params["id"])
                    ->first()
            ) {
                $this->error[] = "seed:exists";
            }
        }
    }
}
