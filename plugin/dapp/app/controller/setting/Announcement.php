<?php

namespace plugin\dapp\app\controller\setting;

# library
use plugin\dapp\app\controller\Base;
use support\Request;
# database & logic
use app\model\logic\HelperLogic;
use app\model\logic\SettingLogic;

class Announcement extends Base
{
    # [validation-rule]
    protected $rule = [
        "lang" => "require",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "lang",
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "title",
    ];

    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->get(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->get(), $this->patternInputs);

        # [proceed]
        if (!count($this->error)) {
            $res = "";

            if(isset($cleanVars["lang"])){
                $lang = SettingLogic::get("lang", ["value" => $cleanVars["lang"]]);
                $cleanVars["lang"] = $lang["id"] ?? 0;
            }

            $res = SettingLogic::get("announcement", [
                "lang" => $cleanVars["lang"],
                "is_show" => 1,
            ], true);

            if(!$res) {
                $res = SettingLogic::get("announcement", [
                    "is_default" => 1,
                    "is_show" => 1,
                ], true);
            }

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