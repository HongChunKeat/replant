<?php

namespace plugin\dapp\app\controller\setting;

# library
use plugin\dapp\app\controller\Base;
use support\Request;
# database & logic
use app\model\logic\HelperLogic;
use app\model\logic\SettingLogic;

class AnnouncementDetails extends Base
{
    # [validation-rule]
    protected $rule = [
        "id" => "require|number|max:11",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "id",
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "title",
        "content"
    ];

    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->post(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->post(), $this->patternInputs);

        # [proceed]
        if (!count($this->error)) {
            $res = "";

            $res = SettingLogic::get("announcement", [
                "id" => $cleanVars["id"],
                "is_show" => 1,
            ], true);

            # [result]
            if ($res) {
                $this->response = [
                    "success" => true,
                    "data" => HelperLogic::formatOutput($res, $this->patternOutputs, 1),
                ];
            }
        }

        # [standard output]
        return $this->output();
    }
}