<?php

namespace plugin\admin\app\middleware;

# system lib

use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;
# database & logic
use app\model\database\AdminPermissionModel;
use app\model\database\PermissionTemplateModel;
use app\model\database\PermissionWarehouseModel;
use app\model\logic\HelperLogic;

//   - PERMISSION: 从 JWT 的信息内获得 用户资讯
//   - PERMISSION: 用户是否拥有执行权限
class PermissionControlMiddleware implements MiddlewareInterface
{
    protected $ignore = "*";

    public function process(Request $request, callable $handler): Response
    {
        $proceed = false;
        $route = $request->route;

        if (isset($request->visitor->id)) {
            $pathMethod = HelperLogic::buildActionCode($route->getPath(), $route->getMethods()[0]);
            $getPath = PermissionWarehouseModel::where("from_site", "admin")
                ->where("code", $pathMethod)
                ->first();
            
            $role = AdminPermissionModel::where("admin_uid", $request->visitor->id)->first();
            if($role) {
                $getPermission = PermissionTemplateModel::where("id", $role["role"])->first();

                if ($getPath && $getPermission && 
                    (in_array($pathMethod, json_decode($getPermission->rule)) || 
                    in_array($this->ignore, json_decode($getPermission->rule)))
                ) {
                    $proceed = true;
                }
            }
        }

        // proceed to onion core
        return $proceed
            ? $handler($request)
            : json([
                "success" => false,
                "data" => "903",
                "msg" => "no_permission",
            ]);
    }
}
