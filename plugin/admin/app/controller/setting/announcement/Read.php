<?php

namespace plugin\admin\app\controller\setting\announcement;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\SettingOperatorModel;
use app\model\database\SettingLangModel;
use app\model\database\SettingAnnouncementModel;
use app\model\logic\HelperLogic;

class Read extends Base
{
    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "created_at",
        "updated_at",
        "code",
        "lang",
        "type",
        "title",
        "content",
        "is_show",
        "is_default",
    ];

    public function index(Request $request, int $targetId = 0)
    {
        $res = SettingAnnouncementModel::where("id", $targetId)->first();

        # [result]
        if ($res) {
            $lang = SettingLangModel::where("id", $res["lang"])->first();
            $res["lang"] = $lang["code"];

            $type = SettingOperatorModel::where("id", $res["type"])->first();
            $res["type"] = $type["code"];

            $res["is_show"] = $res["is_show"] ? "yes" : "no";
            $res["is_default"] = $res["is_default"] ? "yes" : "no";

            $this->response = [
                "success" => true,
                "data" => HelperLogic::formatOutput($res, $this->patternOutputs),
            ];
        }

        # [standard output]
        return $this->output();
    }
}