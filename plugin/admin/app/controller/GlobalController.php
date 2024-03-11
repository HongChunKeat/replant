<?php

namespace plugin\admin\app\controller;

# library
use plugin\admin\app\controller\Base;
use support\Redis;
use support\Request;
# database & logic
use app\model\logic\HelperLogic;

class GlobalController extends Base
{
    # [validation-rule]
    protected $rule = [
        "password" => "require",
        "key" => "max:150",
        "value" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "password",
        "key",
        "value",
    ];

    public function redisFlush(Request $request)
    {
        # [validation]
        $this->validation($request->post(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->post(), $this->patternInputs);

        # [checking]
        $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error)) {
            # [process]
            if (count($cleanVars) > 0) {
                //clear all key
                Redis::flushDB();
                
                # [result]
                $this->response = [
                    "success" => true,
                ];
            }
        }

        # [standard output]
        return $this->output();
    }

    public function redis(Request $request)
    {
        $this->rule["key"] .= "|require";

        # [validation]
        $this->validation($request->post(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->post(), $this->patternInputs);

        # [checking]
        $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error)) {
            # [process]
            if (count($cleanVars) > 0) {
                //set redis
                if(isset($cleanVars["value"])) {
                    Redis::set($cleanVars["key"], $cleanVars["value"]);
                }

                # [result]
                $this->response = [
                    "success" => true,
                    "data" => Redis::get($cleanVars["key"]),
                ];
            }
        }

        # [standard output]
        return $this->output();
    }

    private function checking(array $params = [])
    {
        # [condition]
        // check password
        if (isset($params["password"])) {
            if ($params["password"] != env("COMMON_PASSWORD")) {
                $this->error[] = "password:invalid";
            }
        }
    }
}
