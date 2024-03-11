<?php

namespace plugin\admin\app\controller\setting\announcement;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\SettingLangModel;
use app\model\database\SettingOperatorModel;
use app\model\database\SettingAnnouncementModel;
use app\model\logic\HelperLogic;

class Update extends Base
{
    # [validation-rule]
    protected $rule = [
        "code" => "",
        "lang" => "number|max:11",
        "type" => "",
        "title" => "max:200",
        "content" => "max:1000",
        "is_show" => "in:1,0",
        "is_default" => "in:1,0",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "code",
        "lang",
        "type",
        "title",
        "content",
        "is_show",
        "is_default",
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
                $res = SettingAnnouncementModel::where("id", $targetId)->update($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "update", "setting_announcement", $targetId);
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
        if (!empty($params["lang"])) {
            if (!SettingLangModel::where("id", $params["lang"])->first()) {
                $this->error[] = "lang:invalid";
            }
        }

        if (!empty($params["type"])) {
            if (!SettingOperatorModel::where(["category" => "announcement", "id" => $params["type"]])->first()) {
                $this->error[] = "type:invalid";
            }
        }
    }
}
