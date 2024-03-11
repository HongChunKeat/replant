<?php

namespace plugin\admin\app\controller\user\point;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\AccountUserModel;
use app\model\database\UserPointModel;
use app\model\logic\HelperLogic;

class Create extends Base
{
    # [validation-rule]
    protected $rule = [
        "uid" => "require|number|max:11",
        "from_uid" => "require|number|max:11",
        "point" => "require|float|max:11|negative",
        "source" => "require|in:claim,referral,purchase_nft,refund_nft",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "uid",
        "from_uid",
        "point",
        "source",
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
                $res = UserPointModel::create($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "create", "user_point", $res["id"]);
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

        // check from_uid
        if (isset($params["from_uid"])) {
            if (!AccountUserModel::where("id", $params["from_uid"])->first()) {
                $this->error[] = "from_uid:invalid";
            }
        }
    }
}
