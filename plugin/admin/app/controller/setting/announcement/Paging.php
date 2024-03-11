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

class Paging extends Base
{
    # [validation-rule]
    protected $rule = [
        "size" => "require|number",
        "page" => "require|number",
        "id" => "number|max:11",
        "code" => "",
        "lang" => "number|max:11",
        "type" => "number|max:11",
        "title" => "max:200",
        "content" => "max:1000",
        "is_show" => "in:1,0",
        "is_default" => "in:1,0",
        "remark" => "",
        "created_at_start" => "date",
        "created_at_end" => "date",
        "updated_at_start" => "date",
        "updated_at_end" => "date",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "id",
        "code",
        "lang",
        "type",
        "title",
        "content",
        "is_show",
        "is_default",
        "remark"
    ];

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

    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->get(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->get(), $this->patternInputs);

        # [proceed]
        if (!count($this->error)) {

            # [search date range]
            $cleanVars = array_merge(
                $cleanVars, 
                HelperLogic::buildDateSearch($request, ["created_at", "updated_at"])
            );

            # [paging query]
            $res = SettingAnnouncementModel::paging(
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
                    $lang = SettingLangModel::where("id", $row["lang"])->first();
                    $row["lang"] = $lang["code"];

                    $type = SettingOperatorModel::where("id", $row["type"])->first();
                    $row["type"] = $type["code"];

                    $row["is_show"] = $row["is_show"] ? "yes" : "no";
                    $row["is_default"] = $row["is_default"] ? "yes" : "no";
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
