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

class Create extends Base
{
    # [validation-rule]
    protected $rule = [
        "code" => "require",
        "lang" => "require|number|max:11",
        "type" => "require",
        "title" => "require|max:200",
        "content" => "require|max:1000",
        "is_show" => "require|in:1,0",
        "is_default" => "require|in:1,0",
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
                $res = SettingAnnouncementModel::create($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "create", "setting_announcement", $res["id"]);
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
        if (isset($params["lang"])) {
            if (!SettingLangModel::where("id", $params["lang"])->first()) {
                $this->error[] = "lang:invalid";
            }
        }

        if (isset($params["type"])) {
            if (!SettingOperatorModel::where(["category" => "announcement", "id" => $params["type"]])->first()) {
                $this->error[] = "type:invalid";
            }
        }
    }
}
