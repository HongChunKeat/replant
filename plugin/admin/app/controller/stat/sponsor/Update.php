<?php

namespace plugin\admin\app\controller\stat\sponsor;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\AccountUserModel;
use app\model\database\StatSponsorModel;
use app\model\logic\HelperLogic;

class Update extends Base
{
    # [validation-rule]
    protected $rule = [
        "uid" => "number|max:11",
        "from_uid" => "number|max:11",
        "stat_type" => "",
        "amount" => "float|max:11",
        "is_personal" => "in:1,0",
        "is_cumulative" => "in:1,0",
        "used_at" => "number|length:8",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "uid",
        "from_uid",
        "stat_type",
        "amount",
        "is_personal",
        "is_cumulative",
        "used_at",
        "remark",
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
            $res = "";

            # [process]
            if (count($cleanVars) > 0) {
                $res = StatSponsorModel::where("id", $targetId)->update($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "update", "stat_sponsor", $targetId);
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
        if (isset($params["uid"])) {
            // check uid exists
            if (!AccountUserModel::where(["id" => $params["uid"]])->first()) {
                $this->error[] = "uid:invalid";
            }
        }

        if (isset($params["from_uid"])) {
            // check from_uid exists
            if (!AccountUserModel::where(["id" => $params["from_uid"]])->first()) {
                $this->error[] = "from_uid:invalid";
            }
        }
    }
}
