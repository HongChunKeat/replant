<?php

namespace plugin\admin\app\controller\setting\level;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\SettingLevelModel;
use app\model\logic\HelperLogic;

class Update extends Base
{
    # [validation-rule]
    protected $rule = [
        "level" => "number|max:11",
        "cost" => "float|egt:0|max:11",
        "mining_rate" => "float|egt:0|max:11",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "level",
        "cost",
        "mining_rate",
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
                $res = SettingLevelModel::where("id", $targetId)->update($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "update", "setting_level", $targetId);
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
        if (!empty($params["level"])) {
            if (SettingLevelModel::where("level", $params["level"])
                ->whereNot("id", $params["id"])
                ->first()
            ) {
                $this->error[] = "level:exists";
            }
        }
    }
}