<?php

namespace plugin\dapp\app\controller\inventoryInfo;

# library
use plugin\dapp\app\controller\Base;
use support\Request;
# database & logic
use app\model\logic\HelperLogic;
use app\model\logic\SettingLogic;

class Inventory extends Base
{
    # [validation-rule]
    protected $rule = [
        "name" => "require",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "name",
    ];

    protected $patternOutputs = [
        "image",
        "name",
        "description",
        "category",
        "effect",
    ];

    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->get(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->get(), $this->patternInputs);

        # [proceed]
        if (!count($this->error)) {
            # [paging query]
            $res = SettingLogic::get("item", ["name" => $cleanVars["name"]]);

            # [result]
            if ($res) {
                $res["effect"] = HelperLogic::buildAttribute("item_attribute", ["item_id" => $res["id"]]);

                $this->response = [
                    "success" => true,
                    "data" => HelperLogic::formatOutput($res, $this->patternOutputs),
                ];
            }
        }

        # [standard output]
        return $this->output();
    }
}