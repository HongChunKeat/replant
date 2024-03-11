<?php

namespace plugin\admin\app\middleware;

# system lib
use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;
# database & model
use app\model\logic\SettingLogic;

class LogReaderMiddleware implements MiddlewareInterface
{
    public function process(Request $request, callable $handler): Response
    {
        $proceed = false;

        if (SettingLogic::get("general", ["category" => "log_reader", "code" => "allow_access", "value" => 1])) {
            $proceed = true;
        }

        return ($proceed)
            ? $handler($request)
            : redirect("401.html");
    }
}
