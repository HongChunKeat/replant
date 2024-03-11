<?php

namespace plugin\dapp\app\middleware;

# system lib
use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;
# database & model
use app\model\logic\SettingLogic;

class MaintenanceMiddleware implements MiddlewareInterface
{
    public function process(Request $request, callable $handler): Response
    {
        // check maintenance
        $stop_dapp = SettingLogic::get("general", ["category" => "maintenance", "code" => "stop_dapp", "value" => 1]);
        $stop_login = SettingLogic::get("general", ["category" => "maintenance", "code" => "stop_login", "value" => 1]);
        if ($stop_dapp || $stop_login) {
            return json([
                "success" => false,
                "data" => ["under_maintenance"],
            ]);
        } else {
            return $handler($request);
        }
    }
}
