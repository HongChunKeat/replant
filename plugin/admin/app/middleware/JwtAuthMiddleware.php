<?php

namespace plugin\admin\app\middleware;

# system lib
use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;
# external lib
use Tinywan\Jwt\JwtToken;
# database & model
use app\model\database\AccountAdminModel;
use plugin\admin\app\model\logic\AdminProfileLogic;

class JwtAuthMiddleware implements MiddlewareInterface
{
    protected $name = "Authorization";
    protected $pattern = "/Bearer\s+(.*)$/i";

    public function process(Request $request, callable $handler): Response
    {
        $proceed = false;
        $accessToken = "";

        // check header if contain token
        $authorization = $request->header($this->name);
        if ($authorization && preg_match($this->pattern, $authorization, $matches)) {
            $accessToken = $matches[1];
        }

        // check if token extension is correct and valid
        try {
            $jwtInfo = JwtToken::getExtend();

            if ($jwtInfo && isset($jwtInfo["id"])) {
                $validToken = AdminProfileLogic::getAccessToken($jwtInfo["id"]);
                $availableTime = JwtToken::getTokenExp();

                if ($accessToken && $validToken === $accessToken && $availableTime > 0) {
                    $request->visitor = AccountAdminModel::where("admin_id", $jwtInfo["id"])->first();

                    $proceed = true;
                }
            }
        } catch (\Exception $e) {
            // no authorization header
        }

        // proceed to onion core
        return ($proceed)
            ? $handler($request)
            : json([
                "success" => false,
                "data" => "901",
                "msg" => "jwt_error",
            ]);
    }
}
